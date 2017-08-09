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
        $translated = ['en' => 'How the Steel Was Tempered'];
        $translated1 = ['en' => 'United Nations'];

        $dbpath = Migration::createDbFile(__DIR__ . '/db');
        $pdo = Migration::connectSQLite($dbpath);
        // Migration::clear($pdo);
        Migration::run($pdo);
        $tr = new Translator($pdo);

        $this->assertEquals('mixed', $tr->getMethod());
        $tr->setMethod('web');
        $this->assertEquals('web', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));
        $this->assertEquals($translated1, $tr->translate('оон', 'ru', 'en'));

        $tr->setMethod('mixed');
        $this->assertEquals('mixed', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));

        $tr->setMethod('db');
        $this->assertEquals('db', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));

        error_log(PHP_EOL . 'Test MySQL');

        $pdoMy = Migration::connectMySQL('wikitran_test_db', 'wikitran_test_user');
        // Migration::clear($pdoMy);
        Migration::run($pdoMy);
        $trMy = new Translator($pdoMy);

        $this->assertEquals('mixed', $trMy->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));

        $trMy->setMethod('db');
        $this->assertEquals('db', $trMy->getMethod());
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));
    }
}
