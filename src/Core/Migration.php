<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;
use Wikitran\Translator;

class Migration extends Db
{
    const SERVERS = ['sqlite', 'mysql'];
    public static $server;

    public static function run($pdo = false, $server = 'sqlite')
    {
        error_log(__METHOD__);
        self::$server = (in_array($server, self::SERVERS)) ? $server : 'sqlite';
        if ($pdo instanceof \PDO || $pdo = self::connectBuiltIn(true)) {
            try {
                self::createTables($pdo);
                self::addSource($pdo);
                self::addLangs($pdo);
            } catch (\PDOException $e) {
                error_log(__METHOD__ . ' ' . $e->getMessage());
            }
        } else {
            error_log(__METHOD__ . ' No Db connection');
        }
    }

    public static function clear(\PDO $pdo)
    {
        error_log(__METHOD__);
        $tables = ['term_relation', 'translation', 'term', 'term_source', 'lang_name', 'lang'];
        try {
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS $table;");
            }
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' ' . $e->getMessage());
        }
    }

    public static function createTables(\PDO $pdo)
    {
        $server = self::$server;
        $path = dirname(__DIR__, 2) . "/config/schema_$server.sql";
        if (file_exists($path)) {
            error_log(__METHOD__ . " Schema found at $path");
            $sql = file_get_contents($path);
            $pdo->exec($sql);
        } else {
            throw new \Exception("$path doesn't exist");
        }
    }

    public static function addSource(\PDO $pdo)
    {
        error_log(__METHOD__);
        $sql = 'INSERT INTO term_source (source_id, source) VALUES (1, \'Wikipedia\');';
        $rows = $pdo->exec($sql);
        return $rows;
    }

    public static function addLangs(\PDO $pdo)
    {
        error_log(__METHOD__);

        $sqlLang = 'INSERT INTO lang (lang_code) VALUES (?);';
        $sqlLangName = ' INSERT INTO lang_name (lang_code, name, name_lang)'
                     . ' VALUES (?, ?, \'en\');';
        $rows = 0;


        $st1 = $pdo->prepare($sqlLang);
        $st2 = $pdo->prepare($sqlLangName);

        foreach (Translator::getLangs() as $code => $name) {
            $st1->execute([$code]);
            $st2->execute([$code, $name]);
            $rows += $st1->rowCount() + $st2->rowCount();
        }

        return $rows;
    }
}
