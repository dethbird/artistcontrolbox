<?php

class Api_ContentsController extends House_Controller_Api_Base
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

            if($this->_getParam('id')=="new" && $this->_getParam('gallery_id')==""){
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error");
            }
            if(count($response->errors)>0){
                $this->getResponse()->setHttpResponseCode(302);
                $this->getResponse()->setBody(json_encode($response));
                $this->getResponse()->sendResponse();
                exit();
            }

            if($this->_getParam('id')!=="new"){
                $content = Content::find($this->_getParam('id'));
            } else {
                $content = new Content();
            }

            if(array_key_exists('name', $_POST)){
                $content->name = $this->_getParam('name');
            }
            if(array_key_exists('description', $_POST)){
                $content->description = $this->_getParam('description');
            }
            if(array_key_exists('status', $_POST)){
                $content->status = $this->_getParam('status');
            }
            if(array_key_exists('gallery_id', $_POST)){
                $content->gallery_id = $this->_getParam('gallery_id');
            }
            if(array_key_exists('type', $_POST)){
                $content->type = $this->_getParam('type');
            }
            if(array_key_exists('embed_code', $_POST) && !is_null($this->_getParam('embed_code'))){
                $content->embed_code = $this->_getParam('embed_code');
            }
            if(array_key_exists('image_url', $_POST) && !is_null($this->_getParam('image_url'))){
                $content->image_url = $this->_getParam('image_url');
            }
            if(array_key_exists('youtube_link', $_POST) && !is_null($this->_getParam('youtube_link'))){
                $content->youtube_link = $this->_getParam('youtube_link');
            }
            if(array_key_exists('sort_order', $_POST)){
                $content->sort_order = $this->_getParam('sort_order');
            }
            if(array_key_exists('purchase_download_url', $_POST)){
                $content->purchase_download_url = $this->_getParam('purchase_download_url');
            }
            if(array_key_exists('purchase_description', $_POST)){
                $content->purchase_description = $this->_getParam('purchase_description');
            }
            if(array_key_exists('purchase_cost', $_POST)){
                $content->purchase_cost = $this->_getParam('purchase_cost');
            }
            if(array_key_exists('amazon_button_html', $_POST)){
                $content->amazon_button_html = $this->_getParam('amazon_button_html');
            }
            $content->save();

            $this->_setParam('id', $content->id);

            //House_Log::log($title);
        }

        $service = new House_Service_ContentService($this->view->site_url);
        $response = $service->find($this->_getParam('id'), $this->_getParam('status_in'));
        echo json_encode($response);


    }

    // return all contents
    public function allAction()
    {
        $service = new House_Service_ContentService($this->view->site_url);
        $response = $service->all($this->artist);
        echo json_encode($response);
    }


}

