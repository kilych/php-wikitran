<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;
use Wikitran\Translator;

class Migration extends Db
{
    public static $server;

    public static function run($pdo = false, $server = 'sqlite', $clear = false)
    {
        error_log(__METHOD__);
        self::$server = $server;
        if ($pdo instanceof \PDO || $pdo = self::makeConnection(true)) {
            try {
                if ($clear) {
                    self::clear($pdo);
                }
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
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS $table;");
        }
    }

    public static function createTables(\PDO $pdo)
    {
        // $server = self::$server;
        $path = dirname(__DIR__, 2) . '/config/schema_' . self::$server . '.sql';
        error_log(__METHOD__ . " Schema found at $path");
        if (file_exists($path)) {
            $sql = file_get_contents($path);
        } else {
            throw new \Exception("$path doesn't exist");
        }
        $pdo->exec($sql);
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

        $sql1 = 'INSERT INTO lang (lang_code) VALUES (?);';
        $sql2 = ' INSERT INTO lang_name (lang_code, name, name_lang)'
              . ' VALUES (?, ?, \'en\');';
        $rows = 0;


        $st1 = $pdo->prepare($sql1);
        $st2 = $pdo->prepare($sql2);

        foreach (Translator::getLangs() as $code => $name) {
            $st1->execute([$code]);
            $st2->execute([$code, $name]);
            $rows += $st1->rowCount() + $st2->rowCount();
        }

        return $rows;
    }
}
