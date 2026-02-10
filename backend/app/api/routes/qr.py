from datetime import datetime, date
import math
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from ...core.database import get_db
from ...models.models import User, Student, TimeLog, Location, SystemSetting
from ...schemas import QRTokenPayload, QRValidationResponse, QRDisplayResponse
from ...services.qr_service import qr_generator
from ...utils.hash import hash_token
from .auth import get_current_user, require_role

router = APIRouter()


def calculate_distance(lat1: float, lon1: float, lat2: float, lon2: float) -> float:
    R = 6371000
    phi1 = math.radians(lat1)
    phi2 = math.radians(lat2)
    delta_phi = math.radians(lat2 - lat1)
    delta_lambda = math.radians(lon2 - lon1)

    a = (
        math.sin(delta_phi / 2) ** 2
        + math.cos(phi1) * math.cos(phi2) * math.sin(delta_lambda / 2) ** 2
    )
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))

    return R * c


@router.get("/current", response_model=dict)
async def get_current_qr(
    user: User = Depends(require_role(["guard", "admin", "super_admin"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(
        select(Location).where(Location.is_active == True).limit(1)
    )
    location = result.scalar_one_or_none()

    if not location:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND, detail="No active location found"
        )

    token = qr_generator.generate_token(location.id)
    qr_image = qr_generator.generate_qr_image(token)
    seconds_remaining = qr_generator.get_seconds_remaining()
    valid_until = datetime.utcnow() + __import__("datetime").timedelta(
        seconds=seconds_remaining
    )

    return {
        "success": True,
        "data": {
            "qr_data": qr_image,
            "qr_payload": token,
            "valid_until": valid_until.isoformat(),
            "seconds_remaining": seconds_remaining,
            "location": {"id": location.id, "name": location.name},
        },
    }


@router.post("/validate", response_model=dict)
async def validate_qr(
    payload: QRTokenPayload,
    user: User = Depends(require_role(["student"])),
    db: AsyncSession = Depends(get_db),
):
    result = await db.execute(select(Student).where(Student.user_id == user.id))
    student = result.scalar_one_or_none()

    if not student:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND, detail="Student profile not found"
        )

    result = await db.execute(
        select(Location).where(Location.is_active == True).limit(1)
    )
    location = result.scalar_one_or_none()

    if not location:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND, detail="No active location"
        )

    is_valid, window = qr_generator.validate_token(payload.token, location.id)

    if not is_valid:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Invalid or expired QR code"
        )

    result = await db.execute(
        select(SystemSetting).where(SystemSetting.setting_key == "geolocation_required")
    )
    geolocation_setting = result.scalar_one_or_none()
    geolocation_required = (
        geolocation_setting and geolocation_setting.setting_value.lower() == "true"
    )

    if geolocation_required and payload.latitude and payload.longitude:
        if location.latitude and location.longitude:
            result = await db.execute(
                select(SystemSetting).where(
                    SystemSetting.setting_key == "geolocation_max_distance"
                )
            )
            distance_setting = result.scalar_one_or_none()
            max_distance = (
                int(distance_setting.setting_value) if distance_setting else 200
            )

            distance = calculate_distance(
                float(payload.latitude),
                float(payload.longitude),
                float(location.latitude),
                float(location.longitude),
            )

            if distance > max_distance:
                raise HTTPException(
                    status_code=status.HTTP_400_BAD_REQUEST,
                    detail=f"You are too far from the OJT site ({int(distance)}m away)",
                )

    today = date.today()
    now = datetime.utcnow()

    result = await db.execute(
        select(TimeLog)
        .where(TimeLog.student_id == student.id, TimeLog.date == today)
        .order_by(TimeLog.timestamp)
    )
    today_logs = result.scalars().all()

    result = await db.execute(
        select(SystemSetting).where(SystemSetting.setting_key == "max_scans_per_day")
    )
    max_scans_setting = result.scalar_one_or_none()
    max_scans = int(max_scans_setting.setting_value) if max_scans_setting else 4

    if len(today_logs) >= max_scans:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Maximum scans for today reached",
        )

    result = await db.execute(
        select(SystemSetting).where(
            SystemSetting.setting_key == "scan_debounce_seconds"
        )
    )
    debounce_setting = result.scalar_one_or_none()
    debounce_seconds = int(debounce_setting.setting_value) if debounce_setting else 60

    if today_logs:
        last_scan = today_logs[-1]
        seconds_since_last = (now - last_scan.timestamp).total_seconds()
        if seconds_since_last < debounce_seconds:
            raise HTTPException(
                status_code=status.HTTP_429_TOO_MANY_REQUESTS,
                detail=f"Please wait {int(debounce_seconds - seconds_since_last)} seconds before scanning again",
            )

    scan_count = len(today_logs)
    if scan_count == 0:
        log_type = "IN"
        log_category = "AM"
    elif scan_count == 1:
        log_type = "OUT"
        log_category = "AM"
    elif scan_count == 2:
        log_type = "IN"
        log_category = "PM"
    else:
        log_type = "OUT"
        log_category = "PM"

    time_log = TimeLog(
        student_id=student.id,
        log_type=log_type,
        log_category=log_category,
        timestamp=now,
        date=today,
        qr_token_hash=hash_token(payload.token),
        location_id=location.id,
        latitude=payload.latitude,
        longitude=payload.longitude,
    )
    db.add(time_log)
    await db.commit()
    await db.refresh(time_log)

    formatted_time = now.strftime("%I:%M %p")

    from ...main import broadcast_clock_event

    await broadcast_clock_event(
        f"{student.first_name} {student.last_name}",
        log_type,
        log_category,
        formatted_time,
    )

    greeting = "Good morning" if log_category == "AM" else "Good afternoon"
    message = f"{greeting}, {student.first_name}! You've clocked {log_type.lower()}."

    status_text = {
        0: "Not clocked in",
        1: "Clocked In (AM)",
        2: "Clocked Out (AM)",
        3: "Clocked In (PM)",
        4: "Done for the day",
    }

    return {
        "success": True,
        "data": {
            "log_type": log_type,
            "log_category": log_category,
            "timestamp": now.isoformat(),
            "formatted_time": formatted_time,
            "message": message,
            "today_summary": {
                "date": today.isoformat(),
                "logs": [
                    {
                        "id": str(log.id),
                        "log_type": log.log_type,
                        "log_category": log.log_category,
                        "timestamp": log.timestamp.isoformat(),
                    }
                    for log in today_logs
                ]
                + [
                    {
                        "id": str(time_log.id),
                        "log_type": time_log.log_type,
                        "log_category": time_log.log_category,
                        "timestamp": time_log.timestamp.isoformat(),
                    }
                ],
                "hours_today": 0,
                "scans_remaining": max_scans - scan_count - 1,
                "current_status": status_text[scan_count + 1],
            },
        },
    }
