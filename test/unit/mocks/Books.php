<?php

namespace BfwApi\test\unit\mocks;

class Books extends \BfwApi\Api
{
    public function __construct()
    {
        
    }
    
    public function getRequest()
    {
        echo 'List of all books.';
    }
}
