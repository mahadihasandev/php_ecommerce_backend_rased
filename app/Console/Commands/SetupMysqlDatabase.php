<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class SetupMysqlDatabase extends Command
{
    protected $signature = 'db:setup-mysql';
    protected $description = 'Ensure MySQL database exists, test credentials, and migrate/seed data';

    public function handle(): int
    {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', 'shop_ecommerce');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', 'Arnob@1234');

        $this->info("Connecting to MySQL server at {$host}:{$port} as '{$username}'...");

        try {
            $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $this->info("MySQL connection established successfully.");

            // Create database if not exists
            $this->info("Creating database '{$database}' if it doesn't already exist...");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database '{$database}' is ready.");

            // Run migrations and seeds
            $this->info("Running migrations on MySQL database...");
            $this->call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);

            $this->info("MySQL setup complete! All tables and seeders are in place.");
            return Command::SUCCESS;
        } catch (PDOException $e) {
            $this->error("Failed to connect to MySQL: " . $e->getMessage());
            $this->warn("Please ensure your MySQL server (XAMPP / Laragon / MySQL Service) is running on port {$port}.");
            return Command::FAILURE;
        }
    }
}
