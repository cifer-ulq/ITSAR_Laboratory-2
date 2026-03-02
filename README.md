# Microservices Edge Case Testing - Laboratory 2

## System Architecture and Integration 2

This project implements three independent microservices that communicate via HTTP:

1. **Student Service** (Port 8001) - Manages student records
2. **Course Service** (Port 8002) - Manages course records
3. **Enrollment Service** (Port 8003) - Orchestrates enrollment by calling Student and Course services

---

## Prerequisites

- **XAMPP** installed (provides PHP 8.2+)
- **Composer** (included as `composer.phar` or installed globally)
- **curl** (built into Windows)

---

## Setup Instructions

### Step 1: Start XAMPP

Open XAMPP Control Panel. You do NOT need to start Apache or MySQL — we use PHP's built-in server and SQLite.

### Step 2: Add PHP to PATH (each terminal session)

```powershell
$env:Path = "C:\xampp\php;" + $env:Path
```

### Step 3: Run Database Migrations

Open a terminal and run for each service:

```powershell
# Student Service
cd student-service
php artisan migrate:fresh --force

# Course Service
cd ..\course-service
php artisan migrate:fresh --force

# Enrollment Service
cd ..\enrollment-service
php artisan migrate:fresh --force
```

### Step 4: Start All Three Services

Open **three separate terminals** and run one command in each:

**Terminal 1 - Student Service:**
```powershell
$env:Path = "C:\xampp\php;" + $env:Path
cd student-service
php artisan serve --port=8001
```

**Terminal 2 - Course Service:**
```powershell
$env:Path = "C:\xampp\php;" + $env:Path
cd course-service
php artisan serve --port=8002
```

**Terminal 3 - Enrollment Service:**
```powershell
$env:Path = "C:\xampp\php;" + $env:Path
cd enrollment-service
php artisan serve --port=8003
```

### Step 5: Verify Services Are Running

```powershell
curl.exe -i http://127.0.0.1:8001/api/students
curl.exe -i http://127.0.0.1:8002/api/courses
curl.exe -i http://127.0.0.1:8003/api/enrollments
```

All should return `HTTP/1.1 200 OK` with `[]` (empty arrays).

---

## API Endpoints

### Student Service (Port 8001)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/students` | List all students |
| GET | `/api/students/{id}` | Get a student by ID |
| POST | `/api/students` | Create a new student |

### Course Service (Port 8002)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/courses` | List all courses |
| GET | `/api/courses/{id}` | Get a course by ID |
| POST | `/api/courses` | Create a new course |

### Enrollment Service (Port 8003)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/enrollments` | List all enrollments |
| POST | `/api/enrollments` | Create a new enrollment |

---

## Edge Cases Handled

| Status Code | Scenario | Description |
|-------------|----------|-------------|
| 400 | Bad Request | Missing or empty required fields |
| 404 | Not Found | Resource does not exist |
| 409 | Conflict | Duplicate email, code, or enrollment |
| 503 | Service Unavailable | Dependency service is offline |
| 504 | Gateway Timeout | Dependency service is too slow |

---

## Project Structure

```
rasti/
├── README.md
├── student-service/          # Laravel - Port 8001
├── course-service/           # Laravel - Port 8002
├── enrollment-service/       # Laravel - Port 8003
├── tests/
│   └── curl-tests.md         # All curl test commands
└── docs/
    ├── report.md             # Architecture & edge case report
    └── evidence/             # Curl output text files
```

---

## Tech Stack

- **PHP 8.2** (via XAMPP)
- **Laravel 12** (PHP framework)
- **SQLite** (lightweight database, no MySQL needed)
- **curl** (CLI-based HTTP testing)
