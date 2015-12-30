# Soluble\Schema

[![PHP Version](http://img.shields.io/badge/php-5.4+-ff69b4.svg)](https://packagist.org/packages/soluble/schema)
[![HHVM Status](http://hhvm.h4cc.de/badge/soluble/schema.png?style=flat)](http://hhvm.h4cc.de/package/soluble/schema)
[![Build Status](https://travis-ci.org/belgattitude/soluble-schema.png?branch=master)](https://travis-ci.org/belgattitude/soluble-schema)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/soluble-schema/)
[![Latest Stable Version](https://poser.pugx.org/soluble/schema/v/stable.svg)](https://packagist.org/packages/soluble/schema)
[![Total Downloads](https://poser.pugx.org/soluble/schema/downloads.png)](https://packagist.org/packages/soluble/schema)
[![License](https://poser.pugx.org/soluble/schema/license.png)](https://packagist.org/packages/soluble/schema)

## Introduction

Retrieve information form your database schema.

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

### Retrieve table informations in a database schema

```php
<?php

use Soluble\Schema;
use PDO;

$pdo = new PDO("mysql:host=$hostname", $username, $password, [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
]);

$schema = new Schema\Source\Mysql\MysqlInformationSchema($pdo);

// Retrieve full information of all tables in schema
$info = $schema->getTablesInformation();

/*
Return an associative array index by table names.

Each table contains informations about
[
  ['table_name_1'] => [
    ['name']         => 'Table name'
    ['columns']      => 'Associative array with column names'
                            [
                              'col name_1' => ['name' => '', 'type' => '', ...]',
                              'col name_2' => ['name' => '', 'type' => '', ...]',
                            ]

    ['primary_keys'] => 'Indexed array with primary column name(s)'
    ['unique_keys']  => 'Associative array with each unique indexes'
                            [
                              'index name_1' => ['col1', 'col2']',
                              'index_name_2' => ['col3']
                            ]
    ['foreign_keys'] => 'Associative array with foreign keys specifications'
                            [
                                'col_1' => ['column' => '', 'referenced_column' => '', 'referenced_table' => ''],
                                'col_2' => ['column' => '', 'referenced_column' => '', 'referenced_table' => '']
                            ]
    ['references']   => 'Associative array with relations from other tables'
                            [
                                'ref_table_1' => ['column' => '', 'referenced_column' => '', 'constraint_name' => ''],
                                'ref_table_2' => ['column' => '', 'referenced_column' => '', 'constraint_name' => ''],
                            ]
    ['indexes']      => 'Associative array'
  ],
  ['table_name_2'] => [...]
]
*/
     
// Retrieve all tables names
$info = $schema->getTables();

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

// Retrieve column names from a table
$columns = $schema->getColumns($table); 
// -> ['col1', 'col2']

// Retrieve full columns information from a tabme
$columns = $schema->getColumnsInformation($table); 
// -> ['colname' => ['type' => 'char', 'primary' => false, ...]]


```

### Get information about keys

```php
<?php

use Soluble\Schema;
use mysqli;

$mysqli = new mysqli($hostname,$username,$password,$database);
$mysqli->set_charset($charset);

$schema = new Schema\Source\Mysql\MysqlInformationSchema($mysqli);

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

// Retrieve unique keys
$uniques = $schema->getUniqueKeys($table);
// -> ['index_name1' =>  ['col1], ['index_name2' => ['col2', 'col3']]

```

### Get information about relations

```php
<?php

use Soluble\Schema;
use mysqli;

$mysqli = new mysqli($hostname,$username,$password,$database);
$mysqli->set_charset($charset);

$schema = new Schema\Source\Mysql\MysqlInformationSchema($mysqli);

// Get table foreign keys and relations
$relations = $schema->getRelations($table);

var_dump($relations);

/*
array(5) {
  ["brand_id"]=>
  array(3) {
    ["referenced_table"]=>
    string(13) "product_brand"
    ["referenced_column"]=>
    string(8) "brand_id"
    ["constraint_name"]=>
    string(19) "FK_D34A04AD44F5D008"
  }
  ["group_id"]=>
  array(3) {
    ["referenced_table"]=>
    string(13) "product_group"
    ["referenced_column"]=>
    string(8) "group_id"
    ["constraint_name"]=>
    string(19) "FK_D34A04ADFE54D947"
  }
*/


```




## Future enhancements

- Supporting more sources like postgres, oracle
- PSR-6 cache implementation


## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)





