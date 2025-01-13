<?php
namespace Models;
use PDO;
use Dotenv\Dotenv;

class GetPDO {
    public static $bdd = false;

    public static function init() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }

    public static function getpdo() :PDO 
    {
        if (self::$bdd === false) {
            self::init();
            self::$bdd = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';charset=utf8;dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }
        return self::$bdd;
    }
}
?>
