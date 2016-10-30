<?php
/**
 * Config file for fastroute module
 * Declare all routes
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-fastroute
 * @version 2.0
 */

return (object) [
    'routes' => [
        '/books' => [
            'className'  => '\BfwApi\test\run\Books',
            'httpMethod' => ['GET']
        ]
    ]
];
