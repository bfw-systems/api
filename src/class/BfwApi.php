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
     * @const ERR_RUN_CLASS_NOT_FOUND : Error code if the class to run has
     * not been found
     */
    const ERR_RUN_CLASS_NOT_FOUND = 2001001;
    
    /**
     * @const ERR_RUN_METHOD_NOT_FOUND : Error code if the method to run has
     * not been found into the class
     */
    const ERR_RUN_METHOD_NOT_FOUND = 2001002;
    
    /**
     * @const ERR_RUN_MODE_NOT_DECLARED : Error code if no mode (rest/graphQL)
     * is declared into config file
     */
    const ERR_RUN_MODE_NOT_DECLARED = 2001003;
    
    /**
     * @const ERR_CLASSNAME_NOT_DEFINE_FOR_URI : Error code if the class to use
     * for current api route is not defined
     */
    const ERR_CLASSNAME_NOT_DEFINE_FOR_URI = 2001004;
    
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
     * @var \stdClass|null $ctrlRouterInfos The context object passed to
     * subject for the action "searchRoute".
     */
    protected $ctrlRouterInfos;
    
    /**
     * Constructor
     * 
     * @param \BFW\Module $module
     */
    public function __construct(\BFW\Module $module)
    {
        $this->module = $module;
        $this->config = $module->getConfig();
        
        $this->dispatcher = \FastRoute\simpleDispatcher([
            $this,
            'addRoutesToCollector'
        ]);
    }
    
    /**
     * Getter accessor for module property
     * 
     * @return \BFW\Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Getter accessor for config property
     * 
     * @return \BFW\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Getter accessor for dispatcher property
     * 
     * @return \FastRoute\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Getter accessor for ctrlRouterInfos property
     * 
     * @return \stdClass
     */
    public function getCtrlRouterInfos()
    {
        return $this->ctrlRouterInfos;
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
        $urlPrefix = $this->config->getValue('urlPrefix', 'config.php');
        $routes    = $this->config->getValue('routes', 'routes.php');
        
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
     * Call run method on action "bfw_run_finish" and route has
     * not been already found.
     * Update attribute routeFindByOther on action "request_route_find".
     * 
     * @param \SplSubject $subject
     * 
     * @return void
     */
    public function update(\SplSubject $subject)
    {
        if ($subject->getAction() === 'bfw_ctrlRouterLink_subject_added') {
            $app = \BFW\Application::getInstance();
            $app->getSubjectList()
                ->getSubjectForName('ctrlRouterLink')
                ->attach($this)
            ;
        }
        
        if ($subject->getAction() === 'searchRoute') {
            $this->ctrlRouterInfos = $subject->getContext();
            
            if ($this->ctrlRouterInfos->isFound === false) {
                $this->run();
            }
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
        if ($className === null) {
            return;
        }
        
        //Get current request informations
        $bfwRequest = \BFW\Request::getInstance();
        $method     = strtolower($bfwRequest->getMethod());
        
        if (!class_exists($className)) {
            throw new Exception(
                'Class '.$className.' not found.',
                self::ERR_RUN_CLASS_NOT_FOUND
            );
        }
        if (!method_exists($className, $method.'Request')) {
            throw new Exception(
                'Method '.$method.'Request not found in class '.$className.'.',
                self::ERR_RUN_METHOD_NOT_FOUND
            );
        }
        
        $useRest    = $this->config->getValue('useRest', 'config.php');
        $useGraphQL = $this->config->getValue('useGraphQL', 'config.php');
        
        if ($useRest === true) {
            return $this->runRest($className, $method);
        } elseif ($useGraphQL === true) {
            return $this->runGraphQL();
        }
        
        throw new Exception(
            'Please choose between REST and GraphQL in config file.',
            self::ERR_RUN_MODE_NOT_DECLARED
        );
    }
    
    /**
     * Call the method for the current request for Rest api mode
     * 
     * @param string $className The class name to use for the route
     * @param string $method The method name to use (get/post/delete/put)
     * 
     * @throws Exception If the interface is not implemented by the class
     * 
     * @return void
     */
    protected function runRest($className, $method)
    {
        $api = new $className;
        $api->{$method.'Request'}();
    }
    
    /**
     * Call the method for the current request for GraphQL api mode
     * 
     * Not implemented yet
     */
    protected function runGraphQL()
    {
        //Not implement yet
        http_response_code(501);
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
        
        if ($httpStatus === 404) {
            //404 will be declared by \BFW\Application::runCtrlRouterLink()
            return;
        }
        
        http_response_code($httpStatus);
        $this->ctrlRouterInfos->isFound = true;
        
        if ($httpStatus !== 200) {
            return;
        }

        global $_GET;
        $_GET = array_merge($_GET, $routeInfo[2]);
        
        if (!isset($routeInfo[1]['className'])) {
            throw new Exception(
                'className not define for uri '.$request,
                self::ERR_CLASSNAME_NOT_DEFINE_FOR_URI
            );
        }
        
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
