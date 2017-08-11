<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use Wikitran\Translator;
use Wikitran\Core\DbInit;

class TranslatorTest extends TestCase
{
    /**
     * Test translate
     */
    public function testTranslate()
    {
        $translated = ['en' => 'How the Steel Was Tempered'];
        $translated1 = ['en' => 'United Nations'];

        $db = new DbInit(null, ['server' => 'sqlite', 'file' => __DIR__ . '/db/cache.sqlite', 'createFile' => true]);
        $db->clear();
        $db->run();
        $db->run();             // test idempotency
        $tr = new Translator([], $db->getConnection());

        $this->assertEquals('mixed', $tr->getMethod());
        $tr->setConfig(['method' => 'web']);
        $this->assertEquals('web', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));
        $this->assertEquals($translated1, $tr->translate('оон', 'ru', 'en'));

        $tr->setConfig(['method' => 'mixed']);
        $this->assertEquals('mixed', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));

        $tr->setConfig(['method' => 'db']);
        $this->assertEquals('db', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));

        error_log(PHP_EOL . 'Test MySQL');

        $db = new DbInit(null, ['server' => 'mysql', 'db' => 'wikitran_test_db', 'user' => 'wikitran_test_user']);
        $db->clear();
        $db->run();
        $db->run();             // test idempotency
        $trMy = new Translator([], $db->getConnection());

        $this->assertEquals('mixed', $trMy->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));

        $trMy->setConfig(['method' => 'db']);
        $this->assertEquals('db', $trMy->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));
    }
}
