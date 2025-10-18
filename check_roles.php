<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "Checking user roles:\n";
echo "===================\n";

$users = User::whereIn('id', [16, 9])->get();

foreach ($users as $user) {
    echo "\nUser ID {$user->id} - {$user->name} ({$user->email}):\n";
    echo "  Old role column: " . ($user->role ?? 'NULL') . "\n";
    echo "  Spatie roles: " . $user->getRoleNames()->join(', ') . "\n";
    echo "  hasRole('travel_agent'): " . ($user->hasRole('travel_agent') ? 'YES' : 'NO') . "\n";
    echo "  Can see menu: " . (auth()->check() && auth()->user()->hasRole('travel_agent') ? 'YES' : 'NO (not logged in)') . "\n";
}

echo "\nAvailable Spatie roles:\n";
$roles = \Spatie\Permission\Models\Role::pluck('name')->toArray();
echo "  " . implode(', ', $roles) . "\n";