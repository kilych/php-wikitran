<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use function Wikitran\Lib\generateCuts as generate;

class GenerateCutsTest extends TestCase
{
    public function testGenerateCuts()
    {
        $s = 'abcbeginabmiddlecabcendabc';
        $g1 = generate($s, 'begin', 'end', 'a', 'c');
        $g2 = generate($s, 'begin', 'end', 'abc', 'a');
        $g3 = generate($s, 'begin', 'end', 'b');

        $this->assertTrue(equalsArrayOrGenerator(['abmiddle', 'ab'], $g1));
        $this->assertTrue(equalsArrayOrGenerator([], $g2));
        $this->assertTrue(equalsArrayOrGenerator(['begina', 'bmiddleca'], $g3));
    }
}

function equalsArrayOrGenerator($expected, $given)
{
    foreach ($given as $item) {
        if (empty($expected) || ($item !== array_shift($expected))) {
            return false;
        }
    }
    return true;
}
