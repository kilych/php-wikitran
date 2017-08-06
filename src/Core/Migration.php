<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;
use Wikitran\Translator;

class Migration extends Db
{
    const SERVERS = ['sqlite', 'mysql'];
    public static $server;

    public static function run(\PDO $pdo)
    {
        $server = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if (in_array($server, self::SERVERS)) {
            self::$server = $server;
        } else {
            error_log("Unsupported SQL server: $server. Use SQLite or MySQL instead.\nNothing to do.");
            exit(1);
        }
        try {
            self::createTables($pdo);
            self::addSource($pdo);
            self::addLangs($pdo);
        } catch (\Exception $e) {
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
        $schema = dirname(__DIR__, 2) . "/config/schema.php";
        $sql = '';
        if ($server === 'mysql') {
            // in this case MySQL adds "not null" implicitly and silently
            $specific = ' auto_increment';
        } else {
            $specific = '';
        }
        if (is_file($schema)) {
            require $schema;    // not require_once
            $pdo->exec($sql);
        } else {
            throw new \Exception("Schema file doesn't exist at $schema");
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
        $sql1 = 'SELECT * FROM lang_name WHERE lang_code = ?';
        $sql2 = 'INSERT INTO lang (lang_code) VALUES (?);';
        $sql3 = 'INSERT INTO lang_name (lang_code, name, name_lang) VALUES (?, ?, \'en\');';

        $st1 = $pdo->prepare($sql1);
        $st2 = $pdo->prepare($sql2);
        $st3 = $pdo->prepare($sql3);

        $pdo->beginTransaction(); // faster: from ~45 to 1 sec for SQLite

        foreach (Translator::getLangs() as $code => $name) {
            $st1->execute([$code]);
            if (! $st1->fetchAll()) {
                $st2->execute([$code]);
                $st3->execute([$code, $name]);
                $rows += $st2->rowCount() + $st3->rowCount();
            }
        }

        $pdo->commit();

        return $rows;
   }
}
