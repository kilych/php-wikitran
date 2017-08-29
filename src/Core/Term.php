<?php

namespace Wikitran\Core;

class Term
{
    protected $translations = [];
    protected $links_to = [];

    public function __construct(array $translations = [], array $links_to = [])
    {
        $this->setTranslations($translations);
        $this->setLinksTo($links_to);
    }

    public function setTranslations(array $translations = [])
    {
        foreach ($translations as $lang => $tr) {
            $this->translations[$lang] = $tr;
        }
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setLinksTo(array $links_to = [])
    {
        $this->links_to = $links_to;
    }

    public function translate(array $dests)
    {
        if (in_array('all', $dests)) {
            return $this->translations;
        }
        $res = [];
        foreach ($dests as $dest) {
            if (key_exists($dest, $this->translations)) {
                $res[$dest] = $this->translations[$dest];
            }
        }
        return (empty($res)) ? false : $res;
    }
}
