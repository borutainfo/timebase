# TimeBase

## Installation

Install the library using Composer. Please read
the [Composer Documentation](https://getcomposer.org/doc/01-basic-usage.md) if you are unfamiliar with Composer or
dependency managers in general.

```shell
composer require boruta/timebase
```

## Requirements

Package requires PHP >= 7.3 and ext-json installed.

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

#### Insert data for given timestamp

To add data for a given timestamp (not the current) use the method `->timestamp(int $timestamp)`. Example:
```php
$timebase->insert()
    ->timestamp(1612022907)
    ->set('test')
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

#### Searching data for specific timestamp

To find a record for a specific timestamp use the method use the method `->timestamp(int $timestamp)` during search query:

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