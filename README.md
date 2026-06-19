# Attendance Management System - Project Structure

## 📁 Folder Organization

```
attendance-system/
├── 📂 html/                      # HTML Pages
│   ├── index.html               # Landing/Home page
│   ├── login.html               # Login page
│   ├── student.html             # Student dashboard
│   ├── faculty.html             # Faculty dashboard
│   └── admin.html               # Admin dashboard
│
├── 📂 css/                       # Stylesheets
│   └── style.css                # Main styling
│
├── 📂 js/                        # JavaScript Files
│   ├── script.js                # Frontend logic
│   └── api_client.js            # API client library
│
├── 📂 api/                       # Backend PHP APIs
│   ├── db_config.php            # Database configuration
│   ├── api_auth.php             # Authentication endpoints
│   ├── api_students.php         # Student CRUD operations
│   ├── api_faculty.php          # Faculty CRUD operations
│   ├── api_attendance.php       # Attendance management
│   └── api_admin.php            # Admin operations
│
├── 📂 database/                  # Database Files
│   └── database_schema.sql      # MySQL schema & initial data
│
├── 📂 docs/                      # Documentation
│   ├── INDEX_HTML_DOCUMENTATION.md
│   ├── LOGIN_HTML_DOCUMENTATION.md
│   ├── STUDENT_HTML_DOCUMENTATION.md
│   ├── FACULTY_HTML_DOCUMENTATION.md
│   ├── ADMIN_HTML_DOCUMENTATION.md
│   ├── STYLE_CSS_DOCUMENTATION.md
│   ├── SCRIPT_JS_DOCUMENTATION.md
│   ├── DATABASE_SETUP.md        # Database setup guide
│   ├── COMPLETE_DOCUMENTATION.md
│   ├── QUICK_REFERENCE.md
│   ├── COMPLETION_REPORT.md
│   └── SHOWCASE.md
│
└── README.md                     # This file
```

---

## 🚀 Quick Start

### 1. **Database Setup**
```bash
# Import the SQL schema
mysql -u root -p < database/database_schema.sql
```

### 2. **Configure Database**
Edit `api/db_config.php` with your MySQL credentials:
```php
define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'attendance_system');
```

### 3. **Access the Application**
Place all files in your web server directory and access:
- **Landing Page**: `http://localhost/path/html/index.html`
- **Login Page**: `http://localhost/path/html/login.html`

---

## 📖 File Descriptions

### **HTML Files** (`html/`)
- **index.html**: Home page with system information
- **login.html**: User authentication
- **student.html**: Student attendance view and reports
- **faculty.html**: Attendance marking and session management
- **admin.html**: System administration and analytics

### **CSS Files** (`css/`)
- **style.css**: Responsive design with Bootstrap integration

### **JavaScript Files** (`js/`)
- **script.js**: Frontend logic and event handlers
- **api_client.js**: Reusable API client for all endpoints

### **API Files** (`api/`)
- **db_config.php**: Database connection & configuration
- **api_auth.php**: Login, logout, password change
- **api_students.php**: Student CRUD & filtering
- **api_faculty.php**: Faculty CRUD & management
- **api_attendance.php**: Session creation & marking
- **api_admin.php**: Dashboard stats & reports

### **Database Files** (`database/`)
- **database_schema.sql**: Complete schema with 9 tables and sample data

### **Documentation** (`docs/`)
- Detailed guides for each HTML file
- Setup and configuration instructions
- API reference documentation

---

## 🔐 Default Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin | Admin |
| faculty1 | faculty | Faculty |
| student1 | student | Student |

---

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Architecture**: RESTful API

---

## 📋 Features

✅ User Authentication & Authorization
✅ Student Management
✅ Faculty Management
✅ Attendance Marking & Tracking
✅ Real-time Attendance Reports
✅ Admin Dashboard with Statistics
✅ Responsive Design
✅ Data Validation & Security
✅ MySQL Database Integration

---

## 📚 Documentation

For detailed information:
- **Database Setup**: See `docs/DATABASE_SETUP.md`
- **HTML Documentation**: See `docs/` folder
- **API Reference**: See comments in `api/` files
- **Complete Guide**: See `docs/COMPLETE_DOCUMENTATION.md`

---

## ⚙️ Installation Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache, Nginx)
- Modern Web Browser

---

## 📞 Support

For troubleshooting and additional details, refer to the documentation files in the `docs/` folder.

---

**Last Updated**: 19 June, 2026
