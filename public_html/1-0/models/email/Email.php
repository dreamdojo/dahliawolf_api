<?php
class Email extends _Model
{
    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function send_transactional_post_email($params = array()){
        $user_id = $params['user_id'];
        if($params['user_id']) {
            $query = "
                SELECT user_username.email_address, user_username.mail_love,  posting.posting_id, image.source, image.imagename
                FROM  dahliawolf_v1_2013.user_username
                LEFT JOIN dahliawolf_v1_2013.posting ON posting.posting_id = ".$params['posting_id']."
                LEFT JOIN dahliawolf_v1_2013.image ON image.id = posting.image_id
                WHERE user_username.user_id = posting.user_id
            ";
            $q = "
                SELECT user_username.username, user_username.avatar
                FROM dahliawolf_v1_2013.user_username
                WHERE user_username.user_id = ".$params['user_id']."
            ";
            $params = array(
                ':user_id' => $user_id
            );
            $to = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
            $from =  self::$dbs[$this->db_host][$this->db_name]->select_single($q, $params);

            $str = explode('@', $to['email_address'])[1];

            if($to['mail_love'] && $str != 'dahliawolf.com') {

                try {
                    $mandrill = new Mandrill('Btwe8VxWFA9LToDcq6XbXQ');
                    $template_name = 'loved';
                    $template_content = null;
                    $message = array(
                        'subject' => $from['username'].' Liked Your Post',
                        'to' => array(
                            array(
                                'email' => $to['email_address'],
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
                                'rcpt' => $to['email_address'],
                                'vars' => array(
                                    array(
                                        'name' => 'USERNAME',
                                        'content' => $from['username']
                                    ),
                                    array(
                                        'name' => 'POST',
                                        'content' => $to['posting_id']
                                    ),
                                    array(
                                        'name' => 'AVATAR',
                                        'content' => $from['avatar'].'&width=100'
                                    ),
                                    array(
                                        'name' => 'IMAGESRC',
                                        'content' => $to['source'].$to['imagename']
                                    )
                                )
                            )
                        )
                    );
                    $async = false;
                    $ip_pool = 'Main Pool';
                    $send_at = null;
                    $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
                } catch(Mandrill_Error $e) {
                    // Mandrill errors are thrown as exceptions
                    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
                    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
                    throw $e;
                }
            }
        }
    }

    public function send_transactional_repost_email($params = array()){
        $user_id = $params['user_id'];
        if($params['user_id']) {
            $query = "
                SELECT user_username.email_address, user_username.mail_repost, posting.posting_id, image.source, image.imagename
                FROM  dahliawolf_v1_2013.user_username
                LEFT JOIN dahliawolf_v1_2013.posting ON posting.posting_id = ".$params['posting_id']."
                LEFT JOIN dahliawolf_v1_2013.image ON image.id = posting.image_id
                WHERE user_username.user_id = posting.user_id
            ";
            $q = "
                SELECT user_username.username, user_username.avatar
                FROM dahliawolf_v1_2013.user_username
                WHERE user_username.user_id = ".$params['user_id']."
            ";
            $params = array(
                ':user_id' => $user_id
            );
            $to = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
            $from =  self::$dbs[$this->db_host][$this->db_name]->select_single($q, $params);

            $str = explode('@', $to['email_address'])[1];

            if($to['mail_repost'] && $str != 'dahliawolf.com') {

                try {
                    $mandrill = new Mandrill('Btwe8VxWFA9LToDcq6XbXQ');
                    $template_name = 'repost';
                    $template_content = null;
                    $message = array(
                        'subject' => $from['username'].' Reposted Your Post',
                        'to' => array(
                            array(
                                'email' => $to['email_address'],
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
                                'rcpt' => $to['email_address'],
                                'vars' => array(
                                    array(
                                        'name' => 'USERNAME',
                                        'content' => $from['username']
                                    ),
                                    array(
                                        'name' => 'POST',
                                        'content' => $to['posting_id']
                                    ),
                                    array(
                                        'name' => 'AVATAR',
                                        'content' => $from['avatar'].'&width=100'
                                    ),
                                    array(
                                        'name' => 'IMAGESRC',
                                        'content' => $to['source'].$to['imagename']
                                    )
                                )
                            )
                        )
                    );
                    $async = false;
                    $ip_pool = 'Main Pool';
                    $send_at = null;
                    $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
                } catch(Mandrill_Error $e) {
                    // Mandrill errors are thrown as exceptions
                    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
                    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
                    throw $e;
                }
            }
        }
    }


    public function send_transactional_follower_email($following, $follower){

        if($following) {
            $to_query = "
                SELECT user_username.email_address, user_username.mail_follow
                FROM dahliawolf_v1_2013.user_username
                WHERE user_username.user_id = ".$following."
            ";
            $q = "
                SELECT user_username.username, user_username.avatar
                FROM dahliawolf_v1_2013.user_username
                WHERE user_username.user_id = ".$follower."
            ";

            $params = array(
                ':user_id' => $follower
            );

            $to = self::$dbs[$this->db_host][$this->db_name]->select_single($to_query, $params);
            $from =  self::$dbs[$this->db_host][$this->db_name]->select_single($q, $params);

            $str = explode('@', $to['email_address'])[1];

            if($to['mail_follow'] && $str != 'dahliawolf.com') {
                try {
                    $mandrill = new Mandrill('Btwe8VxWFA9LToDcq6XbXQ');
                    $template_name = 'follower';
                    $template_content = null;
                    $message = array(
                        'subject' => $from['username'].' Started Following You',
                        'to' => array(
                            array(
                                'email' => $to['email_address'],
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
                                'rcpt' => $to['email_address'],
                                'vars' => array(
                                    array(
                                        'name' => 'USERNAME',
                                        'content' => $from['username']
                                    ),
                                    array(
                                        'name' => 'AVATAR',
                                        'content' => $from['avatar'].'&width=100'
                                    )
                                )
                            )
                        )
                    );
                    $async = false;
                    $ip_pool = 'Main Pool';
                    $send_at = null;
                    $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
                } catch(Mandrill_Error $e) {
                    // Mandrill errors are thrown as exceptions
                    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
                    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
                    throw $e;
                }
            }
        }
    }

    public function send_transactional_comment_email($params = array()){
        $user_id = $params['user_id'];
        $comment = $params['comment'];
        if($params['user_id']) {
            $query = "
                SELECT user_username.email_address, posting.posting_id, image.source, image.imagename
                FROM  dahliawolf_v1_2013.user_username
                LEFT JOIN dahliawolf_v1_2013.posting ON posting.posting_id = ".$params['posting_id']."
                LEFT JOIN dahliawolf_v1_2013.image ON image.id = posting.image_id
                WHERE user_username.user_id = posting.user_id
            ";
            $q = "
                SELECT user_username.username, user_username.avatar
                FROM dahliawolf_v1_2013.user_username
                WHERE user_username.user_id = ".$params['user_id']."
            ";
            $params = array(
                ':user_id' => $user_id
            );
            $to = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
            $from =  self::$dbs[$this->db_host][$this->db_name]->select_single($q, $params);

            try {
                $mandrill = new Mandrill('Btwe8VxWFA9LToDcq6XbXQ');
                $template_name = 'comment';
                $template_content = null;
                $message = array(
                    'subject' => $from['username'].' Commented On Your Post',
                    'to' => array(
                        array(
                            'email' => $to['email_address'],
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
                            'rcpt' => $to['email_address'],
                            'vars' => array(
                                array(
                                    'name' => 'USERNAME',
                                    'content' => $from['username']
                                ),
                                array(
                                    'name' => 'POST',
                                    'content' => $to['posting_id']
                                ),
                                array(
                                    'name' => 'AVATAR',
                                    'content' => $from['avatar'].'&width=100'
                                ),
                                array(
                                    'name' => 'IMAGESRC',
                                    'content' => $to['source'].$to['imagename']
                                ),
                                array(
                                    'name' => 'COMMENT',
                                    'content' => $comment
                                )
                            )
                        )
                    )
                );
                $async = false;
                $ip_pool = 'Main Pool';
                $send_at = null;
                $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
            } catch(Mandrill_Error $e) {
                // Mandrill errors are thrown as exceptions
                echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
                // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
                throw $e;
            }
        }
    }
}