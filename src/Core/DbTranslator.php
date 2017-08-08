<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;
use Wikitran\Core\Term;

class DbTranslator extends Db
{
    public function getTerm(array $queries, string $source)
    {
        if ($this->connected()) {
            $server = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($server === 'sqlite') {
                $prefix = 'dest.';
            } elseif ($server === 'mysql') {
                $prefix = '';
            } else {
                throw new \Exception("Unsupported SQL server: $server. Use SQLite or MySQL instead.");
            }

            // SQL query for translation:
            $st = $this->pdo->prepare(
                'SELECT DISTINCT dest.trans_lang, dest.trans FROM translation dest INNER JOIN'
                . ' (SELECT * FROM translation WHERE trans_lang = ? AND trans = ?) `source`'
                . ' ON dest.term_id = source.term_id'
                . ' ORDER BY dest.term_id;'
            );

            foreach ($queries as $try) {
                if ($st->execute([$source, $try]) && $rows = $st->fetchAll()) {
                    $res = [];
                    foreach ($rows as $row) {
                        $res[$row["{$prefix}trans_lang"]] = $row["{$prefix}trans"];
                    }
                    return new Term($res);
                }
            }
        }
        return false;
    }

    public function save($term)
    {
        $sql = "INSERT INTO translation (term_id, trans, trans_lang, source_id) VALUES (?, ?, ?, 1);";
        $rows = 0;
        if ($this->connected()) {
            try {
                $st = $this->pdo->prepare($sql);
                $this->pdo->beginTransaction();
                $this->pdo->exec('INSERT INTO term (term_id) VALUES (null);');
                $id = $this->pdo->lastInsertId();
                foreach ($term->getTranslations() as $key => $value) {
                    $execution = $st->execute([$id, $value, $key]);
                    $rows += $st->rowCount();
                }
                $this->pdo->commit();
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                $rows = 0;
                error_log(__METHOD__);
                if (isset($key, $value)) {
                    error_log("Error for lang: $key translation: $value");
                }
                error_log($e->getMessage());
            }
            return $rows;
        } else {
            return false;
        }
    }
}
