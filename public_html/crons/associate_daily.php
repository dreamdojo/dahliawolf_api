<?
    require '../1-0/config/config.php';

    $dbhost = DB_API_HOST;
    $dbname = 'dahliawolf_v1_2013';
    $dbuser = DB_API_USER;
    $dbpass = DB_API_PASSWORD;

    require 'models/db.php';
    require 'models/User.php';
    require_once '../lib/mandrill/Mandrill_Email.php';

    $User = new User();

    $date = date('Y-m-d');

    $associates = $User->get_associates();

    foreach($associates as $k=>$user) {
        $u = $User->get_associate_progress($user['user_id']);
        echo $user['email_address']." ";
        if($u['posts'] >= 20 && $u['reposts'] >= 20 && $u['comments'] >= 20 && $u['follows'] >= 20  && $u['likes'] >= 20) {
            sendEmail($user['email_address'], 'Congratulations');
        } else {
            sendEmail($user['email_address'], 'Daily Requirements');
        }
    }

    function sendEmail($email, $template) {
        $to = $email;

        try {
            $mandrill = new Mandrill('Btwe8VxWFA9LToDcq6XbXQ');
            $template_name = $template;
            $template_content = null;
            $message = array(
                'subject' => 'Associate update',
                'to' => array(
                    array(
                        'email' => $to,
                        'name' => '',
                        'type' => 'to'
                    )
                ),
                'headers' => array('Reply-To' => 'hello@dahliawolf.com'),
                'important' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'auto_text' => null,
                'auto_html' => null,
                'inline_css' => null,
                'url_strip_qs' => null,
                'preserve_recipients' => null,
                'view_content_link' => null,
                'bcc_address' => null,
                'tracking_domain' => null,
                'signing_domain' => null,
                'return_path_domain' => null,
                'merge' => false,
                'global_merge_vars' => array(
                    array(
                        'name' => 'merge1',
                        'content' => 'merge1 content'
                    )
                ),
                'merge_vars' => array(
                    array(
                        'rcpt' => $to,
                        'vars' => array(
                        )
                    )
                )
            );
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = null;
            $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
            var_dump($result);
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
            throw $e;
        }
    }

?>
