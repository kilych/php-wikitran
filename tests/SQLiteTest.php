<?php

namespace Wikitran;

use Wikitran\Core\DbInit;

class SQLiteTest extends DbTestCase
{
    public function setUp()
    {
        parent::setUp();

        $db = new DbInit(null, [
            'server' => 'sqlite',
            'file' => __DIR__ . '/db/cache.sqlite',
            'createFile' => true
        ]);
        $db->clear();
        $db->run();
        $db->run();             // test idempotency

        $this->translator->setConnection($db->getConnection());
    }
}
