# OJT Time Log Management System

A web-based time log management system for tracking OJT student attendance via QR code scanning.

## Tech Stack

- **Backend**: Laravel 10 (PHP)
- **Frontend**: Blade Templates + Vite
- **Database**: MySQL / SQLite
- **Styling**: TailwindCSS

## Features

- **QR Code Clock-In/Out**: Rotating QR codes (30s) for anti-fraud
- **Student Dashboard**: View logs, progress, and generate DTR
- **Guard Interface**: Display rotating QR codes
- **Admin Dashboard**: Live attendance, student management, reports
- **DTR Generation**: Monthly reports with hours calculation
- **Security**: Rate limiting, account lockout, HMAC-signed QR tokens

## Project Structure

```
ojt_timelog/
├── app/
│   ├── Http/Controllers/   # Controllers
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic (QR, PDF, etc.)
│   └── Middleware/         # Custom middleware
├── bootstrap/
├── config/
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Seeders
├── public/
├── resources/
│   ├── views/              # Blade templates
│   ├── js/                 # JavaScript
│   └── css/                # CSS
├── routes/
│   └── web.php             # Web routes
├── storage/
├── tests/
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

## Quick Start

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 8.0+ (or SQLite for development)

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env with your database credentials
```

### 3. Setup Database

```bash
# Run migrations
php artisan migrate

# (Optional) Seed with test data
php artisan db:seed
```

### 4. Run Development Server

```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite for assets
npm run dev
```

### 5. Access the Application

- **Application**: http://localhost:8000
- **Admin Login**: Configure in `.env` or create via seeder

## Default Login (after seeding)

```
Email: admin@ojt-tlms.local
Password: Admin@123
```

## API Routes

### Auth
- `GET /login` - Login page
- `POST /login` - Login
- `GET /register` - Registration page
- `POST /register` - Register new student
- `POST /logout` - Logout

### QR
- `GET /qr/current` - Get current QR code (Guard/Admin)
- `POST /qr/scan` - Validate scanned QR (Student)

### Logs
- `GET /student/logs` - Student's logs
- `POST /logs/manual` - Manual entry (Admin)
- `PUT /logs/{id}/override` - Admin override

### Reports
- `GET /admin/reports/dtr` - DTR data
- `GET /admin/reports/progress` - Progress report

### Admin
- `GET /admin/dashboard` - Dashboard stats
- `GET /admin/students` - List students
- `PUT /admin/settings/{key}` - Update settings

## Testing

```bash
# Run tests
php artisan test

# Or with PHPUnit
./vendor/bin/phpunit
```

## Deployment

See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for detailed deployment instructions.

## License

MIT
