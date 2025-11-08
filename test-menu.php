<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Gate;

echo "=== Ad Management Menu Visibility Test ===\n\n";

$user = User::find(1);

if (!$user) {
    echo "❌ User ID 1 not found!\n";
    exit(1);
}

echo "User: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Role: {$user->role}\n\n";

echo "=== Permission Checks ===\n";
echo "isAdmin(): " . ($user->isAdmin() ? '✓ YES' : '✗ NO') . "\n";
echo "Gate::allows('admin'): " . (Gate::forUser($user)->allows('admin') ? '✓ YES' : '✗ NO') . "\n";
echo "hasRole('admin'): " . ($user->hasRole('admin') ? '✓ YES' : '✗ NO') . "\n\n";

echo "=== Route Access Check ===\n";
$routes = [
    'admin.ads.index',
    'admin.ads.pending',
    'admin.ads.analytics.index',
];

foreach ($routes as $route) {
    try {
        $url = route($route);
        echo "✓ {$route} -> {$url}\n";
    } catch (Exception $e) {
        echo "✗ {$route} - Route not found!\n";
    }
}

echo "\n=== Menu Config Check ===\n";
$config = config('adminlte.menu');
$found = false;

foreach ($config as $item) {
    if (isset($item['text']) && $item['text'] === 'Ad Management') {
        $found = true;
        echo "✓ Ad Management menu found in config\n";
        echo "  Icon: {$item['icon']}\n";
        echo "  Can: " . ($item['can'] ?? 'none') . "\n";
        echo "  Submenu items: " . count($item['submenu'] ?? []) . "\n";
        break;
    }
}

if (!$found) {
    echo "✗ Ad Management menu NOT found in config\n";
}

echo "\n=== Expected Result ===\n";
if ($user->isAdmin() && Gate::forUser($user)->allows('admin') && $found) {
    echo "✅ Menu SHOULD be visible for this user\n";
} else {
    echo "❌ Menu will NOT be visible\n";
    echo "Reasons:\n";
    if (!$user->isAdmin()) echo "  - User is not admin\n";
    if (!Gate::forUser($user)->allows('admin')) echo "  - Gate 'admin' denied\n";
    if (!$found) echo "  - Menu not in config\n";
}

echo "\n=== Action Required ===\n";
echo "1. Logout from admin panel\n";
echo "2. Login again as: {$user->email}\n";
echo "3. Look for 'Content Management' section in sidebar\n";
echo "4. Expand 'Ad Management' to see submenu\n";
