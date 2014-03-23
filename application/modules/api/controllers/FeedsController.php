<?php

class Api_FeedsController extends House_Controller_Api_Base
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
            
            $response = new stdClass();
            if(array_key_exists('name', $_POST)){
                if($this->_getParam('name')==""){
                    $response->errors[] = array("field"=>"name", "message"=>"Name cannot be null");
                }
            }
            if(array_key_exists('site_url', $_POST)){
                if(!filter_var($this->_getParam('site_url'), FILTER_VALIDATE_URL)){
                    $response->errors[] = array("field"=>"site_url", "message"=>"Site URL is not a valid url");
                }
            }
            if(array_key_exists('rss_url', $_POST)){
                if(!filter_var($this->_getParam('rss_url'), FILTER_VALIDATE_URL)){
                    $response->errors[] = array("field"=>"rss_url", "message"=>"RSS URL is not a valid url");
                }
            }
            if(array_key_exists('item_limit', $_POST)){
                if(!is_numeric($this->_getParam('item_limit'))){
                    $response->errors[] = array("field"=>"item_limit", "message"=>"Item limit must be a number");
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
                $feed = Feed::find($this->_getParam('id'));
            } else {
                $feed = new Feed();
            }
            if(array_key_exists('name', $_POST)!==false){
                $feed->name = $this->_getParam('name');
            }
            if($this->artist->id!==false){
                $feed->artist_id = $this->artist->id;
            }
            if(array_key_exists('description', $_POST)){
                $feed->description = $this->_getParam('description');
            }
            if(array_key_exists('item_limit', $_POST)){
                $feed->item_limit = $this->_getParam('item_limit');
            }
            if(array_key_exists('status', $_POST)){
                $feed->status = $this->_getParam('status');
            }
            if(array_key_exists('cover_image_url', $_POST)){
                $feed->cover_image_url = $this->_getParam('cover_image_url');
            }
            if(array_key_exists('site_url', $_POST)){
                $feed->site_url = $this->_getParam('site_url');
            }
            if(array_key_exists('rss_url', $_POST)){
                $feed->rss_url = $this->_getParam('rss_url');
            }
            $feed->save();
            $this->_setParam('id', $feed->id);
        }
        
        $service = new House_Service_FeedService($this->artist);
        $response = $service->find($this->_getParam('id'), $this->_getParam('status_in'), $this->getRequest()->isPost()?false:true);
        echo json_encode($response);
      
      
    }
    
    public function orderAction(){
        if(count($this->_getParam('sorted'))>0){
            foreach($this->_getParam('sorted') as $i=>$id){
                $feed = Feed::find($id);
                if($feed->id){
                    $feed->sort_order = $i+1;
                    $feed->save();
                }
            }
        }
        
    }


}

