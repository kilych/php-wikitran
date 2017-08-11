<?php

namespace Wikitran;

use Wikitran\Core\DbTranslator;
use function Wikitran\Core\load_page as load;
use function Wikitran\Core\parse_page as parse;

class Translator
{
    protected $config = [
        'method' => 'mixed',
        'source' => 'en',
        'dests' => ['all'],
        'db' => []
    ];
    protected $db;

    public const METHODS = ['web', 'db', 'mixed'];
    public const DB_METHODS = ['db', 'mixed'];
    public const WEB_METHODS = ['web', 'mixed'];

    protected static $langs = [];

    public function __construct($config = [], $pdo = null)
    {
        $dbconfig = (array_key_exists('db', $config)) ?
                  $config['db'] : $this->config['db'];
        $this->db = new DbTranslator($pdo, $dbconfig);
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

        if ($this->isDbMethod()
            && false !== $term = $this->db->getTerm($queries, $source)) {
        } elseif ($this->isWebMethod()) {
            $term = $this->getTerm($queries, $source);
            if ($this->isDbMethod()) {
                $this->db->save($term);
            }
        }

        return (isset($term) && $term) ? $term->translate($dests) : false;
    }

    public function translateSet(string $source, string $dest, array $queries)
    {
        return array_filter(array_map(
            function ($query) use ($source, $dest) {
                return $this->translate($source, $dest, $query);
            },
            $queries
        ));
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

    public function setConfig(array $config)
    {
        if (! $this->db->connected()) {
            $config['method'] = 'web';
            error_log(__METHOD__ . ' No db connection. Translation method is "web"');
        } elseif (array_key_exists('method', $config)
                  && !in_array($config['method'], self::METHODS)) {
            error_log(__METHOD__ . "Unexpected method: {$config['method']}");
            unset($config['method']);
        }

        if (array_key_exists('source', $config)
            && !self::isLang($config['source'], true)) {
            unset($config['source']);
        }

        if (array_key_exists('dests', $config) && is_array($config['dests'])) {
            $config['dests'] = array_filter($config['dests'], function ($lang) {
                return self::isLang($lang, true);
            });
        }

        $this->config = array_merge($this->config, $config);
    }

    public function getMethod()
    {
        return $this->config['method'];
    }

    public function isDbMethod()
    {
        return in_array($this->config['method'], self::DB_METHODS);
    }

    public function isWebMethod()
    {
        return in_array($this->config['method'], self::WEB_METHODS);
    }

    public static function isLang($langCode, $say = false)
    {
        $res = array_key_exists($langCode, self::getLangs());
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
        // http://ru.stackoverflow.com/a/72543 :
        $mb_ucfirst = function ($q) use ($encoding) {
            $str = mb_convert_case(
                substr($q, 0, 2),
                MB_CASE_TITLE,
                $encoding
            );
            $q[0] = $str[0];
            $q[1] = $str[1];    // two bites for one char
            return $q;
        };
        if ($encoding == 'ASCII') {
            return ucfirst(strtolower($q));
        } else {
            // expects known (mb_list_encodings) encoding:
            return $mb_ucfirst(mb_convert_case($q, MB_CASE_LOWER, $encoding));
        }
    }

    // see http://stackoverflow.com/a/17817754
    protected static function varyQuery(string $query)
    {
        if ('ASCII' === $encoding = mb_detect_encoding($query)) {
            return [$query,
                    ucwords($query, ' '),
                    strtoupper($query)];
        } else {
            // expects known (mb_list_encodings) encoding
            return [$query,
                    mb_convert_case($query, MB_CASE_TITLE, $encoding),
                    mb_convert_case($query, MB_CASE_UPPER, $encoding)];
        }
    }
}
