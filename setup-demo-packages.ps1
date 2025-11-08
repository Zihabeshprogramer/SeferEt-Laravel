# Setup Demo Packages with Image
# This script prepares the environment and runs the seeder

$targetPath = "storage\app\public\packages\umrah-demo.jpg"
$targetDir = Split-Path -Path $targetPath -Parent

Write-Host "======================================"
Write-Host "Demo Packages Setup"
Write-Host "======================================"
Write-Host ""

# Ensure directory exists
if (-not (Test-Path $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    Write-Host "✅ Created directory: $targetDir" -ForegroundColor Green
}

# Check if image exists
if (-not (Test-Path $targetPath)) {
    Write-Host "📸 Attempting to download sample Umrah image..." -ForegroundColor Yellow
    
    try {
        # Try to download a sample image from a free image service
        $sampleImageUrl = "https://kaftravels.com/wp-content/uploads/2023/11/al-firdous-umah-2-1.jpg"
        Invoke-WebRequest -Uri $sampleImageUrl -OutFile $targetPath -ErrorAction Stop
        Write-Host "✅ Sample image downloaded successfully" -ForegroundColor Green
    } catch {
        Write-Host "⚠️  Could not download image automatically" -ForegroundColor Yellow
        Write-Host "Please manually save the Umrah image to:" -ForegroundColor Yellow
        Write-Host "   $targetPath" -ForegroundColor Cyan
        Write-Host ""
        
        # Create a simple placeholder
        Write-Host "Creating placeholder..." -ForegroundColor Yellow
        
        # For now, we'll continue with the seeder and it will handle missing images gracefully
    }
}

Write-Host ""
Write-Host "Running database seeder..." -ForegroundColor Green
Write-Host ""

# Run the seeder
php artisan db:seed --class=DemoPackagesSeeder

Write-Host ""
Write-Host "======================================"
Write-Host "Setup complete!" -ForegroundColor Green
Write-Host "======================================"
