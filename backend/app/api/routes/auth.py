from datetime import datetime, timedelta
from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from ...core.database import get_db
from ...core.security import (
    create_access_token,
    create_refresh_token,
    decode_token,
    verify_password,
    get_password_hash,
)
from ...models.models import User, Student, RefreshToken, PasswordResetToken
from ...schemas import UserCreate, UserLogin, UserResponse, TokenResponse, StudentCreate
from ...utils.hash import hash_token
import secrets
from pydantic import BaseModel


class ForgotPasswordRequest(BaseModel):
    email: str


class ResetPasswordRequest(BaseModel):
    token: str
    new_password: str


router = APIRouter()
security = HTTPBearer()


async def get_current_user(
    credentials: HTTPAuthorizationCredentials = Depends(security),
    db: AsyncSession = Depends(get_db),
) -> User:
    token = credentials.credentials
    payload = decode_token(token)

    if not payload or payload.get("type") != "access":
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid or expired token"
        )

    user_id = payload.get("sub")
    if not user_id:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid token payload"
        )

    result = await db.execute(select(User).where(User.id == user_id))
    user = result.scalar_one_or_none()

    if not user or not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="User not found or inactive",
        )

    return user


def require_role(roles: list[str]):
    async def role_checker(user: User = Depends(get_current_user)) -> User:
        if user.role not in roles:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, detail="Insufficient permissions"
            )
        return user

    return role_checker


@router.post("/register", response_model=dict)
async def register(user_data: UserCreate, db: AsyncSession = Depends(get_db)):
    result = await db.execute(select(User).where(User.email == user_data.email))
    if result.scalar_one_or_none():
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT, detail="Email already registered"
        )

    user = User(
        email=user_data.email,
        password_hash=get_password_hash(user_data.password),
        role=user_data.role,
    )
    db.add(user)
    await db.commit()
    await db.refresh(user)

    return {
        "success": True,
        "message": "Registration successful. Please verify your email.",
        "data": {"user_id": user.id, "email": user.email},
    }


@router.post("/register/student", response_model=dict)
async def register_student(data: StudentCreate, db: AsyncSession = Depends(get_db)):
    result = await db.execute(select(User).where(User.email == data.email))
    if result.scalar_one_or_none():
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT, detail="Email already registered"
        )

    result = await db.execute(
        select(Student).where(Student.student_id_no == data.student_id_no)
    )
    if result.scalar_one_or_none():
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT, detail="Student ID already registered"
        )

    user = User(
        email=data.email, password_hash=get_password_hash(data.password), role="student"
    )
    db.add(user)
    await db.flush()

    student = Student(
        user_id=user.id,
        student_id_no=data.student_id_no,
        first_name=data.first_name,
        middle_name=data.middle_name,
        last_name=data.last_name,
        suffix=data.suffix,
        department=data.department,
        program=data.program,
        contact_no=data.contact_no,
    )
    db.add(student)
    await db.commit()

    return {
        "success": True,
        "message": "Registration successful. Please verify your email.",
        "data": {"user_id": user.id, "email": user.email},
    }


@router.post("/login", response_model=dict)
async def login(credentials: UserLogin, db: AsyncSession = Depends(get_db)):
    result = await db.execute(select(User).where(User.email == credentials.email))
    user = result.scalar_one_or_none()

    if not user or not verify_password(credentials.password, user.password_hash):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid email or password"
        )

    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN, detail="Account is deactivated"
        )

    access_token = create_access_token({"sub": user.id, "role": user.role})
    refresh_token = create_refresh_token({"sub": user.id})

    rt = RefreshToken(
        user_id=user.id,
        token_hash=hash_token(refresh_token),
        expires_at=datetime.utcnow() + timedelta(days=7),
    )
    db.add(rt)
    await db.commit()

    response_data = {"id": user.id, "email": user.email, "role": user.role}

    if user.role == "student":
        result = await db.execute(select(Student).where(Student.user_id == user.id))
        student = result.scalar_one_or_none()
        if student:
            response_data["student"] = {
                "id": student.id,
                "student_id_no": student.student_id_no,
                "first_name": student.first_name,
                "last_name": student.last_name,
                "department": student.department,
                "program": student.program,
                "company": student.company,
                "status": student.status,
            }

    return {
        "success": True,
        "data": {
            "user": response_data,
            "session": {
                "access_token": access_token,
                "refresh_token": refresh_token,
                "expires_in": 3600,
            },
        },
    }


@router.post("/logout")
async def logout(
    user: User = Depends(get_current_user),
    credentials: HTTPAuthorizationCredentials = Depends(security),
    db: AsyncSession = Depends(get_db),
):
    await db.execute(select(RefreshToken).where(RefreshToken.user_id == user.id))
    tokens = (
        (await db.execute(select(RefreshToken).where(RefreshToken.user_id == user.id)))
        .scalars()
        .all()
    )

    for token in tokens:
        token.revoked = True

    await db.commit()

    return {"success": True, "message": "Logged out successfully"}


@router.get("/me", response_model=dict)
async def get_me(
    user: User = Depends(get_current_user), db: AsyncSession = Depends(get_db)
):
    response = {"id": user.id, "email": user.email, "role": user.role}

    if user.role == "student":
        result = await db.execute(select(Student).where(Student.user_id == user.id))
        student = result.scalar_one_or_none()
        if student:
            response["student"] = {
                "id": student.id,
                "student_id_no": student.student_id_no,
                "first_name": student.first_name,
                "last_name": student.last_name,
                "department": student.department,
                "program": student.program,
                "company": student.company,
                "status": student.status,
            }

    return {"success": True, "data": response}


@router.post("/forgot-password")
async def forgot_password(
    request: ForgotPasswordRequest,
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(User).where(User.email == request.email))
    user = result.scalar_one_or_none()

    if not user:
        return {
            "success": True,
            "message": "If the email exists, a reset link will be sent",
        }

    await db.execute(
        select(PasswordResetToken).where(PasswordResetToken.user_id == user.id)
    )
    existing_tokens = (
        (
            await db.execute(
                select(PasswordResetToken).where(PasswordResetToken.user_id == user.id)
            )
        )
        .scalars()
        .all()
    )
    for t in existing_tokens:
        t.used = True

    reset_token = secrets.token_urlsafe(32)
    reset_token_record = PasswordResetToken(
        user_id=user.id,
        token_hash=hash_token(reset_token),
        expires_at=datetime.utcnow() + timedelta(hours=1),
    )
    db.add(reset_token_record)
    await db.commit()

    return {
        "success": True,
        "message": "If the email exists, a reset link will be sent",
        "debug_token": reset_token,
    }


@router.post("/reset-password")
async def reset_password(
    request: ResetPasswordRequest,
    db: AsyncSession = Depends(get_db),
):
    token_hash = hash_token(request.token)

    result = await db.execute(
        select(PasswordResetToken).where(PasswordResetToken.token_hash == token_hash)
    )
    token_record = result.scalar_one_or_none()

    if (
        not token_record
        or token_record.used
        or token_record.expires_at < datetime.utcnow()
    ):
        raise HTTPException(status_code=400, detail="Invalid or expired token")

    result = await db.execute(select(User).where(User.id == token_record.user_id))
    user = result.scalar_one_or_none()

    if not user:
        raise HTTPException(status_code=400, detail="User not found")

    user.password_hash = get_password_hash(request.new_password)
    token_record.used = True
    await db.commit()

    return {"success": True, "message": "Password reset successfully"}


@router.post("/change-password")
async def change_password(
    current_password: str,
    new_password: str,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    if not verify_password(current_password, user.password_hash):
        raise HTTPException(status_code=400, detail="Current password is incorrect")

    user.password_hash = get_password_hash(new_password)
    await db.commit()

    return {"success": True, "message": "Password changed successfully"}
