<?php

namespace BfwApi\test\unit;

use \atoum;

$vendorPath = realpath(__DIR__.'/../../../vendor');
require_once($vendorPath.'/autoload.php');
require_once($vendorPath.'/bulton-fr/bfw/test/unit/helpers/Application.php');

class Rest extends atoum
{
    use \BFW\Test\Helpers\Application;
    use \BfwApi\Test\Helpers\Module;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->setRootDir(__DIR__.'/../../..');
        $this->createApp();
        $this->disableSomeCoreSystem();
        $this->initApp();
        $this->removeLoadModules();
        $this->createModule();
        
        if ($testMethod === 'testConstructAndGetters') {
            return;
        }
        
        $this->mockGenerator
            ->makeVisible('obtainDatasFromRequest')
            ->makeVisible('obtainResponseFormat')
            ->makeVisible('obtainResponseFormatFromAcceptHeader')
            ->makeVisible('obtainResponseFormatFromGetParameter')
            ->makeVisible('sendResponse')
            ->makeVisible('sendJsonResponse')
            ->makeVisible('sendXmlResponse')
            ->shunt('__construct')
            ->generate('BfwApi\Rest')
        ;
        
        $this->mock = new \mock\BfwApi\Rest;
    }
    
    public function testConstruct()
    {
        //Shunted method.
    }
    
    public function testGetRequest()
    {
        $this->assert('test Rest::getRequest')
            ->if($this->function->http_response_code = null)
            ->then
            ->variable($this->mock->getRequest())
                ->isNull()
            ->function('http_response_code')
                ->wasCalledWithArguments(501)
                    ->once()
        ;
    }
    
    public function testPostRequest()
    {
        $this->assert('test Rest::postRequest')
            ->if($this->function->http_response_code = null)
            ->then
            ->variable($this->mock->postRequest())
                ->isNull()
            ->function('http_response_code')
                ->wasCalledWithArguments(501)
                    ->once()
        ;
    }
    
    public function testPutRequest()
    {
        $this->assert('test Rest::putRequest')
            ->if($this->function->http_response_code = null)
            ->then
            ->variable($this->mock->putRequest())
                ->isNull()
            ->function('http_response_code')
                ->wasCalledWithArguments(501)
                    ->once()
        ;
    }
    
    public function testDeleteRequest()
    {
        $this->assert('test Rest::deleteRequest')
            ->if($this->function->http_response_code = null)
            ->then
            ->variable($this->mock->deleteRequest())
                ->isNull()
            ->function('http_response_code')
                ->wasCalledWithArguments(501)
                    ->once()
        ;
    }
    
    public function testObtainDatasFromRequestAndGetDates()
    {
        $this->assert('test Rest::obtainDatasFromRequest without CONTENT_TYPE')
            ->if($_SERVER = [])
            ->then
            ->variable($this->mock->obtainDatasFromRequest())
                ->isNull()
            ->array($this->mock->getDatas())
                ->isEmpty()
        ;
        
        $this->assert('test Rest::obtainDatasFromRequest with json type')
            ->if($_SERVER['CONTENT_TYPE'] = 'application/json')
            ->and($this->function->file_get_contents = '{"id":123}')
            ->then
            ->variable($this->mock->obtainDatasFromRequest())
                ->isNull()
            ->array($this->mock->getDatas())
                ->isEqualTo([
                    'id' => 123
                ])
        ;
        
        $this->assert('test Rest::obtainDatasFromRequest from POST parameter')
            ->if($_SERVER['CONTENT_TYPE'] = 'application/xml')
            ->given($_POST = ['id' => 456])
            ->then
            ->variable($this->mock->obtainDatasFromRequest())
                ->isNull()
            ->array($this->mock->getDatas())
                ->isEqualTo([
                    'id' => 456
                ])
        ;
    }
    
    public function testObtainResponseFormat()
    {
        $this->assert('test Rest::obtainResponseFormat for unknown format')
            ->if($this->function->http_response_code = null)
            ->and($this->calling($this->mock)->obtainResponseFormatFromAcceptHeader = null)
            ->and($this->calling($this->mock)->obtainResponseFormatFromGetParameter = null)
            ->then
            ->variable($this->mock->obtainResponseFormat())
                ->isNull()
            ->string($this->mock->getResponseFormat())
                ->isEmpty()
            ->function('http_response_code')
                ->wasCalledWithArguments(406)
                    ->once()
        ;
        
        $this->assert('test Rest::obtainResponseFormat with format in header')
            ->and($this->calling($this->mock)->obtainResponseFormatFromAcceptHeader = 'json')
            ->and($this->calling($this->mock)->obtainResponseFormatFromGetParameter = null)
            ->then
            ->variable($this->mock->obtainResponseFormat())
                ->isNull()
            ->string($this->mock->getResponseFormat())
                ->isEqualTo('json')
        ;
        
        $this->assert('test Rest::obtainResponseFormat with format in header and GET (use GET)')
            ->and($this->calling($this->mock)->obtainResponseFormatFromAcceptHeader = 'json')
            ->and($this->calling($this->mock)->obtainResponseFormatFromGetParameter = 'xml')
            ->then
            ->variable($this->mock->obtainResponseFormat())
                ->isNull()
            ->string($this->mock->getResponseFormat())
                ->isEqualTo('xml')
        ;
    }
    
    public function testObtainResponseFormatFromAcceptHeader()
    {
        $this->assert('test Rest::obtainResponseFormatFromAcceptHeader without header info')
            ->if($_SERVER = [])
            ->then
            ->variable($this->mock->obtainResponseFormatFromAcceptHeader())
                ->isNull()
        ;
        
        $this->assert('test Rest::obtainResponseFormatFromAcceptHeader without xml or json into info')
            ->if($_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml')
            ->then
            ->variable($this->mock->obtainResponseFormatFromAcceptHeader())
                ->isNull()
        ;
        
        $this->assert('test Rest::obtainResponseFormatFromAcceptHeader with xml')
            ->if($_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9')
            ->then
            ->string($this->mock->obtainResponseFormatFromAcceptHeader())
                ->isEqualTo('xml')
        ;
        
        $this->assert('test Rest::obtainResponseFormatFromAcceptHeader with xml and json')
            ->if($_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/json;q=0.9,application/xml;q=0.8')
            ->then
            ->string($this->mock->obtainResponseFormatFromAcceptHeader())
                ->isEqualTo('json')
        ;
    }
    
    public function testObtainResponseFormatFromGetParameter()
    {
        $this->assert('test Rest::obtainResponseFormatFromGetParameter without get datas')
            ->if($_GET = [])
            ->then
            ->variable($this->mock->obtainResponseFormatFromGetParameter())
                ->isNull()
        ;
        
        $this->assert('test Rest::obtainResponseFormatFromGetParameter with bad get datas')
            ->if($_GET['format'] = 'html')
            ->then
            ->variable($this->mock->obtainResponseFormatFromGetParameter())
                ->isNull()
        ;
        
        $this->assert('test Rest::obtainResponseFormatFromGetParameter with xml format value')
            ->if($_GET['format'] = 'xml')
            ->then
            ->string($this->mock->obtainResponseFormatFromGetParameter())
                ->isEqualTo('xml')
        ;
        
        $this->assert('test Rest::obtainResponseFormatFromGetParameter with json format value')
            ->if($_GET['format'] = 'json')
            ->then
            ->string($this->mock->obtainResponseFormatFromGetParameter())
                ->isEqualTo('json')
        ;
    }
    
    public function testSendResponseWithoutFormatDefined()
    {
        $this->assert('test Rest::sendResponse with json format')
            ->given($response = [])
            ->if($this->calling($this->mock)->sendJsonResponse = null)
            ->and($this->calling($this->mock)->sendXmlResponse = null)
            ->then
            ->variable($this->mock->sendResponse($response))
                ->isNull()
            ->mock($this->mock)
                ->call('sendJsonResponse')
                    ->never()
                ->call('sendXmlResponse')
                    ->never()
        ;
    }
    
    public function testSendResponseWithJsonFormat()
    {
        $this->assert('test Rest::sendResponse with json format')
            ->if($_GET['format'] = 'json')
            ->and($this->mock->obtainResponseFormat())
            ->then
            ->string($this->mock->getResponseFormat())
                ->isEqualTo('json') //Only check to be sure
            ->then
            ->given($response = [])
            ->if($this->calling($this->mock)->sendJsonResponse = null)
            ->and($this->calling($this->mock)->sendXmlResponse = null)
            ->then
            ->variable($this->mock->sendResponse($response))
                ->isNull()
            ->mock($this->mock)
                ->call('sendJsonResponse')
                    ->once()
                ->call('sendXmlResponse')
                    ->never()
        ;
    }
    
    public function testSendResponseWithXmlFormat()
    {
        $this->assert('test Rest::sendResponse with xml format')
            ->if($_GET['format'] = 'xml')
            ->and($this->mock->obtainResponseFormat())
            ->then
            ->string($this->mock->getResponseFormat())
                ->isEqualTo('xml') //Only check to be sure
            ->then
            ->given($response = [])
            ->if($this->calling($this->mock)->sendJsonResponse = null)
            ->and($this->calling($this->mock)->sendXmlResponse = null)
            ->then
            ->variable($this->mock->sendResponse($response))
                ->isNull()
            ->mock($this->mock)
                ->call('sendJsonResponse')
                    ->never()
                ->call('sendXmlResponse')
                    ->once()
        ;
    }
    
    public function testSendJsonResponse()
    {
        $this->assert('test Rest::sendJsonResponse')
            ->if($this->function->header = null)
            ->then
            ->output(function() {
                $response = new class {};
                $this->mock->sendJsonResponse($response);
            })
                ->isEqualTo('{}')
            ->function('header')
                ->wasCalledWithArguments('Content-Type: application/json')
                    ->once()
        ;
    }
    
    public function testSendXmlResponse()
    {
        $this->assert('test Rest::sendXmlResponse')
            ->if($this->function->header = null)
            ->then
            ->output(function() {
                $response = new class {};
                $this->mock->sendXmlResponse($response);
            })
                ->isEqualTo('<?xml version="1.0" encoding="UTF-8"?>'."\n")
            ->function('header')
                ->wasCalledWithArguments('Content-Type: application/xml')
                    ->once()
        ;
    }
}