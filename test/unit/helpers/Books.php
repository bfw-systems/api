<?php

namespace BfwApi\Test\Helpers;

class Books extends \BfwApi\Rest
{
    public function getRequest()
    {
        echo 'List of all books.';
    }
}
