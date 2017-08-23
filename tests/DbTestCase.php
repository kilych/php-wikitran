<?php

namespace Wikitran;

use Wikitran\Core\Term;

class DbTestCase extends MyTestCase
{
    public function testTranslate()
    {
        $translations = $this->terms[0];

        $this->translator
            ->expects($this->once())
            ->method('getTerm')
            ->willReturn(new Term($translations));

        $tr = $this->translator;

        $this->assertEquals(true, $tr->getConfig()['viaWeb']);
        $this->assertEquals(true, $tr->getConfig()['viaDb']);
        $this->assertEquals($translations, $tr->translate('как закалялась сталь', 'ru'));

        $tr->setConfig(['viaWeb' => false]);

        $this->assertEquals(false, $tr->getConfig()['viaWeb']);
        $this->assertEquals(true, $tr->getConfig()['viaDb']);
        $this->assertEquals($translations, $tr->translate('как закалялась сталь', 'ru'));
    }
}
