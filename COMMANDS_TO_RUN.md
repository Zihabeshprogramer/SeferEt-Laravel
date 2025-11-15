# 🚀 QUICK COMMAND REFERENCE

## ⚡ Copy-Paste Commands for Security Remediation

**Use this file for quick reference. Full explanations in SECURITY_REMEDIATION_GUIDE.md**

---

## 🔥 IMMEDIATE ACTIONS

### 1. Rotate Amadeus Keys (Manual - Portal)
- Visit: https://developers.amadeus.com/
- Delete key: `h0mcjFRvSjxGSGp2fjrGkJ8iQYe1dVXw`
- Generate new keys
- Update `.env` (NOT `.env.example`)

### 2. Change Database Password

```bash
# Connect to MySQL
mysql -u root -p

# Run in MySQL:
ALTER USER 'root'@'localhost' IDENTIFIED BY 'NewSecurePassword123!@#';
FLUSH PRIVILEGES;
EXIT;

# Update .env file
# DB_PASSWORD=NewSecurePassword123!@#

# Test connection
php artisan migrate:status
```

---

## 🧹 CLEAN UP REPOSITORY

### 3. Remove Insecure Files from Git

```powershell
# Remove from tracking (files stay locally)
git rm --cached check_*.php
git rm --cached reset_*.php
git rm --cached update_*.php
git rm --cached test-menu.php

# Stage security fixes
git add .gitignore
git add .env.example
git add .env.amadeus.example

# Commit
git commit -m "security: Remove hardcoded credentials and update .env examples"
```

---

### 4. Clean Git History (OPTIONAL - Advanced)

⚠️ **Create backup first!**

```powershell
# 1. BACKUP
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupPath = "C:\Users\seide\SeferEt\SeferEt-Laravel-backup-$timestamp"
Copy-Item -Path "C:\Users\seide\SeferEt\SeferEt-Laravel" -Destination $backupPath -Recurse
Write-Host "Backup created at: $backupPath"

# 2. Install git-filter-repo
pip install git-filter-repo

# 3. Remove .env.amadeus.example from history
git filter-repo --path .env.amadeus.example --invert-paths --force

# 4. Re-add remote (git-filter-repo removes it)
git remote add origin https://github.com/YOUR_USERNAME/SeferEt-Laravel.git

# 5. Verify
git log --all --oneline | Select-String "amadeus"

# 6. Force push (⚠️ COORDINATE WITH TEAM)
# git push origin --force --all
# git push origin --force --tags
```

---

## 📦 ADD SECURITY FILES

### 5. Commit New Security Files

```powershell
# Add new files
git add scripts/
git add SECURITY.md
git add SECURITY_REMEDIATION_GUIDE.md
git add COMMANDS_TO_RUN.md

# Commit
git commit -m "security: Add security documentation and secure helpers"

# Push
git push origin main
```

---

## 🛡️ ENABLE PROTECTION

### 6. Enable Pre-commit Hook

**Git Bash (Recommended):**
```bash
chmod +x .git/hooks/pre-commit
```

**PowerShell (Test):**
```powershell
# Test the hook
"AMADEUS_API_KEY=test123456789" | Out-File test_secret.txt
git add test_secret.txt
git commit -m "test"
# Should be REJECTED

# Clean up
git reset HEAD test_secret.txt
Remove-Item test_secret.txt
```

---

## ✅ VERIFICATION COMMANDS

### Check Amadeus Config
```bash
php artisan tinker
>>> config('amadeus.api_key')
>>> exit
```

### Check Database Connection
```bash
php artisan db:show
```

### Check Removed Files
```powershell
git ls-files | Select-String "check_"
# Should return nothing
```

### Check .env.example is Clean
```powershell
Get-Content .env.example | Select-String "AMADEUS"
# Should show placeholder values only
```

### Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Test Application
```bash
php artisan serve
# Visit http://localhost:8000
```

---

## 🔧 PRODUCTION DEPLOYMENT

### Laravel Forge
```
Manual: Login → Site → Environment → Update variables
```

### Direct Server (SSH)
```bash
ssh user@your-server.com

# Edit environment
sudo nano /etc/environment

# Add:
AMADEUS_API_KEY="your_new_key"
AMADEUS_API_SECRET="your_new_secret"

# Reload
source /etc/environment

# Restart server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx

# On Laravel project
php artisan config:cache
php artisan cache:clear
```

---

## 🔍 SCANNING TOOLS

### Install Gitleaks
```bash
# Mac
brew install gitleaks

# Windows
choco install gitleaks

# Scan
gitleaks detect --source . --verbose
```

### Install TruffleHog
```bash
pip install truffleHog
trufflehog git file://. --only-verified
```

---

## 📋 QUICK CHECKLIST

```
[ ] Rotated Amadeus keys at https://developers.amadeus.com/
[ ] Updated .env with new Amadeus keys
[ ] Changed database password
[ ] Updated .env with new DB password
[ ] Removed insecure files from Git (git rm --cached)
[ ] Updated .env.example to have placeholders only
[ ] Committed security fixes
[ ] (Optional) Cleaned Git history with git-filter-repo
[ ] Added security documentation
[ ] Enabled pre-commit hook (chmod +x)
[ ] Tested pre-commit hook
[ ] Verified application works (php artisan serve)
[ ] Updated production environment variables
[ ] Enabled GitHub secret scanning
[ ] (Optional) Setup GitGuardian
```

---

## 🚨 ROLLBACK COMMANDS

### Restore from Backup
```powershell
$backupPath = "C:\Users\seide\SeferEt\SeferEt-Laravel-backup-TIMESTAMP"
Remove-Item -Path "C:\Users\seide\SeferEt\SeferEt-Laravel" -Recurse -Force
Copy-Item -Path $backupPath -Destination "C:\Users\seide\SeferEt\SeferEt-Laravel" -Recurse
```

### Restore Database Password (Emergency)
```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'Seyako@0011';
FLUSH PRIVILEGES;
```

---

## 📞 HELP

- **Full Guide**: Read `SECURITY_REMEDIATION_GUIDE.md`
- **Best Practices**: Read `SECURITY.md`
- **Laravel Logs**: `storage/logs/laravel.log`
- **Amadeus Support**: https://developers.amadeus.com/support

---

**Created**: 2025-11-14
**Quick Reference**: For detailed explanations, see SECURITY_REMEDIATION_GUIDE.md
