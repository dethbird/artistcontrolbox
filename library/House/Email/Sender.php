<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sender
 *
 * @author rsatsangi
 */
class House_Email_Sender {
    
    /**
     * 
     * @param type $body
     * @param type $subject
     * @param type $to
     * @param type $from
     * @param type $fromName
     * @return type
     */
    public static function send($body = "testing", $subject = "Email test. Do not reply", $to = "artistcontrolbox.dev@gmail.com", $from = "noreply@artistcontrolbox.com", $fromName = "ArtistControlbox"){
        
        $config = array('auth' => 'login',
        'username' => $from,
        'password' => 'Uranium239');

        $transport = new Zend_Mail_Transport_Smtp('mail.artistcontrolbox.com', $config);
        //ArtistControlbox_Log::logFirebug($transport);

        $mail = new Zend_Mail();
        $mail->setBodyHtml($body);
        //$mail->setBodyText("test message");
        
        $mail->addTo($to);
        $mail->setFrom($from, $fromName);
        $mail->addBcc("artistcontrolbox.dev@gmail.com");
        $mail->setSubject($subject);
        $t = $mail->send($transport);
        return $t;
    }
}

?>
