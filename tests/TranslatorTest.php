<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use Wikitran\Translator;
use Wikitran\Core\Migration;

class TranslatorTest extends TestCase
{
    /**
     * Test translate
     */
    public function testTranslate()
    {
        $translated = 'How the Steel Was Tempered';

        $dbpath = Migration::createDbFile(__DIR__ . '/data');
        $pdo = Migration::connectSQLite($dbpath);
        // Migration::clear($pdo);
        Migration::run($pdo);
        $tr = new Translator($pdo);

        $this->assertEquals('mixed', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('ru', 'en', 'как закалялась сталь'));

        $tr->setMethod('db');
        $this->assertEquals('db', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('ru', 'en', 'как закалялась сталь'));

        error_log(PHP_EOL . 'Test MySQL');

        $pdoMy = Migration::connectMySQL('wikitran_test_db', 'wikitran_test_user');
        // Migration::clear($pdoMy);
        Migration::run($pdoMy);
        $trMy = new Translator($pdoMy);

        $this->assertEquals('mixed', $trMy->getMethod());
        $this->assertEquals($translated, $trMy->translate('ru', 'en', 'как закалялась сталь'));

        $trMy->setMethod('db');
        $this->assertEquals('db', $trMy->getMethod());
        $this->assertEquals($translated, $trMy->translate('ru', 'en', 'как закалялась сталь'));
    }
}
