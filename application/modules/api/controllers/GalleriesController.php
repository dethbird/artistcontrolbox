<?php

class Api_GalleriesController extends House_Controller_Api_Base
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
        //House_Log::log($this->_getAllParams());
        
        if($this->getRequest()->isPost()){
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
                $gallery = Gallery::find($this->_getParam('id'));
            } else {
                $gallery = new Gallery();
            }
            if(array_key_exists('name', $_POST)!==false){
                $gallery->name = $this->_getParam('name');
            }
            if(array_key_exists('sort_order', $_POST)!==false){
                $gallery->sort_order = $this->_getParam('sort_order');
            }
            if(array_key_exists('description', $_POST)){
                $gallery->description = $this->_getParam('description');
            }
            if(array_key_exists('status', $_POST)){
                $gallery->status = $this->_getParam('status');
            }
            if(array_key_exists('type', $_POST)){
                $gallery->type = $this->_getParam('type');
            }
            if(array_key_exists('cover_image_url', $_POST)){
                $gallery->cover_image_url = $this->_getParam('cover_image_url');
            }
            $gallery->artist_id = $this->_getParam('artist_id');
            
            
            $gallery->save();
            $this->_setParam('id', $gallery->id);
            
        }

        //return response
        $service = new House_Service_GalleryService($this->artist);
        $response = $service->find($this->_getParam('id'), $this->_getParam('status_in'));
        echo json_encode($response);
      
      
    }

    public function orderAction(){
        if(count($this->_getParam('sorted'))>0){
            foreach($this->_getParam('sorted') as $i=>$id){
                $gallery = Gallery::find($id);
                if($gallery->id){
                    $gallery->sort_order = $i+1;
                    $gallery->save();
                }
            }
        }
        $this->indexAction();
        
    }

    public function orderContentAction(){
        if(count($this->_getParam('sorted'))>0){
            foreach($this->_getParam('sorted') as $i=>$id){
                $content = Content::find($id);
                if($content->id){
                    $content->sort_order = $i+1;
                    $content->save();
                }
            }
        }
        $this->indexAction();
        
    }
}

