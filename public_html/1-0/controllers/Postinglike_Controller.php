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

         $message = new Posting_Like();
         $data = $message->addLike($request_data);

         return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
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