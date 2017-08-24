<?php

if (isset($query)) {
    print "<p>Your query in $langs[$source]: $query</p>";
    if (isset($translations)) {
        foreach ($translations as $code => $translation) {
            if (key_exists($code, $langs)) {
                print "<p>$langs[$code]: $translation</p>";
            } else {
                error_log("Unknown code: $code");
            }
        }
    } else {
        print "<p>$langs[$dest]: Translation not found.</p>";
    }
}
