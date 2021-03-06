<?php

namespace Wikitran\Lib;

function generateCuts(string $init, string $begin, string $end, string $start, string $stop = '')
{
    if (false !== $cut = cutString($init, $begin, $end)) {
        return generate($cut[1], $start, $stop);
    }
    return [];
}

// cut string from start to stop excluding stop
// if $init = 'abcabc', $start = $stop = 'abc', cuts 'abc'
// if $init = 'abc', $start = $stop = 'abc', cuts nothing
function cutString(string $init, string $start, string $stop = '')
{
    if ((0 === strlen($init))
        || (0 === $start_len = strlen($start))) {
        return false;
    }
    if (strlen($stop) === 0) {
        $stop = $start;
    }
    if ((false !== $init = strstr($init, $start))
        // if $stop equals $start we need next occurrence of $start:
        && (false !== $init = substr($init, $start_len))
        && (false !== $cut = strstr($init, $stop, true))) {
        // cause it can be multiple $start before $stop:
        $init = strstr($init, $stop);
        return [$init, $start . $cut];
    } else {
        return false;
    }
}

// returns generator of cuts from start to stop excluding stop
function generate(string $init, string $start, string $stop = '')
{
    if (false === cutString($init, $start, $stop)) {
        return [];
    }
    while (false !== $res = cutString($init, $start, $stop)) {
        list($init, $cut) = $res;
        yield $cut;
    }
}
