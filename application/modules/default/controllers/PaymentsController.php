<?php

class PaymentsController extends House_Controller_Default_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function transactionAction()
    {
        if($this->_getParam('receipt')!=""){
            $transaction_info = explode("-", $this->_getParam('receipt'));
            $payment = Payment::find_by_transaction_id_and_transaction_date($transaction_info[0],$transaction_info[1]);
            if($payment->id!=""){
                $item = explode("-", $payment->reference_id);
                $content = Content::find($item[1]);
                if($content->id!=""){
                    $this->view->content = $content;
                    $this->view->payment = $payment;
                } else {
                    $this->_helper->_redirector->gotoUrl("/default/error/error/");
                }
            } else {
                $this->_helper->_redirector->gotoUrl("/default/error/error/");
            }
        } else {
            $this->_helper->_redirector->gotoUrl("/default/error/error/");
        }
        
    }

    public function amazoncompleteAction()
    {
        $this->_helper->_redirector->gotoUrl("/default/payments/transaction/receipt/".$this->_getParam('transactionId')."-".$this->_getParam('transactionDate'));
    }
}

