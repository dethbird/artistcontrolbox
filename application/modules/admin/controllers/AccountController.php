<?php

class Admin_AccountController extends House_Controller_Admin_Base
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
        if($this->getRequest()->isPost()){
            $params = $this->_getAllParams();
            $artist = House_Session::getArtist();
            $params['api_key'] = $artist->api_key;
            $params['get_api_key'] = "Y";
            //House_Log::log($artist);
            
            $client = new House_Http_Client();
            $response = json_decode($client->httpPost($this->view->site_url."/api/artists", $params));
            
            //if errors, return that json through flash messenger
            if(count($response->errors)>0){
                foreach($response->errors as $error){
                    $this->_messenger->addMessage(json_encode($error), "errors");
                }
                $this->_helper->_redirector->gotoUrl("/admin/account");
            } else {    
                $this->_messenger->addMessage("Account saved", "success");
                $artist = $response[0];
                if(is_null($_FILES['bio_image_url'])){
                    $this->_helper->_redirector->gotoUrl("/admin/account");
                } else {
                    //IMAGE UPLOAD
                    //move and rename the file
                    $upload = new Zend_File_Transfer();
                    $file_name_full_path =  Zend_Registry::get('config')->get('image_upload_path')."/artists/".$artist->id.'.'.pathinfo($_FILES['bio_image_url']['name'],PATHINFO_EXTENSION);
                    $upload->addFilter('Rename', array('target' => $file_name_full_path,
                         'overwrite' => true) );
                    $upload->addValidator('MimeType', false, array('image/jpg', 'image/jpeg', 'image/png'));
                    $upload->addValidator('Size', false, array('min' => 20, 'max' => 2097152));
                    //check validity
                    if(!$upload->isValid()){
                        //House_Log::log($upload->getMessages());
                        foreach($upload->getMessages() as $key=>$value){
                            if($key!="fileUploadErrorNoFile"){
                                $this->_messenger->addMessage(json_encode(array("field"=>$key, "message"=>$value)), "errors");
                            }
                        }
                        $this->_helper->_redirector->gotoUrl("/admin/account");
                    } else {
                        
                        //remove if exists
                        if($artist->bio_image_url!=""){
                            $pathinfo = pathinfo($artist->bio_image_url);
                            @unlink(Zend_Registry::get('config')->get('image_upload_path')."/artists/".$pathinfo['basename']);
                        }
                        
                        $upload->receive();
                        //save the new url
                        $name = $upload->getFileName('bio_image_url');
                        $pathinfo = pathinfo($name);
                        
                        $params = array();
                        $params['id'] = $artist->id;;
                        $params['bio_image_url'] = Zend_Registry::get('config')->get('image_server_url')."/artists/".$artist->id.".".$pathinfo['extension'];
                        $client = new House_Http_Client();
                        $response = json_decode($client->httpPost($this->view->site_url."/api/artists", $params));
                        
                    }
                }
            }
        }
        
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/artists", array("api_key"=>$this->view->artist->api_key, "get_api_key"=>"Y"));
        $this->view->data = json_decode($response);
        $this->view->artist = $this->view->data[0];
        
        //list users to spoof for internaladmin
        if(House_Session::isInternalAdmin()==1){
            $this->view->artists = Artist::find_by_sql("select * from `artists` ORDER BY TRIM(UPPER(`name`))");
        }
        
    }
    
    public function changeArtistAction(){
        $artist = Artist::find($this->_getParam('artist_id'));
        House_Session::setArtist($artist, true);
        $this->_helper->_redirector->gotoUrl("/admin/account");
    }


}

