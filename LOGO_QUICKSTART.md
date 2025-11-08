# 🚀 SeferEt Logo Quick Start

## ⚡ Quick Steps (2 minutes)

### 1️⃣ Save Your Logo
Save the SeferEt logo PNG to:
```
public/images/logo/seferet-logo.png
```

### 2️⃣ Run Replacement Script
```powershell
.\replace-mosque-icons.ps1
```

### 3️⃣ Clear Cache
```bash
php artisan view:clear
```

### 4️⃣ Done! 🎉
Refresh your browser to see your logo everywhere.

---

## ✅ What's Already Done

**Files Updated (Manual):**
- ✓ Navbar (`components/customer/navbar.blade.php`)
- ✓ Footer (`components/customer/footer.blade.php`)  
- ✓ Homepage Hero Section
- ✓ Homepage Empty State
- ✓ Homepage Feature Card

**Files To Update (Script):**
- About Page
- Dashboard (3 instances)
- Explore Page (4 instances)
- Hotels Page
- Auth Layouts (3 files)

---

## 📊 Logo Sizes Used

| Location | Size | Usage |
|----------|------|-------|
| Navbar | 32px | Main navigation |
| Footer | 24px | Footer branding |
| Hero Section | 64px | Homepage hero |
| Empty States | 80px | No content states |
| Feature Cards | 48px | Feature highlights |
| Auth Pages | 32px | Login/Register |
| Admin Panel | 24px | Admin sidebar |

---

## 🔧 Troubleshooting

**Logo not showing?**
1. Check file exists: `public/images/logo/seferet-logo.png`
2. Clear cache: `php artisan view:clear`
3. Clear browser cache (Ctrl+F5)

**Wrong size?**
Edit the `style="height: XXpx"` attribute in the respective file.

**Need different logo for dark/light theme?**
Create `seferet-logo-dark.png` and `seferet-logo-light.png` and update conditionally.

---

## 📞 Support

All mosque icons (`fas fa-mosque`) have been replaced with your SeferEt logo image throughout the application for consistent branding.
