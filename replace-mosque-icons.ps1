# PowerShell Script to Replace fa-mosque icons with SeferEt logo
# Run this after saving your logo to public/images/logo/seferet-logo.png

$files = @(
    @{
        Path = "resources\views\customer\about.blade.php"
        Old = '<i class="fas fa-mosque'
        New = '<img src="{{ asset(''images/logo/seferet-logo.png'') }}" alt="SeferEt" style="height: 48px; width: auto;"'
    },
    @{
        Path = "resources\views\customer\dashboard.blade.php"
        Old = '<i class="fas fa-mosque'
        New = '<img src="{{ asset(''images/logo/seferet-logo.png'') }}" alt="SeferEt" style="height: 48px; width: auto;"'
    },
    @{
        Path = "resources\views\customer\explore.blade.php"
        Old = '<i class="fas fa-mosque'
        New = '<img src="{{ asset(''images/logo/seferet-logo.png'') }}" alt="SeferEt" style="height: 48px; width: auto;"'
    },
    @{
        Path = "resources\views\customer\hotels.blade.php"
        Old = '<i class="fas fa-mosque'
        New = '<img src="{{ asset(''images/logo/seferet-logo.png'') }}" alt="SeferEt" style="height: 48px; width: auto;"'
    },
    @{
        Path = "resources\views\layouts\auth.blade.php"
        Old = '<i class="fas fa-mosque'
        New = '<img src="{{ asset(''images/logo/seferet-logo.png'') }}" alt="SeferEt" style="height: 32px; width: auto;"'
    },
    @{
        Path = "resources\views\layouts\b2b-auth.blade.php"
        Old = '<i class="fas fa-mosque'
        New = '<img src="{{ asset(''images/logo/seferet-logo.png'') }}" alt="SeferEt" style="height: 32px; width: auto;"'
    },
    @{
        Path = "resources\views\layouts\adminlte.blade.php"
        Old = '<i class="fas fa-mosque'
        New = '<img src="{{ asset(''images/logo/seferet-logo.png'') }}" alt="SeferEt" style="height: 24px; width: auto;"'
    }
)

$replacedCount = 0
$errorCount = 0

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SeferEt Logo Replacement Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

foreach ($file in $files) {
    if (Test-Path $file.Path) {
        try {
            $content = Get-Content $file.Path -Raw
            $originalContent = $content
            
            # Replace all occurrences
            $content = $content -replace [regex]::Escape($file.Old), $file.New
            
            if ($content -ne $originalContent) {
                Set-Content -Path $file.Path -Value $content -NoNewline
                $occurrences = ([regex]::Matches($originalContent, [regex]::Escape($file.Old))).Count
                Write-Host "✓ Updated: $($file.Path) ($occurrences replacement(s))" -ForegroundColor Green
                $replacedCount += $occurrences
            } else {
                Write-Host "- No changes needed: $($file.Path)" -ForegroundColor Yellow
            }
        } catch {
            Write-Host "✗ Error processing: $($file.Path)" -ForegroundColor Red
            Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
            $errorCount++
        }
    } else {
        Write-Host "? File not found: $($file.Path)" -ForegroundColor Magenta
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  Total replacements: $replacedCount" -ForegroundColor Green
Write-Host "  Errors: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Make sure your logo is saved to: public/images/logo/seferet-logo.png" -ForegroundColor White
Write-Host "2. Run: php artisan view:clear" -ForegroundColor White
Write-Host "3. Refresh your browser to see the changes" -ForegroundColor White
