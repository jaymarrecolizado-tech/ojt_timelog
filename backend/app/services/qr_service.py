import hashlib
import hmac
import time
from datetime import datetime, timedelta
from typing import Optional
import qrcode
from io import BytesIO
import base64
from ..core.config import get_settings

settings = get_settings()


class QRGenerator:
    def __init__(
        self, secret_key: str | None = None, rotation_seconds: int | None = None
    ):
        self.secret_key = secret_key if secret_key else settings.QR_SECRET_KEY
        self.rotation_seconds = (
            rotation_seconds if rotation_seconds else settings.QR_ROTATION_SECONDS
        )

    def get_time_window(self, timestamp: Optional[float] = None) -> int:
        if timestamp is None:
            timestamp = time.time()
        return int(timestamp // self.rotation_seconds)

    def generate_token(
        self, location_id: str, timestamp: Optional[float] = None
    ) -> str:
        window = self.get_time_window(timestamp)
        message = f"{location_id}:{window}"
        signature = hmac.new(
            self.secret_key.encode(), message.encode(), hashlib.sha256
        ).hexdigest()
        return f"{location_id}.{window}.{signature[:16]}"

    def validate_token(
        self, token: str, location_id: str
    ) -> tuple[bool, Optional[int]]:
        try:
            parts = token.split(".")
            if len(parts) != 3:
                return False, None

            token_location_id, window_str, signature = parts
            if token_location_id != location_id:
                return False, None

            current_window = self.get_time_window()
            token_window = int(window_str)

            if abs(current_window - token_window) > 1:
                return False, token_window

            expected_message = f"{location_id}:{token_window}"
            expected_signature = hmac.new(
                self.secret_key.encode(), expected_message.encode(), hashlib.sha256
            ).hexdigest()[:16]

            if not hmac.compare_digest(signature, expected_signature):
                return False, token_window

            return True, token_window
        except (ValueError, IndexError):
            return False, None

    def generate_qr_image(self, token: str) -> str:
        import qrcode.constants

        qr = qrcode.QRCode(
            version=1,
            error_correction=qrcode.constants.ERROR_CORRECT_L,
            box_size=10,
            border=2,
        )
        qr.add_data(token)
        qr.make(fit=True)

        img = qr.make_image(fill_color="black", back_color="white")
        buffer = BytesIO()
        img.save(buffer, "PNG")
        buffer.seek(0)

        return base64.b64encode(buffer.read()).decode()

    def get_seconds_remaining(self) -> int:
        current_time = time.time()
        elapsed_in_window = current_time % self.rotation_seconds
        return self.rotation_seconds - int(elapsed_in_window)


qr_generator = QRGenerator()
