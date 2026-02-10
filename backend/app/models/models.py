from datetime import datetime, date
from uuid import uuid4
from sqlalchemy import (
    Column,
    String,
    Boolean,
    DateTime,
    Date,
    Enum,
    Text,
    DECIMAL,
    Integer,
    JSON,
    ForeignKey,
)
from sqlalchemy.orm import relationship
from ..core.database import Base
import enum


class UserRole(str, enum.Enum):
    STUDENT = "student"
    GUARD = "guard"
    ADMIN = "admin"
    SUPER_ADMIN = "super_admin"


class StudentStatus(str, enum.Enum):
    PENDING = "pending"
    ACTIVE = "active"
    COMPLETED = "completed"
    INACTIVE = "inactive"


class LogType(str, enum.Enum):
    IN = "IN"
    OUT = "OUT"


class LogCategory(str, enum.Enum):
    AM = "AM"
    PM = "PM"


class OverrideAction(str, enum.Enum):
    CREATE = "CREATE"
    UPDATE = "UPDATE"
    DELETE = "DELETE"


class HolidayType(str, enum.Enum):
    REGULAR = "regular"
    SPECIAL = "special"
    COMPANY = "company"


class SettingDataType(str, enum.Enum):
    STRING = "string"
    NUMBER = "number"
    BOOLEAN = "boolean"
    JSON = "json"


class User(Base):
    __tablename__ = "users"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    email = Column(String(255), unique=True, nullable=False, index=True)
    password_hash = Column(String(255), nullable=False)
    role = Column(String(20), nullable=False, default="student")
    is_active = Column(Boolean, nullable=False, default=True)
    email_verified = Column(Boolean, nullable=False, default=False)
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)
    updated_at = Column(
        DateTime, nullable=False, default=datetime.utcnow, onupdate=datetime.utcnow
    )

    student = relationship("Student", back_populates="user", uselist=False)
    refresh_tokens = relationship(
        "RefreshToken", back_populates="user", cascade="all, delete-orphan"
    )


class Student(Base):
    __tablename__ = "students"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    user_id = Column(
        String(36),
        ForeignKey("users.id", ondelete="CASCADE"),
        unique=True,
        nullable=False,
    )
    student_id_no = Column(String(20), unique=True, nullable=False)
    first_name = Column(String(100), nullable=False)
    middle_name = Column(String(100))
    last_name = Column(String(100), nullable=False)
    suffix = Column(String(10))
    department = Column(String(150), nullable=False)
    program = Column(String(150), nullable=False)
    company = Column(String(200))
    company_address = Column(String(300))
    supervisor_name = Column(String(200))
    ojt_start = Column(Date)
    ojt_end = Column(Date)
    required_hours = Column(DECIMAL(7, 2), nullable=False, default=500.00)
    contact_no = Column(String(20))
    status = Column(String(20), nullable=False, default="active")
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)
    updated_at = Column(
        DateTime, nullable=False, default=datetime.utcnow, onupdate=datetime.utcnow
    )

    user = relationship("User", back_populates="student")
    time_logs = relationship(
        "TimeLog", back_populates="student", cascade="all, delete-orphan"
    )


class Location(Base):
    __tablename__ = "locations"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    name = Column(String(100), nullable=False)
    description = Column(String(300))
    latitude = Column(DECIMAL(10, 8))
    longitude = Column(DECIMAL(11, 8))
    radius_meters = Column(Integer, default=100)
    is_active = Column(Boolean, nullable=False, default=True)
    secret_key = Column(String(128), nullable=False)
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)
    updated_at = Column(
        DateTime, nullable=False, default=datetime.utcnow, onupdate=datetime.utcnow
    )

    time_logs = relationship("TimeLog", back_populates="location")


class TimeLog(Base):
    __tablename__ = "time_logs"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    student_id = Column(
        String(36), ForeignKey("students.id", ondelete="CASCADE"), nullable=False
    )
    log_type = Column(Enum(LogType), nullable=False)
    log_category = Column(Enum(LogCategory), nullable=False)
    timestamp = Column(DateTime, nullable=False)
    date = Column(Date, nullable=False)
    qr_token_hash = Column(String(64))
    location_id = Column(String(36), ForeignKey("locations.id", ondelete="SET NULL"))
    latitude = Column(DECIMAL(10, 8))
    longitude = Column(DECIMAL(11, 8))
    device_info = Column(String(500))
    ip_address = Column(String(45))
    is_manual = Column(Boolean, nullable=False, default=False)
    is_flagged = Column(Boolean, nullable=False, default=False)
    flag_reason = Column(String(200))
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)

    student = relationship("Student", back_populates="time_logs")
    location = relationship("Location", back_populates="time_logs")
    overrides = relationship("LogOverride", back_populates="time_log")


class LogOverride(Base):
    __tablename__ = "log_overrides"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    time_log_id = Column(String(36), ForeignKey("time_logs.id", ondelete="SET NULL"))
    student_id = Column(
        String(36), ForeignKey("students.id", ondelete="CASCADE"), nullable=False
    )
    admin_id = Column(
        String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False
    )
    action = Column(Enum(OverrideAction), nullable=False)
    old_values = Column(JSON)
    new_values = Column(JSON)
    reason = Column(Text, nullable=False)
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)

    time_log = relationship("TimeLog", back_populates="overrides")


class Holiday(Base):
    __tablename__ = "holidays"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    date = Column(Date, nullable=False)
    name = Column(String(200), nullable=False)
    type = Column(Enum(HolidayType), default=HolidayType.REGULAR)
    is_recurring = Column(Boolean, nullable=False, default=False)
    created_by = Column(String(36), ForeignKey("users.id", ondelete="SET NULL"))
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)


class SystemSetting(Base):
    __tablename__ = "system_settings"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    setting_key = Column(String(100), unique=True, nullable=False)
    setting_value = Column(Text, nullable=False)
    data_type = Column(
        Enum(SettingDataType), nullable=False, default=SettingDataType.STRING
    )
    description = Column(String(300))
    updated_by = Column(String(36), ForeignKey("users.id", ondelete="SET NULL"))
    updated_at = Column(
        DateTime, nullable=False, default=datetime.utcnow, onupdate=datetime.utcnow
    )


class ActivityLog(Base):
    __tablename__ = "activity_logs"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    user_id = Column(String(36), ForeignKey("users.id", ondelete="SET NULL"))
    action = Column(String(100), nullable=False)
    entity_type = Column(String(50))
    entity_id = Column(String(36))
    details = Column(JSON)
    ip_address = Column(String(45))
    user_agent = Column(String(500))
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)


class RefreshToken(Base):
    __tablename__ = "refresh_tokens"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    user_id = Column(
        String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False
    )
    token_hash = Column(String(64), unique=True, nullable=False)
    expires_at = Column(DateTime, nullable=False)
    revoked = Column(Boolean, nullable=False, default=False)
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)

    user = relationship("User", back_populates="refresh_tokens")


class PasswordResetToken(Base):
    __tablename__ = "password_reset_tokens"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid4()))
    user_id = Column(
        String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False
    )
    token_hash = Column(String(64), unique=True, nullable=False)
    expires_at = Column(DateTime, nullable=False)
    used = Column(Boolean, nullable=False, default=False)
    created_at = Column(DateTime, nullable=False, default=datetime.utcnow)

    user = relationship("User")
