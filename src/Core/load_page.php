<?php

namespace Wikitran\Core;

function load_page(string $source, array $queries)
{
    if (extension_loaded('curl')) {
        // create curl resource
        $ch = curl_init();
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        foreach ($queries as $try) {
            // set url
            $url = url_from_query($source, $try);
            curl_setopt($ch, CURLOPT_URL, $url);
            $page = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // error_log(__METHOD__ . " $url : $httpCode");
            if (! curl_errno($ch) && $httpCode === 200) {
                return [$try, $page];
            }
        }

        // close curl resource to free up system resources
        curl_close($ch);
    } else {
        foreach ($queries as $try) {
            // stfu for 404 warnings
            $page = @file_get_contents(url_from_query($source, $try));
            if ($page) {
                return [$try, $page];
            }
        }
    }
    return false;
}

function url_from_query(string $source, string $query)
{
    $term = str_replace(' ', '_', $query);
    if ($source !== 'en') {
        $term = urlencode($term);
    }
    return "https://$source.wikipedia.org/wiki/$term";
}
