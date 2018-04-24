<?php
/**
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */

namespace BfwApi;

use \Exception;
use \BFW\Helpers\Http;
use \BFW\Helpers\Secure;

/**
 * Abstract class for Rest API user class
 * @package bfw-api
 */
abstract class Rest
{
    /**
     * @var mixed $datas Datas receive by request
     */
    protected $datas;
    
    /**
     * @var string $responseFormat (json|xml) The response format to use
     */
    protected $responseFormat;
    
    /**
     * Constructor
     * Get datas from the request
     */
    public function __construct()
    {
        $this->obtainDatasFromRequest();
        $this->obtainResponseFormat();
    }
    
    /**
     * Getter accessor for datas property
     * 
     * @return miex
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * Getter accessor for responseFormat property
     * 
     * @return string
     */
    public function getResponseFormat()
    {
        return $this->responseFormat;
    }
    
    /**
     * Method called for GET request
     * 
     * @return void
     */
    public function getRequest()
    {
        //Not Implemented
        http_response_code(501);
    }
    
    /**
     * Method called for POST request
     * 
     * @return void
     */
    public function postRequest()
    {
        //Not Implemented
        http_response_code(501);
    }
    
    /**
     * Method called for PUT request
     * 
     * @return void
     */
    public function putRequest()
    {
        //Not Implemented
        http_response_code(501);
    }
    
    /**
     * Method called for DELETE request
     * 
     * @return void
     */
    public function deleteRequest()
    {
        //Not Implemented
        http_response_code(501);
    }
    
    /**
     * Get datas receive from the request
     * 
     * @link http://stackoverflow.com/questions/8945879/how-to-get-body-of-a-post-in-php
     * 
     * @return void
     */
    protected function obtainDatasFromRequest()
    {
        try {
            $contentType = \BFW\Request::getServerValue('CONTENT_TYPE');
        } catch (Exception $e) {
            $this->datas = [];
            return;
        }
        
        if ($contentType === 'application/json') {
            $requestDatas = file_get_contents('php://input');
            $this->datas  = Secure::securise(
                json_decode($requestDatas, true),
                'string',
                true
            );
        } else {
            $this->datas = Secure::securise($_POST, 'string', true);
        }
    }
    
    /**
     * Get the response format to use from header "Accept" or
     * the get parameter "format"
     * 
     * The get parameter have the priority on the "Accept" header
     * 
     * @return void
     */
    protected function obtainResponseFormat()
    {
        $formatFromHeader = $this->obtainResponseFormatFromAcceptHeader();
        $formatFromGet    = $this->obtainResponseFormatFromGetParameter();
        
        if ($formatFromGet !== null) {
            $this->responseFormat = $formatFromGet;
        } elseif ($formatFromHeader !== null) {
            $this->responseFormat = $formatFromHeader;
        } else {
            http_response_code(406);
        }
    }
    
    /**
     * Get the format to use from the "Accept" http header
     * 
     * Header format : text/html,application/xhtml+xml,application/xml;q=0.9
     * 
     * @return null|string
     */
    protected function obtainResponseFormatFromAcceptHeader()
    {
        try {
            $acceptHeader = \BFW\Request::getServerValue('HTTP_ACCEPT');
        } catch (Exception $e) {
            return null;
        }
        
        $availableHeader = [
            'application/xml'  => 'xml',
            'application/json' => 'json'
        ];
        
        $allAcceptedFormat = explode(',', $acceptHeader);
        foreach ($allAcceptedFormat as $mimeTypeWithPreference) {
            $cutMimeAndPreference = explode(';', $mimeTypeWithPreference);
            $mimeType             = $cutMimeAndPreference[0];
            
            if (isset($availableHeader[$mimeType])) {
                return $availableHeader[$mimeType];
            }
        }
        
        return null;
    }
    
    /**
     * Get the response format to use from the get paramter "format"
     * 
     * @return string|null
     */
    protected function obtainResponseFormatFromGetParameter()
    {
        try {
            $format = Http::obtainGetKey('format', 'text');
            
            if ($format !== 'xml' && $format !== 'json') {
                return null;
            }
            
            return $format;
        } catch (Exception $ex) {
            return null;
        }
    }
    
    /**
     * Send a response for the api request with the correct format
     * 
     * @param mixed $response Datas to send
     * 
     * @return void
     */
    protected function sendResponse(&$response)
    {
        if ($this->responseFormat === 'json') {
            $this->sendJsonResponse($response);
        } elseif ($this->responseFormat === 'xml') {
            $this->sendXmlResponse($response);
        }
    }
    
    /**
     * Send the response for json format
     * 
     * @param mixed $response Datas to send
     * 
     * @return void
     */
    protected function sendJsonResponse(&$response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Send the response for xml format
     * 
     * @param mixed $response Datas to send
     * 
     * @return void
     */
    protected function sendXmlResponse(&$response)
    {
        header('Content-Type: application/xml');
        
        $phpToXml = new \bultonFr\PhpToXml\PhpToXml;
        echo $phpToXml->convert($response);
    }
}
