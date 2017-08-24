<?php

namespace Wikitran;

use Wikitran\Core\Term;

class DbTestCase extends MyTestCase
{
    public function testTranslate()
    {
        $terms = $this->terms;

        $this->translator
            ->expects($this->exactly(2))
            ->method('getTerm')
            ->will($this->onConsecutiveCalls(
                new Term($terms[0]),
                new Term($terms[1])
            ));

        $tr = $this->translator;

        $this->assertEquals(true, $tr->getConfig()['viaDb']);
        $this->assertEquals($terms[0], $tr->translate('как закалялась сталь', 'ru'));

        $tr->setConfig(['viaWeb' => false]);

        $this->assertEquals($terms[0], $tr->translate('How the steel was tempered'));
        $this->assertEquals($terms[0], $tr->translate('how the stEel   waS temPEred'));
        $this->assertEquals($terms[0], $tr->translate('как закалЯлась сталь', 'ru'));
        $this->assertEquals($terms[0], $tr->translate('كيف سقينا الفولاذ', 'ar'));
        $this->assertEquals($terms[0], $tr->translate('ਸੂਰਮੇ ਦੀ ਸਿਰਜਣਾ', 'pa'));
        $this->assertEquals($terms[0], $tr->translate('வீரம் விளைந்தது (நூல்)', 'ta'));
        $this->assertEquals($terms[0], $tr->translate('Thép đã tôI tHế đấy !', 'vi'));
        $this->assertEquals($terms[0], $tr->translate('钢铁是怎样炼成的', 'zh'));

        $tr->setConfig(['viaWeb' => true]);

        $this->assertEquals($terms[1], $tr->translate('возведение в степень', 'ru'));

        $tr->setConfig(['viaWeb' => false]);

        foreach ($terms[1] as $code => $translation) {
            $this->assertEquals(
                ['el' => 'Ύψωση σε δύναμη'],
                $tr->translate($translation, $code, 'el')
            );
        }
    }
}
