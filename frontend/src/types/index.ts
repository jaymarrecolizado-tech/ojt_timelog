export interface User {
  id: string
  email: string
  role: 'student' | 'guard' | 'admin' | 'super_admin'
  student?: Student
}

export interface Student {
  id: string
  student_id_no: string
  first_name: string
  middle_name?: string
  last_name: string
  department: string
  program: string
  company?: string
  status: string
}

export interface TimeLog {
  id: string
  log_type: 'IN' | 'OUT'
  log_category: 'AM' | 'PM'
  timestamp: string
  formatted_time?: string
}

export interface TodaySummary {
  date: string
  logs: TimeLog[]
  hours_today: number
  scans_remaining: number
  current_status: string
}

export interface QRValidationResponse {
  success: boolean
  log_type?: 'IN' | 'OUT'
  log_category?: 'AM' | 'PM'
  formatted_time?: string
  message: string
  today_summary?: TodaySummary
}

export interface DTRRow {
  date: string
  day: string
  am_in: string
  am_out: string
  pm_in: string
  pm_out: string
  hours_rendered: string
  remarks: string
}

export interface DTRData {
  student: {
    full_name: string
    student_id_no: string
    department: string
    program: string
    company?: string
    ojt_period: string
  }
  period: string
  rows: DTRRow[]
  totals: {
    monthly_hours: number
    accumulated_hours: number
    required_hours: number
    remaining_hours: number
    completion_percentage: number
  }
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  email: string
  password: string
  student_id_no: string
  first_name: string
  middle_name?: string
  last_name: string
  department: string
  program: string
  contact_no?: string
}

export interface AdminDashboardStats {
  total_students: number
  present_today: number
  absent_today: number
  late_today: number
}

export interface StudentSummary {
  student_id_no: string
  name: string
  department: string
  monthly_hours: number
  accumulated_hours: number
  required_hours: number
  completion: string
  days_present: number
  days_absent: number
  status: string
}

export interface ReportSummary {
  period: string
  students: StudentSummary[]
}

export interface SystemSettings {
  qr_rotation_seconds: string
  max_scans_per_day: string
  grace_period_minutes: string
  schedule_am_start: string
  schedule_am_end: string
  schedule_pm_start: string
  schedule_pm_end: string
  geolocation_required: string
  geolocation_max_distance: string
  scan_debounce_seconds: string
}

export interface Holiday {
  id: string
  date: string
  name: string
  type: string
  is_recurring: boolean
}

export interface Location {
  id: string
  name: string
  description?: string
  is_active: boolean
}

export interface StudentDetail {
  id: string
  student_id_no: string
  first_name: string
  middle_name?: string
  last_name: string
  department: string
  program: string
  company?: string
  ojt_start?: string
  ojt_end?: string
  required_hours: number
  status: string
}

export interface ManualEntry {
  log_type: 'IN' | 'OUT'
  log_category: 'AM' | 'PM'
  time: string
}

export interface LogOverride {
  action: string
  new_values: Record<string, unknown>
  reason: string
}
