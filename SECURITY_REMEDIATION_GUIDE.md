# 🚨 SECURITY REMEDIATION GUIDE - IMMEDIATE ACTIONS REQUIRED

## ⚠️ CRITICAL: Read This First

**Your repository has exposed credentials that MUST be rotated immediately.**

### What Was Found:
1. ✗ Real Amadeus API credentials in `.env.amadeus.example` (committed to Git)
2. ✗ Database password `Seyako@0011` hardcoded in 9 PHP files
3. ✗ Both issues are in Git history and potentially pushed to GitHub

---

## 🔥 PHASE 1: IMMEDIATE ACTIONS (DO THIS NOW)

### Step 1: Rotate Amadeus API Keys (CRITICAL - 5 minutes)

1. **Login to Amadeus Developer Portal**
   - Go to: https://developers.amadeus.com/
   - Login with your account

2. **Deactivate Exposed Keys**
   - Navigate to: My Apps → Your App → API Keys
   - Find the exposed key: `h0mcjFRvSjxGSGp2fjrGkJ8iQYe1dVXw`
   - Click **"Delete"** or **"Deactivate"**

3. **Generate New Keys**
   - Click **"Create New API Key"** or **"Generate Keys"**
   - Copy the new `API Key` and `API Secret`
   - Save them securely (password manager recommended)

4. **Update Your Local `.env`**
   ```bash
   # Open your .env file (NOT .env.example)
   notepad .env
   
   # Update with NEW keys:
   AMADEUS_API_KEY=your_new_api_key_here
   AMADEUS_API_SECRET=your_new_api_secret_here
   ```

5. **Test the Application**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan serve
   ```

---

### Step 2: Change Database Password (CRITICAL - 3 minutes)

1. **Connect to MySQL**
   ```bash
   mysql -u root -p
   # Enter current password: Seyako@0011
   ```

2. **Change the Password**
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'NewSecurePassword123!@#';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Update `.env`**
   ```bash
   # Open .env
   notepad .env
   
   # Update:
   DB_PASSWORD=NewSecurePassword123!@#
   ```

4. **Test Database Connection**
   ```bash
   php artisan migrate:status
   ```

---

## 🧹 PHASE 2: CLEAN UP REPOSITORY (15 minutes)

### Step 3: Remove Insecure Files from Tracking

```powershell
# Remove hardcoded credential files from Git
git rm --cached check_*.php
git rm --cached reset_*.php
git rm --cached update_*.php
git rm --cached test-menu.php

# Stage the updated .gitignore and .env.example
git add .gitignore
git add .env.example
git add .env.amadeus.example

# Commit the security fixes
git commit -m "security: Remove hardcoded credentials and update .env examples"
```

---

### Step 4: Clean Git History (DESTRUCTIVE - Backup First!)

⚠️ **WARNING**: This will rewrite Git history. Make a backup first!

```powershell
# 1. CREATE BACKUP
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupPath = "C:\Users\seide\SeferEt\SeferEt-Laravel-backup-$timestamp"
Copy-Item -Path "C:\Users\seide\SeferEt\SeferEt-Laravel" -Destination $backupPath -Recurse
Write-Host "Backup created at: $backupPath" -ForegroundColor Green

# 2. INSTALL git-filter-repo (if not already installed)
pip install git-filter-repo

# 3. VERIFY INSTALLATION
git filter-repo --version

# 4. CLEAN THE HISTORY
# Option A: Remove specific file with real credentials
git filter-repo --path .env.amadeus.example --invert-paths --force

# Option B: Clean specific files
git filter-repo --path-glob 'check_*.php' --invert-paths --force
git filter-repo --path-glob 'reset_*.php' --invert-paths --force
git filter-repo --path-glob 'update_*.php' --invert-paths --force

# 5. VERIFY THE CLEANUP
git log --all --full-history --oneline | Select-String "amadeus"

# 6. RE-ADD REMOTE (git-filter-repo removes it)
git remote add origin YOUR_GITHUB_URL_HERE

# 7. FORCE PUSH (⚠️ COORDINATE WITH TEAM FIRST)
# git push origin --force --all
# git push origin --force --tags
```

⚠️ **IMPORTANT**: Do NOT force push until:
- You've backed up everything
- You've coordinated with your team
- You understand this will break everyone's local copies

---

## 📦 PHASE 3: ADD NEW SECURITY FILES (5 minutes)

### Step 5: Stage and Commit New Security Files

```powershell
# Add the secure scripts and documentation
git add scripts/
git add SECURITY.md
git add SECURITY_REMEDIATION_GUIDE.md

# Make pre-commit hook executable (Git Bash)
# chmod +x .git/hooks/pre-commit

# Commit
git commit -m "security: Add security documentation and secure script helpers"

# Push (if safe to push now)
git push origin main
```

---

## 🛡️ PHASE 4: ENABLE PROTECTION (10 minutes)

### Step 6: Enable Pre-commit Hook

For **Git Bash** (recommended on Windows):
```bash
chmod +x .git/hooks/pre-commit
```

For **PowerShell** (if Git Bash not available):
The hook should work automatically, but test it:
```powershell
# Create a test file with fake secret
"AMADEUS_API_KEY=test123456789" | Out-File test_secret.txt
git add test_secret.txt
git commit -m "test"
# Should be rejected!

# Clean up
git reset HEAD test_secret.txt
Remove-Item test_secret.txt
```

---

### Step 7: Setup GitHub Secret Scanning

1. **Go to GitHub Repository**
   - Your repository: https://github.com/YOUR_USERNAME/SeferEt-Laravel

2. **Enable Secret Scanning**
   - Settings → Code security and analysis
   - Enable **"Secret scanning"**
   - Enable **"Push protection"**

3. **Review Existing Alerts**
   - Security → Secret scanning alerts
   - Review any detected secrets
   - Mark as resolved after rotation

---

### Step 8: Install GitGuardian (Optional but Recommended)

1. Go to: https://www.gitguardian.com/
2. Sign up with GitHub account
3. Install GitGuardian app on your repository
4. Configure alerts to your email

---

## 📋 PHASE 5: PRODUCTION DEPLOYMENT (Variable)

### Step 9: Update Production Environment Variables

**If using Laravel Forge:**
```
1. Login to Forge
2. Select your server → Site
3. Go to Environment tab
4. Update:
   - AMADEUS_API_KEY=your_new_production_key
   - AMADEUS_API_SECRET=your_new_production_secret
   - DB_PASSWORD=your_production_db_password
5. Save and restart
```

**If using cPanel:**
```
1. Login to cPanel
2. Software → Select PHP Version
3. Switch to Options tab
4. Add environment variables
5. Restart Apache
```

**If using Direct Server Access (Ubuntu/Linux):**
```bash
# SSH into server
ssh user@your-server.com

# Edit environment variables
sudo nano /etc/environment

# Add:
AMADEUS_API_KEY="your_new_production_key"
AMADEUS_API_SECRET="your_new_production_secret"

# Reload
source /etc/environment

# Restart web server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
```

---

## ✅ PHASE 6: VERIFICATION CHECKLIST

### Step 10: Verify Everything Works

- [ ] **Amadeus API keys rotated**
  ```bash
  php artisan tinker
  >>> config('amadeus.api_key')
  # Should show NEW key, not old one
  ```

- [ ] **Database password changed**
  ```bash
  php artisan db:show
  # Should connect successfully
  ```

- [ ] **Old files removed from Git**
  ```bash
  git ls-files | Select-String "check_"
  # Should return nothing
  ```

- [ ] **`.env.example` has placeholders only**
  ```bash
  Get-Content .env.example | Select-String "AMADEUS"
  # Should show: AMADEUS_API_KEY=
  # NOT real values
  ```

- [ ] **Pre-commit hook works**
  ```bash
  # Test as shown in Step 6
  ```

- [ ] **Application runs without errors**
  ```bash
  php artisan serve
  # Visit http://localhost:8000
  # Test flight/hotel search
  ```

- [ ] **Git history cleaned (if you ran filter-repo)**
  ```bash
  git log --all --oneline | Select-String "h0mcjFRvSjxGSGp2fjrGkJ8iQYe1dVXw"
  # Should return nothing
  ```

---

## 🚨 EMERGENCY ROLLBACK

If something goes wrong:

### Restore from Backup
```powershell
# If you created backup in Step 4
$backupPath = "C:\Users\seide\SeferEt\SeferEt-Laravel-backup-YYYYMMDD_HHMMSS"
Remove-Item -Path "C:\Users\seide\SeferEt\SeferEt-Laravel" -Recurse -Force
Copy-Item -Path $backupPath -Destination "C:\Users\seide\SeferEt\SeferEt-Laravel" -Recurse
```

### Restore Database Password
```sql
-- If new password doesn't work
ALTER USER 'root'@'localhost' IDENTIFIED BY 'Seyako@0011';
FLUSH PRIVILEGES;
```

### Restore Amadeus Keys
- Use the old keys temporarily (they should still work if not deleted)
- Update `.env` with old keys
- DO NOT commit!

---

## 📞 GETTING HELP

### If You're Stuck:

1. **Check SECURITY.md** - Detailed explanations for each concept
2. **Laravel Logs** - `storage/logs/laravel.log`
3. **Amadeus Support** - https://developers.amadeus.com/support
4. **GitHub Support** - https://support.github.com/

### Common Issues:

**"git filter-repo: command not found"**
```powershell
pip install git-filter-repo
# or
pip3 install git-filter-repo
```

**"Permission denied" on pre-commit hook**
```bash
# Use Git Bash:
chmod +x .git/hooks/pre-commit
```

**"Application can't connect to database"**
```bash
# Check .env has correct password
php artisan config:clear
php artisan cache:clear
```

**"Amadeus API returns 401 Unauthorized"**
```bash
# Clear config cache
php artisan config:clear

# Verify keys in .env match new keys from Amadeus portal
```

---

## 📊 PROGRESS TRACKER

Track your progress:

### Critical (Do Immediately)
- [ ] **Phase 1, Step 1**: Rotate Amadeus keys
- [ ] **Phase 1, Step 2**: Change database password

### Important (Do Today)
- [ ] **Phase 2, Step 3**: Remove insecure files
- [ ] **Phase 2, Step 4**: Clean Git history

### Essential (Do This Week)
- [ ] **Phase 3, Step 5**: Commit security files
- [ ] **Phase 4, Step 6**: Enable pre-commit hook
- [ ] **Phase 4, Step 7**: Setup GitHub scanning
- [ ] **Phase 5, Step 9**: Update production

### Verification (After Everything)
- [ ] **Phase 6, Step 10**: Complete verification checklist

---

## 🎯 ESTIMATED TIME

- **Phase 1** (Critical): 10 minutes
- **Phase 2** (Cleanup): 15 minutes
- **Phase 3** (New files): 5 minutes
- **Phase 4** (Protection): 10 minutes
- **Phase 5** (Production): Variable (10-30 minutes)
- **Phase 6** (Verification): 10 minutes

**Total: ~1 hour** (excluding production deployment)

---

## ✅ FINAL NOTES

### After Completing All Phases:

1. **Document what happened** in your team wiki/notes
2. **Share SECURITY.md** with all team members
3. **Schedule quarterly key rotation** (add to calendar)
4. **Review this guide monthly** to stay current

### Best Practices Going Forward:

- ✅ Always use `.env` for secrets
- ✅ Review code before committing
- ✅ Let pre-commit hook do its job
- ✅ Rotate keys every 3-6 months
- ✅ Monitor GitHub secret scanning alerts

---

**Created**: 2025-11-14
**Version**: 1.0
**Status**: READY FOR EXECUTION
