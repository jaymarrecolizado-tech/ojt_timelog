# OJT Time Log Management System - Laravel Version

A Laravel + MySQL + Bootstrap implementation of the OJT Time Log Management System equivalent to the original FastAPI/React version.

## Features

- **Authentication & Authorization**: Login/Register with role-based access (Student, Guard, Admin, Super Admin)
- **Student Management**: Complete student profiles with OJT details
- **QR Code Time Logging**: Scan QR codes for clock in/out (AM/PM sessions)
- **Admin Dashboard**: Real-time monitoring, statistics, and reports
- **Time Log Tracking**: Automatic hours calculation and progress tracking
- **System Settings**: Configurable schedules, grace periods, and QR rotation

## Requirements

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Node.js & NPM (optional, for asset compilation)

## Installation

1. **Clone/Create the project**:
   ```bash
   cd newly
   composer install
   ```

2. **Environment Setup**:
   ```bash
   cp .env.example .env
   # Edit .env file with your database credentials
   ```

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Run Migrations and Seeders**:
   ```bash
   php artisan migrate
   ```

5. **Start the Development Server**:
   ```bash
   php artisan serve
   ```

6. **Access the Application**:
   - URL: http://localhost:8000
   - Default Admin: admin@ojt-tlms.test / Admin@123

## Database Schema

The system includes the following tables:
- `users` - User accounts with roles
- `students` - Student profiles
- `time_logs` - Clock in/out records
- `locations` - QR scan locations
- `log_overrides` - Admin audit trail
- `system_settings` - Configuration
- `holidays` - Holiday management
- `refresh_tokens` - Token management

## Default System Settings

- QR Rotation: 30 seconds
- Max Scans Per Day: 4
- Grace Period: 15 minutes
- AM Schedule: 08:00 - 12:00
- PM Schedule: 13:00 - 17:00
- Geolocation Max Distance: 200 meters

## Routes

### Public Routes
- `GET /login` - Login page
- `POST /login` - Authenticate
- `GET /register` - Registration page
- `POST /register` - Create account

### Student Routes (Requires Auth)
- `GET /student/dashboard` - Student dashboard
- `GET /student/logs` - View time logs
- `GET /student/scan` - Scan QR code
- `GET /student/profile` - View profile

### Admin Routes (Requires Auth)
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/students` - Student list
- `GET /admin/students/{id}` - Student details
- `GET /admin/reports` - Reports
- `GET /admin/settings` - System settings
- `GET /admin/locations` - Location management

### API Routes
- `POST /api/qr/validate` - Validate QR scan
- `GET /api/qr/generate` - Generate new QR token

## Project Structure

```
newly/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── StudentController.php
│   │       ├── AdminController.php
│   │       └── QRController.php
│   └── Models/
│       ├── User.php
│       ├── Student.php
│       ├── TimeLog.php
│       ├── Location.php
│       └── ...
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_users_table.php
│       └── ...
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── student/
│       │   └── dashboard.blade.php
│       └── admin/
│           └── dashboard.blade.php
├── routes/
│   └── web.php
├── composer.json
└── .env.example
```

## Key Differences from Original

| Feature | Original (FastAPI/React) | This Version (Laravel/Bootstrap) |
|---------|-------------------------|----------------------------------|
| Backend | FastAPI | Laravel 10 |
| Frontend | React + TypeScript | Blade + Bootstrap 5 |
| Database | MySQL (async SQLAlchemy) | MySQL (Eloquent ORM) |
| Auth | JWT Tokens | Laravel Sanctum + Sessions |
| API Style | REST API | Traditional MVC + API routes |
| Real-time | WebSocket | Server-Sent Events (optional) |

## Security Features

- CSRF Protection
- Rate Limiting (configurable)
- Password Hashing (Bcrypt)
- Role-based Access Control
- Audit Trail for Admin Actions

## Next Steps for Production

1. Configure proper email settings in `.env`
2. Set up queue workers for background jobs
3. Configure caching (Redis/Memcached)
4. Set up SSL/TLS certificates
5. Configure backup strategies
6. Optimize database indexes
7. Add API rate limiting middleware
8. Implement WebSocket for real-time updates

## License

This project is for educational purposes.
