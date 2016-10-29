<?php

namespace BfwApi\test\unit\mocks;

class Dispatcher extends \FastRoute\Dispatcher\GroupCountBased
{
    public function __get($name)
    {
        return $this->{$name};
    }
}
