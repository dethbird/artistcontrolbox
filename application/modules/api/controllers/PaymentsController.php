<?php

class Api_PaymentsController extends House_Controller_Api_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * 
     * This is the post-back action following an amazon payment
     * 
     */
    public function indexAction()
    {
        
        if($this->getRequest()->isPost()){
            //House_Log::log($this->_getAllParams());
            
            $payment = Payment::find_by_transaction_id($this->_getParam('transactionId'));
            if($payment->id=="" && $this->_getParam('transactionId')!=""){

                $payment = new Payment();
                $payment->payment_reason = $this->_getParam('paymentReason');
                $amount = explode(" ",$this->_getParam('transactionAmount'));
                $payment->currency = $amount[0];
                $payment->amount = $amount[1];
                $payment->transaction_id = $this->_getParam('transactionId');
                $payment->status = $this->_getParam('status');
                $payment->buyer_email = $this->_getParam('buyerEmail');
                $payment->reference_id = $this->_getParam('referenceId');
                $payment->recipient_email = $this->_getParam('recipientEmail');
                $payment->transaction_date = $this->_getParam('transactionDate');
                $payment->buyer_name = $this->_getParam('buyerName');
                $payment->operation = $this->_getParam('operation');
                $payment->recipient_name = $this->_getParam('recipientName');
                $payment->signature_version = $this->_getParam('signatureVersion');
                $payment->signature = $this->_getParam('signature');
                $payment->payment_method = $this->_getParam('paymentMethod');
                $payment->certificate_url = $this->_getParam('certificateUrl');
                $payment->save();

                //explode the content
                $item = explode("-", $this->_getParam('referenceId'));
                $this->view->content = Content::find($item[1]);
                
                //send email with link
                
                
                $this->view->payment = $payment;

                //send the new account email
                $this->_helper->layout()->setLayout('layout_email');
                $this->view->content = $this->view->render("payments/purchase-content-complete-email.phtml");
                House_Email_Sender::send($this->_helper->layout->render(), "Thank you for purchase, and for supporting art!", $this->_getParam('buyerEmail'));
                
                
            }
        }
      
    }
    
    public function downloadAction(){
        if($this->_getParam('receipt')!=""){
            $transaction_info = explode("-", $this->_getParam('receipt'));
            $payment = Payment::find_by_transaction_id_and_transaction_date($transaction_info[0],$transaction_info[1]);
            if($payment->id!=""){
                $item = explode("-", $payment->reference_id);
                $content = Content::find($item[1]);
                if($content->id!=""){
                    //push the download
                    //$handle =  fopen($content->purchase_download_url, "r");
                    
                    //$head = array_change_key_case(get_headers($content->purchase_download_url, TRUE));
                    //echo "<pre>".print_r($head,1)."</pre>";
                    
                    
                    //download to temp 
                    /**
                     * @todo change this to streaming !
                     */
                    $name = tempnam("/tmp", $this->_getParam('receipt'));
                    $fp = fopen($name,"w");
                    $ch = curl_init($content->purchase_download_url);
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);
                    
                    //create friendly name
                    $extension = pathinfo($content->purchase_download_url, PATHINFO_EXTENSION);
                    $filename = rawurlencode($payment->payment_reason).".".strtolower($extension);
                    
                    
                    header('Pragma: public');   // required
                    header('Expires: 0');    // no cache
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($name)).' GMT');
                    header('Cache-Control: private',false);
                    header('Content-Type: application/force-download');
                    header('Content-Disposition: attachment; filename="'.$filename.'"');
                    header('Content-Transfer-Encoding: binary');
                    header('Content-Length: '.filesize($name));  // provide file size
                    header('Connection: close');
                    readfile($name);    // push it out
                    unlink($name);
                    exit();
                   
                    
                   
                }
            }
            
        }
    }
    
    


}