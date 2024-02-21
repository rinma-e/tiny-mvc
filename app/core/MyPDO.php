<?php

namespace App\Core;

use PDO;

class MyPDO extends PDO
{
    public function __construct($dsn = NULL, $username = DB_USER, $password = DB_PASSWORD, $options = NULL)
    {
        $dsn ?? $dsn = DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options ?? $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        parent::__construct($dsn, $username, $password, $options);
    }

    public function run($sql, $arguments = NULL)
    {
        if (!$arguments) {
            return $this->query($sql);
        }

        $statement = $this->prepare($sql);

        // check if $arguments is an array of arrays, if so start a transaction
        if (isMultiArray($arguments)) {
            $this->beginTransaction();
            // loop over the data array
            foreach ($arguments as $row) {
                $statement->execute($row);
            }
            $this->commit();
        } else {
            // $statement = $this->prepare($sql);
            $statement->execute($arguments);
        }

        return $statement;
    }
}
