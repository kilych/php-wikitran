<?php

namespace Wikitran;

use Wikitran\Core\DbInit;

class MySQLTest extends DbTestCase
{
    public function setUp()
    {
        parent::setUp();

        $db = new DbInit(null, [
            'server' => 'mysql',
            'db' => 'wikitran_test_db',
            'user' => 'wikitran_test_user'
        ]);
        $db->clear();
        $db->run();
        $db->run();             // test idempotency

        $this->translator->setConnection($db->getConnection());
    }
}
