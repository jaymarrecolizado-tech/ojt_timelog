from datetime import date, timedelta, datetime
from fastapi import APIRouter, Depends, Query
from fastapi.responses import Response
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from calendar import monthrange
from ...core.database import get_db
from ...models.models import User, Student, TimeLog
from ...services.pdf_service import dtr_generator
from .auth import get_current_user, require_role

router = APIRouter()


def calculate_hours_for_day(logs_for_date: list) -> tuple[float, dict]:
    """Calculate hours for a single day based on time logs."""
    day_data = {"am_in": None, "am_out": None, "pm_in": None, "pm_out": None}
    am_in_time = None
    am_out_time = None
    pm_in_time = None
    pm_out_time = None

    for log in logs_for_date:
        time_str = log.timestamp.strftime("%I:%M").lstrip("0")
        if log.log_category == "AM":
            if log.log_type == "IN":
                day_data["am_in"] = time_str
                am_in_time = log.timestamp
            else:
                day_data["am_out"] = time_str
                am_out_time = log.timestamp
        else:
            if log.log_type == "IN":
                day_data["pm_in"] = time_str
                pm_in_time = log.timestamp
            else:
                day_data["pm_out"] = time_str
                pm_out_time = log.timestamp

    hours = 0.0
    if am_in_time and am_out_time:
        diff = (am_out_time - am_in_time).total_seconds() / 3600
        hours += max(0, min(diff, 4))
    if pm_in_time and pm_out_time:
        diff = (pm_out_time - pm_in_time).total_seconds() / 3600
        hours += max(0, min(diff, 4))

    return round(hours, 2), day_data


@router.get("/dtr", response_model=dict)
async def get_dtr(
    student_id: str | None = None,
    month: int = Query(...),
    year: int = Query(...),
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    if user.role == "student":
        result = await db.execute(select(Student).where(Student.user_id == user.id))
        student = result.scalar_one_or_none()
        if not student:
            return {"success": False, "error": "Student not found"}
        target_student_id = student.id
        student_record = student
    else:
        if not student_id:
            return {"success": False, "error": "student_id required"}
        result = await db.execute(select(Student).where(Student.id == student_id))
        student_record = result.scalar_one_or_none()
        target_student_id = student_id

    if not student_record:
        return {"success": False, "error": "Student not found"}

    _, days_in_month = monthrange(year, month)
    from_date = date(year, month, 1)
    to_date = date(year, month, days_in_month)

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

    rows = []
    total_hours = 0.0
    current = from_date

    while current <= to_date:
        day_name = current.strftime("%A")
        day_logs = logs_by_date.get(current, [])

        if day_logs:
            hours, day_data = calculate_hours_for_day(day_logs)
            if hours >= 7.5:
                remarks = ""
            else:
                remarks = "INCOMPLETE"
        else:
            hours = 0.0
            day_data = {"am_in": "", "am_out": "", "pm_in": "", "pm_out": ""}
            if current.weekday() == 5:
                remarks = "SATURDAY"
            elif current.weekday() == 6:
                remarks = "SUNDAY"
            else:
                remarks = "ABSENT"

        hours_str = f"{hours:g} hrs" if hours > 0 else ""
        total_hours += hours

        rows.append(
            {
                "date": current.strftime("%B %d, %Y"),
                "day": day_name,
                "am_in": day_data["am_in"] or "",
                "am_out": day_data["am_out"] or "",
                "pm_in": day_data["pm_in"] or "",
                "pm_out": day_data["pm_out"] or "",
                "hours_rendered": hours_str,
                "remarks": remarks,
            }
        )
        current += timedelta(days=1)

    middle_initial = (
        f" {student_record.middle_name[0]}." if student_record.middle_name else ""
    )
    full_name = f"{student_record.last_name.upper()}, {student_record.first_name}{middle_initial}"
    ojt_period = ""
    if student_record.ojt_start and student_record.ojt_end:
        ojt_period = f"{student_record.ojt_start.strftime('%B %d, %Y')} - {student_record.ojt_end.strftime('%B %d, %Y')}"

    return {
        "success": True,
        "data": {
            "student": {
                "full_name": full_name,
                "student_id_no": student_record.student_id_no,
                "department": student_record.department,
                "program": student_record.program,
                "company": student_record.company or "",
                "ojt_period": ojt_period,
            },
            "period": f"{date(year, month, 1).strftime('%B')} {year}",
            "rows": rows,
            "totals": {
                "monthly_hours": round(total_hours, 1),
                "accumulated_hours": round(total_hours, 1),
                "required_hours": float(student_record.required_hours),
                "remaining_hours": round(
                    float(student_record.required_hours) - total_hours, 1
                ),
                "completion_percentage": round(
                    (total_hours / float(student_record.required_hours)) * 100, 1
                )
                if student_record.required_hours
                else 0,
            },
        },
    }


@router.get("/dtr/pdf")
async def get_dtr_pdf(
    student_id: str | None = None,
    month: int = Query(...),
    year: int = Query(...),
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    dtr_response = await get_dtr(student_id, month, year, user, db)

    if not dtr_response.get("success"):
        return Response(
            content=dtr_response.get("error", "Error generating DTR"), status_code=400
        )

    dtr_data = dtr_response["data"]

    pdf_bytes = dtr_generator.generate_dtr_pdf(
        student_info=dtr_data["student"],
        period=dtr_data["period"],
        rows=dtr_data["rows"],
        totals=dtr_data["totals"],
    )

    filename = f"DTR_{dtr_data['student']['student_id_no']}_{month}_{year}.pdf"

    return Response(
        content=pdf_bytes,
        media_type="application/pdf",
        headers={"Content-Disposition": f'attachment; filename="{filename}"'},
    )


@router.get("/summary", response_model=dict)
async def get_summary(
    month: int = Query(...),
    year: int = Query(...),
    department: str = "all",
    user: User = Depends(require_role(["admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Student))
    students = result.scalars().all()

    student_list = []
    for s in students:
        if department != "all" and s.department != department:
            continue

        result = await db.execute(
            select(TimeLog)
            .where(
                TimeLog.student_id == s.id,
                TimeLog.date >= date(year, month, 1),
                TimeLog.date <= date(year, month, 28),
            )
            .order_by(TimeLog.date, TimeLog.timestamp)
        )
        logs = result.scalars().all()

        logs_by_date = {}
        for log in logs:
            if log.date not in logs_by_date:
                logs_by_date[log.date] = []
            logs_by_date[log.date].append(log)

        monthly_hours = 0.0
        for day_logs in logs_by_date.values():
            hours, _ = calculate_hours_for_day(day_logs)
            monthly_hours += hours

        student_list.append(
            {
                "student_id_no": s.student_id_no,
                "name": f"{s.last_name}, {s.first_name}",
                "department": s.department,
                "monthly_hours": round(monthly_hours, 1),
                "accumulated_hours": round(monthly_hours, 1),
                "required_hours": float(s.required_hours),
                "completion": f"{round((monthly_hours / float(s.required_hours)) * 100, 1)}%"
                if s.required_hours
                else "0%",
                "days_present": len(logs_by_date),
                "days_absent": 0,
                "status": "ON_TRACK" if monthly_hours >= 80 else "BEHIND",
            }
        )

    return {
        "success": True,
        "data": {
            "period": f"{date(year, month, 1).strftime('%B')} {year}",
            "students": student_list,
        },
    }
