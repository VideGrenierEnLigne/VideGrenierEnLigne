<?php

namespace Core;

use PDO;
use PDOException;
use App\Config;

/**
 * Base model
 *
 * PHP version 7.0+
 */
abstract class Model
{
    /**
     * Get the PDO database connection
     *
     * @return PDO
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            Config::init();

            $dsn = 'mysql:host=' . Config::$DB_HOST . ';dbname=' . Config::$DB_NAME . ';charset=utf8mb4';

            try {
                $db = new PDO($dsn, Config::$DB_USER, Config::$DB_PASSWORD);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }

        return $db;
    }
}
