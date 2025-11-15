# 🔒 SeferEt Laravel Security Guide

## 📋 Overview
This document outlines security best practices for managing sensitive credentials in the SeferEt Laravel project.

## 🚨 Critical Rules

### ❌ NEVER DO THIS:
1. **NEVER** commit real API keys, secrets, or passwords to Git
2. **NEVER** hardcode credentials in PHP files
3. **NEVER** share your `.env` file
4. **NEVER** commit `.env` file to version control
5. **NEVER** push sensitive data to GitHub

### ✅ ALWAYS DO THIS:
1. **ALWAYS** use environment variables for sensitive data
2. **ALWAYS** use `.env.example` with placeholder values
3. **ALWAYS** load credentials via `env()` or `config()`
4. **ALWAYS** rotate exposed keys immediately
5. **ALWAYS** review code before committing

---

## 🔑 Managing API Keys

### Amadeus API Keys

#### Local Development
1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Add your Amadeus credentials to `.env`:
   ```env
   AMADEUS_API_KEY=your_test_api_key_here
   AMADEUS_API_SECRET=your_test_api_secret_here
   AMADEUS_ENV=test
   ```

3. **NEVER** commit `.env` file

#### Production Deployment
Set environment variables directly on your server:

**Option 1: Server Environment Variables**
```bash
# Linux/Ubuntu with Apache
nano /etc/environment

# Add:
AMADEUS_API_KEY="your_production_key"
AMADEUS_API_SECRET="your_production_secret"
AMADEUS_ENV="production"
```

**Option 2: Laravel Forge/Cloud Hosting**
- Use the environment variable interface in your hosting dashboard
- Add each variable individually

**Option 3: Docker/Container**
```yaml
# docker-compose.yml
environment:
  - AMADEUS_API_KEY=${AMADEUS_API_KEY}
  - AMADEUS_API_SECRET=${AMADEUS_API_SECRET}
```

---

## 🗄️ Database Credentials

### Local Development
In `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seferet_db
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### Using Secure Scripts
If you need standalone PHP scripts:

```php
<?php
// ✅ CORRECT: Use helper
require_once __DIR__ . '/scripts/db_helper.php';
$pdo = getSecureDbConnection();

// ❌ WRONG: Never do this
$password = 'hardcoded_password';
$pdo = new PDO("mysql:host=localhost", "root", $password);
```

---

## 🛡️ GitHub Secrets (CI/CD)

### Setting Up GitHub Actions Secrets

1. Go to your repository on GitHub
2. Navigate to: **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Add each secret:
   - `AMADEUS_API_KEY`
   - `AMADEUS_API_SECRET`
   - `DB_PASSWORD`
   - etc.

### Using in Workflows
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    env:
      AMADEUS_API_KEY: ${{ secrets.AMADEUS_API_KEY }}
      AMADEUS_API_SECRET: ${{ secrets.AMADEUS_API_SECRET }}
      DB_PASSWORD: ${{ secrets.DB_PASSWORD }}
    
    steps:
      - uses: actions/checkout@v3
      - name: Run Tests
        run: php artisan test
```

---

## 🧹 What to Do If You Exposed Secrets

### Immediate Actions (Critical)

#### 1. Rotate Compromised Keys
- **Amadeus**: Log in to https://developers.amadeus.com/
  - Deactivate exposed keys
  - Generate new keys
  - Update `.env` with new keys
  
- **Database**: Change database password immediately
  ```sql
  ALTER USER 'root'@'localhost' IDENTIFIED BY 'new_secure_password';
  ```

#### 2. Remove from Current Codebase
```bash
# Remove exposed files from Git tracking
git rm --cached check_*.php reset_*.php update_*.php

# Commit the removal
git add .gitignore
git commit -m "Remove scripts with hardcoded credentials"
```

#### 3. Purge from Git History
```bash
# Install git-filter-repo (if not installed)
# Windows: pip install git-filter-repo
# Linux: apt-get install git-filter-repo

# Remove specific file from history
git filter-repo --path .env.amadeus.example --invert-paths

# Or remove all files with specific pattern
git filter-repo --path-glob 'check_*.php' --invert-paths

# Force push to remote (⚠️ DESTRUCTIVE - coordinate with team)
git push origin --force --all
git push origin --force --tags
```

⚠️ **Warning**: `git filter-repo` rewrites history. Coordinate with your team before using.

#### 4. Scan for Leaked Secrets on GitHub
- Check if your repository is public
- Use GitHub's secret scanning: Settings → Security → Secret scanning
- Review all commits for exposed credentials

---

## 🛠️ Tools & Automation

### Pre-commit Hook (Installed)
A pre-commit hook has been installed at `.git/hooks/pre-commit` to automatically scan for secrets.

To enable it:
```bash
# Linux/Mac
chmod +x .git/hooks/pre-commit

# Windows (Git Bash)
chmod +x .git/hooks/pre-commit
```

Test it:
```bash
# Try to commit a file with a fake secret
echo "AMADEUS_API_KEY=abc123def456" > test.txt
git add test.txt
git commit -m "test"
# Should be rejected!
```

### GitGuardian (Recommended)
Install GitGuardian for automatic secret detection:

1. Go to https://www.gitguardian.com/
2. Sign up with your GitHub account
3. Connect your repository
4. Receive alerts when secrets are detected

### Gitleaks (CLI Tool)
```bash
# Install
brew install gitleaks  # Mac
# or
choco install gitleaks  # Windows

# Scan repository
gitleaks detect --source . --verbose

# Scan before commit
gitleaks protect --staged
```

### TruffleHog (Deep Scan)
```bash
# Install
pip install truffleHog

# Scan repository
trufflehog git file://. --only-verified
```

---

## 📦 Laravel Configuration Best Practices

### ✅ Correct Pattern
```php
// config/services.php
return [
    'amadeus' => [
        'api_key' => env('AMADEUS_API_KEY'),
        'api_secret' => env('AMADEUS_API_SECRET'),
    ],
];

// Service class
class AmadeusService {
    public function __construct() {
        $this->apiKey = config('services.amadeus.api_key');
    }
}
```

### ❌ Wrong Pattern
```php
// ❌ NEVER do this
class AmadeusService {
    private $apiKey = 'h0mcjFRvSjxGSGp2fjrGkJ8iQYe1dVXw';
}
```

### Config Caching
In production, cache your config:
```bash
php artisan config:cache
```

This compiles all config files into a single cached file for performance.

⚠️ **Important**: After caching, `env()` only works in config files.

---

## 🔍 Regular Security Audits

### Monthly Checklist
- [ ] Review all `.env.example` files for placeholder values
- [ ] Scan repository with `gitleaks` or `trufflehog`
- [ ] Check GitHub secret scanning alerts
- [ ] Verify pre-commit hooks are active
- [ ] Rotate API keys (quarterly recommended)
- [ ] Review access logs for unusual activity

### Before Each Deployment
- [ ] Ensure `.env` is not in version control
- [ ] Verify config cache is cleared (`php artisan config:clear`)
- [ ] Check that all API keys are set on server
- [ ] Test application with production credentials
- [ ] Review recent commits for sensitive data

---

## 📞 Security Incident Response

If you discover a security breach:

1. **Immediately** rotate all affected credentials
2. Document what was exposed and when
3. Check access logs for unauthorized usage
4. Notify team members
5. Review and update security practices
6. Consider notifying affected parties if customer data exposed

---

## 📚 Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/10.x/security)
- [GitHub Secret Scanning](https://docs.github.com/en/code-security/secret-scanning)
- [Amadeus API Security](https://developers.amadeus.com/get-started/authentication)

---

## ✅ Security Checklist Summary

- [ ] `.env` file is gitignored
- [ ] `.env.example` has placeholder values only
- [ ] All credentials loaded via `env()` or `config()`
- [ ] Pre-commit hook installed and executable
- [ ] No hardcoded credentials in codebase
- [ ] Root-level PHP scripts with credentials deleted or moved to `/scripts` with `.gitignore`
- [ ] Git history cleaned of exposed secrets
- [ ] All exposed keys rotated
- [ ] GitHub secrets configured for CI/CD
- [ ] Team trained on security practices

---

**Last Updated**: 2025-11-14
**Version**: 1.0
