<?php

namespace XmlResponse\Facades;  

use Illuminate\Support\Facades\Facade;  

class XmlFacade extends Facade
{

    protected static function getFacadeAccessor() 
    { 
        return 'XmlResponse\XmlResponse'; 
    }
}