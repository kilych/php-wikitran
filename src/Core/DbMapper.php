<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;
use Wikitran\Core\Term;

class DbMapper extends Db
{
    public function getTerm(array $queries, string $source)
    {
        if ($this->connected()) {
            $pre = self::PREFIX;

            // SQL query for translation:
            $st = $this->pdo->prepare(
                "SELECT dest.trans_lang, dest.trans FROM {$pre}translation dest INNER JOIN"
                . " (SELECT * FROM {$pre}translation WHERE trans_lang = ? AND (trans = ? OR LOWER(trans) = ?)) `source`"
                . ' ON dest.term_id = source.term_id'
                . ' ORDER BY dest.term_id;'
            );

            foreach ($queries as $guess) {
                if ($st->execute([$source, $guess, $guess]) && $rows = $st->fetchAll()) {
                    $res = [];
                    // for SQLite 3.11 result columns naming bug
                    $table = (key_exists('dest.trans', $rows[0])) ? 'dest.' : '';
                    foreach ($rows as $row) {
                        $res[$row["{$table}trans_lang"]] = $row["{$table}trans"];
                    }
                    return new Term($res);
                }
            }
        }
        return false;
    }

    public function save($term)
    {
        $pre = self::PREFIX;
        $sql = "INSERT INTO {$pre}translation (term_id, trans, trans_lang, source_id) VALUES (?, ?, ?, 1);";
        $rows = 0;
        if ($this->connected()) {
            try {
                $st = $this->pdo->prepare($sql);
                $this->pdo->beginTransaction();
                $this->pdo->exec("INSERT INTO {$pre}term (term_id) VALUES (null);");
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
