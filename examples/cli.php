<?php

use Wikitran\Translator;

$autoloadRoot = __DIR__ . '/../vendor/autoload.php';
$autoloadDependency = __DIR__ . '/../../../autoload.php';

if (is_file($autoloadRoot)) {
    require $autoloadRoot;
} elseif (is_file($autoloadDependency)) {
    require $autoloadDependency;
} else {
    error_log('Autoload script not found.');
    exit(1);
}

$tr = new Translator(['source' => 'ru']);

$results = $tr->translateSet([
    'как закалялась сталь',
    'островский'
]);

$results[] = $tr->translate('wiskunde', 'nl', 'en', 'de', 'fr', 'ru');

print_arrays(...$results);

function print_arrays(...$arrays)
{
    foreach ($arrays as $arr) {
        foreach ($arr as $code => $translation) {
            print("$code => $translation\n");
        }
        print("--------------------\n");
    }
}
