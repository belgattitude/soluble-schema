# Soluble\Schema

[![PHP Version](http://img.shields.io/badge/php-5.4+-ff69b4.svg)](https://packagist.org/packages/soluble/schema)
[![HHVM Status](http://hhvm.h4cc.de/badge/soluble/schema.png?style=flat)](http://hhvm.h4cc.de/package/soluble/schema)
[![Build Status](https://travis-ci.org/belgattitude/soluble-schema.png?branch=master)](https://travis-ci.org/belgattitude/soluble-schema)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/)
[![Latest Stable Version](https://poser.pugx.org/soluble/schema/v/stable.svg)](https://packagist.org/packages/soluble/schema)
[![License](https://poser.pugx.org/soluble/schema/license.png)](https://packagist.org/packages/soluble/schema)

## Introduction

Database information schema parser

## Features

- Read database information schema
- Support tables, index, relations, unique keys...
- Get specific table information 

## Requirements

- PHP engine 5.4+, 7.0+ or HHVM >= 3.2.
- See supported platforms (Mysql, MariaDb)


## Installation

### Installation in your PHP project

`Soluble\Schema` works best via [composer](http://getcomposer.org/).

```sh
php composer require soluble/schema:0.*
```
Most modern frameworks will include Composer out of the box, but ensure the following file is included:

```php
<?php
// include the Composer autoloader
require 'vendor/autoload.php';
```

## Supported platforms

Currently only MySQL and MariaDB are supported. 

| Database     | Driver             | Source class                                         |
|--------------|--------------------|------------------------------------------------------|
| MySQL 5.1+   | pdo_mysql, mysqli  | `Soluble\Schema\Source\Mysql\MysqlInformationSchema` |
| Mariadb 5.1+ | pdo_mysql, mysqli  | `Soluble\Schema\Source\Mysql\MysqlInformationSchema` |

To implement new sources for information schema (oracle, postgres...), just extends the `Soluble\Schema\Source\AbstractSource` class and send a pull request.

## Examples

### Read MySQL information schema

```php
<?php

use Soluble\Schema;
use PDO;

$pdo = new PDO("mysql:host=$hostname", $username, $password, [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
]);

$schema = new Schema\Source\Mysql\MysqlInformationSchema($pdo);

// All schema configuration
$config = $schema->getSchemaConfig();
var_dump($config);

// Test if table exists in schema
if ($schema->hasTable($table)) {
    //...
}

```

### Read table specific information

```php
<?php

use Soluble\Schema;
use mysqli;

$mysqli = new mysqli($hostname,$username,$password,$database);
$mysqli->set_charset($charset);

$schema = new Schema\Source\Mysql\MysqlInformationSchema($mysqli);

// Get table columns
$columns = $schema->getColumns($columns);

// Get primary key
try {
    $pk = $schema->getPrimaryKey($table);
} catch (Schema\Exception\MultiplePrimaryKeyException $e) {
    //...
} catch (Schema\Exception\NoPrimaryKeyException $e) {
    //...
}

// Get multiple primary keys
try {
    $pks = $schema->getPrimaryKeys($table);
} catch (Schema\Exception\MultiplePrimaryKeyException $e) {
    //...
} catch (Schema\Exception\NoPrimaryKeyException $e) {
    // ...
}

// Get unique keys
$uniques = $schema->getUniqueKeys($table);


// Get table foreign keys and relations
$relations = $schema->getRelations($table);

// Full table information
$info = $schema->getTableInformation($table);

```


## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)


[![Total Downloads](https://poser.pugx.org/soluble/schema/downloads.png)](https://packagist.org/packages/soluble/schema)


