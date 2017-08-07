<?php

namespace Wikitran\Core;

function load_page(string $lang, array $queries)
{
    if (extension_loaded('curl')) {
        // create curl resource
        $ch = curl_init();
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        foreach ($queries as $try) {
            // set url
            curl_setopt($ch, CURLOPT_URL, url_from_query($lang, $try));
            $page = curl_exec($ch);
            if (!curl_errno($ch)
                && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
                return [$try, $page];
            }
        }

        // close curl resource to free up system resources
        curl_close($ch);
    } else {
        foreach ($queries as $try) {
            // stfu for 404 warnings
            $page = @file_get_contents(url_from_query($lang, $try));
            if ($page) {
                return [$try, $page];
            }
        }
    }
    return false;
}

function url_from_query(string $lang, string $query)
{
    $term = str_replace(' ', '_', $query);
    if ($lang !== 'en') {
        $term = urlencode($term);
    }
    return "https://$lang.wikipedia.org/wiki/$term";
}
