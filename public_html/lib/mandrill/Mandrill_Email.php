<?php
/**
 * User: JDorado
 * Date: 11/19/13
 */
 
class Mandrill_Email {


    public function send()
    {
        try {
            $mandrill = new Mandrill('YOUR_API_KEY');
            $message = array(
                'html' => '<p>Example HTML content</p>',
                'text' => 'Example text content',
                'subject' => 'example subject',
                'from_email' => 'message.from_email@example.com',
                'from_name' => 'Example Name',
                'to' => array(
                    array(
                        'email' => 'recipient.email@example.com',
                        'name' => 'Recipient Name',
                        'type' => 'to'
                    )
                ),
                'headers' => array('Reply-To' => 'message.reply@example.com'),
                'important' => false,
                'track_opens' => null,
                'track_clicks' => null,
                'auto_text' => null,
                'auto_html' => null,
                'inline_css' => null,
                'url_strip_qs' => null,
                'preserve_recipients' => null,
                'view_content_link' => null,
                'bcc_address' => 'message.bcc_address@example.com',
                'tracking_domain' => null,
                'signing_domain' => null,
                'return_path_domain' => null,
                'merge' => true,
                'global_merge_vars' => array(
                    array(
                        'name' => 'merge1',
                        'content' => 'merge1 content'
                    )
                ),
                'merge_vars' => array(
                    array(
                        'rcpt' => 'recipient.email@example.com',
                        'vars' => array(
                            array(
                                'name' => 'merge2',
                                'content' => 'merge2 content'
                            )
                        )
                    )
                ),
                'tags' => array('password-resets'),
                'subaccount' => 'customer-123',
                'google_analytics_domains' => array('example.com'),
                'google_analytics_campaign' => 'message.from_email@example.com',
                'metadata' => array('website' => 'www.example.com'),
                'recipient_metadata' => array(
                    array(
                        'rcpt' => 'recipient.email@example.com',
                        'values' => array('user_id' => 123456)
                    )
                ),
                'attachments' => array(
                    array(
                        'type' => 'text/plain',
                        'name' => 'myfile.txt',
                        'content' => 'ZXhhbXBsZSBmaWxl'
                    )
                ),
                'images' => array(
                    array(
                        'type' => 'image/png',
                        'name' => 'IMAGECID',
                        'content' => 'ZXhhbXBsZSBmaWxl'
                    )
                )
            );
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = 'example send_at';
            $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
            print_r($result);
            /*
            Array
            (
                [0] => Array
                    (
                        [email] => recipient.email@example.com
                        [status] => sent
                        [reject_reason] => hard-bounce
                        [_id] => abc123abc123abc123abc123abc123
                    )

            )
            */
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
            throw $e;
        }



    }



}

?> 