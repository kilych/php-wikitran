<?php

namespace wikitranslator\wikitranslator;

use PHPUnit\Framework\TestCase;
use wikitranslator\wikitranslator\Translator;

class TranslatorTest extends TestCase {
    /**
     * Test translate
     */
    public function testTranslate() {
        $translated = 'How the Steel Was Tempered';
        $tr = new Translator();

        $this->assertEquals('web', $tr->getMethod());
        $this->assertEquals($translated, $tr->translate('ru', 'en', 'как закалялась сталь'));
    }
}
