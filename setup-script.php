<?php
require_once 'app/config/config.php';
require_once 'app/core/DatabaseManager.php';
require_once 'app/core/MyPDO.php';

use App\Core\MyPDO;
use App\Core\DatabaseManager;

if (DB_HOST === '' || DB_USER === '' || DB_NAME === '') {
    echo "Please set your database credentials in 'app/config/config.php'";
    exit;
}

$dbManager = new DatabaseManager(new MyPDO);

$dbName = DB_NAME;

/**
 * define table structure as array
 */

$table['users'] = [
    "columns" => [
        // "column_name" => "column_type",
        "id" => "BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY",
        "name" => "VARCHAR(100) NOT NULL",
        "lastname" => "VARCHAR(100) NOT NULL",
        "email" => "VARCHAR(100) NOT NULL UNIQUE",
        "password" => "CHAR(255) NOT NULL",
        "roll_id" => "INT(10) NOT NULL DEFAULT '0'",
        "avatar" => "VARCHAR(255) NULL DEFAULT NULL",
        "created" => "DATETIME NOT NULL DEFAULT NOW()"
    ],
    "indexes" => [
        // "index_name" => "column_name",
        "name" => "name",
        "lastname" => "lastname",
        "roll" => "roll_id",
        "created" => "created"
    ],
    "foreign_keys" => [
        // 'foreign_key_name' => ['source_table_name', 'column_name'],
    ],
    "options" => [
        "COLLATE" => "utf8mb4_0900_ai_ci",
        "ENGINE" => "InnoDB",
    ]
];

$table['users_config'] = [
    "columns" => [
        // "column_name" => "column_type",
        "user_id" => "BIGINT(19) UNSIGNED NOT NULL PRIMARY KEY",
        "config" => "JSON NOT NULL",
        "added" => "DATETIME NOT NULL DEFAULT NOW()",
        "updated" => "DATETIME NOT NULL DEFAULT NOW() ON UPDATE NOW()",
    ],
    "indexes" => [
        // "index_name" => "column_name",
    ],
    "foreign_keys" => [
        // 'foreign_key_name' => ['source_table_name', 'column_name'],
        'user_id' => [
            'table' => 'users',
            'column' => 'id',
            'on_delete' => 'CASCADE',
            'on_update' => 'CASCADE',
        ],
    ],
    "options" => [
        "COLLATE" => "utf8mb4_0900_ai_ci",
        "ENGINE" => "InnoDB",
    ]
];

$table['users_remember_me_token'] = [
    "columns" => [
        // "column_name" => "column_type",
        "id" => "INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY",
        "selector" => "VARCHAR(255) NOT NULL DEFAULT '0'",
        "hashed_validator" => "VARCHAR(255) NOT NULL DEFAULT '0'",
        "expiry" => "DATETIME NOT NULL",
        "user_id" => "BIGINT(19) UNSIGNED NOT NULL",
    ],
    "indexes" => [
        // "index_name" => "column_name",
        "user_id" => "user_id",
    ],
    "foreign_keys" => [
        // 'foreign_key_name' => ['table' => 'source_table_name', 'column' => 'source_column_name'],
        'user_id' => [
            'table' => 'users',
            'column' => 'id',
            'on_delete' => 'CASCADE',
            'on_update' => 'CASCADE',
        ],
    ],
    "options" => [
        "COLLATE" => "utf8mb4_0900_ai_ci",
        "ENGINE" => "InnoDB",
    ]
];

if (!$dbManager->databaseExists($dbName)) {
    $input = readline('Database "' . $dbName . '" does not exist. Would you like to create it? (y/n): ');
    if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
        $dbManager->createDatabase($dbName);
        echo 'Database "' . $dbName . '" created successfully.' . PHP_EOL;
        echo ' ' . PHP_EOL;
    } else {
        echo ' ' . PHP_EOL;
    }
} else {
    echo 'Database "' . $dbName . '" already exists.' . PHP_EOL;
    $input = readline('Would you like to drop it and create a new one? WARNING: THIS CANNOT BE UNDONE (y/n): ');
    if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
        $dbManager->dropDatabase($dbName);
        $dbManager->createDatabase($dbName);
        echo 'Database "' . $dbName . '" created successfully.' . PHP_EOL;
        echo ' ' . PHP_EOL;
    } else {
        echo ' ' . PHP_EOL;
    }
}

if ($dbManager->databaseExists($dbName)) {
    foreach ($table as $name => $data) {
        if (!$dbManager->tableExists($name)) {
            $input = readline('Table "' . $name . '" does not exist. Would you like to create it? (y/n): ');
            if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
                $dbManager->createTable([$name => $data]);
                echo 'Table "' . $name . '" created successfully.' . PHP_EOL;
                echo ' ' . PHP_EOL;
            } else {
                echo ' ' . PHP_EOL;
            }
        } else {
            echo 'Table "' . $name . '" already exists.' . PHP_EOL;
            $input = readline('Would you like to drop it and create a new one? WARNING: THIS CANNOT BE UNDONE (y/n): ');
            if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
                $dbManager->dropTable($name);
                $dbManager->createTable([$name => $data]);
                echo 'Table "' . $name . '" created successfully.' . PHP_EOL;
                echo ' ' . PHP_EOL;
            } else {
                echo ' ' . PHP_EOL;
            }
        }
    }
    echo 'All done. Open your browser and navigate to where you installed TinyMVC.' . PHP_EOL;
}
