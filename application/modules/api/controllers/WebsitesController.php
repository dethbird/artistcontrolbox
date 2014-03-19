<?php

class Api_WebsitesController extends House_Controller_Api_Base
{

    /**
     * 
     * @return array Array of Arists
     */
    public function indexAction()
    {
        
        if($this->getRequest()->isPost()){
            $response = new stdClass();
            if($this->_getParam('artist_id')==""){
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error");
            }
            if($this->_getParam('id')==""){
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error");
            }
            if(array_key_exists('title', $_POST)){
                if($this->_getParam('title')==""){
                    $response->errors[] = array("field"=>"title", "message"=>"Title cannot be null");
                }
            }
            if(array_key_exists('url', $_POST)){
                if($this->_getParam('url')==""){
                    $response->errors[] = array("field"=>"url", "message"=>"URL cannot be null");
                }
            }
            if(array_key_exists('filepath', $_POST)){
                if($this->_getParam('filepath')==""){
                    $response->errors[] = array("field"=>"filepath", "message"=>"File Path cannot be null");
                }
            }
            if(array_key_exists('dev_url', $_POST)){
                if($this->_getParam('dev_url')==""){
                    $response->errors[] = array("field"=>"dev_url", "message"=>"Dev URL cannot be null");
                }
            }
            if(array_key_exists('dev_filepath', $_POST)){
                if($this->_getParam('dev_filepath')==""){
                    $response->errors[] = array("field"=>"dev_filepath", "message"=>"Dev File Path cannot be null");
                }
            }
            if(count($response->errors)>0){
                $this->getResponse()->setHttpResponseCode(302);
                $this->getResponse()->setBody(json_encode($response));
                $this->getResponse()->sendResponse();
                exit();
            }
            
            if($this->_getParam('id')!=="new"){
                $website = Website::find($this->_getParam('id'));
            } else {
                $website = new Website();
                $website->artist_id = $this->_getParam('artist_id');
            }
            if(array_key_exists('site_name', $_POST)!==false){
                $website->site_name = $this->_getParam('site_name');
            }
            if(array_key_exists('title', $_POST)!==false){
                $website->title = $this->_getParam('title');
            }
            if(array_key_exists('description', $_POST)){
                $website->description = $this->_getParam('description');
            }
            if(array_key_exists('url', $_POST)){
                $website->url = $this->_getParam('url');
            }
            if(array_key_exists('filepath', $_POST)){
                $website->filepath = $this->_getParam('filepath');
            }
            if(array_key_exists('dev_url', $_POST)){
                $website->dev_url = $this->_getParam('dev_url');
            }
            if(array_key_exists('dev_filepath', $_POST)){
                $website->dev_filepath = $this->_getParam('dev_filepath');
            }
            if(array_key_exists('artist_site_repo_name', $_POST)){
                $website->artist_site_repo_name = $this->_getParam('artist_site_repo_name');
            }
            $website->save();
            $this->_setParam('id', $website->id);

        }
        
        $service = new House_Service_WebsiteService($this->artist); 
        $response = $service->find($this->_getParam('id'));
        echo json_encode($response);
      
    }

    /**
    * Grab all data and store to db
    */
    public function publishAction(){
        
        $response = new stdClass();
        
        //artist
        $service = new House_Service_ArtistService($this->artist);
        $response->artist = $service->find($this->_getParam('id'), $this->_getParam('get_api_key'));

        //backgrounds
        $service = new House_Service_BackgroundService($this->artist);
        $response->backgrounds = $service->find($this->_getParam('id'), $this->_getParam('status_in'));

        //galleries
        $service = new House_Service_GalleryService($this->artist);
        $response->galleries = $service->find($this->_getParam('id'), $this->_getParam('status_in'));

        //titles
        $service = new House_Service_TitleService($this->artist);
        $response->titles = $service->find($this->_getParam('id'), $this->_getParam('status_in'));

        //pages
        $service = new House_Service_FeedService($this->artist);
        $response->feeds = $service->find($this->_getParam('id'), $this->_getParam('status_in'), false);
        

        //save site json
        $website = Website::find_by_artist_id($this->artist->id);
        $website->publish_json = json_encode($response);
        $website->save();
        

    }
    

}





