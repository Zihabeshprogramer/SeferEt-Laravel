# Setup Demo Image for Packages Seeder
# This script will help you copy the Umrah package image to the correct location

$targetPath = "storage\app\public\packages\umrah-demo.jpg"
$targetDir = Split-Path -Path $targetPath -Parent

Write-Host "======================================"
Write-Host "Package Demo Image Setup"
Write-Host "======================================"
Write-Host ""

# Ensure directory exists
if (-not (Test-Path $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    Write-Host "✅ Created directory: $targetDir" -ForegroundColor Green
}

# Check if image already exists
if (Test-Path $targetPath) {
    Write-Host "✅ Demo image already exists at: $targetPath" -ForegroundColor Green
} else {
    Write-Host "📸 Please copy your Umrah package image to:" -ForegroundColor Yellow
    Write-Host "   $targetPath" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "You can:" -ForegroundColor Yellow
    Write-Host "   1. Save the image from your screenshot to this location"
    Write-Host "   2. Or use any other travel package image as 'umrah-demo.jpg'"
    Write-Host ""
    Write-Host "After copying the image, run the seeder with:" -ForegroundColor Green
    Write-Host "   php artisan db:seed --class=DemoPackagesSeeder" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "======================================"
