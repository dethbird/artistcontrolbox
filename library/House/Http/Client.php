<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Client
 *
 * @author rishi
 */
class House_Http_Client extends Zend_Http_Client {
    private $_url = null;
    private $_params = null;
    private $_response = null;
    private $_responseBody = null;
    
    
    public function __construct($uri = null, $config = null) {
        parent::__construct();
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $this->setAdapter($adapter);
        
    }
    
    public function httpGet($url, $params = null){
        $params = is_array($params) ? $params : array();
        $this->_url = $url;
        $this->_params = $params;
        $this->setUri($this->_url);
        foreach($params as $key=>$value){
            $this->setParameterGet($key,$value);
        }
        $this->_response = $this->request();
        $this->_responseBody = $this->_response->getBody();
        
        return $this->_responseBody;
        
    }
    
    
    
    public function httpPost($url, $params = null){
        $params = is_array($params) ? $params : array();
        $this->_url = $url;
        $this->_params = $params;
        
        $this->setMethod('POST');
        
        $this->setUri($this->_url);
        foreach($params as $key=>$value){
            $this->setParameterPost($key, $value);
        }
        $this->_response = $this->request();
        $this->_responseBody = $this->_response->getBody();
        
        return $this->_responseBody;
        
    }
}

?>
