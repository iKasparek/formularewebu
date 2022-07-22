<?php

//require_once(__DIR__ . '/file/spojeni.php');

class Databaze
{

    private static $dbspojeni;

    private static $dbnastaveni = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    );

    public static function pripoj($dbhost, $dbuzivatel, $dbheslo, $dbdatabaze)
    {
        if (!isset(self::$dbspojeni))
        {
            self::$dbspojeni = @new PDO(
                "mysql:host=$dbhost;dbname=$dbdatabaze",
                $dbuzivatel,
                $dbheslo,
                self::$dbnastaveni
            );
        }
        return self::$dbspojeni;
    }

    public static function dotaz($dbsql, $dbparametry = array())
    {
        $dbdotaz = self::$dbspojeni->prepare($dbsql);
        $dbdotaz->execute($dbparametry);
        return $dbdotaz;
    }

    public static function getLastId()
    {
        return self::$dbspojeni->lastInsertId();
    }

}