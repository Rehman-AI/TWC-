<?php
/**
 * TWC Content Studio — Web Migration Runner
 * 
 * Usage: https://your-site.com/migrate.php?token=twc_migrate_2026
 * ⚠️  DELETE THIS FILE AFTER RUNNING IT!
 */

$token = 'twc_migrate_2026';
if (!isset($_GET['token']) || $_GET['token'] !== $token) {
    http_response_code(403);
    die('Forbidden. Use ?token=twc_migrate_2026');
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/html; charset=utf-8');
echo '<html><body style="font-family:monospace;background:#0f0f23;color:#00ff41;padding:30px;">';
echo '<h1>🚀 TWC Content Studio — Setup</h1>';
echo '<pre>';

// Step 1: Migrations
echo "\n📦 Running migrations...\n";
$kernel->call('migrate', ['--force' => true]);
echo $kernel->output();

// Step 2: Seed
echo "\n🌱 Running seeders...\n";
$kernel->call('db:seed', ['--force' => true]);
echo $kernel->output();

// Step 3: Clear caches
echo "\n🧹 Clearing caches...\n";
$kernel->call('config:clear');
echo $kernel->output();
$kernel->call('route:clear');
echo $kernel->output();
$kernel->call('view:clear');
echo $kernel->output();

// Step 4: Create storage link
echo "\n🔗 Creating storage link...\n";
$kernel->call('storage:link', ['--force' => true]);
echo $kernel->output();

echo "\n" . str_repeat('=', 50);
echo "\n✅ ALL DONE!";
echo "\n\n👤 Login: boss@twc.com / password";
echo "\n\n⚠️  DELETE THIS FILE NOW (migrate.php)";
echo '</pre></body></html>';