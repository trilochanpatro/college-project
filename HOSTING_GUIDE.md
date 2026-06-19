# Attendance System - Hosting & Deployment Guide

Complete step-by-step guide to host your College Attendance System on free or paid platforms.

---

## 📋 Table of Contents

1. [InfinityFree/000webhost (Easiest - Recommended for Beginners)](#infinityfree--000webhost)
2. [Oracle Cloud Always Free (Best for Long-term)](#oracle-cloud-always-free)
3. [Railway.app (Modern Deployment)](#railwayapp)
4. [Heroku (Alternative)](#heroku)
5. [Self-Hosting on Home Server/Raspberry Pi](#self-hosting)
6. [Post-Deployment Checklist](#post-deployment-checklist)

---

## 🚀 InfinityFree / 000webhost

**Why Choose?** Easiest setup, free domain, cPanel included, MySQL support, takes ~15 minutes.

### Prerequisites
- Email address
- Project files ready

### Step 1: Sign Up

1. Go to [infinityfree.com](https://infinityfree.com) or [000webhost.com](https://000webhost.com)
2. Click **"Sign Up"**
3. Fill in:
   - **Email**: Your email address
   - **Password**: Strong password (8+ characters)
   - **Domain**: Choose your free domain (e.g., `myattendance.rf.gd`)
4. Click **"Create Account"**
5. Verify your email
6. Login to your account

### Step 2: Access Control Panel

1. After login, click **"Control Panel"**
2. You should see your domain and file management options

### Step 3: Create MySQL Database

1. Go to **"MySQL Databases"** section
2. Click **"Create New Database"**
3. Fill in:
   - **Database Name**: `attendance_system` (or your choice)
   - **Database User**: Create new user (e.g., `attend_user`)
   - **Password**: Strong password (save this!)
   - **Confirm Password**: Same password
4. Click **"Create"**
5. **Save these credentials**:
   ```
   Database Host: localhost
   Database Name: attendance_system
   Database User: attend_user
   Database Password: your_password_here
   ```

### Step 4: Upload Project Files

#### Option A: Using File Manager (Easiest)

1. Go to **"File Manager"**
2. Navigate to **`public_html/`** folder
3. Delete the default `index.html` if present
4. Click **"Upload Files"**
5. Upload all project files:
   - `api/` folder (all PHP files)
   - `html/` folder (all HTML files)
   - `css/` folder
   - `js/` folder
   - `database/` folder
   - `db_config.php`
   - Other configuration files
6. Wait for upload to complete

#### Option B: Using FTP (Faster for Large Projects)

1. Go to **"FTP Accounts"**
2. Create new FTP account:
   - **Username**: (auto-filled)
   - **Password**: Create one (save it!)
3. Use FTP client (FileZilla, WinSCP):
   - **Host**: `yourdomain.rf.gd`
   - **Username**: Your FTP username
   - **Password**: Your FTP password
   - **Port**: 21
4. Navigate to `public_html/`
5. Drag and drop project files

### Step 5: Update Database Configuration

1. In File Manager, navigate to `public_html/api/`
2. Click on **`db_config.php`** → **"Edit"**
3. Replace the database connection details:

```php
<?php
// Database Connection Configuration
$servername = "localhost";
$username = "attend_user";        // ← Change this
$password = "your_password_here"; // ← Change this
$database = "attendance_system";  // ← Change this if different

$mysqli = new mysqli($servername, $username, $password, $database);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit();
}

$mysqli->set_charset("utf8mb4");
?>
```

4. Click **"Save"**

### Step 6: Import Database Schema

1. In File Manager, create new file: **`setup.php`**
2. Add this code:

```php
<?php
// Database Connection
$mysqli = new mysqli('localhost', 'attend_user', 'your_password_here', 'attendance_system');

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error);
}

// Read SQL file
$sql = file_get_contents(__DIR__ . '/database/database_schema.sql');

if ($sql === false) {
    die("❌ Could not read database schema file");
}

// Execute SQL
if ($mysqli->multi_query($sql)) {
    echo "✅ Database imported successfully!<br>";
    echo "Tables created: users, students, faculty, departments, courses, subjects, subject_allocations, attendance_sessions, attendance_details<br><br>";
    echo "You can now delete this file (setup.php) and access the system at your domain.<br>";
    echo '<a href="html/index.html">Go to Login Page</a>';
} else {
    echo "❌ Import failed: " . $mysqli->error;
}

$mysqli->close();
?>
```

3. Save the file
4. Open your browser and go to: `https://yourdomain.rf.gd/setup.php`
5. Wait for the ✅ success message
6. **Delete `setup.php`** from File Manager for security

### Step 7: Access Your System

1. Go to your domain: `https://yourdomain.rf.gd/html/index.html`
2. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
3. Change password immediately! (Go to profile settings)

### Step 8: Test All Features

- ✅ Login as student (username: `CS001`, password: `default123`)
- ✅ Check attendance dashboard
- ✅ Login as faculty
- ✅ Login as admin and manage users

---

## 🌐 Oracle Cloud Always Free

**Why Choose?** Most powerful, truly free forever, 2 vCPU, 20GB storage, very reliable.

### Prerequisites
- Oracle Cloud account (free tier)
- Linux basics knowledge
- SSH client (PuTTY for Windows, Terminal for Mac/Linux)

### Step 1: Create Oracle Cloud Account

1. Go to [oracle.com/cloud/free](https://www.oracle.com/cloud/free/)
2. Click **"Start for Free"**
3. Fill in account details
4. Verify email
5. Add payment method (won't be charged for always-free resources)
6. Login to Oracle Cloud Console

### Step 2: Create Linux VM Instance

1. In Oracle Cloud Dashboard, click **"Compute"** → **"Instances"**
2. Click **"Create Instance"**
3. Fill in:
   - **Name**: `attendance-system`
   - **Image**: Ubuntu 22.04 LTS (free eligible)
   - **Shape**: Ampere (A1 Compute) - free tier
   - **vCPUs**: 2 (free)
   - **Memory**: 12 GB (free)
4. Download SSH key pair (save it safely!)
5. Click **"Create"**
6. Wait for instance to start (shows green status)

### Step 3: Connect to Your Instance via SSH

#### Windows (PuTTY):
1. Download and open **PuTTY**
2. **Host Name**: `ubuntu@your_instance_ip`
3. **SSH** → **Auth** → Select your private key file
4. Click **"Open"**

#### Mac/Linux (Terminal):
```bash
chmod 600 /path/to/your/private/key
ssh -i /path/to/your/private/key ubuntu@your_instance_ip
```

### Step 4: Install LAMP Stack

```bash
# Update system
sudo apt update
sudo apt upgrade -y

# Install Apache, MySQL, PHP
sudo apt install -y apache2 mysql-server php php-mysql php-curl php-json

# Start services
sudo systemctl start apache2
sudo systemctl start mysql-server
sudo systemctl enable apache2
sudo systemctl enable mysql-server
```

### Step 5: Create MySQL Database

```bash
# Login to MySQL as root
sudo mysql -u root

# Create database
CREATE DATABASE attendance_system;

# Create user
CREATE USER 'attend_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';

# Grant privileges
GRANT ALL PRIVILEGES ON attendance_system.* TO 'attend_user'@'localhost';
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;
```

### Step 6: Import Database Schema

```bash
# Clone your project or upload files
cd /var/www/html
sudo git clone https://github.com/your-username/attendance-system.git
# OR upload files manually via SFTP

# Set permissions
sudo chown -R www-data:www-data /var/www/html/attendance-system
sudo chmod -R 755 /var/www/html/attendance-system

# Import database schema
sudo mysql -u attend_user -p attendance_system < /var/www/html/attendance-system/database/database_schema.sql
# Enter password when prompted
```

### Step 7: Update Database Config

```bash
# Edit db_config.php
sudo nano /var/www/html/attendance-system/api/db_config.php
```

Update:
```php
$servername = "localhost";
$username = "attend_user";
$password = "your_strong_password_here";
$database = "attendance_system";
```

Press `Ctrl+O`, then `Enter`, then `Ctrl+X` to save.

### Step 8: Configure Apache

```bash
# Create Virtual Host config
sudo nano /etc/apache2/sites-available/attendance.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName your_instance_ip
    DocumentRoot /var/www/html/attendance-system
    
    <Directory /var/www/html/attendance-system>
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory /var/www/html/attendance-system/api>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable:
```bash
sudo a2ensite attendance
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Step 9: Configure Firewall

1. In Oracle Cloud Console, go to **Networking** → **Virtual Cloud Networks**
2. Select your VCN
3. Go to **Security Lists**
4. Click default security list
5. Click **"Add Ingress Rules"**
6. Add:
   - **Port**: 80 (HTTP)
   - **Source**: 0.0.0.0/0
   - Click **"Add Ingress Rules"**
7. Repeat for port 443 (HTTPS)

### Step 10: Access Your System

1. Get your instance public IP from Oracle Console
2. Open browser: `http://your_instance_ip/html/index.html`
3. Login with credentials:
   - **Username**: `admin`
   - **Password**: `admin123`

### Step 11: (Optional) Setup HTTPS with Let's Encrypt

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get free SSL certificate
sudo certbot --apache -d your_instance_ip
# Follow prompts and enter your email

# Verify HTTPS works
# Visit https://your_instance_ip
```

---

## 🚄 Railway.app

**Why Choose?** Modern, Git integration, easy deployments, $5/month free credits.

### Step 1: Create Railway Account

1. Go to [railway.app](https://railway.app)
2. Click **"Start Develop"**
3. Sign up with GitHub or Google
4. Authorize Railway

### Step 2: Create New Project

1. Click **"New Project"**
2. Select **"Deploy from GitHub"**
3. Authorize GitHub access
4. Select your `attendance-system` repository
5. Click **"Deploy"**

### Step 3: Add MySQL Database

1. Click **"+ Add"**
2. Select **"MySQL"**
3. Railway creates a managed MySQL database
4. Database credentials are auto-configured

### Step 4: Configure Environment Variables

1. Go to your project
2. Click **"Variables"**
3. Add:
   ```
   DB_HOST=railway-mysql
   DB_USER=root
   DB_PASSWORD=[auto-generated]
   DB_NAME=attendance_system
   ```

### Step 5: Set Up Build Commands

1. In project settings, add build command:
```bash
# Import database schema
mysql -h $DB_HOST -u $DB_USER -p$DB_PASSWORD $DB_NAME < database/database_schema.sql
```

### Step 6: Deploy

1. Click **"Deploy"**
2. Wait for deployment to complete
3. Get your domain from Railway dashboard
4. Access: `https://your-railway-domain/html/index.html`

---

## 🦸 Heroku (Legacy - Free Tier Ending)

**Note**: Heroku discontinued free tier in November 2022. Only showing if you have existing credits.

(Instructions available on request)

---

## 🏠 Self-Hosting on Raspberry Pi or Home Server

**Why Choose?** Full control, learn DevOps, no monthly costs (only electricity).

### Hardware Needed
- Raspberry Pi 4 (2GB minimum, 4GB recommended) ~$50
- Micro SD Card (32GB) ~$10
- Power supply
- Ethernet cable or WiFi

### Step 1: Install Raspberry Pi OS

1. Download [Raspberry Pi Imager](https://www.raspberrypi.com/software/)
2. Flash OS to SD card
3. Insert SD card into Pi and power on

### Step 2: Install LAMP Stack

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y apache2 mysql-server php php-mysql php-curl
```

### Step 3: Set Up Database

```bash
sudo mysql -u root
CREATE DATABASE attendance_system;
CREATE USER 'attend_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON attendance_system.* TO 'attend_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 4: Upload Project Files

```bash
cd /var/www/html
sudo git clone https://github.com/your-repo/attendance-system.git
sudo chown -R www-data:www-data attendance-system
```

### Step 5: Make Accessible from Internet

#### Option A: DuckDNS (Free Dynamic DNS)

1. Go to [duckdns.org](https://www.duckdns.org)
2. Create account
3. Add subdomain (e.g., `myattendance`)
4. Get token
5. On Raspberry Pi:
```bash
# Create update script
nano ~/duckdns-update.sh
```

Add:
```bash
#!/bin/bash
curl -s "https://www.duckdns.org/update?domains=myattendance&token=YOUR_TOKEN&ip=" >/dev/null 2>&1
```

6. Make executable: `chmod +x ~/duckdns-update.sh`
7. Add to cron: `crontab -e`
   - Add: `*/5 * * * * ~/duckdns-update.sh`

#### Option B: Ngrok (Simple Tunneling)

1. Install Ngrok on Raspberry Pi:
```bash
wget https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-arm.zip
unzip ngrok-stable-linux-arm.zip
sudo mv ngrok /usr/local/bin
```

2. Get authtoken from [ngrok.com](https://ngrok.com)
3. Run:
```bash
ngrok http 80
```

4. Ngrok provides public URL: `https://xyz123.ngrok.io`

### Step 6: Access Your System

- **Local Network**: `http://your_pi_ip/html/index.html`
- **Internet**: `https://myattendance.duckdns.org/html/index.html` or via Ngrok URL

---

## ✅ Post-Deployment Checklist

After deploying to any platform, complete these steps:

### Security
- [ ] Change default admin password
- [ ] Delete setup.php file if you created one
- [ ] Update `db_config.php` with correct credentials
- [ ] Enable HTTPS/SSL certificate
- [ ] Set file permissions correctly (700 for config, 755 for public)
- [ ] Create backup of database

### Testing
- [ ] Test login as admin
- [ ] Test login as student
- [ ] Test login as faculty
- [ ] Verify attendance marking works
- [ ] Check if data persists after refresh
- [ ] Test on mobile device

### Optimization
- [ ] Enable gzip compression in Apache
- [ ] Set up database backups (daily)
- [ ] Configure email notifications (optional)
- [ ] Monitor server logs for errors
- [ ] Test with multiple concurrent users

### Maintenance
- [ ] Set up automated backups
- [ ] Monitor disk space usage
- [ ] Update PHP version if needed
- [ ] Create README for users
- [ ] Set up admin contact email

---

## 🆘 Troubleshooting

### Problem: "Database connection failed"
**Solution**: Check credentials in `db_config.php` match your hosting provider's database settings.

### Problem: "404 Not Found" on HTML files
**Solution**: Ensure files are in correct directory structure. Check Apache document root.

### Problem: "Permission denied" errors
**Solution**: 
```bash
sudo chown -R www-data:www-data /var/www/html/attendance-system
sudo chmod -R 755 /var/www/html/attendance-system
```

### Problem: Large file upload fails
**Solution**: Increase PHP limits in `php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
```

### Problem: Slow performance
**Solution**:
- Increase database index efficiency
- Enable caching headers
- Use CDN for static files
- Upgrade to higher tier server

---

## 📞 Support & Resources

- **InfinityFree Support**: [infinityfree.com/support](https://www.infinityfree.net/support/)
- **Oracle Cloud Docs**: [oracle.com/cloud/free/documentation](https://docs.oracle.com/en-us/iaas/Content/GSG/Concepts/overview.htm)
- **Railway Docs**: [railway.app/docs](https://docs.railway.app/)
- **PHP MySQL Guide**: [php.net/manual/en/book.mysqli.php](https://www.php.net/manual/en/book.mysqli.php)

---

## 🎯 Recommended Path

1. **Start with InfinityFree** - Takes 15 minutes, easiest setup
2. **Upgrade to Oracle Cloud** - More powerful, truly free forever
3. **Scale with Railway/DigitalOcean** - When traffic grows (paid tier)

Good luck with your deployment! 🚀
