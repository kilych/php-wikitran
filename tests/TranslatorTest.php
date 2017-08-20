<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use Wikitran\Translator;
use Wikitran\Core\Term;

class TranslatorTest extends TestCase
{
    protected $terms;
    protected $translator;

    protected function setUp()
    {
        require_once __DIR__ . '/terms.php';

        $this->terms = TERMS;

        $this->translator = $this->getMockBuilder(Translator::class)
            ->setMethods(['getTerm'])
            ->getMock();
    }

    public function testTranslate()
    {
        $this->translator->method('getTerm')
            ->willReturn(new Term($this->terms[0]));

        $this->assertEquals(true, $this->translator->getConfig()['viaWeb']);
        $this->assertEquals(false, $this->translator->getConfig()['viaDb']);

        $this->assertEquals($this->terms[0], $this->translator->translate('как закалялась сталь', 'ru'));
    }

    public function testTranslateSet()
    {
        $this->translator
            ->expects($this->exactly(2))
            ->method('getTerm')
            ->will($this->onConsecutiveCalls(
                new Term($this->terms[0]),
                new Term($this->terms[1])
            ));

        $this->assertEquals(false, $this->translator->getConfig()['viaDb']);

        $this->assertEquals(
            [false, $this->terms[0], false, $this->terms[1]],
            $this->translator->translateSet(
                ['', 'как закалялась сталь', '', 'возведение в степень'],
                'ru'
            ));
    }
}
