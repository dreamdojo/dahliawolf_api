<?php
/**
 * User: JDorado
 * Date: 7/19/13
 */

class Message_Controller extends _Controller
{

    public function send_message($request_data = array())
    {
        $this->load('Message');

        $message = new Message();
        $data = $message->sendMessage($request_data);

        return static::wrap_result( ($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }

    public function get_all($request_data = array())
    {
        $this->load('Message');

        $message = new Message();
        $data = $message->fetchMessages($request_data);

        return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }

    public function get_totals($request_data = array())
    {
        $this->load('Message');

        $message = new Message();
        $data = $message->getTotalPostShares($request_data);

        return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }


    public function get_unread($request_data = array())
    {
        $this->load('Message');

        $message = new Message();
        $data = $message->getPostSharesCount($request_data);

        return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }

    public function get_read($request_data = array())
    {
        $this->load('Message');

        $message = new Message();
        $data = $message->getPostSharesCount($request_data);

        return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }



    public function mark_read($request_data = array())
    {
        $this->load('Message');

        $message = new Message();
        $data = $message->markAsRead($request_data);

        return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }




}

?>