<?php

namespace BfwApi\test\unit;

use \atoum;

require_once(__DIR__.'/../../../../vendor/autoload.php');
require_once(__DIR__.'/../../../../vendor/bulton-fr/bfw/test/unit/mocks/src/class/ApplicationForceConfig.php');
require_once(__DIR__.'/../../../../vendor/bulton-fr/bfw/test/unit/mocks/src/class/Application.php');
require_once(__DIR__.'/../../../../vendor/bulton-fr/bfw/test/unit/mocks/src/class/ConfigForceDatas.php');
require_once(__DIR__.'/../../../../vendor/bulton-fr/bfw/test/unit/mocks/src/class/Modules.php');

require_once(__DIR__.'/../../../../vendor/bulton-fr/bfw/test/unit/helpers/Application.php'); //DEV

class Api extends atoum
{
    use \BFW\test\helpers\Application;
    
    /**
     * @var $class : Instance de la class
     */
    protected $class;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        $this->initApp('');
        
        $this->class = new \BfwApi\test\unit\mocks\Api;
    }
    
    public function testConstructWithoutDatas()
    {
        $this->assert('test Api::__construct without datas')
            ->if($this->class = new \BfwApi\test\unit\mocks\Api)
            ->then
            ->string($this->class->datas)
                ->isEmpty();
    }
    
    /**
     * @TODO Find a way to mock php://input (stream_wrapper_* function ?)
     */
    public function testConstructWithDatas()
    {
        $inputDatas = (object) [
            'name'      => 'foo',
            'firstName' => 'bar'
        ];
        
        $this->assert('test Api::__construct with datas')
            //->if(file_put_contents('php://input', json_encode($inputDatas)))
            ->and($this->class = new \BfwApi\test\unit\mocks\Api)
            ->then
            /*->object($this->class->datas)
                ->isEqualTo($inputDatas)*/;
    }
    
    public function testGetRequest()
    {
        $this->assert('test Api::getRequest')
            ->if($this->class->getRequest())
            ->integer(http_response_code())
                ->isEqualTo(501);
    }
    
    public function testPostRequest()
    {
        $this->assert('test Api::postRequest')
            ->if($this->class->postRequest())
            ->integer(http_response_code())
                ->isEqualTo(501);
    }
    
    public function testPutRequest()
    {
        $this->assert('test Api::putRequest')
            ->if($this->class->putRequest())
            ->integer(http_response_code())
                ->isEqualTo(501);
    }
    
    public function testDeleteRequest()
    {
        $this->assert('test Api::deleteRequest')
            ->if($this->class->deleteRequest())
            ->integer(http_response_code())
                ->isEqualTo(501);
    }
    
    public function testObtainResponseFormatFromAcceptHeader()
    {
        $this->assert('test Api::obtainResponseFormatFromAcceptHeader without header')
            ->variable($this->class->callObtainResponseFormatFromAcceptHeader())
                ->isNull();
        
        $this->assert('test Api::obtainResponseFormatFromAcceptHeader with default browser header')
            ->if($_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8')
            ->string($this->class->callObtainResponseFormatFromAcceptHeader())
                ->isEqualTo('xml');
        
        $this->assert('test Api::obtainResponseFormatFromAcceptHeader with xml header with preference')
            ->if($_SERVER['HTTP_ACCEPT'] = 'application/xml;q=0.9')
            ->string($this->class->callObtainResponseFormatFromAcceptHeader())
                ->isEqualTo('xml');
        
        $this->assert('test Api::obtainResponseFormatFromAcceptHeader with xml header without preference')
            ->if($_SERVER['HTTP_ACCEPT'] = 'application/xml')
            ->string($this->class->callObtainResponseFormatFromAcceptHeader())
                ->isEqualTo('xml');
        
        $this->assert('test Api::obtainResponseFormatFromAcceptHeader with json header without preference')
            ->if($_SERVER['HTTP_ACCEPT'] = 'application/json')
            ->string($this->class->callObtainResponseFormatFromAcceptHeader())
                ->isEqualTo('json');
        
        $this->assert('test Api::obtainResponseFormatFromAcceptHeader with html header without preference')
            ->if($_SERVER['HTTP_ACCEPT'] = 'text/html')
            ->variable($this->class->callObtainResponseFormatFromAcceptHeader())
                ->isNull();
    }
    
    public function testObtainResponseFormatFromGetParameter()
    {
        $this->assert('test Api::obtainResponseFormatFromGetParameter without get parameter')
            ->variable($this->class->callObtainResponseFormatFromGetParameter())
                ->isNull();
        
        $this->assert('test Api::obtainResponseFormatFromGetParameter with get parameter to xml')
            ->if($_GET['format'] = 'xml')
            ->string($this->class->callObtainResponseFormatFromGetParameter())
                ->isEqualTo('xml');
        
        $this->assert('test Api::obtainResponseFormatFromGetParameter with get parameter to json')
            ->if($_GET['format'] = 'json')
            ->string($this->class->callObtainResponseFormatFromGetParameter())
                ->isEqualTo('json');
        
        $this->assert('test Api::obtainResponseFormatFromGetParameter with get parameter to html')
            ->if($_GET['format'] = 'html')
            ->variable($this->class->callObtainResponseFormatFromGetParameter())
                ->isNull();
    }
    
    public function testObtainResponseFormat()
    {
        $this->assert('test Api::obtainResponseFormat without header and get parameter')
            ->if($this->class->callObtainResponseFormat())
            ->variable($this->class->responseFormat)
                ->isNull();
        
        $this->assert('test Api::obtainResponseFormat with only get parameter to json')
            ->if($_GET['format'] = 'json')
            ->and($this->class->callObtainResponseFormat())
            ->string($this->class->responseFormat)
                ->isEqualTo('json');
        
        unset($_GET['format']);
        $this->assert('test Api::obtainResponseFormat with only header to xml')
            ->if($_SERVER['HTTP_ACCEPT'] = 'application/xml')
            ->and($this->class->callObtainResponseFormat())
            ->string($this->class->responseFormat)
                ->isEqualTo('xml');
        
        $this->assert('test Api::obtainResponseFormat with get parameter to xml and header to json')
            ->if($_GET['format'] = 'xml')
            ->and($_SERVER['HTTP_ACCEPT'] = 'application/json')
            ->and($this->class->callObtainResponseFormat())
            ->string($this->class->responseFormat)
                ->isEqualTo('xml');
    }
    
    public function testSendJsonResponse()
    {
        $this->assert('test Api::sendJsonResponse');
        
        $class = $this->class;
        $datas = (object) [
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
        
        $this->output(function() use ($class, $datas) {
            $class->callSendJsonResponse($datas);
        })
            ->isEqualTo('{"elements":{"elemA":[{"elemB":"Foo","elemC":"Bar"},{"elemB":"Foz","elemC":"Baz"}]}}');
    }
    
    public function testSendXmlResponse()
    {
        $this->assert('test Api::sendXmlResponse');
        
        $class = $this->class;
        $datas = (object) [
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
        
        $this->output(function() use ($class, $datas) {
            $class->callSendXmlResponse($datas);
        })
            ->isEqualTo(
                '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<elements>'."\n"
                .' <elemA>'."\n"
                .'  <elemB>Foo</elemB>'."\n"
                .'  <elemC>Bar</elemC>'."\n"
                .' </elemA>'."\n"
                .' <elemA>'."\n"
                .'  <elemB>Foz</elemB>'."\n"
                .'  <elemC>Baz</elemC>'."\n"
                .' </elemA>'."\n"
                .'</elements>'."\n"
            );
    }
    
    public function testSendResponse()
    {
        $class = $this->class;
        $datas = (object) [
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
        
        $this->assert('test Api::sendResponse for xml format')
            ->if($_GET['format'] = 'xml')
            ->and($this->class->callObtainResponseFormat())
            ->then
            ->output(function() use ($class, $datas) {
                $class->callSendResponse($datas);
            })
                ->isEqualTo(
                    '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                    .'<elements>'."\n"
                    .' <elemA>'."\n"
                    .'  <elemB>Foo</elemB>'."\n"
                    .'  <elemC>Bar</elemC>'."\n"
                    .' </elemA>'."\n"
                    .' <elemA>'."\n"
                    .'  <elemB>Foz</elemB>'."\n"
                    .'  <elemC>Baz</elemC>'."\n"
                    .' </elemA>'."\n"
                    .'</elements>'."\n"
                );
        
        $this->assert('test Api::sendResponse for json format')
            ->if($_GET['format'] = 'json')
            ->and($this->class->callObtainResponseFormat())
            ->then
            ->output(function() use ($class, $datas) {
                $class->callSendResponse($datas);
            })
                ->isEqualTo('{"elements":{"elemA":[{"elemB":"Foo","elemC":"Bar"},{"elemB":"Foz","elemC":"Baz"}]}}');
    }
}
