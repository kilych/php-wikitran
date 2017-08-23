<?php

namespace Wikitran;

use Wikitran\Core\Term;

class TranslatorTest extends MyTestCase
{
    public function getConfigDataProvider()
    {
        return [
            ['source', 'en'],
            ['dests', ['all']],
            ['db', []],
            ['viaWeb', true],
            ['viaDb', false]
        ];
    }

    /**
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($key, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->translator->getConfig()[$key]);
    }

    public function translateDataProvider()
    {
        $this->setUp();

        $term = $this->terms[0];

        return [
            ['how the steel was tempered', [], $term],
            ['как закалялась сталь', ['ru'], $term],
            ['как закалялась сталь', ['ru', 'all'], $term],
            ['как закалялась сталь', ['ru', 'bg'], ['bg' => $term['bg']]],
            ['как закалялась сталь', ['ru', 'ta', 'pa', 'zh'], [
                'ta' => $term['ta'],
                'pa' => $term['pa'],
                'zh' => $term['zh']
            ]]
        ];
    }

    /**
     * @dataProvider translateDataProvider
     */
    public function testTranslate($query, $langs, $translations)
    {
        $this->translator->method('getTerm')
            ->willReturn(new Term($this->terms[0]));

        $this->assertEquals(
            $translations,
            $this->translator->translate($query, ...$langs)
        );
    }

    public function setConfigAndTranslateDataProvider()
    {
        $this->setUp();

        $term = $this->terms[0];

        return [
            [['viaWeb' => false], 'how the steel was tempered', false],
            [[], 'how the steel was tempered', $term],
            [['source' => 'ru'], 'как закалялась сталь', $term],
            [['source' => 'ru', 'dests' => ['all']], 'как закалялась сталь', $term],
            [['source' => 'ru', 'dests' => ['bg']], 'как закалялась сталь', ['bg' => $term['bg']]],
            [['source' => 'ru', 'dests' => ['ar', 'hr', 'mk']], 'как закалялась сталь', [
                'ar' => $term['ar'],
                'hr' => $term['hr'],
                'mk' => $term['mk']
            ]]
        ];
    }

    /**
     * @dataProvider setConfigAndTranslateDataProvider
     */
    public function testSetConfigAndTranslate($config, $query, $translations)
    {
        $this->translator->method('getTerm')
            ->willReturn(new Term($this->terms[0]));

        $this->translator->setConfig($config);

        $this->assertEquals($translations, $this->translator->translate($query));
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

        $this->translator->setConfig(['source' => 'ru']);

        $this->assertEquals(
            [
                false,
                $this->terms[0],
                $this->terms[1]
            ],
            $this->translator->translateSet([
                '',
                'как закалялась сталь',
                'возведение в степень'
            ])
        );
    }
}
