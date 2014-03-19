<?php

class Admin_EmailController extends House_Controller_Admin_Base
{

    public function init()
    {
        /* Initialize action controller here */
        if($this->view->is_internal_admin===false){
            House_Log::log("not internal admin");
            $this->_helper->_redirector->gotoUrl("/admin/galleries");
        }
    }

    public function indexAction()
    {
        if($this->getRequest()->isPost()){
            //send the new account email
            $this->_helper->layout()->setLayout('layout_email');
            $this->view->content = $this->_getParam('body');
            House_Email_Sender::send($this->_helper->layout->render(), $this->_getParam('subject'), $this->_getParam('to'));
        }
    }
    
    


}

