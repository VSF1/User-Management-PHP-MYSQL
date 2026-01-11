<?php

include('includes/config.php');
require_once 'includes/User.php';

// 1. Restrict to Command Line Interface (CLI) only
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access Forbidden: This script can only be executed from the command line.');
}

// 2. Check environment (Prevent execution in production)
$appEnv = getenv('APP_ENV') ?: 'production';
if ($appEnv !== 'development' && $appEnv !== 'local') {
    exit("Error: Seeding is not allowed in '$appEnv' environment. Set APP_ENV=development to allow.\n");
}

$userObj = new User($dbh);

// Dummy user data
$users = [
    [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'P@$$wOrd',
        'gender' => 'Male',
        'mobile' => '1234567890',
        'designation' => 'Developer',
        'image' => '',
        'role' => 'admin'
    ],
    [
        'name' => 'Jane Smith',
        'email' => 'jane.smith@example.com',
        'password' => 'Pa$$wOrd',
        'gender' => 'Female',
        'mobile' => '9876543210',
        'designation' => 'Designer',
        'image' => '',
        'role' => 'user'
    ],
];

foreach ($users as $user) {
    $name = $user['name'];
    $email = $user['email'];
    $password = $user['password'];
    $gender = $user['gender'];
    $mobile = $user['mobile'];
    $designation = $user['designation'];
    $image = $user['image'];
    $role = $user['role'];

    $lastInsertId = $userObj->register($name, $email, $password, $gender, $mobile, $designation, $image, $role);

    if ($lastInsertId) {
        echo "User $name created successfully with ID: $lastInsertId\n";
    } else {
        echo "Failed to create user $name\n";
    }
}