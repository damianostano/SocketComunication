<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 22/08/2017
 * Time: 12:06
 */
class DB
{
    protected $log;
    protected $db; // handle of the db connexion
    private static $instance;

    private function __construct()
    {
        $this->log = Logger::getLogger("db.log");
        // building data source name from config
        $config = parse_ini_file(ROOT.'\config\config.ini');
        try {
            $this->db = new PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'], $config['user'], $config['pwd']);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e) {
            die('Attenzione: '.$e->getMessage());
        }
    }

    public static function get()
    {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    public static function getDb(){
        return DB::get()->db;
    }

}

$db = DB::getDb();