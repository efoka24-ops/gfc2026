<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check admin user
$user = App\Models\User::where('email', 'admin@gfc.local')->first();

if ($user) {
    echo "User found!\n";
    echo "Email: " . $user->email . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Active: " . ($user->active ? 'Yes' : 'No') . "\n";
    
    // Test password
    if (Hash::check('GFC@admin2026!', $user->password)) {
        echo "Password: CORRECT\n";
    } else {
        echo "Password: INCORRECT\n";
    }
} else {
    echo "User NOT found!\n";
}
