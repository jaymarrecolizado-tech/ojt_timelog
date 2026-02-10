import pytest
from app.services.qr_service import QRGenerator
from app.utils.time_calc import calculate_hours_for_day, get_day_status
from datetime import datetime, date
from unittest.mock import MagicMock


class TestQRService:
    def test_generate_token(self):
        qr = QRGenerator(secret_key="test-secret-key", rotation_seconds=30)
        token = qr.generate_token("location-123")
        assert token is not None
        parts = token.split(".")
        assert len(parts) == 3
        assert parts[0] == "location-123"

    def test_validate_token_valid(self):
        qr = QRGenerator(secret_key="test-secret-key", rotation_seconds=30)
        token = qr.generate_token("location-123")
        is_valid, window = qr.validate_token(token, "location-123")
        assert is_valid is True

    def test_validate_token_invalid_location(self):
        qr = QRGenerator(secret_key="test-secret-key", rotation_seconds=30)
        token = qr.generate_token("location-123")
        is_valid, window = qr.validate_token(token, "different-location")
        assert is_valid is False

    def test_validate_token_malformed(self):
        qr = QRGenerator(secret_key="test-secret-key", rotation_seconds=30)
        is_valid, window = qr.validate_token("invalid-token", "location-123")
        assert is_valid is False

    def test_get_seconds_remaining(self):
        qr = QRGenerator(secret_key="test-secret-key", rotation_seconds=30)
        remaining = qr.get_seconds_remaining()
        assert 0 < remaining <= 30

    def test_generate_qr_image(self):
        qr = QRGenerator(secret_key="test-secret-key", rotation_seconds=30)
        token = qr.generate_token("location-123")
        image = qr.generate_qr_image(token)
        assert image is not None
        assert isinstance(image, str)


class TestTimeCalc:
    def test_calculate_hours_complete_day(self):
        logs = [
            MagicMock(
                timestamp=datetime(2024, 1, 1, 8, 0),
                log_type="IN",
                log_category="AM",
            ),
            MagicMock(
                timestamp=datetime(2024, 1, 1, 12, 0),
                log_type="OUT",
                log_category="AM",
            ),
            MagicMock(
                timestamp=datetime(2024, 1, 1, 13, 0),
                log_type="IN",
                log_category="PM",
            ),
            MagicMock(
                timestamp=datetime(2024, 1, 1, 17, 0),
                log_type="OUT",
                log_category="PM",
            ),
        ]
        hours, day_data = calculate_hours_for_day(logs)
        assert hours == 8.0

    def test_calculate_hours_partial_day(self):
        logs = [
            MagicMock(
                timestamp=datetime(2024, 1, 1, 8, 0),
                log_type="IN",
                log_category="AM",
            ),
            MagicMock(
                timestamp=datetime(2024, 1, 1, 12, 0),
                log_type="OUT",
                log_category="AM",
            ),
        ]
        hours, day_data = calculate_hours_for_day(logs)
        assert hours == 4.0

    def test_calculate_hours_empty_logs(self):
        hours, day_data = calculate_hours_for_day([])
        assert hours == 0.0

    def test_get_day_status_saturday(self):
        day_data = {"am_in": None, "am_out": None, "pm_in": None, "pm_out": None}
        saturday = date(2024, 1, 6)
        status = get_day_status(day_data, saturday, [])
        assert status == "SATURDAY"

    def test_get_day_status_sunday(self):
        day_data = {"am_in": None, "am_out": None, "pm_in": None, "pm_out": None}
        sunday = date(2024, 1, 7)
        status = get_day_status(day_data, sunday, [])
        assert status == "SUNDAY"

    def test_get_day_status_holiday(self):
        day_data = {"am_in": None, "am_out": None, "pm_in": None, "pm_out": None}
        holiday_date = date(2024, 1, 1)
        holiday = MagicMock(date=holiday_date, name="New Year")
        status = get_day_status(day_data, holiday_date, [holiday])
        assert status == "HOLIDAY"

    def test_get_day_status_absent(self):
        day_data = {"am_in": None, "am_out": None, "pm_in": None, "pm_out": None}
        weekday = date(2024, 1, 3)
        status = get_day_status(day_data, weekday, [])
        assert status == "ABSENT"
