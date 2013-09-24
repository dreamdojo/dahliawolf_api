<?php
/**
 * User: JDorado
 * Date: 09/16/13
 */

class Postinglike_Controller extends _Controller
{

    public function add_like( $request_data = array() )
    {
        $this->load('Posting_Like');

         $posting_like = new Posting_Like();
         $data = $posting_like->addLike($request_data);

         return static::wrap_result(($posting_like->hasError()? false:true), $data, 200, $posting_like->getErrors() );
    }


    public function delete_like( $request_data = array() )
    {
        $this->load('Posting_Like');

         $message = new Posting_Like();
         $data = $message->deleteLike($request_data);

         return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }


}

?>