<?php

class Api_BackgroundsController extends House_Controller_Api_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * 
     * @return array Array of Arists
     */
    public function indexAction()
    {
        if($this->getRequest()->isPost()){
            //House_Log::log($this->_getAllParams());
            $response = new stdClass();
            if($this->_getParam('artist_id')==""){
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error");
            }
            
            if(count($response->errors)>0){
                $this->getResponse()->setHttpResponseCode(302);
                $this->getResponse()->setBody(json_encode($response));
                $this->getResponse()->sendResponse();
                exit();
            }
            
            if($this->_getParam('id')!=="new"){
                $background = Background::find($this->_getParam('id'));
            } else {
                $background = new Background();
            }
            
            
            if(array_key_exists('artist_id', $_POST)){
                $background->artist_id = $this->_getParam('artist_id');
            }
            if(array_key_exists('status', $_POST)){
                $background->status = $this->_getParam('status');
            }
            if(array_key_exists('image_url', $_POST) && !is_null($this->_getParam('image_url'))){
                $background->image_url = $this->_getParam('image_url');
            }
            if(array_key_exists('sort_order', $_POST)){
                $background->sort_order = $this->_getParam('sort_order');
            }
            
            $background->save();

            $this->_setParam('id', $background->id);
            
        }
        
        $service = new House_Service_BackgroundService($this->artist);
        $response = $service->find($this->_getParam('id'), $this->_getParam('status_in'));
        echo json_encode($response);
      
      
    }


}

