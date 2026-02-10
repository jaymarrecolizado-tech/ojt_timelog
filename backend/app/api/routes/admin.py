from datetime import date
from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func
from ...core.database import get_db
from ...models.models import User, Student, TimeLog, SystemSetting, Holiday, Location
from ...schemas import StudentUpdate, SystemSettingUpdate, HolidayCreate, LocationCreate
from ...utils.time_calc import is_late
from .auth import require_role
from ...models.models import LogOverride, ActivityLog

router = APIRouter()


@router.get("/dashboard/live")
async def get_live_dashboard(
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(func.count()).select_from(Student))
    total_students = result.scalar() or 0

    today = date.today()
    result = await db.execute(
        select(func.count(func.distinct(TimeLog.student_id))).where(
            TimeLog.date == today
        )
    )
    present_today = result.scalar() or 0

    result = await db.execute(
        select(SystemSetting).where(SystemSetting.setting_key == "grace_period_minutes")
    )
    grace_setting = result.scalar_one_or_none()
    grace_minutes = int(grace_setting.setting_value) if grace_setting else 15

    result = await db.execute(
        select(SystemSetting).where(SystemSetting.setting_key == "schedule_am_start")
    )
    schedule_setting = result.scalar_one_or_none()
    schedule_start = schedule_setting.setting_value if schedule_setting else "08:00"

    result = await db.execute(
        select(TimeLog).where(
            TimeLog.date == today,
            TimeLog.log_type == "IN",
            TimeLog.log_category == "AM",
        )
    )
    am_ins = result.scalars().all()

    late_count = 0
    for log in am_ins:
        if is_late(log.timestamp, schedule_start, grace_minutes):
            late_count += 1

    return {
        "success": True,
        "data": {
            "total_students": total_students,
            "present_today": present_today,
            "absent_today": max(0, total_students - present_today),
            "late_today": late_count,
        },
    }


@router.get("/students")
async def list_students(
    search: str = None,
    status: str = None,
    page: int = 1,
    limit: int = 20,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    query = select(Student)

    if search:
        query = query.where(
            (Student.first_name.contains(search))
            | (Student.last_name.contains(search))
            | (Student.student_id_no.contains(search))
        )

    if status:
        query = query.where(Student.status == status)

    query = query.offset((page - 1) * limit).limit(limit)
    result = await db.execute(query)
    students = result.scalars().all()

    return {
        "success": True,
        "data": {
            "students": [
                {
                    "id": str(s.id),
                    "student_id_no": s.student_id_no,
                    "full_name": f"{s.first_name} {s.last_name}",
                    "department": s.department,
                    "company": s.company,
                    "status": s.status,
                }
                for s in students
            ],
            "page": page,
            "limit": limit,
        },
    }


@router.get("/students/{student_id}")
async def get_student(
    student_id: str,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Student).where(Student.id == student_id))
    student = result.scalar_one_or_none()

    if not student:
        raise HTTPException(status_code=404, detail="Student not found")

    return {
        "success": True,
        "data": {
            "id": str(student.id),
            "student_id_no": student.student_id_no,
            "first_name": student.first_name,
            "middle_name": student.middle_name,
            "last_name": student.last_name,
            "department": student.department,
            "program": student.program,
            "company": student.company,
            "ojt_start": student.ojt_start,
            "ojt_end": student.ojt_end,
            "required_hours": float(student.required_hours),
            "status": student.status,
        },
    }


@router.put("/students/{student_id}")
async def update_student(
    student_id: str,
    data: StudentUpdate,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Student).where(Student.id == student_id))
    student = result.scalar_one_or_none()

    if not student:
        raise HTTPException(status_code=404, detail="Student not found")

    update_data = data.model_dump(exclude_unset=True)
    for key, value in update_data.items():
        setattr(student, key, value)

    await db.commit()

    return {"success": True, "message": "Student updated"}


@router.get("/settings")
async def get_settings(
    user: User = Depends(require_role(["super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(SystemSetting))
    settings = result.scalars().all()

    return {"success": True, "data": {s.setting_key: s.setting_value for s in settings}}


@router.put("/settings/{key}")
async def update_setting(
    key: str,
    data: SystemSettingUpdate,
    user: User = Depends(require_role(["super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(
        select(SystemSetting).where(SystemSetting.setting_key == key)
    )
    setting = result.scalar_one_or_none()

    if not setting:
        raise HTTPException(status_code=404, detail="Setting not found")

    setting.setting_value = data.value
    setting.updated_by = user.id
    await db.commit()

    return {"success": True, "message": "Setting updated"}


@router.post("/holidays")
async def create_holiday(
    data: HolidayCreate,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    holiday = Holiday(
        date=data.date,
        name=data.name,
        type=data.type,
        is_recurring=data.is_recurring,
        created_by=user.id,
    )
    db.add(holiday)
    await db.commit()

    return {
        "success": True,
        "message": "Holiday created",
        "data": {"id": str(holiday.id)},
    }


@router.delete("/holidays/{holiday_id}")
async def delete_holiday(
    holiday_id: str,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Holiday).where(Holiday.id == holiday_id))
    holiday = result.scalar_one_or_none()

    if not holiday:
        raise HTTPException(status_code=404, detail="Holiday not found")

    await db.delete(holiday)
    await db.commit()

    return {"success": True, "message": "Holiday deleted"}


@router.get("/locations")
async def list_locations(
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Location))
    locations = result.scalars().all()

    return {
        "success": True,
        "data": [
            {
                "id": str(loc.id),
                "name": loc.name,
                "description": loc.description,
                "is_active": loc.is_active,
            }
            for loc in locations
        ],
    }


@router.post("/locations")
async def create_location(
    data: LocationCreate,
    user: User = Depends(require_role(["super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    import secrets

    location = Location(
        name=data.name,
        description=data.description,
        latitude=data.latitude,
        longitude=data.longitude,
        radius_meters=data.radius_meters,
        secret_key=secrets.token_hex(32),
    )
    db.add(location)
    await db.commit()

    return {
        "success": True,
        "message": "Location created",
        "data": {"id": str(location.id)},
    }


@router.get("/holidays")
async def list_holidays(
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Holiday).order_by(Holiday.date))
    holidays = result.scalars().all()

    return {
        "success": True,
        "data": [
            {
                "id": str(h.id),
                "date": h.date.isoformat(),
                "name": h.name,
                "type": h.type,
                "is_recurring": h.is_recurring,
            }
            for h in holidays
        ],
    }


@router.get("/dashboard/clocked-in")
async def get_clocked_in_students(
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    today = date.today()

    result = await db.execute(
        select(TimeLog)
        .where(TimeLog.date == today, TimeLog.log_type == "IN")
        .order_by(TimeLog.timestamp.desc())
    )
    all_ins = result.scalars().all()

    result = await db.execute(
        select(TimeLog).where(TimeLog.date == today, TimeLog.log_type == "OUT")
    )
    all_outs = result.scalars().all()

    out_students = {log.student_id for log in all_outs}

    clocked_in = []
    seen = set()
    for log in all_ins:
        if log.student_id in out_students or log.student_id in seen:
            continue
        seen.add(log.student_id)

        result = await db.execute(select(Student).where(Student.id == log.student_id))
        student = result.scalar_one_or_none()
        if student:
            clocked_in.append(
                {
                    "student_id": str(student.id),
                    "student_id_no": student.student_id_no,
                    "name": f"{student.first_name} {student.last_name}",
                    "department": student.department,
                    "clocked_in_at": log.timestamp.strftime("%I:%M %p"),
                    "category": log.log_category,
                }
            )

    return {
        "success": True,
        "data": {
            "count": len(clocked_in),
            "students": clocked_in,
        },
    }


@router.get("/audit-trail")
async def get_audit_trail(
    page: int = 1,
    limit: int = 50,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(
        select(LogOverride)
        .order_by(LogOverride.created_at.desc())
        .offset((page - 1) * limit)
        .limit(limit)
    )
    overrides = result.scalars().all()

    audit_entries = []
    for o in overrides:
        student_result = await db.execute(
            select(Student).where(Student.id == o.student_id)
        )
        student = student_result.scalar_one_or_none()

        admin_result = await db.execute(select(User).where(User.id == o.admin_id))
        admin = admin_result.scalar_one_or_none()

        audit_entries.append(
            {
                "id": str(o.id),
                "action": o.action,
                "student_name": f"{student.first_name} {student.last_name}"
                if student
                else "Unknown",
                "admin_email": admin.email if admin else "Unknown",
                "old_values": o.old_values,
                "new_values": o.new_values,
                "reason": o.reason,
                "created_at": o.created_at.isoformat(),
            }
        )

    return {
        "success": True,
        "data": {
            "entries": audit_entries,
            "page": page,
            "limit": limit,
        },
    }


@router.post("/users/guard")
async def create_guard(
    email: str,
    password: str,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    from ...core.security import get_password_hash

    result = await db.execute(select(User).where(User.email == email))
    if result.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Email already registered")

    guard = User(
        email=email,
        password_hash=get_password_hash(password),
        role="guard",
    )
    db.add(guard)
    await db.commit()
    await db.refresh(guard)

    return {
        "success": True,
        "message": "Guard account created",
        "data": {"id": str(guard.id), "email": guard.email},
    }


@router.get("/users")
async def list_users(
    role: str = None,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    query = select(User)
    if role:
        query = query.where(User.role == role)

    result = await db.execute(query)
    users = result.scalars().all()

    return {
        "success": True,
        "data": [
            {
                "id": str(u.id),
                "email": u.email,
                "role": u.role,
                "is_active": u.is_active,
                "created_at": u.created_at.isoformat(),
            }
            for u in users
        ],
    }


@router.patch("/users/{user_id}/deactivate")
async def deactivate_user(
    user_id: str,
    current_user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    if user_id == current_user.id:
        raise HTTPException(status_code=400, detail="Cannot deactivate yourself")

    result = await db.execute(select(User).where(User.id == user_id))
    target_user = result.scalar_one_or_none()

    if not target_user:
        raise HTTPException(status_code=404, detail="User not found")

    target_user.is_active = False
    await db.commit()

    return {"success": True, "message": "User deactivated"}
