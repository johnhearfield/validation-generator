# gillidandaweb/validation-generator

An Artisan command for the Laravel Framework to quickly generate validation syntax from database schema.  Still in development.

## Installation

```bash
composer require gillidandaweb/validation-generator
```

Install the service provider:

```php
// config/app.php
'providers' => [
    ...
    GillidandaWeb\DbCommands\ValidationGeneratorServiceProvider::class,
    ...
];
```

## Usage

To generate validation syntax:

```php
artisan generate:validation {table?} {column?} {--output=controller} {--ignoreuser}
```

## Options

### Output

Choose preferred output.  Further choices to follow.

```php
--output
```

```php
        $this->validate($request, [
            'title' => 'nullable|max:255',
            'url' => 'nullable|max:255|url',
            'rank' => 'nullable|integer',
            'owningcategory_id' => 'nullable|integer|min:0',
        ]);
```

### Ignore user columns

Exclude any columns named user_id

```php
--ignoreuser
```

## About Gillidanda

Gillidanda is a web development company in Glasgow, Scotland.

## Postcardware

You are free to use this package as it's [MIT-licensed](LICENSE), but if you find this package useful please send us a postcard from your hometown!

Our address is: Gillidanda, 67 Hillfoot Drive, Glasgow, G61 3QG, Scotland.

