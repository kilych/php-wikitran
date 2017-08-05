<?php

namespace Wikitran\Core;

use Wikitran\Core\Db;

class DbTranslator extends Db
{
    protected $pdo;              // \PDO object or false

    public function __construct($pdo = null)
    {
        if ($pdo instanceof \PDO) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = self::connectBuiltIn();
        }
    }

    public function isConnectionSet()
    {
        return $this->pdo instanceof \PDO;
    }

    public function translate(string $source, string $dest, array $queries)
    {
        if ($this->pdo) {
            // SQL query for translation:
            $st = $this->pdo->prepare(
                'SELECT dest.trans FROM translation dest INNER JOIN '
                . '(SELECT * FROM translation WHERE trans = ? AND trans_lang = ?) `source` '
                . 'ON dest.term_id = source.term_id '
                . 'WHERE dest.trans_lang = ?;'
            );
            foreach ($queries as $try) {
                if ($st->execute([$try, $source, $dest])
                    && $res = $st->fetchAll()) {
                    $server = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
                    if ($server === 'sqlite') {
                        return $res[0]['dest.trans'];
                    } elseif ($server === 'mysql') {
                        return $res[0]['trans'];
                    } else {
                        throw new \Exception("Unsupported SQL server: $server. Use SQLite or MySQL instead.");
                    }
                }
            }
        }
        return false;
    }

    public function save($term)
    {
        $sql = "INSERT INTO translation (term_id, trans, trans_lang, source_id)\n" .
             "VALUES (?, ?, ?, 1);";
        $rows = 0;
        if ($this->pdo) {
            try {
                $st = $this->pdo->prepare($sql);
                $this->pdo->beginTransaction();
                $this->pdo->exec('INSERT INTO term (term_id) VALUES (null);');
                $id = $this->pdo->lastInsertId();
                foreach ($term->translations as $key => $value) {
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
