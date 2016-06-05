# PHP Dictionary
> Collection indexed with objects and scalars

----------------------------------------------------------------

## Example

```php
<?php
    $dict = new \DPolac\Dictionary();

    $dict[new \stdClass()] = 12;
    $dict['php'] = 23;
    $dict[100] = 'dictionary';
```

----------------------------------------------------------------

<a name="install"></a>
## Installation

### Install via Composer:
```bash
composer require dpolac/dictionary
```

----------------------------------------------------------------

## Usage
Class `\DPolac\Dictionary` implements `Iterator`, `ArrayAccess`,
`Countable` and `Serializable`. It also provides methods for
creating and sorting Dictionary and for converting it to array.

Valid types of keys for Dictionary are:
- object
- integer
- float
- string
- bool
- null

You ***cannot*** use:
- Closure
- array

### Creating
To create empty Dictionary, use constructor.
```php
<?php
    $dict = new \DPolac\Dictionary();
```

You can also create Dictionary from key-value pairs.
```php
<?php
    $dict = \DPolac\Dictionary::fromPairs([
        ['key1', 'value1'],
        ['key2', 'value2'],
        ['key3', 'value3'],
    ]);
```

Last option is to create Dictionary from array.
```php
<?php
    $dict = \DPolac\Dictionary::fromArray([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ]);
```

### Converting to PHP array
There are three methods that let you retrieve data as array:

- `Dictionary::keys()`    - returns array of keys,
- `Dictionary::values()`  - returns array of values,
- `Dictionary::toPairs()` - returns array of key-value pairs; each
                            pair is 2-element array.

### Copying Dictionary
Unlike an array, Dictionary is an object and that means it
is reference type. If you want the copy of Dictionary, you have
to use `clone` keyword or call `Dictionary::getCopy()` method.

### Sorting elements
Just like an array, Dictionary is ordered. To sort Dictionary, use
`Dictionary::sortBy($callback, $direction)` method. Any argument
can be omitted.

- `$callback` will be called for every element. Dictionary will
be ordered by values returned by callback.
First argument of the callback is value and second is key of element.
Instead of callable, you can use `"values"` or `"keys"` string.
- `$direction` can be `"asc"` or `"desc"`. Default value is `"asc"`.

Examples of sorting:

```php
<?php
    $dictionary->sortBy('values','asc');
```

```php
<?php
    $dictionary->sortBy(function($value, $key) {
        return $value->title . $key->name;
    }, 'desc');
```

`sortBy` changes Dictionary it is called for. If you want sorted copy,
chain it with `getCopy`.

```php
<?php
    $sortedDictionary = $dictionary->getCopy()->sortBy('values', 'asc');
```

