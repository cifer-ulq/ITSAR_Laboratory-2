# Architecture Report - Microservices Edge Case Testing

## Laboratory 2: System Architecture and Integration 2

---

## 1. System Architecture Overview

This project consists of three independent Laravel microservices that communicate over HTTP:

```
┌─────────────────┐     ┌─────────────────┐
│ Student Service  │     │  Course Service  │
│   Port: 8001    │     │   Port: 8002    │
│   SQLite DB     │     │   SQLite DB     │
└────────┬────────┘     └────────┬────────┘
         │                       │
         │    HTTP GET calls     │
         │                       │
    ┌────┴───────────────────────┴────┐
    │      Enrollment Service         │
    │         Port: 8003              │
    │         SQLite DB               │
    │      (Orchestrator)             │
    └─────────────────────────────────┘
```

### Why This Architecture?

- **Each service has its own database** — they are truly independent. If the Student Service database crashes, the Course Service is unaffected.
- **The Enrollment Service is the orchestrator** — it calls the other two services via HTTP before creating an enrollment. This is a simple but effective way to validate that the student and course exist.
- **SQLite instead of MySQL** — keeps things simple for development and testing. No need to configure MySQL, create databases, or manage credentials.

---

## 2. Edge Case Implementation Details

### 2.1. 400 Bad Request — Validation Errors

**Why 400?** The HTTP specification says 400 should be returned when "the server cannot process the request due to something perceived to be a client error." Missing required fields is a client error.

**How it works:**
- Each controller checks if required fields are present and non-empty
- If a field is missing, a JSON error is returned immediately — no database query is made
- This is checked BEFORE any other logic (fail fast principle)

```php
if (empty($data['name']) || empty($data['email'])) {
    return response()->json([
        'error' => 'VALIDATION_ERROR',
        'message' => 'Both name and email fields are required.'
    ], 400);
}
```

### 2.2. 404 Not Found — Missing Resources

**Why 404?** When a client asks for a resource that does not exist (like `/api/students/999`), the appropriate response is 404. This tells the client "the resource you're looking for is not here."

**How it works:**
- We use `Model::find($id)` which returns `null` if the record doesn't exist
- If `null`, return a descriptive 404 error
- The Enrollment Service also returns 404 when the Student or Course Service says a resource doesn't exist

```php
$student = Student::find($id);
if (!$student) {
    return response()->json([
        'error' => 'STUDENT_NOT_FOUND',
        'message' => 'Student with ID ' . $id . ' was not found.'
    ], 404);
}
```

### 2.3. 409 Conflict — Duplicate Detection

**Why 409?** The 409 status code means "the request conflicts with the current state of the server." This is perfect for duplicate entries — the data already exists.

**How it works:**
- Before creating a record, we check if a record with the same unique field already exists
- Student Service: checks for duplicate `email`
- Course Service: checks for duplicate `code`
- Enrollment Service: checks for duplicate `student_id + course_id` combination
- The database also has unique constraints as a safety net

```php
$existing = Student::where('email', $data['email'])->first();
if ($existing) {
    return response()->json([
        'error' => 'DUPLICATE_EMAIL',
        'message' => 'A student with this email already exists.'
    ], 409);
}
```

### 2.4. 503 Service Unavailable — Dependency Down

**Why 503?** When the Enrollment Service tries to contact the Student or Course Service but the service is offline, 503 is the correct code. It tells the client "the service I depend on is not available right now; try again later."

**How it works:**
- The Enrollment Service wraps HTTP calls in a try-catch block
- If a `ConnectionException` is thrown (meaning the service can't be reached), we return 503
- This only applies to the Enrollment Service because it's the only one that depends on other services

```php
try {
    $response = Http::timeout(5)->get($url);
} catch (\Illuminate\Http\Client\ConnectionException $e) {
    return response()->json([
        'error' => 'SERVICE_UNAVAILABLE',
        'message' => 'Student Service is currently unavailable.'
    ], 503);
}
```

### 2.5. 504 Gateway Timeout — Slow Dependencies

**Why 504?** When a service takes too long to respond, 504 Gateway Timeout is appropriate. The Enrollment Service acts as a gateway between the client and the Student/Course services. If those services are slow, the Enrollment Service reports a gateway timeout.

**How it works:**
- We use `Http::timeout(5)` to set a 5-second timeout for all HTTP calls
- If the timeout is exceeded, Laravel throws a `ConnectionException` with a message containing "timeout"
- We check the exception message to distinguish between "service down" (503) and "service slow" (504)

```php
if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
    return response()->json([
        'error' => 'GATEWAY_TIMEOUT',
        'message' => 'Student Service is taking too long to respond.'
    ], 504);
}
```

---

## 3. Standardized Error Response Format

All errors follow the same JSON structure:

```json
{
    "error": "ERROR_CODE",
    "message": "Human readable explanation"
}
```

**Why this format?**
- `error` is a machine-readable code (e.g., `STUDENT_NOT_FOUND`) — this lets frontend apps handle specific errors programmatically
- `message` is a human-readable string — this can be shown to users or logged for debugging
- Consistent format across all services makes it easy to write client code

---

## 4. How Services Survive "Dependency Down" Scenarios

The key architectural decisions for surviving dependency failures:

1. **Independent Databases:** Each service has its own SQLite database. If Student Service goes down, Course Service keeps working normally.

2. **Graceful Error Handling:** The Enrollment Service catches connection failures and returns meaningful errors (503/504) instead of crashing with a 500 Internal Server Error.

3. **Timeout Protection:** The 5-second timeout prevents the Enrollment Service from hanging indefinitely when a dependency is slow. Without this, a single slow service could freeze the entire system.

4. **Fail Fast:** Validation (400) is checked before making any HTTP calls. This means if the input is bad, we don't waste time calling other services.

5. **Order of Checks:**
   - First: Validate input (400)
   - Second: Check student exists via HTTP (404/503/504)
   - Third: Check course exists via HTTP (404/503/504)
   - Fourth: Check for duplicate enrollment (409)
   - Finally: Create enrollment (201)

---

## 5. Technology Choices

| Choice | Reason |
|--------|--------|
| **Laravel** | Popular PHP framework, easy to set up, built-in HTTP client |
| **SQLite** | No external database server needed, simple file-based database |
| **PHP Built-in Server** | Simple `php artisan serve`, no Apache/Nginx configuration needed |
| **Laravel HTTP Client** | Built-in support for timeouts and exception handling |

---

## 6. Reflections

### What I Learned
- How to structure microservices that depend on each other
- The importance of proper HTTP status codes for different error scenarios
- How to handle timeout and connection failures gracefully
- Why "fail fast" validation before making HTTP calls is important

### Challenges Faced
- Getting JSON data to pass correctly through PowerShell's `curl` required using `cmd /c` wrapper
- Understanding the difference between 503 (service down) and 504 (service slow)
- Making sure each service runs on a different port to avoid conflicts

### What Could Be Improved
- Add retry logic for 503/504 errors (retry 2-3 times before giving up)
- Add a circuit breaker pattern (stop calling a down service for a period)
- Use a message queue (like RabbitMQ) instead of direct HTTP calls for better resilience
- Add logging to track when services go down
