<?php
/**
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */

namespace BfwApi;

/**
 * Interface for Rest API user class
 * @package bfw-api
 */
interface RestInterface
{
    public function getRequest();
    
    public function postRequest();
    
    public function putRequest();
    
    public function deleteRequest();
}
