<?php

namespace xandco\WhoisProxy\Facades;

use Illuminate\Support\Facades\Facade;

class WhoisProxy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'whoisproxy';
    }
}
