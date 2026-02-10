# OJT Time Log Management System

A web-based time log management system for tracking OJT student attendance via QR code scanning.

## Tech Stack

- **Backend**: FastAPI (Python)
- **Frontend**: React + TypeScript + Vite
- **Database**: MySQL
- **Styling**: TailwindCSS

## Project Structure

```
ojt_timelog/
├── backend/                 # FastAPI backend
│   ├── app/
│   │   ├── api/routes/      # API endpoints
│   │   ├── models/          # SQLAlchemy models
│   │   ├── schemas/         # Pydantic schemas
│   │   ├── services/        # Business logic (QR, etc.)
│   │   ├── core/            # Config, database, security
│   │   └── utils/           # Utility functions
│   ├── migrations/          # SQL migration scripts
│   └── requirements.txt     # Python dependencies
│
├── frontend/                # React frontend
│   ├── src/
│   │   ├── pages/           # Page components
│   │   ├── hooks/           # Custom hooks
│   │   ├── lib/             # API client
│   │   ├── types/           # TypeScript types
│   │   └── utils/           # Utilities
│   └── package.json         # Node dependencies
│
└── spec.txt                 # Full specification document
```

## Quick Start

### Prerequisites

- Python 3.10+
- Node.js 18+
- MySQL 8.0+

### 1. Database Setup

```bash
# Create MySQL database
mysql -u root -p < backend/migrations/001_init_schema.sql
```

### 2. Backend Setup

```bash
cd backend

# Create virtual environment
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Copy and configure environment
cp .env.example .env
# Edit .env with your database credentials

# Run development server
uvicorn app.main:app --reload --port 8000
```

### 3. Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Run development server
npm run dev
```

### 4. Access the Application

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **API Docs**: http://localhost:8000/api/docs

## Default Login

```
Email: admin@ojt-tlms.local
Password: Admin@123
```

## Features

- **QR Code Clock-In/Out**: Rotating QR codes (30s) for anti-fraud
- **Student Dashboard**: View logs, progress, and generate DTR
- **Guard Interface**: Display rotating QR codes
- **Admin Dashboard**: Live attendance, student management, reports
- **DTR Generation**: Monthly reports with hours calculation

## API Endpoints

### Auth
- `POST /api/auth/register/student` - Register new student
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### QR
- `GET /api/qr/current` - Get current QR code (Guard/Admin)
- `POST /api/qr/validate` - Validate scanned QR (Student)

### Logs
- `GET /api/logs/today` - Today's logs
- `GET /api/logs/range` - Logs by date range
- `PUT /api/logs/{id}/override` - Admin override
- `POST /api/logs/manual` - Manual entry

### Reports
- `GET /api/reports/dtr` - DTR data
- `GET /api/reports/summary` - Summary report

### Admin
- `GET /api/admin/dashboard/live` - Dashboard stats
- `GET /api/admin/students` - List students
- `PUT /api/admin/settings/{key}` - Update settings
