from datetime import date
from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func
from ...core.database import get_db
from ...models.models import User, Student, TimeLog, SystemSetting, Holiday, Location
from ...schemas import StudentUpdate, SystemSettingUpdate, HolidayCreate, LocationCreate
from .auth import require_role

router = APIRouter()


@router.get("/dashboard/live")
async def get_live_dashboard(
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(func.count()).select_from(Student))
    total_students = result.scalar()

    today = date.today()
    result = await db.execute(
        select(func.count(func.distinct(TimeLog.student_id))).where(
            TimeLog.date == today
        )
    )
    present_today = result.scalar()

    return {
        "success": True,
        "data": {
            "total_students": total_students,
            "present_today": present_today,
            "absent_today": total_students - present_today,
            "late_today": 0,
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
