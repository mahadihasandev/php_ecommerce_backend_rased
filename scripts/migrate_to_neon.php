<?php
/**
 * Data Migration Script: Local MariaDB -> Neon PostgreSQL
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$neonUrl = $argv[1] ?? env('NEON_DATABASE_URL');

if (!$neonUrl) {
    echo "Usage: php migrate_to_neon.php <NEON_DATABASE_URL>\n";
    exit(1);
}

// Dynamically configure Neon connection
config([
    'database.connections.neon' => [
        'driver' => 'pgsql',
        'url' => $neonUrl,
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'require',
        'options' => [
            \PDO::ATTR_EMULATE_PREPARES => true,
        ],
    ]
]);

echo "Testing connection to Neon PostgreSQL...\n";
try {
    $neonPdo = DB::connection('neon')->getPdo();
    echo "Successfully connected to Neon!\n";
} catch (\Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Order of migration to respect foreign keys
$tables = [
    'users',
    'personal_access_tokens',
    'categories',
    'brands',
    'products',
    'category_product',
    'banners',
    'authors',
    'blog_categories',
    'blogs',
    'blog_blog_category',
    'addresses',
    'orders',
    'order_items',
];

echo "\nCopying data from local MariaDB to Neon...\n";

foreach ($tables as $table) {
    if (!Schema::connection('mysql')->hasTable($table)) {
        continue;
    }
    if (!Schema::connection('neon')->hasTable($table)) {
        echo "Table '$table' does not exist on Neon yet. Skipping.\n";
        continue;
    }

    $rows = DB::connection('mysql')->table($table)->get()->map(function ($item) {
        return (array) $item;
    })->toArray();

    $count = count($rows);
    if ($count === 0) {
        echo "  - $table: 0 records\n";
        continue;
    }

    // Disable triggers / FK checks if needed or insert in batches
    DB::connection('neon')->table($table)->delete();
    
    // Insert in chunks of 50
    $chunks = array_chunk($rows, 50);
    foreach ($chunks as $chunk) {
        DB::connection('neon')->table($table)->insert($chunk);
    }

    echo "  ✓ $table: $count records migrated\n";

    // Reset sequence for PostgreSQL if table has auto-increment 'id'
    try {
        $maxId = DB::connection('neon')->table($table)->max('id');
        if ($maxId) {
            DB::connection('neon')->statement("SELECT setval(pg_get_serial_sequence('$table', 'id'), $maxId)");
        }
    } catch (\Exception $e) {
        // Table might not have 'id' sequence or composite key
    }
}

echo "\nData migration completed successfully!\n";
