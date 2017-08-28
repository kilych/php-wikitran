<?php

namespace Wikitran\Core;

use Wikitran\Core\Term;
use function Wikitran\Lib\generateCuts as generate;

function parsePage(string $source, string $query, string $page)
{
    $term = new Term(getTranslations($page), getLinks($source, $page));
    $term->setTranslations([$source => $query]);
    return $term;
}

function getTranslations(string $page): array
{
    $translations = [];
    $urls = generate($page, 'p-lang-label', 'after-portlet-lang', 'http', '"');
    foreach ($urls as $url) {
        list($lang, $translation) = handleWikiurl($url);
        $translations[$lang] = unsharpTranslation($translation);
    }
    return $translations;
}

function getLinks(string $source, string $page): array
{
    $links_to = [];
    $urls = generate($page, 'mw-content-text', 'printfooter', 'href="', '"');
    foreach ($urls as $url) {
        list($lang, $term_name) = handleWikiurl($url);
        if (!$lang) {
            $lang = $source;
        }
        if ($term_name && isTermName($term_name)) {
            $links_to[$lang][] = unsharpLink($term_name); // FIXIT:
        }
    }
    return $links_to;
}

// works from tail of url
function handleWikiurl(string $url)
{
    $arr = explode('/', $url);
    $term = array_pop($arr);
    if (isEncoded($term)) {
        $term = urldecode($term);
    }
    $term = str_replace('_', ' ', $term);
    if (isWikiurl($url)) {
        array_pop($arr);        // drop "wiki"
        $host  = explode('.', array_pop($arr));
        $lang = array_shift($host);
        return [$lang, $term];
    } elseif (isShortWikiurl($url)) {
        return ['', $term];
    } else {
        return ['', ''];
    }
}

function isEncoded(string $str): bool
{
    // "===" cause strpos can return 0 that coerces to false:
    return strpos($str, '%') !== false;
}

function isWikiurl(string $url): bool
{
    return false !== strpos($url, '.wikipedia.org');
}

function isShortWikiurl(string $url): bool
{
    return false !== strpos($url, '/wiki/');
}

function isTermName(string $term_name): bool
{
    return (false === strpos($term_name, ':') || false !== strpos($term_name, ': '))
           && (false === strpos($term_name, '.') || false !== strpos($term_name, '. '));
}

// if # returns after sharp
function unsharpTranslation(string $tran)
{
    if (false !== strpos($tran, '#')
        && (false !== $tran = substr(strstr($tran, '#'), 1))) {
        return $tran;
    } else {
        return $tran;
    }
}

// if "#" returns before "#"
function unsharpLink(string $link)
{
    return (false === $unsharped = strstr($link, '#', true)) ? $link : $unsharped;
}
