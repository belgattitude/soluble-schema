<?php

class SolubleTestFactories {

    /**
     * @return string
     */
    public static function getCachePath() {
        $cache_dir = $_SERVER['PHPUNIT_CACHE_DIR'];
        if (!preg_match('/^\//', $cache_dir)) {
            $cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $cache_dir;
        }
        return $cache_dir;
    }
    
    /**
     * 
     * @param string $type
     * @return PDO|Mysqli
     */
    public static function getDbConnection($type, $charset="UTF8")
    {
        $hostname = $_SERVER['MYSQL_HOSTNAME'];
        $username = $_SERVER['MYSQL_USERNAME'];
        $password = $_SERVER['MYSQL_PASSWORD'];
        $database = $_SERVER['MYSQL_DATABASE'];
        switch ($type) {
            case 'pdo:mysql':
                $options = array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset",
                );                 
                $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password, $options);
                break;
            case 'mysqli' :
                $conn = new \mysqli($hostname,$username,$password,$database);
                 $conn->set_charset($charset);
                break;
            default:
                throw new \Exception(__METHOD__ . " Unsupported driver type ($type)");
        }
        return $conn;
    }
    
    public static function getDatabaseName($type) {
        $name = false;
        switch ($type) {
            case 'pdo:mysql':
            case 'mysqli' :
                $name = $_SERVER['MYSQL_DATABASE'];
                break;
            default:
                throw new \Exception(__METHOD__ . " Unsupported driver type ($type)");
        }
        return $name;
    }

}
