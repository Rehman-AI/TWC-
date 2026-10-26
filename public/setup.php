<?php
/**
 * TWC Content Studio — One-time Setup Script
 * Run this ONCE after uploading to InfinityFree, then DELETE this file!
 */

// Security: only allow if a secret token is provided
$secret = 'twc_setup_2025';
if (($_GET['key'] ?? '') !== $secret) {
    die('Access denied. Use ?key=' . $secret);
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>TWC Content Studio Setup</h1><pre>";

// Generate app key
echo "=== Generating App Key ===\n";
$kernel->call('key:generate', ['--force' => true]);
echo $kernel->output();

// Run migrations
echo "\n=== Running Migrations ===\n";
$kernel->call('migrate', ['--force' => true]);
echo $kernel->output();

// Seed database
echo "\n=== Seeding Database ===\n";
$kernel->call('db:seed', ['--force' => true]);
echo $kernel->output();

// Clear caches
echo "\n=== Clearing Caches ===\n";
$kernel->call('config:clear');
$kernel->call('cache:clear');
$kernel->call('view:clear');

echo "\n\n=== DONE! ===\n";
echo "Login at: " . config('app.url') . "/admin\n";
echo "Boss: boss@twc.com / password\n";
echo "\n⚠️  DELETE THIS FILE NOW! (setup.php)\n";
echo "</pre>";