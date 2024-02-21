<?php

namespace App\Core;

use PDOException;
use App\Core\MyPDO;

class DatabaseManager
{
    protected $db;

    public function __construct(MyPDO $db)
    {
        $this->db = $db;
    }

    /**
     * Check if the database exists. Also sets as default database to use.
     *
     * @param string $databaseName The name of the database to check.
     * @throws PDOException description of exception
     * @return bool
     */
    public function databaseExists(string $databaseName): bool
    {
        try {
            $this->db->run("USE $databaseName");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create a new database if it does not already exist. Also sets as default database to use.
     *
     * @param string $databaseName The name of the database to create.
     * @return void
     */
    public function createDatabase(string $databaseName): void
    {
        if (!$this->databaseExists($databaseName)) {
            $this->db->run("CREATE DATABASE $databaseName");
            $this->db->run("USE $databaseName");
        }
    }

    /**
     *! DANGER: Drops the specified database if it exists. IRREVERSIBLE OPERATION!!!
     *
     * @param string $databaseName The name of the database to be dropped
     * @return void
     */
    public function dropDatabase(string $databaseName): void
    {
        if ($this->databaseExists($databaseName)) {
            $this->db->run("DROP DATABASE $databaseName");
        }
    }


    /**
     * Check if a table exists in the database.
     *
     * @param string $tableName The name of the table to check.
     * @return bool
     */
    public function tableExists(string $tableName): bool
    {
        $sql = "SHOW TABLES LIKE '{$tableName}'";
        $stmt = $this->db->run($sql)->fetch();

        return $stmt !== false;
    }

    /**
     * Create a new table in the database based on the provided table schema.
     *
     * @param array $table The table schema containing table name, columns, indexes, foreign keys, and options.
     * @return void
     * format: 'table_name' => [
     *                            'columns' => [
     *                                             'column_name_1' => 'column_type',
     *                                             'column_name_2' => 'column_type',
     *                                             ...
     *                                         ],
     *                            'indexes' => [
     *                                             'index_name_1' => 'column_name_1',
     *                                             'index_name_2' => 'column_name_2',
     *                                             ...
     *                                         ],
     *                            'foreign_keys' => [
     *                                                 'foreign_key_name_1' => ['table' => 'source_table_name_1', 'column' => 'source_column_name_1'],
     *                                                 'foreign_key_name_2' => ['table' => 'source_table_name_2', 'column' => 'source_column_name_2'],
     *                                                 ...
     *                                              ],
     *                            'options' => [
     *                                             'option_name_1' => 'option_value',
     *                                             'option_name_2' => 'option_value',
     *                                             ...
     *                                         ],
     *                          ]
     */
    public function createTable(array $table): void
    {
        $tableName = key($table);
        $tableData = $table[$tableName];

        if (!$this->tableExists($tableName)) {
            $sql = "CREATE TABLE `{$tableName}` (";
            foreach ($tableData['columns'] as $name => $type) {
                $sql .= "`{$name}` {$type},";
            }

            // Add indexes
            if (isset($tableData['indexes'])) {
                foreach ($tableData['indexes'] as $indexName => $column) {
                    $sql .= "KEY `$indexName` (`$column`),";
                }
            }

            // Add foreign keys
            if (isset($tableData['foreign_keys'])) {
                foreach ($tableData['foreign_keys'] as $column => $foreignKey) {
                    $on_update = isset($foreignKey['on_update']) ? strtoupper($foreignKey['on_update']) : 'NO ACTION';
                    $on_delete = isset($foreignKey['on_delete']) ? strtoupper($foreignKey['on_delete']) : 'NO ACTION';
                    $sql .= "CONSTRAINT `FK_{$tableName}_{$column}` FOREIGN KEY (`$column`) REFERENCES `{$foreignKey['table']}`(`{$foreignKey['column']}`) ON UPDATE {$on_update} ON DELETE {$on_delete},";
                }
            }

            $sql = rtrim($sql, ',') . ")";

            // Add options
            if (isset($tableData['options'])) {
                foreach ($tableData['options'] as $optionName => $value) {
                    $sql .= " $optionName = $value";
                }
            }

            // run the query
            $this->db->run($sql);
        }
    }

    /**
     *! DANGER: Drops the table if it exists. IRREVERSIBLE OPERATION!!!
     *
     * @param string $tableName The name of the table to be dropped
     * @throws Some_Exception_Class description of exception
     * @return void
     */
    public function dropTable(string $tableName): void
    {
        if ($this->tableExists($tableName)) {
            $this->db->run("DROP TABLE $tableName");
        }
    }
}
