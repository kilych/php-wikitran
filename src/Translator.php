<?php

namespace Wikitran;

use Wikitran\Core\DbTranslator;
use function Wikitran\Core\load_page as load;
use function Wikitran\Core\parse_page as parse;

class Translator
{
    protected $db;
    protected $method;

    public const METHODS = ['web', 'db', 'mixed'];
    public const DB_METHODS = ['db', 'mixed'];
    public const WEB_METHODS = ['web', 'mixed'];

    protected static $langs = [];

    public function __construct($pdo = null, $method = 'mixed')
    {
        $this->db = new DbTranslator($pdo);
        $this->setMethod($method);
    }

    /**
     * Main functionality
     * @param string $query Query for translation
     * @param string $source Source language
     * @param string $dest Destination language
     * @param string[] $dests Rest destination languages
     * @return mixed Returns translation array or false
     */
    public function translate(string $query, string $source, string $dest, string ...$dests)
    {
        array_unshift($dests, $dest);
        if (in_array('all', $dests)) {
            $dests = ['all'];
        }
        $queries = self::varyQuery(self::normalize($query));
        if ($this->isDbMethod()
            && false !== $term = $this->db->getTerm($queries, $source)) {
        } elseif ($this->isWebMethod()) {
            $term = $this->getTerm($queries, $source);
            if ($this->isDbMethod()) {
                $this->db->save($term);
            }
        }
        if (isset($term) && $term) {
            return $term->translate($dests);
        }
        return false;
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

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        if (! $this->db->connected()) {
            $this->method = 'web';
        } elseif ($this->isMethod($method)) {
            $this->method = $method;
        } else {
            $this->method = 'mixed';
        }
    }

    public function isMethod($method = null)
    {
        $method = ($method) ? $method : $this->method;
        return in_array($method, self::METHODS);
    }

    public function isDbMethod($method = null)
    {
        $method = ($method) ? $method : $this->method;
        return in_array($method, self::DB_METHODS);
    }

    public function isWebMethod($method = null)
    {
        $method = ($method) ? $method : $this->method;
        return in_array($method, self::WEB_METHODS);
    }

    public static function getLangs()
    {
        $path = dirname(__DIR__) . '/config/langs.php';

        if (count(self::$langs) > 0) {
            return self::$langs;
        } elseif (is_file($path)) {
            require_once $path;
            self::$langs = LANGS;
            return self::$langs;
        } else {
            throw new \Exception(__METHOD__ . " $path doesn't exist");
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
        } // expects known (mb_list_encodings) encoding:
        else {
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
