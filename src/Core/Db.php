<?php

namespace Wikitran\Core;

class Db
{
    const BUILTIN_BASENAME = 'cache.sqlite';
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
                $this->pdo = self::connectSQLite($config['file']);
            } else {
                throw new Exception('Should specify db file to connect SQLite');
            }
        } elseif (array_key_exists('server', $config)
                  && $config['server'] === 'mysql') {
            if (array_key_exists(['db', 'user'], $config)) {
                $this->pdo = self::connectMySQL($config);
            } else {
                throw new Exception('Should specify at least db and user to connect SQLite');
            }
        }
    }

    public function connected()
    {
        return $this->pdo instanceof \PDO;
    }

    protected static function getBuiltInDirname()
    {
        return dirname(__DIR__, 2) . '/db';
    }

    public static function connectBuiltIn($createFile = false)
    {
        $dir = self::getBuiltInDirname();
        $file = $dir . '/' . self::BUILTIN_BASENAME;
        if (is_file($file) || ($createFile && self::createDbFile($dir))) {
            return self::connectSQLite($file);
        }
        error_log(__METHOD__ . " Db file not found and can't be created at $file");
        return false;
    }

    public static function connectSQLite($file)
    {
        try {
            $pdo = new \PDO("sqlite:$file");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA foreign_keys = ON;');
            error_log(__METHOD__ . " Db file: $file");
            return $pdo;
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' ' . $e->getMessage());
            return false;
        }
    }

    public static function connectMySQL(array $config) {
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
            return $pdo;
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' ' . $e->getMessage());
            return false;
        }
    }

    public static function createDbFile($dir)
    {
        $file = $dir . '/' . self::BUILTIN_BASENAME;
        if ((is_dir($dir) || mkdir($dir)) && touch($file)) {
            error_log(__METHOD__ . " Db file: $file");
            return $file;
        } else {
            return false;
        }
    }
}
