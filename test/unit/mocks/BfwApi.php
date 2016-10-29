<?php

namespace BfwApi\test\unit\mocks;

class BfwApi extends \BfwApi\BfwApi
{
    public function __get($name)
    {
        return $this->{$name};
    }
    
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
    
    public function setDispatcher($newDispatcher)
    {
        $this->dispatcher = $newDispatcher;
    }
    
    public function callObtainClassNameForCurrentRoute()
    {
        return $this->obtainClassNameForCurrentRoute();
    }
}
