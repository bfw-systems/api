<?php

namespace BfwApi\Test\Helpers;

trait Module
{
    protected $module;
    
    protected function createModule()
    {
        $config     = new \BFW\Config('bfw-api');
        $moduleList = $this->app->getModuleList();
        $moduleList->setModuleConfig('bfw-api', $config);
        $moduleList->addModule('bfw-api');
        
        $this->module = $this->app->getModuleForName('bfw-api');
        
        $this->module->monolog = new \BFW\Monolog(
            'bfw-api',
            \BFW\Application::getInstance()->getConfig()
        );
        $this->module->monolog->addAllHandlers();
        
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
}
