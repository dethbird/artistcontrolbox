<?php

class House_Session {
    
	
	protected static $_session = null;
	
    /**
     *
     * @return Zend_Session_Namespace 
     */
    public static function get(){
        
        /*
        $mc = new Memcache();
        $mc->addserver('memcache1');
        $mc->addserver('memcache2');
        BuddyMedia_Log::log($mc->getstats());
        */
        
        if (!self::$_session)
        	self::$_session = new Zend_Session_Namespace('ActionPulp');
            self::$_session->setExpirationSeconds( 600 );
 
        if (!isset(self::$_session->initialized))
        {
        	//BuddyMedia_Log::log("Forcing session regeneration due to initialized session.");
        	Zend_Session::regenerateId();
        	self::$_session->initialized = true;
        }
        
        return self::$_session;
    }
    
    
    
    
    /**
     *
     * @return boolean
     */
    public static function logout(){
        $session = self::get();
        $session->logged_in=false;
        
        Zend_Session::namespaceUnset('ActionPulp');
    }
    
    public static function isLoggedIn(){
        $session = self::get();
        if(get_class($session->artist)!="Artist")
        {
            return false;
        }
        return true;
    }
    
    /**
     *
     * @param Zend_Oauth_Token_Request $token 
     */
    public static function setTwitterRequestToken(Zend_Oauth_Token_Request $token){
        $session = self::get();
        $session->twitter_request_token=$token;
    }
    
    /**
     *
     * @return Zend_Oauth_Token_Request $token 
     */
    public static function getTwitterRequestToken(){
        $session = self::get();
        return $session->twitter_request_token;
    }
    
    /**
     * Set the logged in artist
     * 
     * @param Artist $artist
     * @param type $set_internal_admin
     */
    public static function setArtist(Artist $artist, $set_internal_admin = false){
        $session = self::get();
        $session->artist = $artist;
        
        //set internal admin if this user is in interal admin list
        if(in_array($artist->id, Zend_Registry::get('config')->get('acl')->internal_admin_users->toArray()) || $set_internal_admin==true){
            $session->internal_admin = true;
        } else {
            $session->internal_admin = false;
        }
        
    }
    public static function getArtist(){
        $session = self::get();
        return $session->artist;
    }
    
    /**
     * Check to see if logged in user is an internal admin
     * 
     * @return type
     */
    public static function isInternalAdmin(){
        $session = self::get();
        return $session->internal_admin;
    }
    
    public static function generateCsrfKey(){
        $session = self::get();
        $session->old_csrf_key = $session->csrf_key;
        $session->csrf_key = BuddyMedia_Encryption::str_rand(32);
    }
    public function getCsrfKey(){
        $session = self::get();
        return $session->csrf_key;
    }
    public function getOldCsrfKey(){
        $session = self::get();
        return $session->old_csrf_key;
    }
    
    
    
    
}
?>
