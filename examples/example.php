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
$res = $tr->translate('как закалялась сталь');

if ($res) {
    foreach ($res as $code => $translation) {
        print("$code => $translation\n");
    }
}
