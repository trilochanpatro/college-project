# MySQL Database Integration Guide for Attendance Management System

## Overview
Your attendance management system now has a complete MySQL database backend with PHP APIs to manage:
- Users (Admin, Faculty, Students)
- Students & Faculty Data
- Departments & Courses
- Subjects & Allocations
- Attendance Sessions & Records

---

## Installation & Setup

### 1. **Database Setup**

**Option A: Using phpMyAdmin or MySQL Workbench**
1. Open phpMyAdmin (if using XAMPP/WAMP)
2. Create new database: `attendance_system`
3. Import the `database_schema.sql` file
4. Data will be auto-populated

**Option B: Using Command Line**
```bash
mysql -u root -p < database_schema.sql
```

### 2. **PHP Configuration**

Edit `db_config.php` and update credentials:
```php
define('DB_SERVER', 'localhost');      // Your MySQL server
define('DB_USER', 'root');             // MySQL username
define('DB_PASSWORD', '');             // MySQL password
define('DB_NAME', 'attendance_system'); // Database name
```

### 3. **Server Setup**

- Place all files in your web server directory (htdocs for XAMPP, www for WAMP)
- Ensure MySQL server is running
- Access through: `http://localhost/path-to-folder/`

---

## API Endpoints

### **Authentication** (`api_auth.php`)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `api_auth.php?action=login` | Login user |
| POST | `api_auth.php?action=logout` | Logout user |
| GET | `api_auth.php?action=current-user` | Get current session user |
| POST | `api_auth.php?action=change-password` | Change user password |

**Example: Login**
```javascript
const result = await API.login('faculty1', 'faculty');
// Returns: {status: 'success', user: {...}}
```

---

### **Students** (`api_students.php`)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `api_students.php?action=all` | Get all students |
| GET | `api_students.php?action=get&id=1` | Get student by ID |
| GET | `api_students.php?action=section&section=A&semester=1` | Get students by section |
| POST | `api_students.php?action=create` | Create new student |
| PUT | `api_students.php?action=update&id=1` | Update student |
| DELETE | `api_students.php?action=delete&id=1` | Delete student |

**Example: Get Students by Section**
```javascript
const result = await API.getStudentsBySection('A', 1);
// Returns: {status: 'success', data: [{id, roll_no, name, ...}]}
```

---

### **Faculty** (`api_faculty.php`)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `api_faculty.php?action=all` | Get all faculty |
| GET | `api_faculty.php?action=get&id=1` | Get faculty by ID |
| GET | `api_faculty.php?action=department&dept_id=1` | Get faculty by department |
| POST | `api_faculty.php?action=create` | Create faculty member |
| PUT | `api_faculty.php?action=update&id=1` | Update faculty |
| DELETE | `api_faculty.php?action=delete&id=1` | Delete faculty |

---

### **Attendance** (`api_attendance.php`)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `api_attendance.php?action=create-session` | Create attendance session |
| POST | `api_attendance.php?action=mark-attendance` | Mark student attendance |
| GET | `api_attendance.php?action=record&session_id=1` | Get attendance records |
| GET | `api_attendance.php?action=report?student_id=1` | Get attendance report |
| GET | `api_attendance.php?action=sessions&faculty_id=1` | Get faculty sessions |

**Example: Create Session**
```javascript
const result = await API.createAttendanceSession({
    faculty_id: 1,
    subject_id: 1,
    section: 'A',
    session_date: '2026-01-22',
    session_time: '09:00:00'
});
```

---

### **Admin** (`api_admin.php`)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `api_admin.php?action=dashboard-stats` | Get dashboard statistics |
| GET | `api_admin.php?action=users&role=student` | Get users by role |
| POST | `api_admin.php?action=create-user` | Create new user |
| PUT | `api_admin.php?action=deactivate-user&id=1` | Deactivate user |
| GET | `api_admin.php?action=departments` | Get all departments |
| GET | `api_admin.php?action=courses` | Get all courses |
| GET | `api_admin.php?action=subjects` | Get all subjects |
| GET | `api_admin.php?action=attendance-reports?type=overall` | Get attendance reports |

---

## Database Schema

### Tables Created

1. **users** - User accounts (admin, faculty, student)
2. **departments** - College departments
3. **courses** - Academic courses
4. **subjects** - Course subjects
5. **faculty** - Faculty members
6. **students** - Student records
7. **subject_allocations** - Faculty-Subject assignments
8. **attendance_sessions** - Daily attendance sessions
9. **attendance_details** - Individual attendance records

---

## Using with HTML Files

### Include API Client in HTML
```html
<script src="api_client.js"></script>
```

### Example: Load Students in Admin Panel
```javascript
// In admin.html, add this to your script:
document.addEventListener('DOMContentLoaded', async () => {
    const students = await API.getAllStudents();
    // Process students data
    console.log(students);
});
```

### Example: Mark Attendance in Faculty View
```javascript
async function markAttendance() {
    const sessionId = 1;
    const studentId = 1;
    const status = 'present';
    
    const result = await API.markAttendance(sessionId, studentId, status);
    if (result.status === 'success') {
        showNotification('Attendance marked successfully');
    }
}
```

---

## Default Login Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin | Admin |
| faculty1 | faculty | Faculty |
| faculty2 | faculty | Faculty |
| faculty3 | faculty | Faculty |
| student1 | student | Student |

---

## Security Best Practices

1. **Change Default Passwords** - Update all default passwords immediately
2. **Use HTTPS** - Enable SSL/TLS in production
3. **Database Backup** - Regular backups of your database
4. **Input Validation** - All APIs validate input
5. **SQL Injection Protection** - Uses prepared statements
6. **Session Management** - PHP sessions for authentication

---

## File Structure

```
project-root/
├── api_auth.php              # Authentication APIs
├── api_students.php          # Student management APIs
├── api_faculty.php           # Faculty management APIs
├── api_attendance.php        # Attendance APIs
├── api_admin.php             # Admin dashboard APIs
├── db_config.php             # Database configuration
├── api_client.js             # JavaScript API client
├── database_schema.sql       # SQL schema and sample data
├── index.html                # Landing page
├── login.html                # Login page
├── student.html              # Student dashboard
├── faculty.html              # Faculty dashboard
├── admin.html                # Admin dashboard
├── style.css                 # Styling
└── script.js                 # Frontend JavaScript
```

---

## Troubleshooting

**Issue: "Database connection failed"**
- Check if MySQL is running
- Verify credentials in `db_config.php`
- Check database name is `attendance_system`

**Issue: "CORS errors"**
- Ensure `api_*.php` files have CORS headers (included by default)

**Issue: "Permission denied"**
- Check user role has access to the action
- Faculty can't delete students (admin only)

**Issue: "Session not found"**
- Verify session cookie is enabled
- Check PHP session path has write permissions

---

## Next Steps

1. ✅ Set up the database using `database_schema.sql`
2. ✅ Configure `db_config.php` with your MySQL credentials
3. ✅ Include `api_client.js` in your HTML files
4. ✅ Update your HTML pages to use API calls instead of localStorage
5. ✅ Test all APIs using the provided credentials
6. ✅ Deploy to production with proper security measures

---

## Support & Documentation

For more details on any specific API, check the comments in the respective PHP files.
Each file has comprehensive documentation in the header and throughout the code.

Happy coding! 🚀
