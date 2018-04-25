<?php

namespace BfwApi\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/mocks/src/class/Subject.php');

class BfwApi extends Atoum
{
    use \BFW\Test\Helpers\Application;
    
    protected $mock;
    protected $module;
    
    public function beforeTestMethod($testMethod)
    {
        //Define PHP_SAPI on namespace BFW (mock) to have the methode
        //BFW\Application::initCtrlRouterLink executed
        eval('namespace BFW {const PHP_SAPI = \'www\';}');
        
        $this->setRootDir(__DIR__.'/../../../..');
        $this->createApp();
        $this->app->setRunSteps([
            [$this->app, 'initCtrlRouterLink'],
            [$this->app, 'runCtrlRouterLink']
        ]);
        $this->initApp();
        $this->createModule();
        $this->app->run();
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mockGenerator
            ->makeVisible('obtainCtrlRouterInfos')
            ->makeVisible('searchRoute')
            ->makeVisible('execRoute')
            ->makeVisible('runRest')
            ->makeVisible('runGraphQL')
            ->makeVisible('checkStatus')
            ->generate('BfwApi\BfwApi')
        ;
        $this->mock = new \mock\BfwApi\BfwApi($this->module);
    }
    
    protected function createModule()
    {
        $this->module = new \BFW\Test\Mock\Module('bfw-api');
        $config = new \BFW\Config('bfw-api');
        $this->module->setConfig($config);
        $this->module->setStatus(true, true);
        
        $config->setConfigForFile(
            'config.php',
            (object) [
                'urlPrefix'  =>  '/api',
                'useRest'    => true,
                'useGraphQL' => false
            ]
        );
        
        $config->setConfigForFile(
            'routes.php',
            (object) [
                'routes' =>  [
                    '/books' => [
                        'className'  => '\BfwApi\test\unit\mocks\Books',
                        'httpMethod' => ['GET']
                    ],
                    '/books/{bookId:\d+}' => [
                        'className' => 'Books'
                    ],
                    '/books/{bookId:\d+}/comments' => [
                        'className'  => 'BooksComments',
                        'httpMethod' => ['GET', 'POST']
                    ],
                    '/books/{bookId:\d+}/comments/{commentId:\d+}' => [
                        'className'  => 'BooksComments',
                        'httpMethod' => ['GET']
                    ],
                    '/author' => [
                        'httpMethod' => ['GET']
                    ],
                    '/editors' => [
                        'className'  => '\BfwApi\test\unit\mocks\Editors',
                        'httpMethod' => ['GET']
                    ],
                    '/libraries' => [
                        'className'  => '\BfwApi\test\unit\mocks\Libraries',
                        'httpMethod' => ['GET']
                    ]
                ]
            ]
        );
    }
    
    public function testConstructAndGetters()
    {
        $this->assert('test BfwApi::__construct')
            ->object($bfwApi = new \BfwApi\BfwApi($this->module))
                ->isInstanceOf('\SplObserver')
        ;
        
        $this->assert('test BfwApi::getters')
            ->object($bfwApi->getModule())
                ->isIdenticalTo($this->module)
            ->object($bfwApi->getConfig())
                ->isIdenticalto($this->module->getConfig())
            ->object($bfwApi->getDispatcher())
                //It's in the dependency, so I can't check the class name.
                //->isInstanceOf('\FastRoute\\Dispatcher\\GroupCountBased')
            ->string($bfwApi->getExecRouteSystemName())
                ->isEqualTo('bfw-api')
        ;
    }
    
    public function testAddRoutesToCollector()
    {
        $this->assert('test BfwApi::addRoutesToCollector')
            ->given($routeCollector = new \FastRoute\RouteCollector(
                new \FastRoute\RouteParser\Std,
                new \FastRoute\DataGenerator\GroupCountBased
            ))
            ->then
            ->variable($this->mock->addRoutesToCollector($routeCollector))
                ->isNull()
            ->array($routeCollector->getData())
                ->isEqualTo([
                    0 => [ //static routes
                        'GET' => [
                            '/api/books' => [
                                'className' => '\BfwApi\test\unit\mocks\Books'
                            ],
                            '/api/author' => [],
                            '/api/editors' => [
                                'className' => '\BfwApi\test\unit\mocks\Editors'
                            ],
                            '/api/libraries' => [
                                'className'  => '\BfwApi\test\unit\mocks\Libraries'
                            ]
                        ]
                    ],
                    1 => [ //variable routes
                        'GET' => [
                            0 => [
                                'regex' => '~^(?|/api/books/(\d+)|/api/books/(\d+)/comments()|/api/books/(\d+)/comments/(\d+)())$~',
                                'routeMap' => [
                                    2 => [
                                        0 => ['className' => 'Books'],
                                        1 => ['bookId' => 'bookId']
                                    ],
                                    3 => [
                                        0 => ['className' => 'BooksComments'],
                                        1 => ['bookId' => 'bookId']
                                    ],
                                    4 => [
                                        0 => ['className' => 'BooksComments'],
                                        1 => [
                                            'bookId' => 'bookId',
                                            'commentId' => 'commentId'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'POST' => [
                            0 => [
                                'regex' => '~^(?|/api/books/(\d+)|/api/books/(\d+)/comments())$~',
                                'routeMap' => [
                                    2 => [
                                        0 => ['className' => 'Books'],
                                        1 => ['bookId' => 'bookId']
                                    ],
                                    3 => [
                                        0 => ['className' => 'BooksComments'],
                                        1 => ['bookId' => 'bookId']
                                    ]
                                ]
                            ]
                        ],
                        'PUT' => [
                            0 => [
                                'regex' => '~^(?|/api/books/(\d+))$~',
                                'routeMap' => [
                                    2 => [
                                        0 => ['className' => 'Books'],
                                        1 => ['bookId' => 'bookId']
                                    ]
                                ]
                            ]
                        ],
                        'DELETE' => [
                            0 => [
                                'regex' => '~^(?|/api/books/(\d+))$~',
                                'routeMap' => [
                                    2 => [
                                        0 => ['className' => 'Books'],
                                        1 => ['bookId' => 'bookId']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ])
        ;
    }
    
    public function testUpdate()
    {
        $this->assert('test BfwApi::update for adding to linker subject')
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->and($subject->setAction('bfw_ctrlRouterLink_subject_added'))
            ->then
            ->variable($this->mock->update($subject))
                ->isNull()
            ->given($subjectList = \BFW\Application::getInstance()->getSubjectList())
            ->array($observers = $subjectList->getSubjectForName('ctrlRouterLink')->getObservers())
                ->contains($this->mock)
        ;
        
        $this->assert('test BfwApi::update for searchRoute system')
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->and($subject->setAction('searchRoute'))
            ->and($subject->setContext($this->app->getCtrlRouterInfos()))
            ->then
            ->if($this->calling($this->mock)->searchRoute = null)
            ->then
            ->variable($this->mock->update($subject))
                ->isNull()
            ->object($this->mock->getCtrlRouterInfos())
                ->isIdenticalTo($this->app->getCtrlRouterInfos())
            ->mock($this->mock)
                ->call('searchRoute')
                    ->once()
        ;
        
        $this->assert('test BfwApi::update for searchRoute system')
            ->given($ctrlRouterInfos = $this->app->getCtrlRouterInfos())
            ->if($ctrlRouterInfos->isFound = true)
            ->if($ctrlRouterInfos->forWho = $this->mock->getExecRouteSystemName())
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->and($subject->setAction('execRoute'))
            ->and($subject->setContext($ctrlRouterInfos))
            ->then
            ->if($this->calling($this->mock)->execRoute = null)
            ->then
            ->variable($this->mock->update($subject))
                ->isNull()
            ->object($this->mock->getCtrlRouterInfos())
                ->isIdenticalTo($this->app->getCtrlRouterInfos())
            ->mock($this->mock)
                ->call('execRoute')
                    ->once()
        ;
    }
    
    public function testSearchRoute()
    {
        $this->assert('test BfwApi::searchRoute - prepare')
            ->if($this->function->http_response_code = null)
            ->and($_SERVER['REQUEST_URI'] = '/api/books')
            ->and($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and(\BFW\Request::getInstance()->runDetect())
            ->then
            ->given($ctrlRouterInfos = $this->app->getCtrlRouterInfos())
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->if($subject->setContext($ctrlRouterInfos))
            ->and($this->mock->obtainCtrlRouterInfos($subject))
        ;
        
        $this->assert('test BfwApi::searchRoute with a 404 route')
            ->if($this->calling($this->mock)->checkStatus = 404)
            ->and($ctrlRouterInfos->isFound = false)
            ->then
            ->variable($this->mock->searchRoute())
                ->isNull()
            ->function('http_response_code')
                ->never()
            ->boolean($this->mock->getCtrlRouterInfos()->isFound)
                ->isFalse()
        ;
        
        $this->assert('test BfwApi::searchRoute with a 405 route')
            ->if($this->calling($this->mock)->checkStatus = 405)
            ->and($ctrlRouterInfos->isFound = false)
            ->then
            ->variable($this->mock->searchRoute())
                ->isNull()
            ->function('http_response_code')
                ->wasCalledWithArguments(405)
                    ->once()
            ->boolean($this->mock->getCtrlRouterInfos()->isFound)
                ->isTrue()
        ;
        
        $this->assert('test BfwApi::searchRoute with a 200 route without get parameters')
            ->if($this->calling($this->mock)->checkStatus = 200)
            ->and($ctrlRouterInfos->isFound = false)
            ->then
            ->variable($this->mock->searchRoute())
                ->isNull()
            ->string($ctrlRouterInfos->target)
                ->isEqualTo('\BfwApi\test\unit\mocks\Books')
            ->function('http_response_code')
                ->wasCalledWithArguments(200)
                    ->atLeastOnce()
            ->boolean($this->mock->getCtrlRouterInfos()->isFound)
                ->isTrue()
            ->array($_GET)
                ->isEmpty()
        ;
        
        $this->assert('test BfwApi::searchRoute with a 200 route with get parameters')
            ->if($this->calling($this->mock)->checkStatus = 200)
            ->and($ctrlRouterInfos->isFound = false)
            //->and($_SERVER['REQUEST_URI'] = '/books/{bookId:\d+}/comments/{commentId:\d+}')
            ->and($_SERVER['REQUEST_URI'] = '/api/books/123/comments/456')
            ->and($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and(\BFW\Request::getInstance()->runDetect())
            ->then
            ->variable($this->mock->searchRoute())
                ->isNull()
            ->string($ctrlRouterInfos->target)
                ->isEqualTo('BooksComments')
            ->function('http_response_code')
                ->wasCalledWithArguments(200)
                    ->atLeastOnce()
            ->boolean($this->mock->getCtrlRouterInfos()->isFound)
                ->isTrue()
            ->array($_GET)
                ->isEqualTo([
                    'bookId'    => '123',
                    'commentId' => '456'
                ])
        ;
        
        $this->assert('test BfwApi::searchRoute with a 200 route with existing get parameters')
            ->if($this->calling($this->mock)->checkStatus = 200)
            ->and($ctrlRouterInfos->isFound = false)
            //->and($_SERVER['REQUEST_URI'] = '/books/{bookId:\d+}/comments/{commentId:\d+}')
            ->and($_SERVER['REQUEST_URI'] = '/api/books/123/comments/456')
            ->and($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and(\BFW\Request::getInstance()->runDetect())
            ->then
            ->if($_GET = [
                'limit' => '20'
            ])
            ->then
            ->variable($this->mock->searchRoute())
                ->isNull()
            ->string($ctrlRouterInfos->target)
                ->isEqualTo('BooksComments')
            ->function('http_response_code')
                ->wasCalledWithArguments(200)
                    ->atLeastOnce()
            ->boolean($this->mock->getCtrlRouterInfos()->isFound)
                ->isTrue()
            ->array($_GET)
                ->isEqualTo([
                    'limit'     => '20',
                    'bookId'    => '123',
                    'commentId' => '456'
                ])
        ;
        
        $this->assert('test BfwApi::searchRoute with a 200 route without classname')
            ->if($this->calling($this->mock)->checkStatus = 200)
            ->and($ctrlRouterInfos->isFound = false)
            ->and($_SERVER['REQUEST_URI'] = '/api/author')
            ->and($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and(\BFW\Request::getInstance()->runDetect())
            ->then
            ->exception(function() {
                $this->mock->searchRoute();
            })
                ->hasCode(\BfwApi\BfwApi::ERR_CLASSNAME_NOT_DEFINE_FOR_URI)
        ;
    }
    
    public function testCheckStatus()
    {
        $this->assert('test BfwApi::checkStatus with default value')
            ->integer($this->mock->checkStatus('atoum'))
                ->isEqualTo(200)
        ;
        
        $this->assert('test BfwApi::checkStatus with no existing route')
            ->integer($this->mock->checkStatus(\FastRoute\Dispatcher::NOT_FOUND))
                ->isEqualTo(404)
        ;
        
        $this->assert('test BfwApi::checkStatus with method not allowed for the route')
            ->integer($this->mock->checkStatus(\FastRoute\Dispatcher::METHOD_NOT_ALLOWED))
                ->isEqualTo(405)
        ;
    }
    
    public function testExecRoute()
    {
        $this->assert('test BfwApi::execRoute - prepare for all case')
            ->given($ctrlRouterInfos = $this->app->getCtrlRouterInfos())
            ->given($subject = new \BFW\Test\Mock\Subject)
            ->if($subject->setContext($ctrlRouterInfos))
            ->and($this->mock->obtainCtrlRouterInfos($subject))
        ;
        
        $this->assert('test BfwApi::execRoute without class found for route')
            ->if($ctrlRouterInfos->target = null)
            ->then
            ->variable($this->mock->execRoute())
                ->isNull()
        ;
        
        $this->assert('test BfwApi::execRoute - prepare')
            ->if($ctrlRouterInfos->target = '\BfwApi\Test\Helpers\Books')
            ->and($this->calling($this->mock)->runRest = null)
            ->and($this->calling($this->mock)->runGraphQL = null)
            ->and($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and(\BFW\Request::getInstance()->runDetect())
        ;
        
        $this->assert('test BfwApi::execRoute with non existing class')
            ->if($this->function->class_exists = false)
            ->then
            ->exception(function() {
                $this->mock->execRoute();
            })
                ->hasCode(\BfwApi\BfwApi::ERR_RUN_CLASS_NOT_FOUND)
        ;
        
        $this->assert('test BfwApi::execRoute with non existing class')
            ->and($this->function->class_exists = true)
            ->and($this->function->method_exists = false)
            ->then
            ->exception(function() {
                $this->mock->execRoute();
            })
                ->hasCode(\BfwApi\BfwApi::ERR_RUN_METHOD_NOT_FOUND)
        ;
        
        $this->assert('test BfwApi::execRoute with no mode declared')
            ->if($this->function->class_exists = true)
            ->and($this->function->method_exists = true)
            ->then
            ->if($this->module->getConfig()->setConfigKeyForFile('config.php', 'useRest', false))
            ->and($this->module->getConfig()->setConfigKeyForFile('config.php', 'useGraphQL', false))
            ->then
            ->exception(function() {
                $this->mock->execRoute();
            })
                ->hasCode(\BfwApi\BfwApi::ERR_RUN_MODE_NOT_DECLARED)
        ;
        
        $this->assert('test BfwApi::execRoute with rest mode')
            ->if($this->module->getConfig()->setConfigKeyForFile('config.php', 'useRest', true))
            ->and($this->module->getConfig()->setConfigKeyForFile('config.php', 'useGraphQL', false))
            ->then
            ->variable($this->mock->execRoute())
                ->isNull()
            ->mock($this->mock)
                ->call('runRest')
                    ->withArguments('\BfwApi\Test\Helpers\Books', 'get')
                    ->once()
        ;
        
        $this->assert('test BfwApi::execRoute with graphQL mode')
            ->if($this->module->getConfig()->setConfigKeyForFile('config.php', 'useRest', false))
            ->and($this->module->getConfig()->setConfigKeyForFile('config.php', 'useGraphQL', true))
            ->then
            ->variable($this->mock->execRoute())
                ->isNull()
            ->mock($this->mock)
                ->call('runGraphQL')
                    ->withoutAnyArgument()
                    ->once()
        ;
    }
    
    public function testRunRest()
    {
        $this->assert('test BfwApi::runRest with implemented method')
            ->output(function() {
                $this->mock->runRest('\BfwApi\Test\Helpers\Books', 'get');
            })
                ->isEqualTo('List of all books.')
        ;
        
        $this->assert('test BfwApi::runRest without implemented class')
            ->exception(function() {
                $this->mock->runRest('\BfwApi\Test\Helpers\Editors', 'get');
            })
                ->hasCode(\BfwApi\BfwApi::ERR_RUN_REST_NOT_IMPLEMENT_INTERFACE)
        ;
    }
    
    public function testRunGraphQL()
    {
        $this->assert('test BfwApi::runGraphQL')
            ->if($this->function->http_response_code = null)
            ->then
            ->variable($this->mock->runGraphQL())
                ->isNull()
            ->function('http_response_code')
                ->wasCalledWithArguments(501)
                    ->atLeastOnce()
        ;
    }
}