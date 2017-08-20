<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use Wikitran\Translator;
use Wikitran\Core\DbInit;
use Wikitran\Core\Term;

class SQLiteTest extends TestCase
{
    public function testTranslate()
    {
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

        $db = new DbInit(null, ['server' => 'sqlite', 'file' => __DIR__ . '/db/cache.sqlite', 'createFile' => true]);
        $db->clear();
        $db->run();
        $db->run();             // test idempotency

        $tr = $this->getMockBuilder(Translator::class)
            ->setMethods(['getTerm'])
            ->getMock();

        $tr->method('getTerm')
            ->willReturn(new Term($translations));

        $tr->getDb()->setConnection($db->getConnection());

        $this->assertEquals(true, $tr->getConfig()['viaWeb']);
        $this->assertEquals(false, $tr->getConfig()['viaDb']);

        $tr->setConfig(['viaDb' => true]);

        $this->assertEquals(true, $tr->getConfig()['viaDb']);
        $this->assertEquals($translations, $tr->translate('как закалялась сталь', 'ru'));

        $tr->setConfig(['viaWeb' => false, 'viaDb' => true]);
        $this->assertEquals(false, $tr->getConfig()['viaWeb']);
        $this->assertEquals(true, $tr->getConfig()['viaDb']);
        $this->assertEquals($translations, $tr->translate('как закалялась сталь', 'ru'));
    }
}
