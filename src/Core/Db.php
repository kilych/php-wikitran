<?php

namespace Wikitran\Core;

class Db
{
    protected static function getCacheDirPath()
    {
        return dirname(__DIR__, 2) . '/data';
    }

    protected static function getCacheFilePath()
    {
        return self::getCacheDirPath() . '/cache.sqlite';
    }

    public static function connectBuiltIn($createFile = false)
    {
        $dir = self::getCacheDirPath();
        $file = self::getCacheFilePath();
        if (file_exists($file)) {
            error_log(__METHOD__ . " Db file found at $file");
        } elseif ($createFile && (is_dir($dir) || mkdir($dir)) && touch($file)) {
            error_log(__METHOD__ . " Db file created at $file");
        } else {
            error_log(__METHOD__ . " Db file not found and can not be created at $file");
            return false;
        }
        return self::connectSQLite($file);
    }

    public static function connectSQLite($file)
    {
        try {
            $pdo = new \PDO("sqlite:$file");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA foreign_keys = ON;');
            return $pdo;
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' ' . $e->getMessage());
            return false;
        }
    }

    public static function connectMySQL($host, $db, $user, $password, $port = '', $charset = 'utf8')
    {
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
}
