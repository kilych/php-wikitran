<?php

namespace Wikitran\core;

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

    protected static function makeConnection($createFile = false)
    {
        $dirpath = self::getCacheDirPath();
        $filepath = self::getCacheFilePath();
        if (file_exists($filepath)) {
            error_log(__METHOD__ . " Db file found at $filepath");
        } elseif ($createFile && (is_dir($dirpath) || mkdir($dirpath)) && touch($filepath)) {
            error_log(__METHOD__ . " Db file created at $filepath");
        } else {
            error_log(__METHOD__ . " Db file not found and can not be created at $filepath");
            return false;
        }
        try {
            $pdo = new \PDO("sqlite:$filepath");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA foreign_keys = ON;');
            return $pdo;
        } catch (\PDOException $e) {
            error_log(__METHOD__ . $e->getMessage());
            return false;
        }
    }
}
