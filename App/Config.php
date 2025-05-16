<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 7.0+
 */
class Config
{
    public static $DB_HOST;
    public static $DB_NAME;
    public static $DB_USER;
    public static $DB_PASSWORD;
    public static $SHOW_ERRORS;

    public static function init()
    {
        // Récupère les variables d'environnement, avec fallback par défaut
        self::$DB_HOST = getenv('DB_HOST') ?: 'localhost';
        self::$DB_NAME = getenv('DB_NAME') ?: 'db_name';
        self::$DB_USER = getenv('DB_USER') ?: 'db_user';
        self::$DB_PASSWORD = getenv('DB_PASSWORD') ?: 'db_password';

        // Optionnel: active ou non l'affichage des erreurs (exemple: SHOW_ERRORS=true dans l'env)
        $showErrors = getenv('SHOW_ERRORS');
        self::$SHOW_ERRORS = ($showErrors !== false) ? filter_var($showErrors, FILTER_VALIDATE_BOOLEAN) : true;
    }
}
