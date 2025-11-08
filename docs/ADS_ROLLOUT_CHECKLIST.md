# Ads System Rollout - Quick Checklist

**Use this checklist during deployment. For detailed instructions, see [ADS_ROLLOUT_PLAN.md](./ADS_ROLLOUT_PLAN.md)**

---

## 📋 Pre-Deployment (Before Starting)

### Environment Check
- [ ] PHP 8.1+ installed
- [ ] MySQL 5.7+ running
- [ ] Redis installed and running
- [ ] Supervisor installed
- [ ] GD/Imagick extension enabled
- [ ] Minimum 2GB disk space available

### Backup (CRITICAL!)
```bash
# Backup database
mysqldump -u root -p seferet_db > backup_pre_ads_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh backup_pre_ads_*.sql
```
- [ ] Database backed up
- [ ] Backup stored off-site
- [ ] Backup verified

### Environment Variables
```bash
# Add to .env
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
FEATURE_ADS_ENABLED=false
FEATURE_ADS_ADMIN_ONLY=true
FEATURE_ADS_B2B_ENABLED=false
FEATURE_ADS_PUBLIC_ENABLED=false
```
- [ ] Environment variables added

---

## 🚀 Day 1: Infrastructure & Migration

### Step 1: Dependencies
```bash
cd /var/www/SeferEt-Laravel
composer install --no-dev --optimize-autoloader
php artisan config:clear && php artisan cache:clear
php artisan config:cache && php artisan route:cache
```
- [ ] Dependencies installed
- [ ] Cache cleared and rebuilt

### Step 2: Storage Setup
```bash
mkdir -p storage/app/public/ads/{original,cropped,variants}
sudo chown -R www-data:www-data storage/app/public/ads
sudo chmod -R 775 storage/app/public/ads
php artisan storage:link
```
- [ ] Directories created
- [ ] Permissions set
- [ ] Symbolic link created

### Step 3: Redis Verification
```bash
redis-cli ping  # Should return PONG
```
- [ ] Redis responding

### Step 4: Run Migrations
```bash
# Enable maintenance mode
php artisan down --message="Upgrading system. Back in 5 minutes."

# Run migrations
php artisan migrate --force

# Verify
php artisan migrate:status | grep ads

# Disable maintenance mode
php artisan up
```
- [ ] Maintenance mode enabled
- [ ] Migrations ran successfully
- [ ] Tables verified in database
- [ ] Maintenance mode disabled

**Expected Downtime:** 2-5 minutes

---

## 🔧 Day 2: Workers & Cron

### Step 5: Queue Workers
```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/seferet-queue-ads.conf
# (Paste config from rollout plan)

# Start workers
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start seferet-queue-ads:*

# Verify
sudo supervisorctl status seferet-queue-ads:*
```
- [ ] Supervisor config created
- [ ] Workers started
- [ ] Workers status: RUNNING

### Step 6: Cron Jobs
```bash
# Edit crontab
crontab -e

# Add line:
# * * * * * cd /var/www/SeferEt-Laravel && php artisan schedule:run >> /dev/null 2>&1

# Verify scheduled jobs
php artisan schedule:list
```
- [ ] Cron job added
- [ ] Scheduled tasks listed

### Step 7: Test Queue & Cron
```bash
# Test queue
php artisan tinker
>>> dispatch(new \App\Jobs\ProcessAdScheduling());
>>> exit

# Test scheduler
php artisan schedule:run

# Check logs
tail -f storage/logs/laravel.log
```
- [ ] Queue processing works
- [ ] Scheduler running

---

## 🎚️ Week 1: Admin Testing

### Day 3: Enable for Admins
```bash
# Update .env
FEATURE_ADS_ENABLED=true
FEATURE_ADS_ADMIN_ONLY=true
FEATURE_ADS_B2B_ENABLED=false
FEATURE_ADS_PUBLIC_ENABLED=false

# Clear cache
php artisan config:clear && php artisan config:cache
```
- [ ] Feature flags updated
- [ ] Config cache cleared

### Testing Tasks
- [ ] Admin creates test ad
- [ ] Upload test image (JPEG, PNG, WebP)
- [ ] Submit ad for approval
- [ ] Approve ad
- [ ] Reject ad with reason
- [ ] Verify image variants generated
- [ ] Check audit logs
- [ ] Test ad activation/deactivation

### Monitor for 3-5 Days
- [ ] No critical errors in logs
- [ ] Queue processing normally
- [ ] Image processing < 10s
- [ ] No database issues

---

## 👥 Week 2: B2B Beta (10%)

### Day 8: Enable B2B Beta
```bash
# Update .env
FEATURE_ADS_ENABLED=true
FEATURE_ADS_ADMIN_ONLY=false
FEATURE_ADS_B2B_ENABLED=true
FEATURE_ADS_B2B_PERCENTAGE=10
FEATURE_ADS_PUBLIC_ENABLED=false

php artisan config:clear && php artisan config:cache
```
- [ ] 10% of B2B users have access

### Daily Monitoring (Day 8-10)
- [ ] Error rate < 2%
- [ ] Response time < 200ms
- [ ] Queue depth < 100
- [ ] User feedback collected
- [ ] No critical bugs

### Decision Point
- If successful → Proceed to 50%
- If issues → Investigate and fix, stay at 10%

---

## 📈 Week 2-3: B2B Expansion

### Day 11: 50% Rollout
```bash
FEATURE_ADS_B2B_PERCENTAGE=50
php artisan config:clear && php artisan config:cache
```
- [ ] 50% of B2B users have access

### Monitor (Day 11-14)
- [ ] Error rate < 1.5%
- [ ] Performance stable
- [ ] Positive user feedback

### Day 18: 100% B2B Rollout
```bash
FEATURE_ADS_B2B_PERCENTAGE=100
php artisan config:clear && php artisan config:cache
```
- [ ] All B2B users have access

### Monitor (Day 18-21)
- [ ] Error rate < 1%
- [ ] No critical issues for 3 days
- [ ] User adoption metrics tracked

---

## 🌍 Week 4: Public Ad Serving

### Day 22: Public Beta (5%)
```bash
FEATURE_ADS_PUBLIC_ENABLED=true
FEATURE_ADS_PUBLIC_PERCENTAGE=5
php artisan config:clear && php artisan config:cache
```
- [ ] 5% of public traffic seeing ads

### Monitor Cache Performance
- [ ] Cache hit rate > 70%
- [ ] Response time < 100ms
- [ ] No cache server issues

### Day 24: Public Expansion (50%)
```bash
FEATURE_ADS_PUBLIC_PERCENTAGE=50
php artisan config:clear && php artisan config:cache
```
- [ ] 50% of public traffic seeing ads
- [ ] Performance stable

### Day 27: Full Public Rollout
```bash
FEATURE_ADS_PUBLIC_PERCENTAGE=100
php artisan config:clear && php artisan config:cache
```
- [ ] 100% public rollout complete

---

## 🎉 Rollout Complete!

### Final Verification
- [ ] Error rate < 1% for 7 days
- [ ] Response time targets met
- [ ] Queue processing healthy
- [ ] No emergency rollbacks
- [ ] Positive user feedback

### Post-Rollout (Week 5+)
- [ ] Document lessons learned
- [ ] Update user documentation
- [ ] Plan feature enhancements
- [ ] Schedule performance optimization

---

## ⚠️ Emergency Rollback (If Needed)

### Quick Disable (< 1 min)
```bash
# Update .env
FEATURE_ADS_ENABLED=false

php artisan config:clear && php artisan config:cache
```
- [ ] Ads disabled

### Full Rollback (5-15 min)
```bash
php artisan down
mysql -u root -p seferet_db < backup_pre_ads_YYYYMMDD_HHMMSS.sql
php artisan migrate:rollback --step=6
php artisan cache:clear && php artisan config:clear
php artisan up
```
- [ ] Database restored
- [ ] Migrations rolled back
- [ ] System back online

---

## 📞 Support Contacts

**Emergency:** _______________  
**DevOps:** _______________  
**Backend Lead:** _______________  
**Slack Channel:** #rollout-ads-system

---

## ✅ Key Metrics to Watch

| Metric | Target | Status |
|--------|--------|--------|
| Error Rate | < 1% | ⬜ |
| Response Time (p95) | < 200ms | ⬜ |
| Queue Depth | < 100 jobs | ⬜ |
| Cache Hit Rate | > 80% | ⬜ |
| Image Processing | < 10s | ⬜ |
| Uptime | > 99.9% | ⬜ |

---

**Checklist Version:** 1.0  
**Rollout Date:** __________  
**Completed By:** __________  
**Sign-off:** __________
