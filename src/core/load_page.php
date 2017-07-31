<?php

namespace wikitranslator\wikitranslator\core;

function load_page(string $lang, array $queries) {
    foreach($queries as $try) {
        $page = file_get_contents(url_from_query($lang, $try));
        if ($page) return [$try, $page];
    }
    return false;
}

function url_from_query(string $lang, string $query) {
        $term = str_replace(' ', '_', $query);
        if ($lang !== 'en') $term = urlencode($term);
        return "https://{$lang}.wikipedia.org/wiki/$term";
}
