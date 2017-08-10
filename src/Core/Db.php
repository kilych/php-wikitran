<?php

namespace Wikitran\Core;

class Db
{
    protected $pdo;             // \PDO object or false

    public function __construct($pdo = null, array $dbconfig = [])
    {
        if ($pdo instanceof \PDO) {
            $this->pdo = $pdo;
        } else {
            $this->connect($dbconfig);
        }
    }

    public function connect(array $config = [])
    {
        if (empty($config)) {
            $this->pdo = self::connectBuiltIn();
        } elseif (array_key_exists('server', $config)
                  && $config['server'] === 'sqlite') {
            if (array_key_exists('file', $config)) {
                $this->pdo = self::connectSQLite($config);
            } else {
                throw new Exception('Should specify db file to connect SQLite');
            }
        } elseif (array_key_exists('server', $config)
                  && $config['server'] === 'mysql') {
            if (array_key_exists('db', $config)
                && array_key_exists('user', $config)) {
                $this->pdo = self::connectMySQL($config);
            } else {
                throw new Exception('Should specify at least db and user to connect MySQL');
            }
        } else {
            error_log(__METHOD__ . ' Db connection is not set');
        }
    }

    public function connected()
    {
        return $this->pdo instanceof \PDO;
    }

    public function getConnection()
    {
        if ($this->connected()) {
            return $this->pdo;
        } else {
            return false;
        }
    }

    protected static function getBuiltInFilePath()
    {
        return dirname(__DIR__, 2) . '/db/cache.sqlite';
    }

    public static function connectBuiltIn()
    {
        $file = self::getBuiltInFilePath();
        if (is_file($file)) {
            return self::connectSQLite(['file' => $file]);
        }
        error_log(__METHOD__ . " Db file not found at $file");
        return false;
    }

    public static function connectSQLite($config)
    {
        $default = ['createFile' => false];
        $config = array_merge($default, $config);
        extract($config);
        if (is_file($file) || ($createFile && self::createDbFile($file))) {
            try {
                $pdo = new \PDO("sqlite:$file");
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->exec('PRAGMA foreign_keys = ON;');
                error_log(__METHOD__ . " at $file");
                return $pdo;
            } catch (\Exception $e) {
                error_log(__METHOD__ . ' ' . $e->getMessage());
                return false;
            }
        }
        error_log(__METHOD__ . " Db file not found and not created at $file");
        return false;
    }

    public static function connectMySQL(array $config)
    {
        $default = [
            'password' => '',
            'host' => 'localhost',
            'port' => '',
            'charset' => 'utf8'
        ];
        $config = array_merge($default, $config);
        extract($config);
        try {
            $pdo = new \PDO(
                "mysql:host=$host;port=$port;dbname=$db;charset=$charset",
                $user,
                $password,
                [\PDO::ATTR_EMULATE_PREPARES => false, // Really need ???
                 \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            error_log(__METHOD__);
            return $pdo;
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' ' . $e->getMessage());
            return false;
        }
    }

    public static function createDbFile($file)
    {
        $dir = dirname($file);
        if ((is_dir($dir) || mkdir($dir)) && touch($file)) {
            error_log(__METHOD__ . " at $file");
            return $file;
        } else {
            error_log(__METHOD__ . " failed at $file");
            return false;
        }
    }
}
