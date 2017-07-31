<?php

namespace wikitranslator\wikitranslator\core;

class Db {
    protected static function getPathToDb() {
        return dirname(__DIR__, 2) . '/data/cache.sqlite';
    }

    protected static function makeConnection() {
        $path = self::getPathToDb();
        if (file_exists($path)) {
            error_log(__METHOD__ . " Db file found at $path");
        } else {
            error_log(__METHOD__ . " Db file not found at $path");
            return false;
        }
        try {
            $pdo = new \PDO("sqlite:$path");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA foreign_keys = ON;');
            return $pdo;
        } catch (\PDOException $e) {
            error_log(__METHOD__ . $e->getMessage());
            return false;
        }
    }
}
