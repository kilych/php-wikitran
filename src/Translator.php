<?php

namespace Wikitran;

use Wikitran\Core\DbMapper;
use function Wikitran\Core\loadPage as load;
use function Wikitran\Core\parsePage as parse;

class Translator
{
    protected $config = [
        'source' => 'en',
        'dests' => ['all'],
        'db' => [],
        'viaWeb' => true,
        'viaDb' => true
    ];
    protected $db;
    protected static $langs = [];

    public function __construct($config = [], $pdo = null)
    {
        $dbconfig = (key_exists('db', $config)) ?
                  $config['db'] : $this->config['db'];
        $this->db = new DbMapper($pdo, $dbconfig);
        $this->setConfig($config);
    }

    /**
     * Main functionality
     * @param string $query Query for translation
     * @param string[] $langs Languages: first as source language,
     * rest as destination languages.
     * @return mixed Returns translation array or false
     */
    public function translate(string $query, string ...$langs)
    {
        if ($query === '') {
            error_log(__METHOD__ . ' empty query for translation');
            return false;
        }
        $queries = self::varyQuery(self::normalize($query));

        switch (count($langs)) {
            case 0:
                $source = $this->config['source'];
                $dests = $this->config['dests'];
                break;
            case 1:
                $source = $langs[0];
                $dests = $this->config['dests'];
                break;
            default:
                $source = array_shift($langs);
                $dests = $langs;
        }

        if (in_array('all', $dests)) {
            $dests = ['all'];
        }

        if ($this->config['viaDb']
            && false !== $term = $this->db->getTerm($queries, $source)) {
        } elseif ($this->config['viaWeb']) {
            $term = $this->getTerm($queries, $source);
            if ($this->config['viaDb'] && $term) {
                $this->db->save($term);
            }
        }

        return (isset($term) && $term) ? $term->translate($dests) : false;
    }

    public function translateSet(array $queries, string ...$langs)
    {
        return array_map(
            function ($query) use ($langs) {
                return $this->translate($query, ...$langs);
            },
            $queries
        );
    }

    public function getTerm(array $queries, $source)
    {
        if (false !== $from_web = load($source, $queries)) {
            list($succesful_query, $page) = $from_web;
            $term = parse($source, $succesful_query, $page);
            return $term;
        }
        return false;
    }

    public function setConnection(\PDO $pdo)
    {
        $this->db->setConnection($pdo);
        $this->setConfig(['viaDb' => true]);
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(array $config)
    {
        if (! $this->db->connected()) {
            $config['viaDb'] = false;
        }

        foreach ($config as $key => $value) {
            switch ($key) {
                case 'viaWeb':
                case 'viaDb':
                    if (! is_bool($value)) {
                        throw new \Exception("For key $key boolean expected but $value given");
                    }
                    break;
                case 'source':
                    if (! self::isLang($value)) {
                        throw new \Exception("Unknown source language code $value");
                    }
                    break;
                case 'dests':
                    if (is_array($value) && empty($value)) {
                        throw new \Exception(
                            "\"dests\" config option must be non-empty array of language codes but empty array given"
                        );
                    } elseif (! is_array($value)) {
                        throw new \Exception(
                            "\"dests\" config option must be non-empty array of language codes but $value given"
                        );
                    } else {
                        foreach ($value as $langCode) {
                            if ($langCode === 'all') {
                                $config[$key] = ['all'];
                                break;
                            } elseif (! self::isLang($langCode)) {
                                throw new \Exception("Unknown destination language code $langCode");
                            }
                        }
                    }
                    break;
                default:
                    unset($config[$key]);
            }
        }

        $this->config = array_merge($this->config, $config);
    }

    public static function isLang($langCode, $say = false)
    {
        $res = key_exists($langCode, self::getLangs());
        if (!$res && $say) {
            error_log(__METHOD__ . " Unknown language code: $langCode");
        }
        return $res;
    }

    public static function getLangs()
    {
        $path = dirname(__DIR__) . '/config/langs.php';

        if (! empty(self::$langs)) {
            return self::$langs;
        } elseif (is_file($path)) {
            require_once $path;
            self::$langs = LANGS;
            return self::$langs;
        } else {
            throw new \Exception(__METHOD__ . " $path isn't file");
        }
    }

    protected static function normalize(string $query): string
    {
        // array_filter with no pred arg coerces '' to false than drops it:
        $q = implode(' ', array_filter(explode(' ', str_replace(
            ['\t','\n','\r','_'],
            ' ',
            $query
        ))));

        $encoding = mb_detect_encoding($q);

        if ($encoding == 'ASCII') {
            return strtolower($q);
        } else {
            // expects known (mb_list_encodings) encoding:
            return mb_convert_case($q, MB_CASE_LOWER, $encoding);
        }
    }

    // see http://stackoverflow.com/a/17817754
    protected static function varyQuery(string $query)
    {
        $encoding = mb_detect_encoding($query);

        // http://ru.stackoverflow.com/a/72543 :
        $mb_ucfirst = function ($q) use ($encoding) {
            // two, three or four bytes for one char
            $bytes = ($len = strlen($q) >= 4) ? 4 : $len;
            $str = mb_convert_case(
                substr($q, 0, $bytes),
                MB_CASE_TITLE,
                $encoding
            );
            for ($i = 0; $i < $bytes; $i++) {
                $q[$i] = $str[$i];
            }
            return $q;
        };

        if ('ASCII' === $encoding = mb_detect_encoding($query)) {
            return [ucfirst($query),
                    ucwords($query, ' '),
                    $query,
                    strtoupper($query)];
        } else {
            // expects known (mb_list_encodings) encoding
            return [$mb_ucfirst($query),
                    mb_convert_case($query, MB_CASE_TITLE, $encoding),
                    $query,
                    mb_convert_case($query, MB_CASE_UPPER, $encoding)];
        }
    }
}
