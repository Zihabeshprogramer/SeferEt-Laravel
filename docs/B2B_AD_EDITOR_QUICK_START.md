# B2B Ad Editor - Quick Start Guide

## 🚀 Getting Started (5 Minutes)

### Step 1: Access the Feature
1. Login to your B2B dashboard
2. Look for **"My Ads"** in the sidebar menu
3. Click it to see your ads dashboard

### Step 2: Create Your First Ad
1. Click **"Create New Ad"** button
2. Select a product from your inventory
3. Enter a catchy title (max 100 characters)
4. Upload a banner image (recommended: 1200x600px)
5. **Drag the button** to position it on your image
6. Customize button text and color
7. Set start/end dates (optional)
8. Click **"Submit for Approval"**

### Step 3: Wait for Approval
- Admin reviews within 24-48 hours
- You'll see status change from "Pending" to "Approved" or "Rejected"
- If rejected, you can edit and resubmit

### Step 4: Activate Your Ad
- Once approved, click the toggle button to activate
- Monitor performance (impressions, clicks, CTR)
- Pause/reactivate anytime

---

## 📍 Key URLs

```
Main Dashboard:    /b2b/ads
Create New Ad:     /b2b/ads/create
View Ad:           /b2b/ads/{id}
Edit Ad:           /b2b/ads/{id}/edit
```

---

## 🎯 What You Can Do

### ✅ All Users Can:
- Create ads for their own products
- Upload banner images (JPEG/PNG, max 2MB)
- Position CTA button anywhere on image
- Save drafts without approval
- Submit for admin approval
- Track performance metrics

### ✅ By Status:
**Draft**: Edit, Delete, Submit  
**Pending**: Withdraw  
**Approved**: Activate/Deactivate  
**Rejected**: Edit, Delete, Resubmit

---

## 🖼️ Image Requirements

| Requirement | Specification |
|------------|---------------|
| Format | JPEG or PNG |
| Max Size | 2 MB |
| Recommended Dimensions | 1200 x 600 pixels |
| Aspect Ratio | 2:1 (horizontal) |

---

## 🔘 CTA Button Options

### Text
- Max 30 characters
- Examples: "Book Now", "Learn More", "Get Offer"

### Styles
- Primary (Blue)
- Success (Green)
- Warning (Yellow)
- Danger (Red)
- Info (Cyan)
- Secondary (Gray)

### Position
- Drag anywhere on the image
- Stays within image bounds
- Position saved as percentages (responsive)

---

## 📊 Performance Metrics

| Metric | Description |
|--------|-------------|
| Impressions | How many times ad was displayed |
| Clicks | How many times button was clicked |
| CTR | Click-through rate (clicks ÷ impressions × 100) |

---

## ⚠️ Common Issues

### "No products in dropdown"
→ You need active products (packages/flights/hotels/offers) first

### "Image won't upload"
→ Check file size (<2MB) and type (JPEG/PNG)

### "Can't edit my ad"
→ You can only edit draft or rejected ads

### "CTA button won't drag"
→ Only works when uploading a new image

---

## 🎓 Tips for Better Ads

1. **Use high-quality images** - Clear, professional photos perform best
2. **Keep titles short** - Under 80 characters for better visibility
3. **Position CTA carefully** - Avoid corners, prefer center-right or bottom-right
4. **Test button colors** - Green often performs best, but test with your audience
5. **Monitor CTR** - Above 5% is good, above 10% is excellent
6. **Update regularly** - Refresh content every 2-3 months

---

## 🔄 Ad Workflow

```
Draft → Submit → Pending → Admin Review → Approved/Rejected
                             ↓
                         If Rejected
                             ↓
                    Edit & Resubmit → Pending
```

---

## 📞 Need Help?

1. Check full documentation: `docs/B2B_AD_EDITOR_IMPLEMENTATION.md`
2. Contact support through dashboard
3. Review error messages carefully
4. Check browser console for JavaScript errors

---

## ✅ Quick Checklist

Before submitting an ad:
- [ ] Selected correct product
- [ ] Title is clear and compelling
- [ ] Image meets requirements (1200x600, <2MB)
- [ ] CTA button is positioned well
- [ ] Button text is action-oriented
- [ ] Schedule is set (if needed)
- [ ] Preview looks good

---

**Version:** 1.0.0  
**Last Updated:** January 8, 2025

Happy advertising! 🎉
