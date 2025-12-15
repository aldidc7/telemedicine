# MySQL Setup for Windows - Complete Guide

**Status**: MySQL not found on system - follow this guide to install

---

## ✅ Step 1: Download MySQL

1. Go to: https://dev.mysql.com/downloads/mysql/
2. Select **Windows** as platform
3. Download **MySQL Community Server 8.0** (recommended)
4. Choose: **Windows (x86, 64-bit), ZIP Archive** (easiest for development)

---

## ✅ Step 2: Install MySQL

### Option A: Windows MSI Installer (Easiest)

1. Download: **mysql-installer-community-8.0.x-winx64.msi**
2. Run the installer
3. Follow setup wizard:
   - **Setup Type**: Choose "Server only" 
   - **Config Type**: "Development Machine"
   - **Port**: 3306 (default)
   - **MySQL Root Password**: Set to `root` (for development)
4. Start MySQL Server

### Option B: ZIP Archive (Manual)

1. Extract ZIP to: `C:\mysql-8.0`
2. Open PowerShell as Administrator
3. Run these commands:

```powershell
# Navigate to MySQL bin folder
cd "C:\mysql-8.0\bin"

# Initialize MySQL data directory
.\mysqld --initialize-insecure --user=mysql

# Install as Windows service
.\mysqld --install MySQL80

# Start the service
net start MySQL80

# Verify installation
.\mysql -u root
# Should open MySQL prompt
```

---

## ✅ Step 3: Verify MySQL is Running

```powershell
# Check if MySQL service is running
Get-Service MySQL80 | Select-Object Name, Status

# Should show: Status = Running

# Or connect to MySQL
mysql -u root -p
# (just press Enter if no password set, or use 'root' as password)
```

---

## ✅ Step 4: Add MySQL to PATH (Optional but Recommended)

So you can use `mysql` command from anywhere:

1. Open **Environment Variables**:
   - Right-click "This PC" → Properties
   - Click "Advanced system settings"
   - Click "Environment Variables"
   - Click "New" under System variables
   
2. Create new variable:
   - **Variable name**: PATH
   - **Variable value**: `C:\Program Files\MySQL\MySQL Server 8.0\bin`
   - (or your MySQL installation path)

3. Click OK, restart PowerShell

4. Test:
```powershell
mysql --version
# Should show: mysql Ver 8.0.x
```

---

## ✅ Step 5: Create Database

Once MySQL is installed and running:

```powershell
# Connect to MySQL
mysql -u root -p
# (enter password, or just press Enter if none set)

# In MySQL prompt, run:
CREATE DATABASE telemedicine_mysql CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES;
EXIT;
```

Expected output:
```
Query OK, 1 row affected (0.01 sec)
```

---

## ⏭️ Next Steps

After MySQL is installed and database created:

1. Return to the terminal
2. Run: `php artisan migrate:fresh`
3. Continue with step 5 in MYSQL_MIGRATION_EXECUTION.md

---

## 🆘 Troubleshooting

### "mysql" command not found
- Add MySQL bin folder to PATH (Step 4 above)

### "Connection refused" error
- MySQL service not running: `net start MySQL80`
- Wrong password: MySQL default is no password or 'root'

### Port 3306 already in use
- Change in .env: `DB_PORT=3307`
- Configure MySQL to use different port

### Need to reset MySQL root password
```powershell
mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';
FLUSH PRIVILEGES;
```

---

## Installation Complete ✅

Once MySQL is running and database created, you're ready for Laravel migration!
