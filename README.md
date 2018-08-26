bfw-api
===========

[![Build Status](https://travis-ci.org/bulton-fr/bfw-api.svg?branch=2.0)](https://travis-ci.org/bulton-fr/bfw-api) [![Coverage Status](https://coveralls.io/repos/github/bulton-fr/bfw-api/badge.svg?branch=2.0)](https://coveralls.io/github/bulton-fr/bfw-api?branch=2.0) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bulton-fr/bfw-api/badges/quality-score.png?b=2.0)](https://scrutinizer-ci.com/g/bulton-fr/bfw-api/?branch=2.0)
[![Latest Stable Version](https://poser.pugx.org/bulton-fr/bfw-api/v/stable)](https://packagist.org/packages/bulton-fr/bfw-api) [![License](https://poser.pugx.org/bulton-fr/bfw-api/license)](https://packagist.org/packages/bulton-fr/bfw-api)

Module to use an API with the BFW framework

---

__Install :__

You can use composer to get the module : `composer require bulton-fr/bfw-api @stable`

And to install the module : `./vendor/bin/bfwInstallModules`

__Config :__

All config file for this module will be into `app/config/bfw-api/`. There are two files to configure (manifest.json is for the module update system).

First, the file config.php

* `urlPrefix` : The prefix of all url used by the api.
* `useRest` : If the API will use the REST format
* `useGraphQL` : If the API will use the GraphQL format. But you can't use it for the moment, it's not implemented yet ! (issue [#2](https://github.com/bulton-fr/bfw-api/issues/2))

Next, the file routes.php

It's all routes of your api. The prefix write into the other config file should not be added into the route url. To know the route format to use, please refer you to the exemple write into the routes.php config file.

Note : If not method is present, the route will respond to all http methods (get, set, put and delete).

__Use it :__

I will only explain to REST API because the GraphQL API is not implemented yet. I will update this section when it will implemented.

You will create you API class controllers into the directory /src/api/. All classes should extends the class \BfwApi\Rest class.

You will add a method for each used HTTP method for you controller. All datas receive from the request will be present into the property `$datas`. Il you want return an response, you can use the method `sendResponsesendResponse(&$response)`. This method will automaticaly detect the response format to use (xml or json) from the HTTP request and convert your response to the correct format before sent it.

__Example :__

Configs :
```php
return [
    'urlPrefix' => '/api',
    'useRest' => true,
    'useGraphQL' => false
];
```
```php
return [
    'routes' => [
       '/books/{bookId:\d+}' => [
            'className' => 'Book',
            'method'    => ['GET', 'POST']
        ],
    ]
];
```


Controller class :
```php
namespace Api;

class Book extends \BfwApi\Rest
{
    public function getRequest()
    {
        $returnedDatas = (object) [
            'elements' => (object) [
                'elemA' => [
                    0 => (object) [
                        'elemB' => 'Foo',
                        'elemC' => 'Bar'
                    ],
                    1 => (object) [
                        'elemB' => 'Foz',
                        'elemC' => 'Baz'
                    ]
                ]
            ]
        ];
        
        $this->sendResponse($returnedDatas);
    }
    
    public function postRequest()
    {
        $modele = new \Modeles\Books;
        //We consider to have some checks of the datas here.
        $status = $modele->updateBooks($this->datas);
        
        $response = (object) [
            'status' => $status,
        ];
        $this->sendResponse($response);
    }
}
```
