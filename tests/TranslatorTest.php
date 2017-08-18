<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use Wikitran\Translator;
use Wikitran\Core\Term;

class TranslatorTest extends TestCase
{
    public function testTranslate()
    {
        $translated = ['en' => 'How the Steel Was Tempered'];
        // $translated1 = ['en' => 'United Nations'];

        $translations = [
            'ar' => 'كيف سقينا الفولاذ',
            'bg' => 'Как се каляваше стоманата (роман)',
            'de' => 'Wie der Stahl gehärtet wurde',
            'en' => 'How the Steel Was Tempered',
            'hr' => 'Kako se kalio čelik',
            'mk' => 'Како се калеше челикот',
            'pa' => 'ਸੂਰਮੇ ਦੀ ਸਿਰਜਣਾ',
            'pl' => 'Jak hartowała się stal (powieść)',
            'sh' => 'Kako se kalio čelik',
            'sr' => 'Како се калио челик',
            'ta' => 'வீரம் விளைந்தது (நூல்)',
            'tr' => 'Ve Çeliğe Su Verildi',
            'vi' => 'Thép đã tôi thế đấy !',
            'zh' => '钢铁是怎样炼成的',
            'ru' => 'Как закалялась сталь'
        ];

        $tr = $this->getMockBuilder(Translator::class)
            ->setMethods(['getTerm'])
            ->getMock();

        $tr->method('getTerm')
            ->willReturn(new Term($translations));

        $this->assertEquals(true, $tr->getConfig()['viaWeb']);
        $this->assertEquals(true, $tr->getConfig()['viaDb']);
        $tr->setConfig(['viaDb' => false]);
        $this->assertEquals(false, $tr->getConfig()['viaDb']);
        $this->assertEquals($translated, $tr->translate('как закалялась сталь', 'ru', 'en'));
    }
}
