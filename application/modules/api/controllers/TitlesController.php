<?php

class Api_TitlesController extends House_Controller_Api_Base
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
            if(array_key_exists('name', $_POST)){
                if($this->_getParam('name')==""){
                    $response->errors[] = array("field"=>"name", "message"=>"Name cannot be null");
                }
            }
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
                $title = Title::find($this->_getParam('id'));
            } else {
                $title = new Title();
            }
            if(array_key_exists('name', $_POST)!==false){
                $title->name = $this->_getParam('name');
            }
            if(array_key_exists('description', $_POST)){
                $title->description = $this->_getParam('description');
            }
            if(array_key_exists('status', $_POST)){
                $title->status = $this->_getParam('status');
            }
            if(array_key_exists('cover_image_url', $_POST)){
                $title->cover_image_url = $this->_getParam('cover_image_url');
            }
            $title->artist_id = $this->_getParam('artist_id');
            $title->save();
            $this->_setParam('id', $title->id);
            
        }
        
        $service = new House_Service_TitleService($this->artist); 
        $response = $service->find($this->_getParam('id'), $this->_getParam('status_in'));
        echo json_encode($response);
      
      
    }


}

