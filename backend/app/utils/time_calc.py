from datetime import datetime, date, timedelta
from typing import Optional


def calculate_hours_for_day(logs: list) -> tuple[float, dict]:
    day_data = {"am_in": None, "am_out": None, "pm_in": None, "pm_out": None}
    am_in_time = None
    am_out_time = None
    pm_in_time = None
    pm_out_time = None

    for log in logs:
        time_str = log.timestamp.strftime("%I:%M").lstrip("0")
        log_category = (
            log.log_category
            if hasattr(log.log_category, "value")
            else str(log.log_category)
        )
        log_type = log.log_type if hasattr(log.log_type, "value") else str(log.log_type)

        if log_category == "AM" or log_category == "AM":
            if log_type == "IN" or log_type == "IN":
                day_data["am_in"] = time_str
                am_in_time = log.timestamp
            else:
                day_data["am_out"] = time_str
                am_out_time = log.timestamp
        else:
            if log_type == "IN" or log_type == "IN":
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


def get_day_status(day_data: dict, day_date: date, holidays: list = None) -> str:
    if holidays:
        for h in holidays:
            h_date = (
                h.date if isinstance(h.date, date) else date.fromisoformat(str(h.date))
            )
            if h_date == day_date:
                return "HOLIDAY"

    if any(day_data.values()):
        am_complete = day_data["am_in"] and day_data["am_out"]
        pm_complete = day_data["pm_in"] and day_data["pm_out"]
        if am_complete and pm_complete:
            return "COMPLETE"
        return "INCOMPLETE"

    if day_date.weekday() == 5:
        return "SATURDAY"
    if day_date.weekday() == 6:
        return "SUNDAY"

    return "ABSENT"


def is_late(timestamp: datetime, schedule_start: str, grace_minutes: int = 15) -> bool:
    try:
        hour, minute = map(int, schedule_start.split(":"))
        schedule_time = timestamp.replace(
            hour=hour, minute=minute, second=0, microsecond=0
        )
        grace_period = timedelta(minutes=grace_minutes)
        return timestamp > (schedule_time + grace_period)
    except:
        return False
