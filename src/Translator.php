<?php

namespace Wikitran;

use Wikitran\Core\DbTranslator;
use function Wikitran\Core\load_page as load;
use Wikitran\Core\Term;

class Translator
{
    protected $db;
    protected $method;

    public const METHODS = ['web', 'db', 'mixed'];
    public const DB_METHODS = ['db', 'mixed'];

    protected static $langs = [];

    public function __construct($pdo = null, $method = 'mixed')
    {
        if ($pdo instanceof \PDO) {
            $this->db = new DbTranslator($pdo);
        } else {
            $this->db = new DbTranslator();
        }

        $this->setMethod($method);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        if (! $this->db->isConnectionSet()) {
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

    /**
     * Main functionality
     *
     * @param string $source Source language
     *
     * @param string $dest Destination language
     *
     * @param string $query Query for translation
     *
     * @return mixed Returns translation string or false
     */
    public function translate(string $source, string $dest, string $query)
    {
        $queries = self::varyQuery(self::normalize($query));
        if ($this->isDbMethod()
            && false !== $translation = $this->db->translate($source, $dest, $queries)) {
            return $translation;
        } elseif (false !== $from_web = load($source, $queries)) {
            list($succesful_query, $page) = $from_web;
            $term = new Term($source, $succesful_query, $page);

            if ($this->isDbMethod()) {
                $saved = $this->db->save($term);
            }

            if (false !== $translation = $term->translate($dest)) {
                return $translation;
            }
        } else {
            return false;
        }
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

    public static function getLangs()
    {
        $path = dirname(__DIR__) . '/config/langs.php';

        if (count(self::$langs) > 0) {
            return self::$langs;
        } elseif (file_exists($path)) {
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
