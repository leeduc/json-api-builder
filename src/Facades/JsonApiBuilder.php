<?php
namespace PhpSoft\JsonApiBuilder\Facades;
use Illuminate\Support\Facades\Facade;

class JsonApiBuilder extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'phpsoft.jsonapibuilder'; }

}
