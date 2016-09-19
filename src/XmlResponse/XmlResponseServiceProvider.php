<?php

namespace XmlResponse;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

/**
 * Class XmlResponseServiceProvider
 * @package XmlResponse
 */
class XmlResponseServiceProvider extends ServiceProvider
{
    public function register()
    {
        Response::macro('xml', function ($value, $headerTemplate = []) {
            return (new XmlResponse())->array2xml($value, false, $headerTemplate);
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/Config.php' => config_path('xml.php'),
        ]);
    }
}