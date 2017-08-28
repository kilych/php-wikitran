<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;
use Wikitran\Translator;

class DbInit extends Db
{
    const SERVERS = ['sqlite', 'mysql'];
    protected static $server;

    public function run()
    {
        error_log(__METHOD__);
        if ($this->connected()) {
            $pdo = $this->pdo;
            $server = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if (in_array($server, self::SERVERS)) {
                self::$server = $server;
            } else {
                error_log("Unsupported SQL server: $server. Use SQLite or MySQL instead.\nNothing to do.");
                exit(1);
            }
            try {
                self::createTables($pdo);
                self::addValues($pdo);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        } else {
            error_log("Db connection is not set.\nNothing to do.");
            exit(1);
        }
    }

    public function clear()
    {
        error_log(__METHOD__);
        $pre = self::PREFIX;
        $tables = ['term_relation', 'translation', 'term', 'term_source', 'lang_name', 'lang'];
        if ($this->connected()) {
            $pdo = $this->pdo;
            try {
                foreach ($tables as $table) {
                    $pdo->exec("DROP TABLE IF EXISTS $pre$table;");
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        } else {
            error_log("Db connection is not set.\nNothing to do.");
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

    protected static function addValues(\PDO $pdo)
    {
        $rows = 0;
        $pre = self::PREFIX;
        $sqlCheckSource = "SELECT * FROM {$pre}term_source WHERE source_id = 1 AND source = 'Wikipedia';";
        $sqlAddSource = "INSERT INTO  {$pre}term_source (source) VALUES ('Wikipedia');";
        // lang_code is foreign key in lang_name table
        $sqlCheckCode = "SELECT * FROM {$pre}lang_name WHERE lang_code = ?;";
        $sqlAddCode = "INSERT INTO {$pre}lang (lang_code) VALUES (?);";
        $sqlAddLang = "INSERT INTO {$pre}lang_name (lang_code, name, name_lang) VALUES (?, ?, 'en');";

        $stCheckSource = $pdo->prepare($sqlCheckSource);
        $stAddSource = $pdo->prepare($sqlAddSource);
        $stCheckCode = $pdo->prepare($sqlCheckCode);
        $stAddCode = $pdo->prepare($sqlAddCode);
        $stAddLang = $pdo->prepare($sqlAddLang);

        $pdo->beginTransaction(); // faster: from ~45 to 1 sec for SQLite

        $stCheckSource->execute();
        if (empty($stCheckSource->fetchAll())) {
            $stAddSource->execute();
        }

        foreach (Translator::getLangs() as $code => $name) {
            $stCheckCode->execute([$code]);
            if (empty($stCheckCode->fetchAll())) {
                $stAddCode->execute([$code]);
                $stAddLang->execute([$code, $name]);
                $rows += $stAddCode->rowCount() + $stAddLang->rowCount();
            }
        }

        $pdo->commit();

        return $rows;
    }
}
