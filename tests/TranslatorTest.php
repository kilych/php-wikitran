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
        Migration::run($pdo);
        $tr = new Translator($pdo);

        $this->assertEquals('mixed', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('ru', 'en', 'как закалялась сталь'));

        $tr->setMethod('db');
        $this->assertEquals('db', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('ru', 'en', 'как закалялась сталь'));
    }
}
