<?php
/**
 * Fichier de configuration du module bfw-api
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-api
 * @version 2.0
 */

return [
    /**
     * @var string $urlPrefix : Prefix url to use for all api url
     * @example '/api' for have an api url /api/books/
     */
    'urlPrefix' => '/api',
    
    /**
     * @var boolean $useRest : To use REST api mode
     */
    'useRest' => true,
    
    /**
     * @var boolean $useGraphQL : To use GraphQL api mode (WIP)
     * @link https://github.com/bulton-fr/bfw-api/issues/2
     * 
     * Not implemented yet. If you set the value to true, you will obtain a
     * 501 http response.
     */
    'useGraphQL' => false
];
