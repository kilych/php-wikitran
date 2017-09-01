# Wikitran

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
<!-- [![Build Status][ico-travis]][link-travis] -->
<!-- [![Coverage Status][ico-scrutinizer]][link-scrutinizer] -->
<!-- [![Quality Score][ico-code-quality]][link-code-quality] -->
[![Total Downloads][ico-downloads]][link-downloads]

Translate terms using Wikipedia multilingual articles

## Installation and usage

### As command-line tool

``` bash
$ composer g require wikitran/wikitran
$ wikitran [<source> <destination1> <destination2>...] <query for translation>
```
(Be sure set up your $PATH for Composer.)

Use [Wikipedia language codes](https://meta.wikimedia.org/wiki/List_of_Wikipedias#All_Wikipedias_ordered_by_number_of_articles) as source and destination languages.
First code is for source language. Rest codes are for destination languages.
First word that isn't valid language code will be interpreted as first word of your query for translation.

#### Examples

##### Specify source and destination languages

``` bash
$ wikitran de ar es schreiber
```
Query is "schreiber". Source language is "de" (German). Destination languages are "ar" (Arabic) and "es" (Spanish).

##### Specify destination as all available languages

``` bash
$ wikitran de all schreiber
```
Source language is "de" (German). Destination languages are all available languages for the query.

##### Use default destination

``` bash
$ wikitran de schreiber
```
Source language is "de" (German). Destination language is not set. Default value is "all".

##### Use default source and destination

``` bash
$ wikitran scrivener
```
Source language is not set. Default value is "en" (English). Destination language is not set. Default value is "all".

### As PHP library

``` bash
$ composer require wikitran/wikitran
```

``` php
use Wikitran\Translator;

// set russian as default source language instead of english
$tr = new Translator(['source' => 'ru']);

// First arg is for query. Second is for source language (optional).
// Rest are for destination languages (optional).
$pushkin = $tr->translate('Пушкин');
$wiskunde = $tr->translate('wiskunde', 'nl', 'en', 'de', 'fr', 'ru');
```
See also [examples](examples).

## Database

You can use SQL database (SQLite and MySQL supported) as cache for already translated terms.

### Migration

(Using "binary" PHP script)

#### Built-in SQLite database

``` bash
$ wikitran --migrate --createFile
```

#### SQLite

``` bash
$ wikitran --migrate --server sqlite --file <path/to/your/db/file> [--createFile]
```

#### MySQL

``` bash
$ wikitran --migrate --server mysql --db <database name> --user <db user> [--host <host>] [--port <port>] [--password <password>] [--charset <charset>]
```

### Connection

#### Built-in SQLite database

As default (if built-in db exists).

#### SQLite

``` php
use Wikitran\Translator;

$tr = new Translator(['db' => [
    'server' => 'sqlite',
    'file' => 'path/to/your/db/file'
]]);
```

#### MySQL

``` php
use Wikitran\Translator;

// 'password', 'port', etc. are optional
$tr = new Translator(['db' => [
    'server' => 'mysql',
    'db' => 'db_name',
    'user' => 'db_user'
]]);
```

#### PDO instance

If you have instance of class PDO, you can set db connection directly in constructor:
``` php
use Wikitran\Translator;

$tr = new Translator([], $pdo);
```
Or, later:
``` php
use Wikitran\Translator;

$tr = new Translator();

$tr->setConnection($pdo);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/wikitran/wikitran.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
<!-- [ico-travis]: https://img.shields.io/travis/wikitran/wikitran/master.svg?style=flat-square -->
<!-- [ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/wikitran/wikitran.svg?style=flat-square -->
<!-- [ico-code-quality]: https://img.shields.io/scrutinizer/g/wikitran/wikitran.svg?style=flat-square -->
[ico-downloads]: https://img.shields.io/packagist/dt/wikitran/wikitran.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/wikitran/wikitran
<!-- [link-travis]: https://travis-ci.org/wikitran/wikitran -->
<!-- [link-scrutinizer]: https://scrutinizer-ci.com/g/wikitran/wikitran/code-structure -->
<!-- [link-code-quality]: https://scrutinizer-ci.com/g/wikitran/wikitran -->
[link-downloads]: https://packagist.org/packages/wikitran/wikitran
[link-author]: https://github.com/kilych
<!-- [link-contributors]: ../../contributors -->

<!-- 

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practises by being named the following.

```
bin/        
config/
src/
tests/
vendor/
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email kilych@zoho.com instead of using the issue tracker.

## Credits

- [kilych][link-author]
- [All Contributors][link-contributors]
-->
