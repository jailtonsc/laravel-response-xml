# Laravel Response XML
Add the  method "xml" integrating the laravel's response, converting eloquent return to XML.

### Composer Installation

```php
composer require jailtonsc/laravel-response-xml
```

### Integration with Laravel 5.3.*

Add in config/app.php in place providers

```php
XmlResponse\XmlResponseServiceProvider::class
```

### Publish

```php
php artisan vendor:publish
```

### Example
```php
return response()->xml(User::all());
```

or

```php
Route::get('/', function () {
    return response()->xml(User::all());
});
```

### License

The Laravel Response XML is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)