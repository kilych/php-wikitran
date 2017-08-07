<?php

namespace Wikitran\Core;

class Term
{
    public $translations;
    public $links_to;

    public function __construct(array $translations, array $links_to)
    {
        $this->translations = $translations;
        $this->links_to = $links_to;
    }

    public function translate(string $dest)
    {
        if (isset($this->translations) &&
            isset($this->translations[$dest])) {
            return $this->translations[$dest];
        } else {
            return false;
        }
    }
}
