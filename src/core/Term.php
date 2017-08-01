<?php

namespace Wikitran\core;

use function Wikitran\lib\generate_cuts;

class Term
{
    public $translations;
    public $links_to;

    public function __construct(string $source, string $query, string $page)
    {
        $this->translations = get_translations($page);
        $this->translations[$source] = $query;
        $this->links_to = get_links($source, $page);
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

function get_translations(string $page): array
{
    $translations = [];
    $urls = generate_cuts($page, 'p-lang-label', 'after-portlet-lang', 'http', '"');
    foreach ($urls as $url) {
        list($lang, $translation) = handle_wikiurl($url);
        $translations[$lang] = unsharp_translation($translation);
    }
    return $translations;
}

function get_links(string $source, string $page): array
{
    $links_to = [];
    $urls = generate_cuts($page, 'mw-content-text', 'printfooter', 'href="', '"');
    foreach ($urls as $url) {
        list($lang, $term_name) = handle_wikiurl($url);
        if (!$lang) {
            $lang = $source;
        }
        if ($term_name && is_term_name($term_name)) {
            $links_to[$lang][] = unsharp_link($term_name); // FIXIT:
        }
    }
    return $links_to;
}

// works from tail of url
function handle_wikiurl(string $url)
{
    $arr = explode('/', $url);
    $term = array_pop($arr);
    if (is_encoded($term)) {
        $term = urldecode($term);
    }
    $term = str_replace('_', ' ', $term);
    if (is_wikiurl($url)) {
        array_pop($arr);        // drop "wiki"
        $host  = explode('.', array_pop($arr));
        $lang = array_shift($host);
        return [$lang, $term];
    } elseif (is_short_wikiurl($url)) {
        return ['', $term];
    } else {
        return ['', ''];
    }
}

function is_encoded(string $str): bool
{
    // "===" cause strpos can return 0 that coerces to false:
    return strpos($str, '%') !== false;
}

function is_wikiurl(string $url): bool
{
    return false !== strpos($url, '.wikipedia.org');
}

function is_short_wikiurl(string $url): bool
{
    return false !== strpos($url, '/wiki/');
}

function is_term_name(string $term_name): bool
{
    return (false === strpos($term_name, ':') || false !== strpos($term_name, ': '))
           && (false === strpos($term_name, '.') || false !== strpos($term_name, '. '));
}

// if # returns after sharp
function unsharp_translation(string $tran)
{
    if (false !== strpos($tran, '#')
        && (false !== $tran = substr(strstr($tran, '#'), 1))) {
        return $tran;
    } else {
        return $tran;
    }
}

// if # returns before sharp
function unsharp_link(string $link)
{
    return (false === $unsharped = strstr($link, '#', true)) ? $link : $unsharped;
}
