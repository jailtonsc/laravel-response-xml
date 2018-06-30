# Laravel Response XML
Add the  method "xml" integrating the laravel's response, converting eloquent return to XML.

[![Total Downloads](https://poser.pugx.org/jailtonsc/laravel-response-xml/d/total.svg)](https://packagist.org/packages/jailtonsc/laravel-response-xml)
[![Latest Stable Version](https://poser.pugx.org/jailtonsc/laravel-response-xml/v/stable.svg)](https://packagist.org/packages/jailtonsc/laravel-response-xml)
[![Latest Unstable Version](https://poser.pugx.org/jailtonsc/laravel-response-xml/v/unstable.svg)](https://packagist.org/packages/jailtonsc/laravel-response-xml)

### Composer Installation

```php
composer require jailtonsc/laravel-response-xml
```

### Integration with Laravel 5.*

Add in config/app.php in place providers

```php
XmlResponse\XmlResponseServiceProvider::class
```

Add in config/app.php in place aliases

```php
'Xml' => XmlResponse\Facades\XmlFacade::class
```

### Publish

```php
php artisan vendor:publish
```

### Example
```php
Route::get('/', function () {
    return response()->xml(User::all());
});
```

With status code

```php
Route::get('/', function () {
    return response()->xml(User::all(), 404);
});
```

Setting by code

```php
$config = [
        'template' => '<test></test>',
        'rowName' => 'name'
    ];

Route::get('/', function () {
    return response()->xml(User::all(), 200, $config);
});
```

Return string xml

```php
$xml = Xml::asXml(User::all());
```

Or

```php
$config = [
        'template' => '<test></test>',
        'rowName' => 'name'
    ];

$xml = Xml::asXml(User::all(), $config);
```

### Configuration

file config/xml.php


**template**: xml template.

**caseSensitive**: case sensitive xml tag.

**showEmptyField**: Show empty field.

**charset**: encoding.

**rowName**: line name if it is an array.


### License

The Laravel Response XML is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
