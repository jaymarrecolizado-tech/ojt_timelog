from datetime import datetime, date
from typing import Optional
from pydantic import BaseModel, EmailStr, Field, field_validator
from ..models.models import UserRole, StudentStatus, LogType, LogCategory


# ============================================
# User Schemas
# ============================================
class UserBase(BaseModel):
    email: EmailStr


class UserCreate(UserBase):
    password: str = Field(..., min_length=8)
    role: UserRole = UserRole.STUDENT

    @field_validator("password")
    @classmethod
    def password_strength(cls, v):
        if not any(c.isupper() for c in v):
            raise ValueError("Password must contain at least one uppercase letter")
        if not any(c.isdigit() for c in v):
            raise ValueError("Password must contain at least one number")
        return v


class UserLogin(BaseModel):
    email: EmailStr
    password: str


class UserResponse(UserBase):
    id: str
    role: UserRole
    is_active: bool
    email_verified: bool
    created_at: datetime

    model_config = {"from_attributes": True}


class TokenResponse(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    expires_in: int


# ============================================
# Student Schemas
# ============================================
class StudentBase(BaseModel):
    student_id_no: str = Field(..., min_length=3, max_length=20)
    first_name: str = Field(..., min_length=1, max_length=100)
    middle_name: Optional[str] = Field(None, max_length=100)
    last_name: str = Field(..., min_length=1, max_length=100)
    suffix: Optional[str] = Field(None, max_length=10)
    department: str = Field(..., min_length=1, max_length=150)
    program: str = Field(..., min_length=1, max_length=150)
    contact_no: Optional[str] = Field(None, max_length=20)


class StudentCreate(StudentBase):
    email: EmailStr
    password: str = Field(..., min_length=8)

    @field_validator("password")
    @classmethod
    def password_strength(cls, v):
        if not any(c.isupper() for c in v):
            raise ValueError("Password must contain at least one uppercase letter")
        if not any(c.isdigit() for c in v):
            raise ValueError("Password must contain at least one number")
        return v


class StudentUpdate(BaseModel):
    first_name: Optional[str] = Field(None, max_length=100)
    middle_name: Optional[str] = Field(None, max_length=100)
    last_name: Optional[str] = Field(None, max_length=100)
    suffix: Optional[str] = Field(None, max_length=10)
    department: Optional[str] = Field(None, max_length=150)
    program: Optional[str] = Field(None, max_length=150)
    company: Optional[str] = Field(None, max_length=200)
    company_address: Optional[str] = Field(None, max_length=300)
    supervisor_name: Optional[str] = Field(None, max_length=200)
    ojt_start: Optional[date] = None
    ojt_end: Optional[date] = None
    required_hours: Optional[float] = Field(None, gt=0)
    contact_no: Optional[str] = Field(None, max_length=20)
    status: Optional[StudentStatus] = None


class StudentResponse(StudentBase):
    id: str
    user_id: str
    company: Optional[str]
    company_address: Optional[str]
    supervisor_name: Optional[str]
    ojt_start: Optional[date]
    ojt_end: Optional[date]
    required_hours: float
    status: StudentStatus
    created_at: datetime
    user: Optional[UserResponse] = None

    model_config = {"from_attributes": True}


class StudentSummary(BaseModel):
    id: str
    student_id_no: str
    full_name: str
    department: str
    company: Optional[str]
    status: StudentStatus
    total_hours: float
    required_hours: float
    completion_percentage: float

    model_config = {"from_attributes": True}


# ============================================
# Time Log Schemas
# ============================================
class TimeLogBase(BaseModel):
    log_type: LogType
    log_category: LogCategory
    timestamp: datetime
    date: date


class TimeLogCreate(TimeLogBase):
    pass


class TimeLogResponse(TimeLogBase):
    id: str
    student_id: str
    qr_token_hash: Optional[str]
    location_id: Optional[str]
    latitude: Optional[float]
    longitude: Optional[float]
    is_manual: bool
    is_flagged: bool
    flag_reason: Optional[str]
    created_at: datetime

    model_config = {"from_attributes": True}


class TimeLogWithStudent(TimeLogResponse):
    student: Optional[StudentResponse] = None


class DailyLogSummary(BaseModel):
    date: date
    day_name: str
    am_in: Optional[str]
    am_out: Optional[str]
    pm_in: Optional[str]
    pm_out: Optional[str]
    hours: float
    status: str


class TodayLogResponse(BaseModel):
    date: date
    logs: list[TimeLogResponse]
    hours_today: float
    scans_remaining: int
    current_status: str


# ============================================
# QR Code Schemas
# ============================================
class QRTokenPayload(BaseModel):
    token: str
    latitude: Optional[float] = None
    longitude: Optional[float] = None


class QRValidationResponse(BaseModel):
    success: bool
    log_type: Optional[LogType] = None
    log_category: Optional[LogCategory] = None
    timestamp: Optional[datetime] = None
    formatted_time: Optional[str] = None
    message: str
    today_summary: Optional[TodayLogResponse] = None


class QRDisplayResponse(BaseModel):
    qr_data: str
    qr_payload: str
    valid_until: datetime
    seconds_remaining: int
    location: dict


# ============================================
# DTR Report Schemas
# ============================================
class DTRRow(BaseModel):
    date: str
    day: str
    am_in: Optional[str]
    am_out: Optional[str]
    pm_in: Optional[str]
    pm_out: Optional[str]
    hours_rendered: str
    remarks: str


class DTRStudentInfo(BaseModel):
    full_name: str
    student_id_no: str
    department: str
    program: str
    company: Optional[str]
    ojt_period: str


class DTRTotals(BaseModel):
    monthly_hours: float
    accumulated_hours: float
    required_hours: float
    remaining_hours: float
    completion_percentage: float


class DTRResponse(BaseModel):
    student: DTRStudentInfo
    period: str
    rows: list[DTRRow]
    totals: DTRTotals


# ============================================
# Admin Schemas
# ============================================
class AdminDashboardStats(BaseModel):
    total_students: int
    present_today: int
    absent_today: int
    late_today: int


class LogOverrideRequest(BaseModel):
    action: str
    new_values: dict
    reason: str = Field(..., min_length=5)


class ManualEntryRequest(BaseModel):
    student_id: str
    date: date
    entries: list[dict]
    reason: str = Field(..., min_length=5)


class SystemSettingUpdate(BaseModel):
    value: str


class HolidayCreate(BaseModel):
    date: date
    name: str = Field(..., min_length=1, max_length=200)
    type: str = "regular"
    is_recurring: bool = False


class LocationCreate(BaseModel):
    name: str = Field(..., min_length=1, max_length=100)
    description: Optional[str] = Field(None, max_length=300)
    latitude: Optional[float] = None
    longitude: Optional[float] = None
    radius_meters: int = 100


# ============================================
# Generic Response
# ============================================
class SuccessResponse(BaseModel):
    success: bool = True
    message: str


class ErrorResponse(BaseModel):
    error: str
    detail: Optional[str] = None
