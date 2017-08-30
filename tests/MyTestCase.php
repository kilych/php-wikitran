<?php

namespace Wikitran;

use PHPUnit\Framework\TestCase;
use Wikitran\Translator;

class MyTestCase extends TestCase
{
    protected $terms;
    protected $translator;

    protected function setUp()
    {
        require_once __DIR__ . '/data/terms.php';

        $this->terms = TERMS;

        $this->translator = $this->getMockBuilder(Translator::class)
                          ->setMethods(['getTerm'])
                          ->getMock();

        $this->translator->setConfig(['viaDb' => false]);
    }
}
