import logging
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from typing import Optional
from pydantic import EmailStr

logger = logging.getLogger(__name__)


class EmailService:
    def __init__(
        self,
        smtp_host: str = None,
        smtp_port: int = 587,
        smtp_user: str = None,
        smtp_password: str = None,
        email_from: str = None,
    ):
        self.smtp_host = smtp_host
        self.smtp_port = smtp_port
        self.smtp_user = smtp_user
        self.smtp_password = smtp_password
        self.email_from = email_from
        self.enabled = all([smtp_host, smtp_user, smtp_password])

    async def send_password_reset_email(
        self, to_email: EmailStr, reset_token: str, reset_url: str
    ) -> bool:
        if not self.enabled:
            logger.info(
                f"Email not configured. Reset token for {to_email}: {reset_token}"
            )
            return True

        subject = "Password Reset - OJT Time Log System"
        body = f"""
        You requested a password reset for your OJT Time Log System account.
        
        Click the following link to reset your password:
        {reset_url}?token={reset_token}
        
        This link will expire in 1 hour.
        
        If you did not request this reset, please ignore this email.
        """

        return await self._send_email(to_email, subject, body)

    async def send_verification_email(
        self, to_email: EmailStr, verification_token: str, verification_url: str
    ) -> bool:
        if not self.enabled:
            logger.info(
                f"Email not configured. Verification token for {to_email}: {verification_token}"
            )
            return True

        subject = "Verify Your Email - OJT Time Log System"
        body = f"""
        Welcome to the OJT Time Log System!
        
        Please verify your email address by clicking the following link:
        {verification_url}?token={verification_token}
        
        This link will expire in 24 hours.
        """

        return await self._send_email(to_email, subject, body)

    async def _send_email(self, to_email: EmailStr, subject: str, body: str) -> bool:
        try:
            msg = MIMEMultipart()
            msg["From"] = self.email_from
            msg["To"] = to_email
            msg["Subject"] = subject

            msg.attach(MIMEText(body, "plain"))

            with smtplib.SMTP(self.smtp_host, self.smtp_port) as server:
                server.starttls()
                server.login(self.smtp_user, self.smtp_password)
                server.send_message(msg)

            logger.info(f"Email sent successfully to {to_email}")
            return True

        except Exception as e:
            logger.error(f"Failed to send email to {to_email}: {e}")
            return False


email_service = EmailService()
