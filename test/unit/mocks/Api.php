<?php

namespace BfwApi\test\unit\mocks;

class Api extends \BfwApi\Api
{
    public function __get($name)
    {
        return $this->{$name};
    }
    
    public function callObtainResponseFormat()
    {
        return $this->obtainResponseFormat();
    }
    
    public function callObtainResponseFormatFromAcceptHeader()
    {
        return $this->obtainResponseFormatFromAcceptHeader();
    }
    
    public function callObtainResponseFormatFromGetParameter()
    {
        return $this->obtainResponseFormatFromGetParameter();
    }
    
    public function callSendJsonResponse(&$response)
    {
        return $this->sendJsonResponse($response);
    }
    
    public function callSendXmlResponse(&$response)
    {
        return $this->sendXmlResponse($response);
    }
    
    public function callSendResponse(&$response)
    {
        return $this->sendResponse($response);
    }
}
