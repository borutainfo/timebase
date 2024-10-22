# TimeBase
![Tests Status](https://badgen.net/badge/tests/success/green)

Flat file database for storage any data as events in timeline and finding it for a given timestamp.

### Table of contents
1. [Description](#description)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Usage](#usage)
    1. [Quick introduction](#quick-introduction)
    2. [Details](#details)
       1. [Extra logger in constructor](#extra-logger-in-constructor)
       2. [Data types](#data-types)
       3. [Setting the storage namespace](#setting-the-storage-namespace) 
       4. [Inserting data for specific timestamp](#inserting-data-for-specific-timestamp)
       5. [Searching data for specific timestamp](#searching-data-for-specific-timestamp)
       6. [Getting multiple records with the same timestamp](#getting-multiple-records-with-the-same-timestamp)
       7. [Searching strategies](#searching-strategies)
5. [Tests](#tests)

## Description

The data are stored in files, with the possibility of using multi-level namespaces - in this case, the files are stored in subdirectories. Each day is stored in a separate file named `YYYY-MM-DD.tb`.

The data records are searched using a binary search algorithm, so it's quick and doesn't require loading the entire file into memory. Each record is one line in file, in format `{timestamp}/{data}` (`data` is result of `base64_encode(json_encode(...))`). The records are sorted, and you can add multiple records for one timestamp - they are saved and returned in the order they were added.

There are many use cases for such a database, e.g. storing stock exchange data (volumes, price values etc.) for trading strategy backtesting.

## Requirements

Package requires PHP >= 7.3 and ext-json installed.

## Installation

Install the library using Composer. Please read
the [Composer Documentation](https://getcomposer.org/doc/01-basic-usage.md) if you are unfamiliar with Composer or
dependency managers in general.

```shell
composer require boruta/timebase
```

## Usage

### Quick introduction

Creating database instance. First constructor argument is a path where to store database files.

```php
$timebase = new Boruta\Timebase\Timebase(__DIR__ . '/database/');
```

Inserting data (for current timestamp) :

```php
$timebase->insert()->set('test')->execute();
```

Getting last inserted data (last record):

```php
$result = $timebase->search()->execute();
```

You will get array as result, with keys `timestamp` and `value`:
```php
array(2) {
  ["timestamp"]=>
  int(1612022907)
  ["value"]=>
  string(4) "test"
}
```

### Details 

#### Extra logger in constructor

You can optionally give the second argument which is a logger object, compatible with `Psr\Log\LoggerInterface`. The application append logs only in case of errors.
```php
$timebase = new Boruta\Timebase\Timebase(__DIR__ . '/database/', $logger);
```

#### Data types

The data can be of any native type, e.g. `int`, `string`, `array`. When searching for data, they will be of the same type.
```php
$timebase->insert()
    ->set(123) // or ->set('test') or ->set(['test' => 123]) etc.
    ->execute();
```

#### Setting the storage namespace

You can store your data in different storages on many levels. Please use the method `->storage(array $storage)` to set this.

Examples of inserting data into specific storage:
```php
$timebase->insert()
    ->storage(['test']) // storage path: __DIR__ . '/database/test/'
    ->set('test')
    ->execute();
```

```php
$timebase->insert()
    ->storage(['level0', 'level1']) // storage path: __DIR__ . '/database/level0/level1/'
    ->set('test')
    ->execute();
```

Examples of reading data from specific storage:
```php
$result = $timebase->search()
    ->storage(['test']) // storage path: __DIR__ . '/database/test/'
    ->execute();
```

```php
$result = $timebase->search()
    ->storage(['level0', 'level1']) // storage path: __DIR__ . '/database/level0/level1/'
    ->execute();
```

#### Inserting data for specific timestamp

To add data for a given timestamp (not the current) use the method `->timestamp(int $timestamp)`. Example:
```php
$timebase->insert()
    ->timestamp(1612022907)
    ->set('test')
    ->execute();
```

#### Searching data for specific timestamp

To find a record for a specific timestamp use the method `->timestamp(int $timestamp)` during search query:

```php
$result = $timebase->search()
    ->timestamp(1612022907)
    ->execute();
```
In the default strategy, you will get one record that is closest to the timestamp you entered.

#### Getting multiple records with the same timestamp
If you have more than one record with the same timestamp you can retrieve them using the `->all()` method. 
```php
$result = $timebase->search()
    ->timestamp(1612022907)
    ->all()
    ->execute();
```
In the result array there will be an additional key `all` containing all records for given timestamp, in the order in which they were added:
```php
array(3) {
  ["timestamp"]=>
  int(1612012606)
  ["value"]=>
  string(4) "test"
  ["all"]=>
  array(2) {
    [0]=>
    string(4) "test"
    [1]=>
    string(4) "test"
  }
}
```


#### Searching strategies

**Nearest** (default)

The default strategy returns the record with the nearest timestamp to the given one. Usage (is not necessary as it is the default):
```php
$result = $timebase->search()
    ->strategy(\Boruta\Timebase\Common\Constant\SearchStrategyConstant::NEAREST)
    ->timestamp(1612022907)
    ->execute();
```
If the time distance is the same for two records, you will get the earlier one. 

**Exact**

If there is no record for the timestamp you provided, you will get `null` value. There must be a record in the database with exactly the same timestamp.
```php
$result = $timebase->search()
    ->strategy(\Boruta\Timebase\Common\Constant\SearchStrategyConstant::EXACT)
    ->timestamp(1612022907)
    ->execute();
```

**Earlier**

If there is no record for the timestamp you entered, you will get the closest one before that timestamp or `null`.
```php
$result = $timebase->search()
    ->strategy(\Boruta\Timebase\Common\Constant\SearchStrategyConstant::EARLIER)
    ->timestamp(1612022907)
    ->execute();
```

**Later**

If there is no record for the timestamp you entered, you will get the closest one after that timestamp or `null`.
```php
$result = $timebase->search()
    ->strategy(\Boruta\Timebase\Common\Constant\SearchStrategyConstant::LATER)
    ->timestamp(1612022907)
    ->execute();
```

## Tests
To run the tests in the package, execute the following command:
```shell
vendor/bin/phpunit tests
```
After a while you will get the result, example:
```
OK (31 tests, 526577 assertions)
```