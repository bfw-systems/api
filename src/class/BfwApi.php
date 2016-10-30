<?php
/**
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */

namespace BfwApi;

use \Exception;

/**
 * Class for API system
 * @package bfw-api
 */
class BfwApi implements \SplObserver
{
    /**
     * @var \BFW\Module $module The bfw module instance for this module
     */
    protected $module;
    
    /**
     * @var \BFW\Config $config The bfw config instance for this module
     */
    protected $config;
    
    /**
     * @var \FastRoute\Dispatcher $dispatcher FastRoute dispatcher
     */
    protected $dispatcher;
    
    /**
     * Constructor
     * 
     * @param \BFW\Module $module
     */
    public function __construct(\BFW\Module $module)
    {
        $this->module = $module;
        $this->config = $module->getConfig();
        
        $this->dispatcher = \FastRoute\simpleDispatcher([$this, 'addRoutesToCollector']);
    }
    
    /**
     * Call by dispatcher; Add route in config to fastRoute router
     * 
     * @param \FastRoute\RouteCollector $router FastRoute router
     * 
     * @return void
     */
    public function addRoutesToCollector(\FastRoute\RouteCollector $router)
    {
        $urlPrefix = $this->config->getConfig('urlPrefix', 'config.php');
        $routes    = $this->config->getConfig('routes', 'routes.php');
        
        foreach ($routes as $slug => $infos) {
            $slug = trim($urlPrefix.$slug);

            //Défault method
            $method = ['GET', 'POST', 'PUT', 'DELETE'];
            
            //If method is declared for the route
            if (isset($infos['httpMethod'])) {
                //Get the method ans remove it from httpMethod array
                $method = $infos['httpMethod'];
                unset($infos['httpMethod']);
            }

            $router->addRoute($method, $slug, $infos);
        }
    }
    
    /**
     * Observer update method
     * Call run method on action "bfw_run_finish".
     * 
     * @param \SplSubject $subject
     * 
     * @return void
     */
    public function update(\SplSubject $subject)
    {
        if ($subject->getAction() === 'bfw_run_finish') {
            $this->run();
        }
    }
    
    /**
     * Run when the notify "bfw_run_finish" is emit
     * Check if we are in an API route
     * If it's an API route,
     * * Get the class name to use for this route
     * * Call the method corresponding to request in the class declared
     * 
     * @return void
     */
    public function run()
    {
        $className = $this->obtainClassNameForCurrentRoute();
        
        //Get current request informations
        $bfwRequest = \BFW\Request::getInstance();
        $method     = strtolower($bfwRequest->getMethod());
        
        if (!class_exists($className)) {
            throw new \Exception('Class '.$className.' not found.');
        }
        if (!method_exists($className, $method.'Request')) {
            throw new \Exception(
                'Method '.$method.'Request not found in class '.$className.'.'
            );
        }
        
        $api = new $className;
        $api->{$method.'Request'}();
    }
    
    /**
     * Obtain the classname to use for current route from fastRoute dispatcher
     * 
     * @return string|void The classname or nothing if error
     * 
     * @throw \Exception If no "className" is define in config for the route.
     */
    protected function obtainClassNameForCurrentRoute()
    {
        //Get current request informations
        $bfwRequest = \BFW\Request::getInstance();
        $request    = $bfwRequest->getRequest()->path;
        $method     = $bfwRequest->getMethod();

        //Get route information from dispatcher
        $routeInfo   = $this->dispatcher->dispatch($method, $request);
        $routeStatus = $routeInfo[0];
        
        //Get and send request http status to the controller/router linker
        $httpStatus = $this->checkStatus($routeStatus);
        
        if ($httpStatus !== 200) {
            http_response_code($httpStatus);
            return;
        }

        global $_GET;
        $_GET = array_merge($_GET, $routeInfo[2]);
        
        if (!isset($routeInfo[1]['className'])) {
            throw new Exception('className not define for uri '.$request);
        }
        
        http_response_code(200);
        return $routeInfo[1]['className'];
    }
    
    /**
     * Get http status for response from dispatcher
     * 
     * @param int $routeStatus : Route status send by dispatcher for request
     * 
     * @return int
     */
    protected function checkStatus($routeStatus)
    {
        $httpStatus = 200;
        
        if ($routeStatus === \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            $httpStatus = 405;
        } elseif ($routeStatus === \FastRoute\Dispatcher::NOT_FOUND) {
            $httpStatus = 404;
        }
        
        return $httpStatus;
    }
}
