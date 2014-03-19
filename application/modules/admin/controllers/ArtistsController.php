<?php

class Admin_ArtistsController extends House_Controller_Admin_Base
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
      
    }
    
    public function authAction(){
        //House_Log::log($this->_getAllParams());
        $client = new House_Http_Client();
        $response = $client->httpPost($this->view->site_url."/api/artists/auth", $this->_getAllParams());
        if(!is_null($response)){
            $_artist = json_decode($response);
            if($this->getRequest()->getCookie('last_url')!=""){
                $_artist->last_url = $this->getRequest()->getCookie('last_url');
            }
            $artist = Artist::find($_artist->id);
            House_Session::setArtist($artist);
        }
        echo json_encode($_artist);
        //$this->view->data = json_decode($response);
    }


}

