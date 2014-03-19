<?php

class IndexController extends House_Controller_Default_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
        if($this->getRequest()->isPost()){
            //validate
            $response = new stdClass();
            if(trim($_POST['name'])==""){
                $e = new stdClass();
                $e->field = "name";
                $e->message = "Name is required.";
                $response->errors[] = $e;
            }
            // find email in artist table
            // find email in signup table
            $signup = Signup::find_by_email(strtolower($this->_getParam('email')));
            $artist = Artist::find_by_email(strtolower($this->_getParam('email')));
            if($artist->email!=""){
                $e = new stdClass();
                $e->field = "email";
                $e->message = "This email already belongs to an ArtistControlbox account.";
                $response->errors[] = $e;
            } else if($signup->email!=""){
                $e = new stdClass();
                $e->field = "email";
                $e->message = "This email has already been signed up.";
                $response->errors[] = $e;
            } else if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)==false){
                $e = new stdClass();
                $e->field = "email";
                $e->message = "Please enter a valid email address.";
                $response->errors[] = $e;
            }
            if(filter_var($_POST['url'], FILTER_VALIDATE_URL)==false){
                $e = new stdClass();
                $e->field = "url";
                $e->message = "Please enter a valid website.";
                $response->errors[] = $e;
            }
            
            if(count($response->errors)>0){
                $this->getResponse()->setHttpResponseCode(302);
            } else {
                //store the info
                $signup = new Signup();
                $signup->name = $this->_getParam('name');
                $signup->email = strtolower($this->_getParam('email'));
                $signup->url = $this->_getParam('url');
                $signup->wishes = $this->_getParam('wishes');
                $signup->drawbacks = $this->_getParam('drawbacks');
                $signup->save();
                
                
                $this->view->signup = $signup;

                //send the new account email
                $this->_helper->layout()->setLayout('layout_email');
                $this->view->content = $this->view->render("index/more-info-email.phtml");
                House_Email_Sender::send($this->_helper->layout->render(), "Thank you for your info request", $this->_getParam('email'));
            }
            
            echo json_encode($response);
            
        }
    }


}

