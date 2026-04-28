<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$users = User::all();
$output = "Name | Username | Password\n";
$output .= "---------------------------\n";

foreach ($users as $user) {
    // Update password in DB while we are at it
    $user->password = Hash::make($user->username);
    $user->save();
    
    $output .= "{$user->name} | {$user->username} | {$user->username}\n";
}

file_put_contents('users_credentials.txt', $output);
echo "Credentials file generated: users_credentials.txt\n";
echo "All passwords have been synced with usernames.\n";
