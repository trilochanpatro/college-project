# Running Attendance System with XAMPP - Complete Guide

## 📥 Installation & Setup

### **Step 1: Download & Install XAMPP**

1. Download XAMPP from: https://www.apachefriends.org/
2. Choose the latest version (PHP 8.0 or higher recommended)
3. Run installer: `xampp-windows-x64-8.x.x-installer.exe`
4. Install in default location: `C:\xampp\`

---

## ⚙️ Starting XAMPP Services

### **Method 1: Using XAMPP Control Panel** (Easiest)

1. Open `C:\xampp\xampp-control.exe`
2. Click **Start** for:
   - ✓ **Apache**
   - ✓ **MySQL**
3. Wait for both to show green and "Running"

```
┌─────────────────────────────────┐
│  XAMPP Control Panel            │
├─────────────────────────────────┤
│ Apache........[Start] ✓ Running │
│ MySQL.........[Start] ✓ Running │
│ FileZilla.....[  ]              │
│ Tomcat........[  ]              │
└─────────────────────────────────┘
```

### **Method 2: Using Command Line**

```powershell
# Start Apache
cd C:\xampp
apache_start.bat

# In another terminal, start MySQL
cd C:\xampp
mysql_start.bat
```

### **Method 3: Check Service Status**

```powershell
# Check if services are running
Get-Service | Where-Object {$_.Name -like "*Apache*" -or $_.Name -like "*MySQL*"}

# Start MySQL service
net start MySQL80

# Start Apache service
net start Apache2.4
```

---

## 📂 Step 2: Setup Project Files

### **Move Project to XAMPP htdocs**

1. Copy entire project folder to XAMPP:
   ```
   Source: d:\sem 5 p1\
   Target: C:\xampp\htdocs\attendance-system\
   ```

2. Open Command Prompt as Admin:
   ```powershell
   # Navigate to project
   cd "d:\sem 5 p1"
   
   # Copy all files to XAMPP
   xcopy * "C:\xampp\htdocs\attendance-system\" /E /I
   ```

3. Verify structure in `C:\xampp\htdocs\attendance-system\`:
   ```
   attendance-system/
   ├── html/
   ├── css/
   ├── js/
   ├── api/
   ├── database/
   ├── docs/
   └── README.md
   ```

---

## 🗄️ Step 3: Setup Database

### **Method A: phpMyAdmin (Recommended)**

1. Open phpMyAdmin:
   ```
   http://localhost/phpmyadmin
   ```

2. Click **"New"** on left sidebar:
   ```
   Create database: attendance_system
   Collation: utf8mb4_unicode_ci
   Click [Create]
   ```

3. Select the new `attendance_system` database

4. Go to **"Import"** tab:
   - Click **"Choose File"**
   - Browse to: `C:\xampp\htdocs\attendance-system\database\database_schema.sql`
   - Click **[Import]**

5. Wait for success message ✓

---

### **Method B: MySQL Command Line**

```powershell
# Open Command Prompt as Admin
cd C:\xampp\mysql\bin

# Login to MySQL
mysql -u root -p

# If prompted for password, just press Enter (no password by default)
```

Then execute:
```sql
-- Import the database
source C:/xampp/htdocs/attendance-system/database/database_schema.sql;

-- Verify
SHOW DATABASES;
USE attendance_system;
SHOW TABLES;
```

---

## 🔧 Step 4: Configure Database Connection

### **Edit api/db_config.php**

1. Open file: `C:\xampp\htdocs\attendance-system\api\db_config.php`
2. Update database credentials (usually already correct):

```php
<?php
// Database credentials
define('DB_SERVER', 'localhost');      // Keep as is
define('DB_USER', 'root');             // Default XAMPP user
define('DB_PASSWORD', '');             // Empty password (default)
define('DB_NAME', 'attendance_system'); // Database name
?>
```

**Note**: If you set a MySQL password during XAMPP installation, update it here.

---

## 🌐 Step 5: Access the Application

### **Open in Web Browser**

| Page | URL |
|------|-----|
| 🏠 Home Page | `http://localhost/attendance-system/html/index.html` |
| 🔐 Login | `http://localhost/attendance-system/html/login.html` |
| 👤 Student | `http://localhost/attendance-system/html/student.html` |
| 👨‍🏫 Faculty | `http://localhost/attendance-system/html/faculty.html` |
| ⚙️ Admin | `http://localhost/attendance-system/html/admin.html` |

---

## 🔑 Login Credentials

### **Test Accounts**

```
Admin Account:
├─ Username: admin
├─ Password: admin
└─ Access: Full system access

Faculty Accounts:
├─ Username: faculty1 (or faculty2, faculty3)
├─ Password: faculty
└─ Access: Mark attendance, create sessions

Student Account:
├─ Username: student1
├─ Password: student
└─ Access: View attendance records
```

### **First Login Steps**

1. Go to: `http://localhost/attendance-system/html/login.html`
2. Enter: **admin** / **admin**
3. Click **Login**
4. You'll be in Admin Dashboard

---

## ✅ Verification Checklist

- [ ] XAMPP installed in `C:\xampp\`
- [ ] Apache running (green in Control Panel)
- [ ] MySQL running (green in Control Panel)
- [ ] Project copied to `C:\xampp\htdocs\attendance-system\`
- [ ] Database `attendance_system` created
- [ ] `database_schema.sql` imported successfully
- [ ] `api/db_config.php` configured
- [ ] Can access `http://localhost/attendance-system/html/index.html`
- [ ] Can login with admin/admin credentials
- [ ] Dashboard loads without errors

---

## 🆘 Troubleshooting for XAMPP

### **Issue: Apache won't start**
```
❌ Error: Apache refused to start
✓ Solution:
  1. Check if port 80 is already in use
  2. Look at error log: C:\xampp\apache\logs\error.log
  3. Try changing port in C:\xampp\apache\conf\httpd.conf
     (Find: Listen 80, Change to: Listen 8080)
  4. Or disable IIS/other web services using port 80
```

### **Issue: MySQL won't start**
```
❌ Error: MySQL refused to start
✓ Solution:
  1. Check if port 3306 is in use
  2. Delete: C:\xampp\mysql\data\ibdata1 (careful!)
  3. Reinstall MySQL from XAMPP Control Panel
  4. Or check: C:\xampp\mysql\data\mysql_error.log
```

### **Issue: Database import failed**
```
❌ Error: Import not working in phpMyAdmin
✓ Solution:
  1. Check file size (should be ~20KB)
  2. Try importing via MySQL command line instead
  3. Increase max file size in phpMyAdmin config:
     C:\xampp\phpMyAdmin\config.inc.php
     Change: $cfg['UploadDir'] = 'upload';
  4. Or copy .sql file to phpmyadmin/upload/ folder
```

### **Issue: "Cannot connect to database"**
```
❌ Error: API returns database connection error
✓ Solution:
  1. Verify MySQL is running in XAMPP Control Panel
  2. Check api/db_config.php credentials
  3. Verify database name is "attendance_system"
  4. Test in phpMyAdmin: http://localhost/phpmyadmin
  5. Check: mysql_error.log for MySQL errors
```

### **Issue: Pages show code instead of rendering**
```
❌ Error: PHP files showing as text
✓ Solution:
  1. Check Apache started in XAMPP Control Panel
  2. Verify files are in htdocs folder
  3. Clear browser cache (Ctrl+Shift+Delete)
  4. Check Apache error log: C:\xampp\apache\logs\error.log
```

### **Issue: API calls return 404**
```
❌ Error: API endpoints not found
✓ Solution:
  1. Verify api/ folder exists in htdocs/attendance-system/
  2. Check file names: api_auth.php, api_students.php, etc.
  3. Open browser console (F12) to see exact URL being called
  4. Verify url format in api_client.js
```

---

## 🎯 Common Tasks

### **Stop Services**
```powershell
# Stop from XAMPP Control Panel
# OR use command line:
net stop Apache2.4
net stop MySQL80
```

### **View MySQL Data**
```
1. Open: http://localhost/phpmyadmin
2. Left sidebar: attendance_system
3. Click table names to view data
4. Click "Browse" to see records
```

### **View PHP Errors**
```
1. Right-click XAMPP Control Panel
2. Click "Shell"
3. Or open: C:\xampp\apache\logs\error.log
4. Or open: C:\xampp\mysql\data\mysql_error.log
```

### **Change Apache Port** (if port 80 in use)
```
1. Edit: C:\xampp\apache\conf\httpd.conf
2. Find: Listen 80
3. Change to: Listen 8080
4. Save and restart Apache
5. Access: http://localhost:8080/attendance-system/...
```

---

## 📊 File Permissions

XAMPP automatically sets correct permissions. If you get permission errors:

```powershell
# Give full permissions to project folder
icacls "C:\xampp\htdocs\attendance-system" /grant Everyone:F /T

# Or change owner
takeown /F "C:\xampp\htdocs\attendance-system" /R /D Y
```

---

## 🔄 Restart Everything

If things aren't working:

```powershell
# 1. Close XAMPP Control Panel
# 2. Stop services:
net stop Apache2.4
net stop MySQL80

# 3. Restart XAMPP Control Panel
# 4. Start Apache and MySQL
# 5. Access http://localhost/attendance-system/html/login.html
```

---

## 📝 Summary

1. ✓ Install XAMPP
2. ✓ Start Apache & MySQL
3. ✓ Copy project to `C:\xampp\htdocs\attendance-system\`
4. ✓ Create database via phpMyAdmin
5. ✓ Import `database_schema.sql`
6. ✓ Configure `api/db_config.php`
7. ✓ Open browser → `http://localhost/attendance-system/html/login.html`
8. ✓ Login with admin/admin
9. ✓ Done! 🎉

---

## 🆘 Still Having Issues?

1. Check XAMPP Control Panel - are Apache & MySQL running?
2. Check browser console (F12) for error messages
3. Check MySQL error log: `C:\xampp\mysql\data\mysql_error.log`
4. Check Apache error log: `C:\xampp\apache\logs\error.log`
5. Verify database exists in phpMyAdmin
6. Verify api folder has .php files

**For more help, see: `docs/DATABASE_SETUP.md`**

---

**Happy coding! 🚀**
