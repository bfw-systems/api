<?php

namespace BfwApi\test\unit\mocks;

class Books extends \BfwApi\Rest
{
    public function __construct()
    {
        
    }
    
    public function getRequest()
    {
        echo 'List of all books.';
    }
}
