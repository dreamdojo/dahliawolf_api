<?php

class Email extends db {
	private $from;
	private $from_email;
    private $content;
    private $content_vars = array();

	public function __construct() {
		parent::__construct();

		$this->from = FROM;
		$this->from_email = FROM_EMAIL;
	}

    public function email($type, $user_id, $optional_params = NULL, $send_at=null)
    {
        $schedule = false;
        if( strtotime($send_at) > 0 )
        {
            $schedule=true;
        }else{
            $send_at = null;
        }

        $user = $this->get_user($user_id);

        $function = 'get_' . $type . '_params';
        $params = $this->$function($user, $optional_params);

        $email_content = self::getFinalContent();

        $data = array();
        if (!empty($params)) {

            if($schedule){
                $mandril = new Mandrill_Email();
                //////////send($from,       $fromEmail,        $to,                                                       $toEmail,       $subject,           $htmlBody,      $plainBody = '', $send_at)
                $mandril->send($this->from, $this->from_email, strlen($user['name'])>5?$user['name'] : $user['username'], $user['email'], $params['subject'], $email_content, '',              $send_at);
            }
            else{
                $result = email($this->from, $this->from_email, $user['name'], $user['email'], $params['subject'], $email_content );
            }
            $data[$user['email']] = $result;
        }

        return json_encode(resultArray(true, $data));
    }


    public function setVar($var, $value)
    {
        $this->content_vars[$var] = $value;
    }

    protected function renderContentVar($var = 'cvar', $content = '')
    {
        $this->content = str_replace("<!--$var-->",  $content, $this->content);
    }

    protected function renderContentVars()
    {
        foreach($this->content_vars as $var => $value)
        {
            self::renderContentVar($var, $value);
        }
    }

    protected function getFinalContent()
    {
        self::renderContentVars();

        return $this->content;
    }


    protected function get_signup_params($user)
       {
   		$subject = 'Welcome to Dahlia Wolf';

           $this->content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/emails/custom/welcome.html');

   		return array(
   			'subject' => $subject,
   			'html_body' => $this->content
   		);
   	}

    protected function get_signup_scheduled_params($user)
       {
   		$subject = 'Welcome to Dahlia Wolf - scheduled';

           $this->content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/emails/custom/welcome.html');

   		return array(
   			'subject' => $subject,
   			'html_body' => $this->content
   		);
   	}


	public function invite_emails($user_id, $emails, $message) {
		$user = $this->get_user($user_id);
		if (empty($user)) {
			return resultArray(false, NULL, 'User email does not exist.');
		}
		$user_email = $user['email'];

		$from = $this->from;
		$from_email = FROM_EMAIL_INVITE;
		$subject = 'You have been invited to join Dahlia Wolf';

		$data = array();
		foreach ($emails as $i => $email) {
			// max 4
			if ($i >= 4) {
				break;
			}

			$to = $email;
			$to_email = $email;

			$html_body = $to . ',

            You have been invited to join Dahlia Wolf
            Simply click on the link below to check it out:
            <a href="http://www.dahliawolf.com">http://www.dahliawolf.com</a>

            ' .
            ( !empty($message) ? "Your friend also included this message for you:\n $message" : '' )
            . $this->get_email_footer();

			$html_body = nl2br($html_body);

			$result = email($from, $from_email, $to, $to_email, $subject, $html_body);
			$data[$to_email] = $result;
		}

		return json_encode(resultArray(true, $data));
	}


    protected function get_add_product_params($user, $params = array())
    {

		if (!empty($params['is_primary'])) {
			$subject = 'Your inspiration has post has WON! Your item will be created! You are one step away from having you stuff sold in our shop and  earning you money!';
			ob_start();
			require $_SERVER['DOCUMENT_ROOT'] . '/emails/custom/post-winner-email.php';
			$html_body = ob_get_contents();
			ob_end_clean();
		}
		else {
			$subject = 'Your inspiration has been selected!';
			ob_start();
			require $_SERVER['DOCUMENT_ROOT'] . '/emails/custom/post-secondary-winner-email.php';
			$html_body = ob_get_contents();
			ob_end_clean();
		}

		return array(
			'subject' => $subject
			, 'html_body' => $html_body
		);
	}

    protected function get_add_vote_winner_params($user, $params)
    {
		$subject = 'The people have spoken and they love you style! Your items has been chosen to be produced and will be listed in the shop.';
		ob_start();
		require $_SERVER['DOCUMENT_ROOT'] . '/emails/custom/vote-winner-email.php';
		$html_body = ob_get_contents();
		ob_end_clean();

		return array(
			'subject' => $subject
			, 'html_body' => $html_body
		);
	}

    protected function get_order_user_product_params($user) {
		$subject = 'An order has been placed on your item';
		$html_body = 'Dear ' . $user['first_name'] . ',

        An order has been placed on your item.
        ' . $this->get_email_footer() . '
        ';
		$html_body = nl2br($html_body);

		return array(
			'subject' => $subject
			, 'html_body' => $html_body
		);
	}

    protected function get_tagged_in_post_params($user, $params = NULL) {
		$subject = 'You\'ve been tagged in a post';
		$html_body = 'Dear ' . $user['first_name'] . ',

        You\'ve been tagged in a post.

        <a href="' . WEBSITE_URL . '/post-details?posting_id=' . $params['posting_id'] . '">Click here to view the post.</a>
        ' . $this->get_email_footer() . '
        ';
		$html_body = nl2br($html_body);

		return array(
			'subject' => $subject
			, 'html_body' => $html_body
		);
	}

    protected function get_liked_params($user, $params = NULL) {
		if (!empty($user['like_notifications'])) {
			$subject = 'You\'re post has been liked';
			$html_body = 'Dear ' . $user['first_name'] . ',

            You\'re post has been liked.

            <a href="' . WEBSITE_URL . '/post-details?posting_id=' . $params['posting_id'] . '">Click here to view the post.</a>
            ' . $this->get_email_footer() . '
            ';
			$html_body = nl2br($html_body);

			return array(
				'subject' => $subject
				, 'html_body' => $html_body
			);
		}
		return NULL;
	}

    protected function get_commented_params($user, $params = NULL) {
		if (!empty($user['comment_notifications'])) {
			$subject = 'You\'re post has been commented on';
			$html_body = 'Dear ' . $user['first_name'] . ',

            You\'re post has been commented on.

            <a href="' . WEBSITE_URL . '/post-details?posting_id=' . $params['posting_id'] . '">Click here to view the post.</a>
            ' . $this->get_email_footer() . '
            ';
			$html_body = nl2br($html_body);

			return array(
				'subject' => $subject
				, 'html_body' => $html_body
			);
		}
		return NULL;
	}


    protected function get_user($user_id)
    {
   		// Already have user array
   		if (is_array($user_id)) {
   			$user_id['name'] = $user_id['first_name'] . ' ' . $user_id['last_name'];
   			$user_id['email'] = $user_id['email_address'];

   			return $user_id;
   		}
   		// Look up user based on user_id
   		else if (is_numeric($user_id)) {
   			$user = $this->get_row('user_username', array('user_id' => $user_id));

   			if (empty($user)) {
   				return false;
   			}

   			return array(
   				'name' => $user[0]['first_name'] . ' ' . $user[0]['last_name'],
   				'first_name' => $user[0]['first_name'],
   				'last_name' => $user[0]['last_name'],
   				'username' => $user[0]['username'],
   				'email' => $user[0]['email_address'],
   				'comment_notifications' => $user[0]['comment_notifications'],
   				'like_notifications' => $user[0]['like_notifications'],
   			);
   		}

   		return false;
   	}

    protected function get_email_footer() {
		return '
        Thanks,
        Dahlia
		';
	}
}

?>