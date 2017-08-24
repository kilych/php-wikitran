<?php

use Wikitran\Translator;

$autoloadRoot = __DIR__ . '/../../vendor/autoload.php';
$autoloadDependency = __DIR__ . '/../../../../autoload.php';

if (is_file($autoloadRoot)) {
    require $autoloadRoot;
} elseif (is_file($autoloadDependency)) {
    require $autoloadDependency;
} else {
    error_log('Autoload script not found.');
    exit(1);
}

$values = [
    'title' => 'Wikitran Demo',
    'selected_source' => 'en',
    'selected_dest' => 'all'
];

session_start();

setLangs($values);
setLang('source', $values);
setLang('dest', $values);
setQuery($values);
setTranslation($values);

render($values);

function setLangs(&$values) {
    $values['langs'] = array_merge(['all' => 'All languages'], Translator::getLangs());
}

// $langCase is "source" or "dest"
// $langValue can be, for example, "en", "ru", etc.
function setLang(string $langCase, array &$values) {
    if (key_exists($langCase, $_GET)
        && ($_GET[$langCase] === 'all' || Translator::isLang($_GET[$langCase]))) {
        $langValue = $_GET[$langCase];
        $values[$langCase] = $langValue;
        $values["selected_$langCase"] = $langValue;
        $_SESSION["selected_$langCase"] = $langValue;
    } elseif (key_exists("selected_$langCase", $_SESSION)
              && Translator::isLang($_SESSION["selected_$langCase"])) {
        $values["selected_$langCase"] = $_SESSION["selected_$langCase"];
    }
}

function setQuery(array &$values) {
    if (isset($values['source'], $values['dest'], $_GET['query'])
        && strlen($_GET['query']) > 0) {
        // htmlentities() here cause then substitution into view:
        $values['query'] = htmlentities($_GET['query']);
    }
}

function setTranslation(array &$values) {
    if (key_exists('query', $values)) {
        $query = $values['query'];
        $source = $values['source'];
        $dest = $values['dest'];
        $tr = new Translator();
        if (false !== $translations = $tr->translate($query, $source, $dest))
            $values['translations'] = $translations;
    }
}

function render($values = []) {
        // extract variables into local scope
        extract($values);
        require_once __DIR__ . '/chunks/header.php';
        require_once __DIR__ . '/chunks/form.php';
        require_once __DIR__ . '/chunks/message.php';
        require_once __DIR__ . '/chunks/footer.php';
        exit;
}
