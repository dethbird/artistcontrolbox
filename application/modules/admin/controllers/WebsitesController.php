<?php

class Admin_WebsitesController extends House_Controller_Admin_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * 
     */
    public function indexAction()
    {
        $artist = House_Session::getArtist();
        if($this->getRequest()->isPost()){
            $params = $this->_getAllParams();
            $params['api_key'] = $artist->api_key;
            $params['id'] = $this->_getParam("id")!="" ? $this->_getParam("id") : "new";
            //House_Log::log($params);
            
            $client = new House_Http_Client();
            $response = json_decode($client->httpPost($this->view->site_url."/api/websites", $params));
            
            //if errors, return that json through flash messenger
            if(count($response->errors)>0){
                foreach($response->errors as $error){
                    $this->_messenger->addMessage(json_encode($error), "errors");
                }
            } else {    
                $this->_messenger->addMessage("Account saved", "success");
            }
            $this->_helper->_redirector->gotoUrl("/admin/websites");
        }
        
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("id"=>$this->_getParam("id"), "api_key"=>$artist->api_key));
        //House_Log::log($response);
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $this->view->website->id = $this->view->website->id!="" ? $this->view->website->id : "new";
        
    }

    public function editorAction()
    {  
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        
        
    }
    
    public function getStatusAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath, $this->view->website->filepath);
        echo json_encode($vcs->getStatus());
        exit();
    }
    
    public function getAssetsAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath);
        echo json_encode($vcs->getAssets());
        exit();
    }
    
    public function getFileDetailsAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath);
        echo json_encode($vcs->getFileDetails($this->_getParam("file")));
        exit();
    }
    
    public function createFileAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath);
        echo json_encode($vcs->createWebsiteFile($this->_getParam("filename"), $this->_getParam("type")));
        exit();
    }
    
    public function saveFileAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath);
        echo json_encode($vcs->putFileContents($this->_getParam("file"), $this->_getParam("contents")));
        exit();
    }
    
    public function commitModificationsAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath);
        echo json_encode($vcs->commitModifications($this->_getParam('msg')));
        exit();
    }
    
    public function publishToProdAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath, $this->view->website->filepath);
        echo json_encode($vcs->publishToProd());
        exit();
    }
    
    public function revertFileAction(){
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        $this->view->data = json_decode($response);
        $this->view->website = $this->view->data[0];
        $vcs = new House_Vcs_SVN($this->view->website->artist_site_repo_name, $this->view->website->dev_filepath);
        echo json_encode($vcs->revertFileContents($this->_getParam("file"), $this->_getParam('revision')));
        exit();
    }
    
    public function getFileViewDataAction(){
        
        $api_remaps = array(
            "about"=>"artists",
            "blogs"=>"feeds"
        );
        
        $_pathinfo = pathinfo($this->_getParam('file'));
        $endpoint = "index";
        $endpoint = $_pathinfo['filename'];
        $requested_endpoint = $_pathinfo['filename'];
        if(array_key_exists($endpoint, $api_remaps)){
            $endpoint = $api_remaps[$endpoint];
        }
        
        $artist = House_Session::getArtist();
        $client = new House_Http_Client();
        $data_response = $client->httpGet($this->view->site_url."/api/".$endpoint, array("api_key"=>$artist->api_key, "status_in"=>array("enabled", "disabled")));
        
        $data = json_decode($data_response);
        
        $response = new stdClass();
        
        $response->data = $data;
        $response->data_print = print_r($data,1);
        $response->endpoint = $requested_endpoint;
        
        echo json_encode($response);
        
        exit();
    }

    /**
    * Index view of published website JSON revisions
    */
    public function publisherAction()
    {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/websites", array("api_key"=>$this->view->artist->api_key));
        
        echo $response;

    }
    
    
    
    
}

