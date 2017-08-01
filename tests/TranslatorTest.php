<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use Wikitran\Translator;

class TranslatorTest extends TestCase
{
    /**
     * Test translate
     */
    public function testTranslate()
    {
        $translated = 'How the Steel Was Tempered';
        $tr = new Translator();
        $tr->setMethod('web');

        $this->assertEquals('web', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('ru', 'en', 'как закалялась сталь'));
    }
}
