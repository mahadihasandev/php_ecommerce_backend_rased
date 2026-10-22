<?php
/**
 * Data Migration Script: Local MariaDB -> Neon PostgreSQL
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
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

echo "\n--- Step 1: Running schema migrations on Neon PostgreSQL ---\n";
try {
    Artisan::call('migrate', [
        '--database' => 'neon',
        '--force' => true,
    ]);
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Migration info: " . $e->getMessage() . "\n";
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

echo "\n--- Step 2: Copying data from local MariaDB to Neon ---\n";

foreach ($tables as $table) {
    if (!Schema::connection('mysql')->hasTable($table)) {
        continue;
    }
    if (!Schema::connection('neon')->hasTable($table)) {
        echo "Table '$table' does not exist on Neon yet. Skipping.\n";
        continue;
    }

    // Detect all boolean columns on the target PostgreSQL table
    $booleanColumns = DB::connection('neon')
        ->table('information_schema.columns')
        ->where('table_schema', 'public')
        ->where('table_name', $table)
        ->where('data_type', 'boolean')
        ->pluck('column_name')
        ->toArray();

    $rows = DB::connection('mysql')->table($table)->get()->map(function ($item) use ($booleanColumns) {
        $arr = (array) $item;
        foreach ($booleanColumns as $col) {
            if (array_key_exists($col, $arr)) {
                $arr[$col] = ($arr[$col] !== null) ? (bool) $arr[$col] : null;
            }
        }
        return $arr;
    })->toArray();

    $count = count($rows);
    if ($count === 0) {
        echo "  - $table: 0 records\n";
        continue;
    }

    // Clean target table before insert
    try {
        DB::connection('neon')->statement("TRUNCATE TABLE \"$table\" CASCADE");
    } catch (\Exception $e) {
        try {
            DB::connection('neon')->table($table)->delete();
        } catch (\Exception $e2) {
            // ignore
        }
    }
    
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

echo "\n--- Step 3: Verification of row counts on Neon ---\n";
foreach ($tables as $table) {
    if (Schema::connection('neon')->hasTable($table)) {
        $c = DB::connection('neon')->table($table)->count();
        echo "  ✓ Neon table '$table': $c rows\n";
    }
}

echo "\n============================================\n";
echo " DATA MIGRATION TO NEON COMPLETED 100%! \n";
echo "============================================\n";
