<?php

namespace Wikitran\Core;

class Db
{
    protected static function getBuiltInDirname()
    {
        return dirname(__DIR__, 2) . '/data';
    }

    protected static function getBuiltInBasename()
    {
        return '/cache.sqlite';
    }

    public static function connectBuiltIn($createFile = false)
    {
        error_log(__METHOD__);
        $dir = self::getBuiltInDirname();
        $file = $dir . self::getBuiltInBasename();
        if (file_exists($file) || ($createFile && self::createDbFile($dir))) {
            error_log("Db file: $file");
            return self::connectSQLite($file);
        }
        error_log("Db file not found and can not be created at $file");
        return false;
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

    public static function createDbFile($dir)
    {
        error_log(__METHOD__);
        $file = $dir . self::getBuiltInBasename();
        if ((is_dir($dir) || mkdir($dir)) && touch($file)) {
            return $file;
        } else {
            return false;
        }
    }
}
