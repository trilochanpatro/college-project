# How to Run - Attendance Management System

## ✅ Prerequisites

- **PHP 7.4+** with MySQLi extension
- **MySQL 5.7+** server running
- **Web Server** (Apache, Nginx, or PHP built-in server)
- **Web Browser** (Chrome, Firefox, Edge, Safari)

---

## 🚀 Step-by-Step Installation

### **Step 1: Setup Web Server**

**Option A: Using XAMPP/WAMP**
1. Download & install XAMPP or WAMP
2. Start Apache & MySQL services
3. Place project folder in:
   - XAMPP: `C:\xampp\htdocs\attendance-system\`
   - WAMP: `C:\wamp64\www\attendance-system\`

**Option B: Using PHP Built-in Server**
```powershell
cd "d:\sem 5 p1"
php -S localhost:8000
```

---

### **Step 2: Setup Database**

**Method 1: Using phpMyAdmin (Easiest)**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" → Create database: `attendance_system`
3. Go to "Import" tab
4. Upload file: `database/database_schema.sql`
5. Click "Import" ✓

**Method 2: Using MySQL Command Line**
```bash
mysql -u root -p < "d:\sem 5 p1\database\database_schema.sql"
```

**Method 3: Using MySQL Workbench**
1. Open MySQL Workbench
2. File → Run SQL Script
3. Select: `database/database_schema.sql`
4. Execute

---

### **Step 3: Configure Database Connection**

Edit `api/db_config.php`:

```php
define('DB_SERVER', 'localhost');      // Your MySQL host
define('DB_USER', 'root');             // MySQL username
define('DB_PASSWORD', '');             // MySQL password (empty by default)
define('DB_NAME', 'attendance_system'); // Database name (don't change)
```

**If you have a MySQL password:**
```php
define('DB_PASSWORD', 'your_password_here');
```

---

### **Step 4: Run the Application**

**Option A: Via Web Browser**

1. Open your browser
2. Navigate to:
   - **XAMPP**: `http://localhost/attendance-system/html/index.html`
   - **WAMP**: `http://localhost/attendance-system/html/index.html`
   - **PHP Server**: `http://localhost:8000/html/index.html`

3. Click **Login** button
4. Enter credentials:
   - **Username**: `admin`
   - **Password**: `admin`

**Option B: Via PHP Built-in Server**

```powershell
cd "d:\sem 5 p1"
php -S localhost:8000
```

Then open: `http://localhost:8000/html/login.html`

---

## 🔑 Default Login Credentials

| Username | Password | Role | Access |
|----------|----------|------|--------|
| admin | admin | Admin | All features + statistics |
| faculty1 | faculty | Faculty | Create sessions, mark attendance |
| faculty2 | faculty | Faculty | Create sessions, mark attendance |
| faculty3 | faculty | Faculty | Create sessions, mark attendance |
| student1 | student | Student | View own attendance |

---

## 🌐 Access URLs

### **Using XAMPP/WAMP**
```
🏠 Home:     http://localhost/attendance-system/html/index.html
🔐 Login:    http://localhost/attendance-system/html/login.html
👤 Student:  http://localhost/attendance-system/html/student.html
👨‍🏫 Faculty:   http://localhost/attendance-system/html/faculty.html
⚙️ Admin:     http://localhost/attendance-system/html/admin.html
```

### **Using PHP Built-in Server**
```
🏠 Home:     http://localhost:8000/html/index.html
🔐 Login:    http://localhost:8000/html/login.html
👤 Student:  http://localhost:8000/html/student.html
👨‍🏫 Faculty:   http://localhost:8000/html/faculty.html
⚙️ Admin:     http://localhost:8000/html/admin.html
```

---

## 📂 File Structure Reference

```
d:\sem 5 p1\
├── html/              ← Start here! (index.html)
├── css/               ← Styles
├── js/                ← Frontend logic
├── api/               ← Backend APIs
├── database/          ← SQL schema
├── docs/              ← Documentation
└── README.md          ← Project info
```

---

## ⚠️ Troubleshooting

### **Issue: "Cannot connect to database"**
```
❌ Error: Database connection failed
✓ Solution:
  1. Check if MySQL is running
  2. Verify credentials in api/db_config.php
  3. Ensure database name is "attendance_system"
  4. Test MySQL connection:
     mysql -u root -p
```

### **Issue: "Connection refused on port 3306"**
```
❌ Error: MySQL not running
✓ Solution:
  1. Start MySQL service:
     - XAMPP: Click "Start" next to MySQL
     - Windows Services: Search "Services" → MySQL
     - Command: net start MySQL80
```

### **Issue: "Page not found / 404 error"**
```
❌ Error: Files not in correct location
✓ Solution:
  1. Verify files are in web root
  2. Check folder structure: html/, css/, js/, api/
  3. Use correct URL with project name
```

### **Issue: "PHP files download instead of execute"**
```
❌ Error: PHP not configured on server
✓ Solution:
  1. Use XAMPP/WAMP (easier)
  2. Or configure your web server for PHP
  3. Ensure .php files have correct mime type
```

### **Issue: "API calls not working"**
```
❌ Error: API endpoints unreachable
✓ Solution:
  1. Check browser console (F12 → Console tab)
  2. Verify api_*.php files exist in api/ folder
  3. Check database connection in api/db_config.php
  4. Ensure MySQL is running
```

---

## ✨ First Steps After Login

### **As Admin:**
1. ✓ Go to Admin Dashboard
2. ✓ View Dashboard Statistics
3. ✓ Manage Users, Departments, Subjects
4. ✓ View Attendance Reports

### **As Faculty:**
1. ✓ Go to Faculty Dashboard
2. ✓ Create Attendance Session
3. ✓ Mark Student Attendance
4. ✓ View Session History

### **As Student:**
1. ✓ Go to Student Dashboard
2. ✓ View Own Attendance
3. ✓ Check Attendance Percentage
4. ✓ View Subject-wise Reports

---

## 📋 Quick Checklist

- [ ] PHP 7.4+ installed
- [ ] MySQL 5.7+ installed
- [ ] Apache/Nginx/PHP Server running
- [ ] Project folder in web root
- [ ] Database imported (`attendance_system`)
- [ ] `api/db_config.php` configured
- [ ] Can access `http://localhost/.../html/index.html`
- [ ] Can login with admin/admin
- [ ] Database tables visible in phpMyAdmin

---

## 🆘 Need Help?

1. Check `docs/DATABASE_SETUP.md` for detailed setup
2. Review `docs/COMPLETE_DOCUMENTATION.md` for features
3. Check browser console (F12) for error messages
4. Verify all files are in correct folders
5. Test MySQL connection directly

---

**Ready to go! 🎉 Open your browser and visit the login page.**
