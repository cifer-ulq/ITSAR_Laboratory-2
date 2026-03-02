# Curl Test Commands - Edge Case Testing

> **Note:** Run these in PowerShell. Use `cmd /c` wrapper for POST requests with JSON bodies.  
> Make sure all three services are running before testing.

---

## 1. HAPPY PATH TESTS

### 1.1 Create a Student (201 Created)
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{\"name\":\"John Doe\",\"email\":\"john@example.com\"}"'
```
**Expected:** `HTTP/1.1 201 Created` with `{"id":1,"message":"created"}`

### 1.2 Create a Course (201 Created)
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8002/api/courses -H "Content-Type: application/json" -d "{\"name\":\"Math 101\",\"code\":\"MATH101\"}"'
```
**Expected:** `HTTP/1.1 201 Created` with `{"id":1,"message":"created"}`

### 1.3 Create an Enrollment (201 Created)
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":1}"'
```
**Expected:** `HTTP/1.1 201 Created` with `{"id":1,"message":"created"}`

### 1.4 List All Students (200 OK)
```powershell
curl.exe -i http://127.0.0.1:8001/api/students
```
**Expected:** `HTTP/1.1 200 OK` with JSON array of students

### 1.5 List All Courses (200 OK)
```powershell
curl.exe -i http://127.0.0.1:8002/api/courses
```
**Expected:** `HTTP/1.1 200 OK` with JSON array of courses

### 1.6 List All Enrollments (200 OK)
```powershell
curl.exe -i http://127.0.0.1:8003/api/enrollments
```
**Expected:** `HTTP/1.1 200 OK` with JSON array of enrollments

---

## 2. VALIDATION ERROR TESTS (400 Bad Request)

### 2.1 Create Student - Missing Name
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{\"email\":\"test@example.com\"}"'
```
**Expected:** `HTTP/1.1 400 Bad Request` with `{"error":"VALIDATION_ERROR","message":"Both name and email fields are required."}`

### 2.2 Create Student - Missing Email
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{\"name\":\"Jane\"}"'
```
**Expected:** `HTTP/1.1 400 Bad Request`

### 2.3 Create Student - Empty Body
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{}"'
```
**Expected:** `HTTP/1.1 400 Bad Request`

### 2.4 Create Course - Missing Code
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8002/api/courses -H "Content-Type: application/json" -d "{\"name\":\"Science\"}"'
```
**Expected:** `HTTP/1.1 400 Bad Request` with `{"error":"VALIDATION_ERROR","message":"Both name and code fields are required."}`

### 2.5 Create Course - Empty Body
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8002/api/courses -H "Content-Type: application/json" -d "{}"'
```
**Expected:** `HTTP/1.1 400 Bad Request`

### 2.6 Create Enrollment - Missing student_id
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"course_id\":1}"'
```
**Expected:** `HTTP/1.1 400 Bad Request` with `{"error":"VALIDATION_ERROR","message":"Both student_id and course_id are required."}`

### 2.7 Create Enrollment - Empty Body
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{}"'
```
**Expected:** `HTTP/1.1 400 Bad Request`

---

## 3. NOT FOUND TESTS (404 Not Found)

### 3.1 Get Non-Existent Student
```powershell
curl.exe -i http://127.0.0.1:8001/api/students/999
```
**Expected:** `HTTP/1.1 404 Not Found` with `{"error":"STUDENT_NOT_FOUND","message":"Student with ID 999 was not found."}`

### 3.2 Get Non-Existent Course
```powershell
curl.exe -i http://127.0.0.1:8002/api/courses/999
```
**Expected:** `HTTP/1.1 404 Not Found` with `{"error":"COURSE_NOT_FOUND","message":"Course with ID 999 was not found."}`

### 3.3 Enroll Non-Existent Student
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":999,\"course_id\":1}"'
```
**Expected:** `HTTP/1.1 404 Not Found` with `{"error":"STUDENT_NOT_FOUND","message":"Student with ID 999 was not found."}`

### 3.4 Enroll in Non-Existent Course
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":999}"'
```
**Expected:** `HTTP/1.1 404 Not Found` with `{"error":"COURSE_NOT_FOUND","message":"Course with ID 999 was not found."}`

---

## 4. DUPLICATE / CONFLICT TESTS (409 Conflict)

### 4.1 Duplicate Student Email
First create a student, then try the same email again:
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{\"name\":\"John Doe\",\"email\":\"john@example.com\"}"'
```
**Expected:** `HTTP/1.1 409 Conflict` with `{"error":"DUPLICATE_EMAIL","message":"A student with this email already exists."}`

### 4.2 Duplicate Course Code
First create a course, then try the same code again:
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8002/api/courses -H "Content-Type: application/json" -d "{\"name\":\"Math 101\",\"code\":\"MATH101\"}"'
```
**Expected:** `HTTP/1.1 409 Conflict` with `{"error":"DUPLICATE_CODE","message":"A course with this code already exists."}`

### 4.3 Duplicate Enrollment
After creating enrollment for student 1 / course 1, try again:
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":1}"'
```
**Expected:** `HTTP/1.1 409 Conflict` with `{"error":"DUPLICATE_ENROLLMENT","message":"This student is already enrolled in this course."}`

---

## 5. DEPENDENCY DOWN TESTS (503 Service Unavailable)

### 5.1 Student Service Down
**Step 1:** Stop the Student Service (close Terminal 1 or press Ctrl+C).

**Step 2:** Try to create an enrollment:
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":1}"'
```
**Expected:** `HTTP/1.1 503 Service Unavailable` with `{"error":"SERVICE_UNAVAILABLE","message":"Student Service is currently unavailable."}`

**Step 3:** Restart Student Service after testing.

### 5.2 Course Service Down
**Step 1:** Stop the Course Service (close Terminal 2 or press Ctrl+C).

**Step 2:** Try to create an enrollment:
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":1}"'
```
**Expected:** `HTTP/1.1 503 Service Unavailable` with `{"error":"SERVICE_UNAVAILABLE","message":"Course Service is currently unavailable."}`

**Step 3:** Restart Course Service after testing.

---

## 6. TIMEOUT TESTS (504 Gateway Timeout)

> To test timeouts, you need to temporarily modify the Student or Course Service to add a slow endpoint.

### 6.1 Add Slow Endpoint to Student Service

Add this route to `student-service/routes/api.php`:
```php
Route::get('/students/slow/{id}', function ($id) {
    sleep(10); // Simulate slow response (10 seconds)
    return response()->json(['id' => $id, 'name' => 'Slow Student']);
});
```

Then temporarily change the Enrollment Service controller (`enrollment-service/app/Http/Controllers/EnrollmentController.php`) to call the slow endpoint:
```php
// Change this line in checkStudent():
$response = Http::timeout(3)->get($this->studentServiceUrl . '/students/slow/' . $studentId);
```

### 6.2 Test Timeout
```powershell
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":1}"'
```
**Expected:** `HTTP/1.1 504 Gateway Timeout` with `{"error":"GATEWAY_TIMEOUT","message":"Student Service is taking too long to respond."}`

> **Important:** Remember to revert the changes after testing!

---

## Quick Test Sequence (Run All)

Run these commands in order for a full test:

```powershell
# 1. Create student
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{\"name\":\"John Doe\",\"email\":\"john@example.com\"}"'

# 2. Create course
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8002/api/courses -H "Content-Type: application/json" -d "{\"name\":\"Math 101\",\"code\":\"MATH101\"}"'

# 3. Create enrollment (happy path)
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":1}"'

# 4. Duplicate student (409)
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{\"name\":\"John Doe\",\"email\":\"john@example.com\"}"'

# 5. Missing fields (400)
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8001/api/students -H "Content-Type: application/json" -d "{}"'

# 6. Not found (404)
curl.exe -i http://127.0.0.1:8001/api/students/999

# 7. Duplicate enrollment (409)
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":1,\"course_id\":1}"'

# 8. Non-existent student enrollment (404)
cmd /c 'curl.exe -i -X POST http://127.0.0.1:8003/api/enrollments -H "Content-Type: application/json" -d "{\"student_id\":999,\"course_id\":1}"'
```
