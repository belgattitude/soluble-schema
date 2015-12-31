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

Retrieve information form your database information schema.

## Features

- Schema discovery made easy.
- Provide an abstraction layer over information tables.
- Support database extended informations (indexes, relations...)

## Requirements

- PHP engine 5.4+, 7.0+ or HHVM >= 3.2.
- Currently supported database platforms (Mysql, MariaDb)

## Installation

Instant installation via [composer](http://getcomposer.org/).

```console
php composer require soluble/schema:0.*
```
Most modern frameworks will include Composer out of the box, but ensure the following file is included:

```php
<?php
// include the Composer autoloader
require 'vendor/autoload.php';
```

## Quick start

### Connection

Initialize the `Schema\Source\MysqlInformationSchema` with a valid `PDO` or `mysqli` connection.

```php
<?php

use Soluble\Schema;

$conn = new \PDO("mysql:host=$hostname", $username, $password, [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
]);

/* Alternatively, use a \mysqli connection instead of PDO */
// $conn = new \mysqli($hostname,$username,$password,$database);
// $conn->set_charset($charset);

$schema = new Schema\Source\MysqlInformationSchema($conn);

// By default the schema (database) is taken from current connection. 
// If you wnat to query a different schema, set it in the second parameter.
$otherDbSchema = new Schema\Source\MysqlInformationSchema($conn, 'otherDbSchema');
```


### API methods

Once a `Schema\Source\SchemaSourceInterface` is intitalized, you have access to the following methods

| Methods                         | Return        | Description                                 |
|---------------------------------|---------------|---------------------------------------------|
| `getSchemaConfig()`             | `ArrayObject` | Retrieve full extended schema config        |
| `getTables()`                   | `array`       | Retrieve table names                        |
| `getTablesInformation()`        | `array`       | Retrieve extended tables information        |
| `hasTable()`                    | `boolean`     | Whether table exists                        |
| `getColumns($table)`            | `array`       | Retrieve column names                       |
| `getColumnsInformation($table)` | `array`       | Retrieve extended columns information       |
| `getPrimaryKey($table)`         | `string`      | Retrieve primary key (unique)               |
| `getPrimaryKeys($table)`        | `array`       | Retrieve primary keys (multiple)            |
| `getUniqueKeys($table)`         | `array`       | Retrieve unique keys                        |
| `getForeignKeys($table)`        | `array`       | Retrieve foreign keys information           |
| `getReferences($table)`         | `array`       | Retrieve referencing tables (relations)     |
| `getIndexes($table)`            | `array`       | Retrieve indexes info                       |


## Examples


### Retrieve table informations in a database schema

```php
<?php

// Retrieve table names defined in schema
$tables = $schema->getTables();

// Retrieve full information of tables defined in schema
$infos = $schema->getTablesInformation();

// The resulting array looks like
[
 ["table_name_1"] => [
    ["name"]    => (string) 'Table name'
    ["columns"] => [ // Columns information, 
                     // @see AbstractSource::getColumnsInformation()
                     "col name_1" => ["name" => "", "type" => "", ...]',
                     "col name_2" => ["name" => "", "type" => "", ...]'
                   ]
    ["primary_keys"] => [ // Primary key column(s) or empty
                      "pk_col1", "pk_col2"
                   ],
    ["unique_keys"]  => [ // Uniques constraints or empty if none
                      "unique_index_name_1" => ["col1", "col3"],
                      "unique_index_name_2" => ["col4"]
                   ],
    ["foreign_keys"] => [ // Foreign keys columns and their references or empty if none
                       "col_1" => [
                                    "referenced_table"  => "Referenced table name",
                                    "referenced_column" => "Referenced column name",
                                    "constraint_name"   => "Constraint name i.e. 'FK_6A2CA10CBC21F742'"
                                  ],
                       "col_2" => [ // ...  
                                  ]
                      ],
    ["references"] => [ // Relations referencing this table
                        "ref_table:ref_column->column1" => [
                             "column"             => "Colum name in this table",
                             "referencing_table"  => "Referencing table name", 
                             "referencing_column" => "Column name in the referencing table", 
                             "constraint_name"    => "Constraint name i.e. 'FK_6A2CA10CBC21F742'"
                           ],
                        "ref_table:ref_column->column2" => [ 
                             //...
                           ]
                      ]
    ["indexes"]  => [],
    ["options"]  => [ // Specific table creation options
                      "comment"   => (string) "Table comment",
                      "collation" => (string) "Table collation, i.e. 'utf8_general_ci'",
                      "type"      => (string) "Table type, i.e: 'BASE TABLE'",
                      "engine"    => (string) "Engine type if applicable, i.e. 'InnoDB'",
                    ]
 ],
 ["table_name_2"] => [
   //...
 ]
]
     
// Test if table exists in schema
if ($schema->hasTable($table)) {
    //...
}
```

### Get table columns information

```php
<?php

// Retrieve just column names from a table
$columns = $schema->getColumns($table); 
// -> ['column_name_1', 'column_name_2']

// Retrieve full columns information from a tabme
$columns = $schema->getColumnsInformation($table); 

// resulting column array looks like ->
[
  ["column_name_1"] => [
   ["type"]      => (string)  "Database type, i.e: 'char', 'int', 'bigint', 'decimal'...",
   ["primary"]   => (boolean) "Whether column is (part of) a primary key",
   ["nullable"]  => (boolean) "Whether column is nullable",
   ["default"]   => (string)  "Default value for column or null if none",

   // Specific to primary key(s) columns
   ["autoincrement"] => (boolean) "Whether the primary key is autoincremented"

   // Specific to numeric, decimal, boolean... types
   ["unsigned"]  => (boolean) "Whether the column is unsigned",
   ["precision"] => (int)     "Number precision (or maximum length)",

   // Specific to character oriented types as well as enum, blobs...
   ["length"]       => (int) "Maximum length",
   ["octet_length"] => (int) "Maximum length in octets (differs from length when using multibyte charsets",

   // Columns specific ddl information
   ["options"]  => [ // Column specific options
        "comment"          => "Column comment",
        "definition"       => "DDL definition, i.e. varchar(250)",
        "ordinal_position" => "Column position number",
        "constraint_type"  => "Type of constraint if applicable",
        "column_key"       => "",
        "charset"          => "Column charset, i.e. 'utf8'",
        "collation"        => "Column collation, i.e. 'utf8_unicode_ci'"
        ],
   ],
   ["column_name_2"] => [ 
       //... 
   ]
]

```


### Retrieve table primary key(s)

```php
<?php

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

```

### Retrieve information about unique keys

```php
<?php

$uniques = $schema->getUniqueKeys($table);

// The resulting array look like
[ 
    "unique_index_name_1" => [
           "column_name_1", "column_name_2"
          ],
    "unique_index_name_2" => [ "column_name_1" ]
]

```

### Get foreign keys informations

```php
<?php

$foreign_keys = $schema->getForeignKeys($table);

// The resulting array looks like
[
  "column_name_1" => [
      "referenced_table"  => "Referenced table name",
      "referenced_column" => "Referenced column name",
      "constraint_name"   => "Constraint name i.e. 'FK_6A2CA10CBC21F742'"
     ],
   "column_name_2" => [ 
      // ...  
     ]
]
```

### Retrieve references informations

```php
<?php

$references = $schema->getReferences($table);

// The resulting array looks like
[
    "ref_table:ref_column->column1" => [
         "column"             => "Colum name in this table",
         "referencing_table"  => "Referencing table name", 
         "referencing_column" => "Column name in the referencing table", 
         "constraint_name"    => "Constaint name i.e. 'FK_6A2CA10CBC21F742'"
       ],
    "ref_table:ref_column->column2" => [ 
         //...
       ]
]
```

## Supported platforms

Currently only MySQL and MariaDB are supported. 

| Database     | Driver             | Source class                                         |
|--------------|--------------------|------------------------------------------------------|
| MySQL 5.1+   | pdo_mysql, mysqli  | `Soluble\Schema\Source\MysqlInformationSchema` |
| Mariadb 5.1+ | pdo_mysql, mysqli  | `Soluble\Schema\Source\MysqlInformationSchema` |

To implement new sources for information schema (oracle, postgres...), just extends the `Soluble\Schema\Source\AbstractSource` class and send a pull request.

## Future enhancements

- Supporting more sources like postgres, oracle
- PSR-6 cache implementation

## Contributing

Contribution are welcome see [contribution guide](./CONTRIBUTING.md)


## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)





