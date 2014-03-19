<?php

class Admin_IndexController extends House_Controller_Admin_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->_helper->_redirector->gotoUrl("/admin/galleries");
    }
    
    public function logoutAction(){
        House_Session::logout();
    }
    


}

