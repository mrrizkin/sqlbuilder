# SQLBuilder

SQLBuilder is a PHP library for building SQL queries in a safe and efficient manner.

## Installation

Use the package manager [composer](https://getcomposer.org/) to install SQLBuilder.

```sh
composer require mrrizkin/sqlbuilder
```

## Usage

```php
use SQLBuilder\SQLBuilder;

// Select query
$sql = SQLBuilder::select("name, email")
    ->from("users")
    ->where("id", "=", 1)
    ->build();

print_r($sql);
/**
 * Output:
 * Array (
 *   [0] => SELECT "name", "email" FROM "users" WHERE "id" = ?
 *   [1] => Array (
 *     [0] => 1
 *   )
 * )
 */




// Update query
$sql = SQLBuilder::update("users")
    ->set("name", "John")
    ->where("id", "=", 1)
    ->build();

print_r($sql);
/**
 * Output:
 * Array (
 *   [0] => UPDATE "users" SET "name" = ? WHERE "id" = ?
 *   [1] => Array (
 *     [0] => John
 *     [1] => 1
 *   )
 * )
 */




// Delete query
$sql = SQLBuilder::delete("users")
    ->where("id", "=", 1)
    ->build();

print_r($sql);
/**
 * Output:
 * Array (
 *   [0] => DELETE FROM "users" WHERE "id" = ?
 *   [1] => Array (
 *     [0] => 1
 *   )
 * )
 */




// Insert query
$sql = SQLBuilder::insert("users")
    ->columns(["name", "email"])
    ->values(["john", "john@email.com"])
    ->build();

print_r($sql);
/**
 * Output:
 * Array (
 *   [0] => INSERT INTO "users" ("name", "email") VALUES (?, ?)
 *   [1] => Array (
 *     [0] => john
 *     [1] => john@email.com
 *   )
 * )
 */
```

## Testing

Run the tests with [PHPUnit](https://phpunit.de/):

```sh
./vendor/bin/phpunit tests
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](/LICENSE)