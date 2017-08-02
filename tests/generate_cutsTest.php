<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use function Wikitran\Lib\generate_cuts as generate;

class GenerateCutsTest extends TestCase
{
    public function testGenerateCuts()
    {
        $s = 'abcbeginabmiddlecabcendabc';
        $g1 = generate($s, 'begin', 'end', 'a', 'c');
        $g2 = generate($s, 'begin', 'end', 'abc', 'a');
        $g3 = generate($s, 'begin', 'end', 'b');

        $this->assertTrue(equals_array_or_generator(['abmiddle', 'ab'], $g1));
        $this->assertTrue(equals_array_or_generator([], $g2));
        $this->assertTrue(equals_array_or_generator(['begina', 'bmiddleca'], $g3));
    }
}

function equals_array_or_generator($expected, $given)
{
    foreach ($given as $item) {
        if (empty($expected) || ($item !== array_shift($expected))) {
            return false;
        }
    }
    return true;
}
