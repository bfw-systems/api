<?php
/**
 * Actions � effectuer lors de l'initialisation du module par le framework.
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-api
 * @version 1.0
 */

require_once($rootPath.'configs/bfw-api/config.php');

if(!empty($apiUrl) && !empty($apiFile) && !empty($apiClass))
{
    $callAPI = false;
    
    if(strpos($request, $apiUrl) === 0)
    {
        $explode_path = explode('/', $request);
        
        $apiMethod = 'index';
        if(isset($explode_path[2]))
        {
            $apiMethod = $explode_path[2];
            unset($explode_path[2]);
        }
        
        unset($explode_path[0], $explode_path[1]);
        $methodParam = implode('/', $explode_path);
        
        $callAPI = true;
        require_once($rootPath.'controllers/'.$apiFile);
        
        $api = new $apiClass;
        $api->{$apiMethod}($methodParam);
        exit;
    }
    
    if($request == $apiFile && $callAPI == false)
    {
        redirection('/');
    }
}
