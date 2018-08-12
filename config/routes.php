<?php
/**
 * Config file for fastroute module
 * Declare all routes
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-fastroute
 * @version 2.0
 */

/**
 * Exemple of config route
 * To know data to set in "target" property, please refer you
 * to the controller module.
 * 
 * Exemple if target contains php file to call
 *
 * return [
 *     'routes' => [
 *         '/books' => [
 *             'className'  => 'Books',
 *             'httpMethod' => ['GET']
 *         ],
 *         '/books/{bookId:\d+}' => [
 *             'className' => 'Books'
 *         ],
 *         '/books/{bookId:\d+}/comments' => [
 *             'className'  => 'BooksComments',
 *             'httpMethod' => ['GET', 'POST']
 *         ],
 *         '/books/{bookId:\d+}/comments/{commentId:\d+}' => [
 *             'className'  => 'BooksComments',
 *             'httpMethod' => ['GET']
 *         ]
 *     ]
 * ];
 */

return [
    'routes' => []
];
