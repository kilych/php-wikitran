<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;
use Wikitran\Translator;

class Migration extends Db
{
    const SERVERS = ['sqlite', 'mysql'];
    public static $server;

    public static function run(\PDO $pdo, $server = 'sqlite')
    {
        error_log(__METHOD__);
        self::$server = (in_array($server, self::SERVERS)) ? $server : 'sqlite';
        try {
            self::createTables($pdo);
            self::addSource($pdo);
            self::addLangs($pdo);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
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
            error_log($e->getMessage());
        }
    }

    protected static function createTables(\PDO $pdo)
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

    protected static function addSource(\PDO $pdo)
    {
        if (self::isEmptyTable($pdo, 'term_source')) {
            error_log(__METHOD__ . ' Adding term source');
            $sql = 'INSERT INTO term_source (source_id, source) VALUES (1, \'Wikipedia\');';
            $rows = $pdo->exec($sql);
            return $rows;
        }
    }

    protected static function addLangs(\PDO $pdo)
    {
        $rows = 0;

        if (self::isEmptyTable($pdo, 'lang')) {
            error_log(__METHOD__ . ' Adding langs');

            $sqlLang = 'INSERT INTO lang (lang_code) VALUES (?);';
            $sqlLangName = ' INSERT INTO lang_name (lang_code, name, name_lang)'
                         . ' VALUES (?, ?, \'en\');';

            $stLang = $pdo->prepare($sqlLang);
            $stLangName = $pdo->prepare($sqlLangName);

            $pdo->beginTransaction(); // faster: from ~45 to 1 sec

            foreach (Translator::getLangs() as $code => $name) {
                $stLang->execute([$code]);
                $stLangName->execute([$code, $name]);
                $rows += $stLang->rowCount() + $stLangName->rowCount();
            }

            $pdo->commit();
        }

        return $rows;
    }
}
