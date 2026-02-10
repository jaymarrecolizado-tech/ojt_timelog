from datetime import datetime, date, timedelta
from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_
from ...core.database import get_db
from ...models.models import User, Student, TimeLog, LogOverride
from ...schemas import LogOverrideRequest, ManualEntryRequest
from ...utils.time_calc import calculate_hours_for_day
from .auth import get_current_user, require_role

router = APIRouter()


@router.get("/today", response_model=dict)
async def get_today_logs(
    student_id: str = None,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    if user.role == "student":
        result = await db.execute(select(Student).where(Student.user_id == user.id))
        student = result.scalar_one_or_none()
        if not student:
            raise HTTPException(status_code=404, detail="Student not found")
        target_student_id = student.id
    else:
        if not student_id:
            raise HTTPException(status_code=400, detail="student_id required for admin")
        target_student_id = student_id

    today = date.today()

    result = await db.execute(
        select(TimeLog)
        .where(TimeLog.student_id == target_student_id, TimeLog.date == today)
        .order_by(TimeLog.timestamp)
    )
    logs = list(result.scalars().all())

    hours_today, _ = calculate_hours_for_day(logs)

    status_map = {
        0: "Not clocked in",
        1: "Clocked In (AM)",
        2: "Clocked Out (AM)",
        3: "Clocked In (PM)",
        4: "Done for the day",
    }

    return {
        "success": True,
        "data": {
            "date": today.isoformat(),
            "logs": [
                {
                    "id": str(log.id),
                    "log_type": log.log_type,
                    "log_category": log.log_category,
                    "timestamp": log.timestamp.isoformat(),
                    "formatted_time": log.timestamp.strftime("%I:%M %p"),
                }
                for log in logs
            ],
            "hours_today": hours_today,
            "scans_remaining": 4 - len(logs),
            "current_status": status_map.get(len(logs), "Done for the day"),
        },
    }


@router.get("/range", response_model=dict)
async def get_logs_range(
    student_id: str = None,
    from_date: date = Query(...),
    to_date: date = Query(...),
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    if user.role == "student":
        result = await db.execute(select(Student).where(Student.user_id == user.id))
        student = result.scalar_one_or_none()
        if not student:
            raise HTTPException(status_code=404, detail="Student not found")
        target_student_id = student.id
        student_record = student
    else:
        if not student_id:
            raise HTTPException(status_code=400, detail="student_id required")
        result = await db.execute(select(Student).where(Student.id == student_id))
        student_record = result.scalar_one_or_none()
        target_student_id = student_id

    result = await db.execute(
        select(TimeLog)
        .where(
            TimeLog.student_id == target_student_id,
            TimeLog.date >= from_date,
            TimeLog.date <= to_date,
        )
        .order_by(TimeLog.date, TimeLog.timestamp)
    )
    logs = result.scalars().all()

    logs_by_date = {}
    for log in logs:
        if log.date not in logs_by_date:
            logs_by_date[log.date] = []
        logs_by_date[log.date].append(log)

    days = []
    current = from_date
    while current <= to_date:
        day_logs = logs_by_date.get(current, [])
        day_name = current.strftime("%A")

        if day_logs:
            hours, day_data = calculate_hours_for_day(day_logs)
            status = "COMPLETE" if hours >= 7.5 else "INCOMPLETE"
        else:
            hours = 0
            day_data = {"am_in": None, "am_out": None, "pm_in": None, "pm_out": None}
            if current.weekday() == 5:
                status = "SATURDAY"
            elif current.weekday() == 6:
                status = "SUNDAY"
            else:
                status = "ABSENT"

        days.append(
            {
                "date": current.strftime("%B %d, %Y"),
                "day_name": day_name,
                "am_in": day_data["am_in"],
                "am_out": day_data["am_out"],
                "pm_in": day_data["pm_in"],
                "pm_out": day_data["pm_out"],
                "hours": hours,
                "status": status,
            }
        )
        current += timedelta(days=1)

    return {
        "success": True,
        "data": {
            "student": {
                "id": str(student_record.id),
                "student_id_no": student_record.student_id_no,
                "full_name": f"{student_record.last_name}, {student_record.first_name}",
            },
            "from": from_date.isoformat(),
            "to": to_date.isoformat(),
            "days": days,
            "summary": {
                "total_hours": sum(d["hours"] for d in days),
                "days_present": sum(
                    1 for d in days if d["status"] in ["COMPLETE", "INCOMPLETE"]
                ),
                "days_absent": sum(1 for d in days if d["status"] == "ABSENT"),
                "days_incomplete": sum(1 for d in days if d["status"] == "INCOMPLETE"),
            },
        },
    }


@router.put("/{log_id}/override")
async def override_log(
    log_id: str,
    data: LogOverrideRequest,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(TimeLog).where(TimeLog.id == log_id))
    time_log = result.scalar_one_or_none()

    if not time_log:
        raise HTTPException(status_code=404, detail="Log not found")

    override = LogOverride(
        time_log_id=time_log.id,
        student_id=time_log.student_id,
        admin_id=user.id,
        action=data.action,
        old_values={"timestamp": time_log.timestamp.isoformat()},
        new_values=data.new_values,
        reason=data.reason,
    )

    if "timestamp" in data.new_values:
        time_log.timestamp = datetime.fromisoformat(data.new_values["timestamp"])
    if "log_type" in data.new_values:
        time_log.log_type = data.new_values["log_type"]
    if "log_category" in data.new_values:
        time_log.log_category = data.new_values["log_category"]
    time_log.is_manual = True

    db.add(override)
    await db.commit()

    return {
        "success": True,
        "message": "Log entry updated. Override recorded in audit trail.",
        "data": {"override_id": str(override.id)},
    }


@router.post("/manual")
async def create_manual_entry(
    data: ManualEntryRequest,
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Student).where(Student.id == data.student_id))
    student = result.scalar_one_or_none()

    if not student:
        raise HTTPException(status_code=404, detail="Student not found")

    created_logs = []
    for entry in data.entries:
        time_str = entry.get("time", "08:00")
        hour, minute = map(int, time_str.split(":"))
        timestamp = datetime.combine(
            data.date, datetime.min.time().replace(hour=hour, minute=minute)
        )

        log = TimeLog(
            student_id=student.id,
            log_type=entry.get("log_type", "IN"),
            log_category=entry.get("log_category", "AM"),
            timestamp=timestamp,
            date=data.date,
            is_manual=True,
        )
        db.add(log)
        created_logs.append(log)

    override = LogOverride(
        student_id=student.id,
        admin_id=user.id,
        action="CREATE",
        new_values={"entries": data.entries, "date": data.date.isoformat()},
        reason=data.reason,
    )
    db.add(override)

    await db.commit()

    return {
        "success": True,
        "message": f"Created {len(created_logs)} log entries",
        "data": {"override_id": str(override.id)},
    }
