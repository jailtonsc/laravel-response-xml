<?php

namespace XmlResponse;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class XmlResponseServiceProvider extends ServiceProvider
{
    public function register()
    {
        Response::macro('xml', function ($value) {
            return (new XmlResponse())->array2xml($value);
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
            __DIR__.'/Config/config.php' => config_path('xml.php'),
        ]);
    }
}