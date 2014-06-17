<?
class User extends _Model
{
    const TABLE = 'user_username';
    const PRIMARY_KEY_FIELD = 'user_username_id';

    /*
       `user_username_id,
       `user_id,
       `username,
       `date_of_birth,
       `location,
       `avatar,
       `points,
       `email_address,
       `first_name,
       `last_name,
       `instagram_username,
       `pinterest_username,
       `fb_uid,
       `gender,
       `about,
       `website,
       `facebook_post,
       `instagram_import,
       `comment_notifications,
       `like_notifications,
       `daily_notifications,
       `auto_approve,
       `active,
       `member_id,
       `verified,
       `notification_interval,
       `created_at,
     */


    protected $fields = array(
        'user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'profile_type',
        'gender',
        'referrer_user_id',
        'username',
        'email',
        'hash',
        'active',
        'newsletter',
        'api_website_id',
        'twitter_username'
    );

    protected $public_fields = array(
        'user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'profile_type',
        'gender',
        'username',
        'email',
        'newsletter',
        'api_website_id',
        'twitter_username'
    );


    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {

        if($db_name == DW_API_DATABASE)
        {
            $this->fields = array(
                   'user_id',
                   'first_name',
                   'last_name',
                   'date_of_birth',
                   'profile_type',
                   'gender',
                   'referrer_user_id',
                   'username',
                   'email_address',
                   'hash',
                   'active',
                   'newsletter',
                   'api_website_id',
                   'twitter_username'
               );

            $this->public_fields = array(
               'user_id',
               'first_name',
               'last_name',
               'date_of_birth',
               'profile_type',
               'gender',
               'username',
               'email_address',
               'newsletter',
               'api_website_id',
               'twitter_username'
           );
        }

        parent::__construct($db_host, $db_user, $db_password, $db_name );

    }


    public function get_user($email)
    {
        $data_table  = self::getDataTable();
        $query = "
            SELECT user.*
            FROM {$data_table} user
            WHERE user.email = :email
        ";

        if($this->db_name == DW_API_DATABASE)
        {
            $query = "
            			SELECT user.*
            			FROM {$data_table} user
            			WHERE user.email_address = :email
            		";
        }


        $params = array(
            ':email' => $email
        );

        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');

        $logger->LogInfo("FETCH USER INFO: -h:{$this->db_host} -db:$this->db_name");

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function getProfileSettings($params) {
        $data_table  = self::getDataTable();

        $query = "
            			SELECT profile_options.*
            			FROM {$data_table} profile_options
            			WHERE profile_options.user_id = :userId
            		";

        $params = array(
            ':userId' => $params['user_id']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setProfileSettings($params) {
        $data_table  = self::getDataTable();
        $prof_setting = $params['profile_setting'];
        $user_id = $params['user_id'];
        $setting_val = $params['new_value'];
        $query = "
            			SELECT profile_options.*
            			FROM {$data_table} profile_options
            			WHERE profile_options.user_id = :userId
            		";

        $params = array(
            ':userId' => $params['user_id'],
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);

        if(empty($result)) {//Check to see if user profile settings exist, if not create
            $query = "
            			INSERT INTO {$data_table} profile_options (user_id)
            			VALUES (:userId)
            		";
            $result = $this->query($query, $params);
            $result['created'] = true;
        }

        $query = "
            UPDATE profile_options
            SET profile_options.".$prof_setting." = '".$setting_val."'
            WHERE profile_options.user_id = ".$user_id."
        ";

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query);
        return $result;
    }

    public function setMailSettings($params) {
        $data_table  = self::getDataTable();
        $mail_setting = 'mail_'.$params['mail_setting'];
        $user_id = $params['user_id'];
        $setting_val = $params['new_value'];

        $query = "
            UPDATE user_username
            SET user_username.".$mail_setting." = '".$setting_val."'
            WHERE user_username.user_id = ".$user_id."
        ";

        if(isset($_GET['t']))
        {
            echo sprintf("query: \n%s\n", $query);
            //echo sprintf("sql binds: \n%s\n", var_export($values, true) );
            //echo sprintf("params: \n%s\n", var_export($params, true));
        }

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query);
        return $result;
    }

    public function setShopSettings($params) {
        $data_table  = self::getDataTable();
        $shop_setting = $params['shop_setting'];
        $user_id = $params['user_id'];
        $setting_val = $params['new_value'];
        $query = "
            			SELECT shop_options.*
            			FROM {$data_table} shop_options
            			WHERE shop_options.user_id = :userId
            		";

        $params = array(
            ':userId' => $params['user_id'],
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);

        if(empty($result)) {//Check to see if user profile settings exist, if not create
            $query = "
            			INSERT INTO {$data_table} shop_options (user_id)
            			VALUES (:userId)
            		";
            $result = $this->query($query, $params);
            $result['created'] = true;
        }

        $query = "
            UPDATE shop_options
            SET shop_options.".$shop_setting." = '".$setting_val."'
            WHERE shop_options.user_id = ".$user_id."
        ";

        if(isset($_GET['t']))
        {
            echo sprintf("query: \n%s\n", $query);
            //echo sprintf("sql binds: \n%s\n", var_export($values, true) );
            //echo sprintf("params: \n%s\n", var_export($params, true));
        }

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query);
        return $result;
    }

    public function setCartId($params) {
        $data_table  = self::getDataTable();

        $query = "
            UPDATE user_username
            SET user_username.cart_id = :Id
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':Id' => $params['cart_id']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setUserAuto($params) {
        $data_table  = self::getDataTable();
        $social = 'auto_'.$params['sync_action'].'_'.$params['platform'];
        $query = "
            UPDATE user_username
            SET user_username.".$social." = :sync
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':sync' => $params['sync']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setWolfTicketId($params) {
        $data_table  = self::getDataTable();
        $query = "
            UPDATE user_username
            SET user_username.wolf_ticket = :ticket
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':ticket' => $params['ticket_id']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setWolfAccount($params) {
        $data_table  = self::getDataTable();
        $query = "
            UPDATE user_username
            SET user_username.wolf_account = :ticket
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':ticket' => $params['ticket_id']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setProfileType($params) {
        $data_table  = self::getDataTable();

        $query = "
            UPDATE user_username
            SET user_username.profile_type = :profileType
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':profileType' => $params['profile_type']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setOneClick($params) {
        $data_table  = self::getDataTable();

        $query = "
            UPDATE user_username
            SET user_username.one_click_checkout = :last4
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':last4' => $params['last4']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setUserTumblrBlog($params) {
        $data_table  = self::getDataTable();

        $query = "
            UPDATE user_username
            SET user_username.auto_tumblr_blog = :tumblrName
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':tumblrName' => $params['tumblr_blog_name']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setBillingAddress($params) {
        $data_table  = self::getDataTable();

        $query = "
            UPDATE user_username
            SET user_username.billing_address_id = :Id
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':Id' => $params['billing_address_id']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function setShippingAddress($params) {
        $data_table  = self::getDataTable();

        $query = "
            UPDATE user_username
            SET user_username.shipping_address_id = :Id
            WHERE user_username.user_id = :userId
        ";

        $params = array(
            ':userId' => $params['user_id'],
            ':Id' => $params['shipping_address_id']
        );

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function getUserByUsername($username)
    {
        $data_table  = self::getDataTable();

        $query = "
			SELECT *
			FROM {$data_table} user
			WHERE user.username = :username
		";
        $params = array(
            ':username' => $username
        );

        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');

        $logger->LogInfo("FETCH USER INFO: -h:{$this->db_host} -db:$this->db_name");

        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function getUserById($user_id)
    {
        $data_table  = self::getDataTable();

        $query = "
			SELECT user.*
			FROM  {$data_table} user
			WHERE user.user_id = :user_id
		";
        $params = array(
            ':user_id' => $user_id
        );

        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');


        $result = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $params);
        return $result;
    }

    public function get_user_by_token($user_id, $token)
    {
        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');

        $query = "
			SELECT user.user_id, user.first_name, user.last_name, user.username, user.email, user_username.*
			FROM login_instance
				/*INNER JOIN login_instance ON user.user_id = login_instance.user_id*/
				INNER JOIN user ON user.user_id = login_instance.user_id
				LEFT JOIN dahliawolf_v1_2013.user_username ON user_username.user_id = user.user_id

			WHERE login_instance.token = :token
				AND login_instance.logout IS NULL
		";
        $values = array(
            'token' => $token
        );

        if(isset($_GET['t']))
        {
            echo "QUERY: \n$query\n";
            echo "BIND VALUES: " . var_export($values,true);
            echo "DB INFO: -h:{$this->db_host} -db:$this->db_name";
        }


        $logger->LogInfo("FETCH USER INFO BY TOKEN : -token:$token");

        try {
            $user = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $values);

            try{
                $sql = "
                  INSERT INTO admin_offline_v1_2013.login_instance (user_id, token)
                  VALUES (".$user['user_id'].", '".$token."')
                ";

                $values = array(
                    //'last_login' => date('Y-m-d H:i:s', TIME),
                    //':userId'=>2418
                );
                $result = $this->query($sql, $values);
            } catch(Exception $e) {}

            return $user;
        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception('Unable to get user by token.');
        }
    }

    public function filter_columns($user)
    {
        return array(
            'user_id' => $user['user_id']
        , 'first_name' => $user['first_name']
        , 'last_name' => $user['last_name']
        , 'username' => $user['username']
        , 'email' => $user['email']
        , 'avatar' => $user['avatar']
        );
    }

    public function check_social_network_email_exists($email, $social_network_id)
    {
        $select_str = 'user.' . implode(', user.', $this->public_fields);

        $query = '
			SELECT ' . $select_str . '
			FROM user
				INNER JOIN user_social_network_link ON user.user_id = user_social_network_link.user_id
			WHERE user.email = :email
				AND user_social_network_link.social_network_id = :social_network_id
		';
        $values = array(
            ':email' => $email,
            ':social_network_id' => $social_network_id
        );

        //$logger = new Jk_Logger(APP_PATH . 'logs/facebook.log');
        //$logger->LogInfo( __FUNCTION__ . " DB: -h:{$this->db_host} -db:$this->db_name :\n " . var_export($values, true));;

        try {
            $user = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $values);
            //$logger->LogInfo( " USER:\n " . var_export($user, true));;
            return $user;
        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception('Unable to check user social network email.');
            //$logger->LogInfo( " Exception: " . $e->getMessage());
            return false;
        }

        return false;
    }

    public function save($info)
    {
        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');
        $logger->LogInfo("SAVE NEW USER DATA: -h:{$this->db_host} -db:$this->db_name :\n " . var_export($info, true));

        return parent::save($info);
    }


    public function registerDefaultFollows($user_id)
    {
        //follow default
        $follow_these = array(658, 1375, 790, 1385, 3797, 2763, 3584, 2776, 3577, 2736);

        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');

        foreach ($follow_these as $ftk => $fthisone) {
            $follow = array(
                'user_id' => $user_id,
                'follower_user_id' => $fthisone
            );
            $logger->LogInfo(sprintf("registration autofollow following user: \n%s ", var_export($follow, true)));

            $result_id = $this->follow($follow);
        }
    }


    public function follow($data = array())
    {
        $error = NULL;
        //$this->load('Follow');
        $follow = new Follow();

        try {
            $insert_id = $follow->followUser($data);
            return array(
                strtolower(self::PRIMARY_KEY_FIELD) => $insert_id,
                //'model_data' => $data
            );

        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to follow user.  " . $e->getMessage());
        }

    }

    public function get_regexp_username($username)
    {
        $query = '
			SELECT username
			FROM user
			WHERE username REGEXP :username
			ORDER BY LENGTH(username)
		';
        $values = array(
            ':username' => '^' . $username . '[0-9]*$'
        );

        try {
            $usernames = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $values);

            return $usernames;
        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception('Unable to get regexp usernames.');
        }
    }

    public function get_sales($user_id, $summary=false, $id_shop=3, $id_lang=1 )
    {
        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');

        $params = array(
            ':id_shop' => $id_shop,
            ':id_lang' => $id_lang,
            ':user_id' => $user_id,
            //':active' => '1',
        );


        $select_sql = "SUM(order_detail.product_price) as sales_total";
        $group_sql = "";


        if( $summary )
        {
            $select_sql = "order_detail.product_id,
                        SUM(order_detail.product_price) as sales_total";

            $group_sql = "GROUP BY order_detail.product_id";
        }

        $sql = "
        SELECT {$select_sql}
        FROM offline_commerce_v1_2013.order_detail
        WHERE order_detail.product_id IN
            (
              SELECT products.product_id
              FROM
                (
                    SELECT  DISTINCT  product.id_product as 'product_id',
                    (
                            SELECT product_file.product_file_id FROM offline_commerce_v1_2013.product_file WHERE product_file.product_id = product.id_product ORDER BY product_file.product_file_id ASC LIMIT 1) AS product_file_id,
                            IF(EXISTS(SELECT category_product.id_category_product FROM offline_commerce_v1_2013.category_product WHERE category_product.id_category = 1 AND category_product.id_product = product.id_product), 1, 0) AS is_new,
                            user_username.username as username, IF(user_username.location IS NULL, '', user_username.location) AS 'location',
                            user_username.user_id

                            FROM offline_commerce_v1_2013.product

                                LEFT JOIN
                                (
                                    SELECT m.*, posting_product.posting_id, posting_product.product_id
                                    FROM
                                    (
                                        SELECT MIN(posting_product.created) AS pp_created, GROUP_CONCAT(posting_product.posting_id SEPARATOR '|') AS posting_ids
                                        FROM dahliawolf_v1_2013.posting
                                            INNER JOIN dahliawolf_v1_2013.posting_product ON posting.posting_id = posting_product.posting_id
                                        GROUP BY posting_product.product_id
                                    ) AS m
                                    INNER JOIN dahliawolf_v1_2013.posting_product ON posting_product.created = m.pp_created
                                ) AS mm ON product.id_product = mm.product_id


                                LEFT JOIN dahliawolf_v1_2013.posting AS posting ON mm.posting_id = posting.posting_id
                                INNER JOIN offline_commerce_v1_2013.product_shop ON product.id_product = product_shop.id_product
                                INNER JOIN offline_commerce_v1_2013.shop ON product_shop.id_shop = shop.id_shop
                                INNER JOIN offline_commerce_v1_2013.product_lang ON product.id_product = product_lang.id_product
                                INNER JOIN offline_commerce_v1_2013.lang ON product_lang.id_lang = lang.id_lang
                                LEFT JOIN offline_commerce_v1_2013.shop AS default_shop ON product.id_shop_default = default_shop.id_shop
                                LEFT JOIN dahliawolf_v1_2013.user_username ON user_username.user_id = posting.user_id

                            WHERE shop.id_shop = :id_shop AND lang.id_lang = :id_lang
                            AND product.user_id = :user_id

                    ) AS products

                )

            {$group_sql}
        ";

        $logger->LogInfo("query params: " . var_export($params,true));

        if(isset($_GET['t'])) var_dump($sql);


        try {
            $data = self::$dbs[$this->db_host][$this->db_name]->exec($sql, $params);

            if( $summary ) return $data;
            else return ($data && isset($data[0]) ? $data[0] : null  );
        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception('Unable to get user comissions.');
        }

    }
    
    public function getItemCount($user_id) {
        $sql = "
                SELECT  DISTINCT  COUNT(*) AS products
                FROM offline_commerce_v1_2013.product
                WHERE user_id = ".$user_id." AND active = 1
                ";
        $data = self::$dbs[$this->db_host][$this->db_name]->exec($sql, $params);

        return ($data && isset($data[0]) ? $data[0] : null  );
    }

    public function getOrderCount($user_id) {
        $q = "
            SELECT customer.id_customer
            WHERE customer.user_id = ".$user_id."
            FROM offline_commerce_v1_2013.customer
        ";
        $data = self::$dbs[$this->db_host][$this->db_name]->exec($q, $params);


        $sql = "
                SELECT  DISTINCT  COUNT(*) AS products
                FROM offline_commerce_v1_2013.product
                WHERE user_id = ".$user_id." AND active = 1
                ";
        //$data = self::$dbs[$this->db_host][$this->db_name]->exec($sql, $params);

        return ($data && isset($data[0]) ? $data[0] : null  );
    }
    
    public function getUserDetails($params = array()) 
    {
        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');
        
        //$logger->LogInfo("getUserDetails USER INFO: -h:{$this->db_host} -db:$this->db_name");
        
        $error = NULL;

        $where_str = '';
        $values = array();
        // user_id or username
        if (!empty($params['user_id'])) {
            $where_str = 'user_username.user_id = :user_id';
            $values[':user_id'] = $params['user_id'];
        }
        else {
            $where_str = 'username = :username';
            $values[':username'] = !empty($params['username']) ? $params['username'] : '';
        }

        $select_str = '';
        $join_str = '';
        $join_str .= 'LEFT JOIN profile_options ON user_username.user_id = profile_options.user_id ';
        // Optional viewer_user_id
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
            $join_str .= 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $active_limit = (60*60*24)*30;

        $sql = "SELECT
          user_username.*
        , (
            SELECT COUNT(*)
            FROM user_username AS u
            WHERE
                u.points > user_username.points
        ) + 1 AS rank
        , (
            SELECT COUNT(*)
            FROM follow
            WHERE follow.follower_user_id = user_username.user_id
        ) AS following
        , (
            SELECT COUNT(*)
            FROM follow
            WHERE follow.user_id = user_username.user_id
        ) AS followers
        ,
         profile_options.*
        , user_username.*
        , (
            SELECT COUNT(*)
            FROM posting
            WHERE posting.user_id = user_username.user_id
        ) AS total_posts
        , (
            SELECT COUNT(*)
            FROM offline_commerce_v1_2013.favorite_product
            WHERE favorite_product.id_customer = user_username.user_id
        ) AS wishlist_count
        , (
            SELECT COUNT(*)
            FROM offline_commerce_v1_2013.favorite_product
            WHERE favorite_product.id_customer = user_username.user_id
        ) AS total_wishlist
        /*, (
            SELECT COUNT(*)
            FROM comment
            WHERE comment.user_id = user_username.user_id
        ) AS comments*/
        , (
            SELECT COUNT(*)
            FROM posting_like
                INNER JOIN posting ON posting_like.posting_id = posting.posting_id
            WHERE posting.user_id = user_username.user_id
        ) AS total_loves
        ,(
              SELECT
              ml.name
              FROM membership_level ml, user_username user
              WHERE user.user_id = user_username.user_id
                AND CAST(user.points AS SIGNED)  / ml.points > 1
              order by ABS(CAST(ml.points AS SIGNED) - CAST(user.points AS SIGNED)) ASC
              LIMIT 1
          ) AS membership_level
          /*,(
              SELECT COUNT(*)
              FROM posting
              WHERE posting.user_id = user_username.user_id
                AND posting.deleted IS NULL
                AND UNIX_TIMESTAMP(posting.created)+2592000 < UNIX_TIMESTAMP()

          )AS posts_expired
          ,(
              SELECT COUNT(*)
              FROM posting
              WHERE posting.user_id = user_username.user_id
                AND posting.deleted IS NULL
                AND UNIX_TIMESTAMP(posting.created)+2592000 > UNIX_TIMESTAMP()

          ) AS posts_active
          ,(
              SELECT COUNT(*)
              FROM posting
               LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id
              WHERE posting.user_id = user_username.user_id  AND like_winner.like_winner_id IS NOT NULL
          ) AS winner_posts
          ,(
            SELECT COUNT(*)
            FROM posting
            WHERE posting.user_id = user_username.user_id
                AND posting.deleted IS NULL
        ) AS posts_total*/

            {$select_str}

        FROM user_username
            {$join_str}
        WHERE {$where_str}
        LIMIT 1";


        if(isset($_GET['t']))
        {
            echo sprintf("query: \n%s\n", $sql);
            echo sprintf("sql binds: \n%s\n", var_export($values, true) );
            echo sprintf("params: \n%s\n", var_export($params, true));
        }

        try {
           $data = self::$dbs[$this->db_host][$this->db_name]->exec($sql, $values);
           return ($data && isset($data[0]) ? $data[0] : null  );
       } catch (Exception $e) {
            $logger->LogInfo("can not get user info: " . $e->getMessage() );
            self::$Exception_Helper->server_error_exception('Unable to get user details.');
       }
    }

    public function getUserShop($params = array())
    {
        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');

        //$logger->LogInfo("getUserDetails USER INFO: -h:{$this->db_host} -db:$this->db_name");

        $error = NULL;

        $where_str = '';
        $values = array();
        // user_id or username
        if (!empty($params['user_id'])) {
            $where_str = 'user_username.user_id = :user_id';
            $values[':user_id'] = $params['user_id'];
        }
        else {
            $where_str = 'username = :username';
            $values[':username'] = !empty($params['username']) ? $params['username'] : '';
        }

        $select_str = '';
        $join_str = '';
        $join_str .= 'LEFT JOIN shop_options ON user_username.user_id = shop_options.user_id ';
        // Optional viewer_user_id
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
            $join_str .= 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $sql = "SELECT
          user_username.*
        , (
            SELECT COUNT(*)
            FROM follow
            WHERE follow.follower_user_id = user_username.user_id
        ) AS following
        , (
            SELECT COUNT(*)
            FROM follow
            WHERE follow.user_id = user_username.user_id
        ) AS followers
        ,
         shop_options.*
        , user_username.*

        {$select_str}

        FROM user_username
            {$join_str}
        WHERE {$where_str}
        LIMIT 1";


        if(isset($_GET['t']))
        {
            echo sprintf("query: \n%s\n", $sql);
            echo sprintf("sql binds: \n%s\n", var_export($values, true) );
            echo sprintf("params: \n%s\n", var_export($params, true));
        }

        try {
            $data = self::$dbs[$this->db_host][$this->db_name]->exec($sql, $values);
            return ($data && isset($data[0]) ? $data[0] : null  );
        } catch (Exception $e) {
            $logger->LogInfo("can not get user info: " . $e->getMessage() );
            self::$Exception_Helper->server_error_exception('Unable to get user details.');
        }
    }



    public function getUsersWithDetails($params = array())
    {
        $logger = new Jk_Logger(APP_PATH . 'logs/user.log');

        //$logger->LogInfo("getUserDetails USER INFO: -h:{$this->db_host} -db:$this->db_name");

        $error = NULL;

        $where_str = '';
        $values = array();
        // user_id or username
        /*
        if (!empty($params['user_id'])) {
            $where_str = 'user_username.user_id = :user_id';
            $values[':user_id'] = $params['user_id'];
        }
        */

        if (!empty($params['q']))
        {
            $where_str = 'user_username.username like :q';
            $values[':q'] = "%{$params['q']}%";
        }

        $select_str = '';
        $join_str = '';
        // Optional viewer_user_id
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
            $join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $active_limit = (60*60*24)*30;

        $sql = "SELECT
          user_username.*
        , (
            SELECT COUNT(*)
            FROM user_username AS u
            WHERE
                u.points > user_username.points
        ) + 1 AS rank
        , (
            SELECT COUNT(*)
            FROM follow
            WHERE follow.follower_user_id = user_username.user_id
        ) AS following
        , (
            SELECT COUNT(*)
            FROM follow
            WHERE follow.user_id = user_username.user_id
        ) AS followers
        , (
            SELECT COUNT(*)
            FROM posting
            WHERE posting.user_id = user_username.user_id
        ) AS posts
        , (
            SELECT COUNT(*)
            FROM comment
            WHERE comment.user_id = user_username.user_id
        ) AS comments
        , (
            SELECT COUNT(*)
            FROM posting_like
                INNER JOIN posting ON posting_like.posting_id = posting.posting_id
            WHERE posting.user_id = user_username.user_id
        ) AS likes
        ,(
              SELECT
              ml.name
              FROM membership_level ml, user_username user
              WHERE user.user_id = user_username.user_id
                AND CAST(user.points AS SIGNED)  / ml.points > 1
              order by ABS(CAST(ml.points AS SIGNED) - CAST(user.points AS SIGNED)) ASC
              LIMIT 1
          ) AS membership_level
          ,(
              SELECT COUNT(*)
              FROM posting
              WHERE posting.user_id = user_username.user_id
                AND posting.deleted IS NULL
                AND UNIX_TIMESTAMP(posting.created)+2592000 < UNIX_TIMESTAMP()

          )AS posts_expired
          ,(
              SELECT COUNT(*)
              FROM posting
              WHERE posting.user_id = user_username.user_id
                AND posting.deleted IS NULL
                AND UNIX_TIMESTAMP(posting.created)+2592000 > UNIX_TIMESTAMP()

          ) AS posts_active
          ,(
              SELECT COUNT(*)
              FROM posting
               LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id
              WHERE posting.user_id = user_username.user_id  AND like_winner.like_winner_id IS NOT NULL
          ) AS winner_posts
          ,(
            SELECT COUNT(*)
            FROM posting
            WHERE posting.user_id = user_username.user_id
                AND posting.deleted IS NULL
        ) AS posts_total

            {$select_str}

        FROM user_username
            {$join_str}
        WHERE {$where_str}
        /* LIMIT 1 */";


        if(isset($_GET['t']))
        {
            echo sprintf("query: \n%s\n", $sql);
            echo sprintf("sql binds: \n%s\n", var_export($values, true) );
            echo sprintf("params: \n%s\n", var_export($params, true));
        }

        try {
           $data = self::$dbs[$this->db_host][$this->db_name]->exec($sql, $values);

           return ( $data !== false ? $data : null );
       } catch (Exception $e) {
            $logger->LogInfo("can not get user info: " . $e->getMessage() );
            self::$Exception_Helper->server_error_exception('Unable to get user details.');
       }
    }




    public function getTopUsers( $params = array() )
    {
        $error = NULL;

        $select_str = '';
        $join_str = '';
        $where_str = '';

        $values = array();
        // user_id or username
        if (!empty($params['user_id'])) {
            $where_str = 'follow.follower_user_id = :user_id';
            $values[':user_id'] = $params['user_id'];
        }
        else{
            $where_str = '1';
            $join_followers = " INNER JOIN user_username ON follow.user_id = user_username.user_id";
                                //LEFT JOIN follow AS f ON user_username.user_id = f.user_id";
        }

        // Optional viewer_user_id
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
            $join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
            $values[':viewer_user_id'] = $params['viewer_user_id'];

            $join_followers = " INNER JOIN user_username ON follow.user_id = user_username.user_id";

            $is_followed =", IF(f.user_id IS NULL, 0, 1) AS is_followed";
        }

        $offset_string = $this->generate_limit_offset_str($params);

        $following_query = "SELECT distinct
        	user_username.user_username_id,
        	user_username.user_id,
        	user_username.username,
        	user_username.first_name,
        	user_username.last_name,
        	user_username.points,
        	user_username.location,
        	user_username.fb_uid,
        	user_username.avatar,
        	user_username.verified,

        	rank.rank

        	{$select_str}

        	/*,(
        		SELECT
        		ml.name
        		FROM membership_level ml, user_username user
        		WHERE user.user_id = user_username.user_id
        			AND CAST(user.points AS SIGNED)  / ml.points > 1
        		order by ABS(CAST(ml.points AS SIGNED) - CAST(user.points AS SIGNED)) ASC
        		LIMIT 1
        	) AS membership_level
        	, (
                SELECT COUNT(*)
                FROM follow
                WHERE follow.follower_user_id = user_username.user_id
            ) AS following
            , (
                SELECT COUNT(*)
                FROM follow
                WHERE follow.user_id = user_username.user_id
            ) AS followers*/

        	{$is_followed}

            FROM follow
                {$join_followers}
                {$join_str}

                INNER JOIN
                        ( SELECT
                            u.user_id,
                            @row:=@row+1 as rank
                            FROM user_username AS u
                            join (SELECT @row:=0) pos
                            ORDER BY u.points DESC
                        limit 999999999999999  )
                  AS rank ON rank.user_id = user_username.user_id

            WHERE {$where_str}
            ORDER BY rank.rank ASC
            {$offset_string};
            ";


        if(isset($_GET['t'])) {
            echo sprintf("query: \n%s \nparams: %s\n: params: %s", $following_query, var_export($values, true), var_export($params, true));
        }

        if(isset($_GET['t'])) {
            echo "query:\n";
            var_dump($following_query);
            echo "values:\n";
            var_dump($values);
            echo "params:\n";
            var_dump($params);
        }

        $result = $this->fetch($following_query, $values);

        foreach ($result->data->get_top_users->users as $key=>$user)
            $result->data->get_top_users->users[$key]['test'] = 'blop';


        if ($result === false) {
             if(isset($_GET['t'])) echo "\nERROR: {$this->error}" ;
             return array('error' => 'Could not get top following.');
        }


        return $result;

    }




    public function getTopFollowingByUser( $params = array() )
    {
        $error = NULL;

        $select_str = '';
        $join_str = '';
        $where_str = '';

        $values = array();
        //$values[':viewer_user_id'] = $params['viewer_user_id'];
        $values[':user_id'] = $params['user_id'];

        if (!empty($params['viewer_user_id'])) {
            $select_str .= ', IF(following.user_id IS NULL, 0, 1) AS is_followed';
            $join_str .= 'LEFT JOIN follow AS following ON following.user_id = user_username.user_id
                AND following.follower_user_id = :viewer_user_id';

            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $offset_string = $this->generate_limit_offset_str($params);

        $following_query = "
        SELECT distinct
            user_username.username,
            user_username.user_username_id,
            user_username.points,
            user_username.user_id,
            user_username.first_name,
        	user_username.last_name,
            user_username.location,
            user_username.fb_uid,
            user_username.avatar,
            user_username.verified,

            rank.*

            {$select_str}

            /*,(
                SELECT
                ml.name
                FROM membership_level ml, user_username user
                WHERE user.user_id = user_username.user_id
                    AND CAST(user.points AS SIGNED)  / ml.points > 1
                order by ABS(CAST(ml.points AS SIGNED) - CAST(user.points AS SIGNED)) ASC
                LIMIT 1
            ) AS membership_level*/


            FROM user_username
                INNER JOIN ( select *
                                FROM follow as following
                                WHERE
                                following.follower_user_id = :user_id )

                AS follow ON follow.user_id = user_username.user_id

                INNER JOIN
                        ( SELECT
                        	  u.user_id, u.points,
                        	  @row:=@row+1 as rank
                        	FROM user_username AS u
                        	join (SELECT @row:=0) pos
                        	ORDER BY u.points DESC
                        )
                AS rank ON rank.user_id = follow.user_id

                {$join_str}

            WHERE 1
              {$where_str}

            ORDER BY rank.rank ASC
            {$offset_string};
            ";


        if(isset($_GET['t'])) {
            echo "query:\n";
            var_dump($following_query);
            echo "values:\n";
            var_dump($values);
            echo "params:\n";
            var_dump($params);
            echo "here here!!";

        }

        $result = $this->fetch($following_query, $values);


        if ($result === false) {
             if(isset($_GET['t'])) echo $this->error;
             return array('error' => 'Could not get top following.');
        }


        return $result;

    }

    public function getTestTopFollowingByUser( $params = array() )
    {
        $error = NULL;

        $select_str = '';
        $join_str = '';
        $where_str = '';

        $values = array();
        //$values[':viewer_user_id'] = $params['viewer_user_id'];
        $values[':user_id'] = $params['user_id'];

        if (!empty($params['viewer_user_id'])) {
            $select_str .= ', IF(following.user_id IS NULL, 0, 1) AS is_followed';
            $join_str .= 'LEFT JOIN follow AS following ON following.user_id = users.user_id
                AND following.follower_user_id = :viewer_user_id';

            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $offset_string = $this->generate_limit_offset_str($params);

        $following_query = "
            SELECT users.*
            {$select_str}
            , (SELECT COUNT(*)
                FROM follow
                WHERE follow.follower_user_id = users.user_id
            ) AS following
            , (SELECT COUNT(*)
                FROM follow
                WHERE follow.user_id = users.user_id
            ) AS followers
            FROM
                ( SELECT followers.user_id, user_username.username, user_username.avatar, user_username.first_name, user_username.last_name
                FROM follow AS followers
                  LEFT JOIN user_username ON followers.user_id = user_username.user_id
                WHERE followers.follower_user_id = :user_id
                GROUP BY user_id
                {$offset_string} ) AS users
                {$join_str}
        ";


        if(isset($_GET['t'])) {
            echo "query:\n";
            var_dump($following_query);
            echo "values:\n";
            var_dump($values);
            echo "params:\n";
            var_dump($params);
            echo "here here!!";

        }

        $result = $this->fetch($following_query, $values);


        if ($result === false) {
            if(isset($_GET['t'])) echo $this->error;
            return array('error' => 'Could not get top following.');
        }


        return $result;

    }

    public function getCustomers($user_id) {
        $query = "
            SELECT distinct customers.*, user_username.username, user_username.avatar
            FROM dahliawolf_v1_2013.customers
            LEFT JOIN dahliawolf_v1_2013.user_username ON user_username.user_id = customers.customer_id
            WHERE customers.user_id = :userId
            GROUP BY customers.customer_id
        ";

        $values = Array(
            ':userId' => $user_id
        );

        $result = $this->fetch($query, $values);


        if ($result === false) {
            if(isset($_GET['t'])) echo $this->error;
            return array('error' => 'Could not get top following.');
        }


        return $result;

    }

    public function getUserSales($user_id) {
        $query = "
            SELECT customers.*, user_username.username,user_username.avatar, product.price,product_file.product_file_id, product_lang.name
            FROM dahliawolf_v1_2013.customers
            LEFT JOIN offline_commerce_v1_2013.product ON product.id_product = customers.id_product
            LEFT JOIN offline_commerce_v1_2013.product_file ON product_file.product_id = customers.id_product
            LEFT JOIN offline_commerce_v1_2013.product_lang ON product_lang.id_product = customers.id_product
            LEFT JOIN dahliawolf_v1_2013.user_username ON user_username.user_id = customers.customer_id
            WHERE customers.user_id = :userId
            GROUP BY customers.index
        ";

        $values = Array(
            ':userId' => $user_id
        );

        $result = $this->fetch($query, $values);


        if ($result === false) {
            if(isset($_GET['t'])) echo $this->error;
            return array('error' => 'Could not get top following.');
        }


        return $result;

    }
    public function getCommisionList($user_id) {
        $query = "
            SELECT commission.*
            FROM offline_commerce_v1_2013.commission
            WHERE commission.user_id = :userId
        ";

        $values = Array(
            ':userId' => $user_id
        );

        $result = $this->fetch($query, $values);


        if ($result === false) {
            if(isset($_GET['t'])) echo $this->error;
            return array('error' => 'Could not get top following.');
        }


        return $result;

    }

    public function getTopFollowersByUser( $params = array() )
    {
        $error = NULL;
        $select_str = '';
        $join_str = '';
        $where_str = '';

        $values = array();
        if($values[':viewer_user_id'])
        {
            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $values[':user_id'] = $params['user_id'];

        if (!empty($params['viewer_user_id'])) {
            $select_str .= ', IF(following.user_id IS NULL, 0, 1) AS is_followed';
            $join_str .= 'LEFT JOIN follow AS following ON following.user_id = user_username.user_id
                AND following.follower_user_id = :viewer_user_id';

            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $offset_string = $this->generate_limit_offset_str($params);

        $following_query = "
        SELECT distinct
            user_username.user_username_id,
            user_username.user_id,
            user_username.username,
            user_username.points,
            user_username.first_name,
        	user_username.last_name,
            user_username.location,
            user_username.fb_uid,
            user_username.avatar,
            user_username.verified,

            rank.rank

            {$select_str}

            /*,(
                SELECT
                ml.name
                FROM membership_level ml, user_username user
                WHERE user.user_id = user_username.user_id
                    AND CAST(user.points AS SIGNED)  / ml.points > 1
                order by ABS(CAST(ml.points AS SIGNED) - CAST(user.points AS SIGNED)) ASC
                LIMIT 1
            ) AS membership_level*/


            FROM user_username
                INNER JOIN ( select *
                                FROM follow as following
                                WHERE
                                following.user_id = :user_id )

                AS follow ON follow.follower_user_id = user_username.user_id


                INNER JOIN
                        ( SELECT
                              u.user_id, u.points as rank_points,
                              @row:=@row+1 as rank
                            FROM user_username AS u
                            join (SELECT @row:=0) pos
                            ORDER BY u.points DESC
                        )
                AS rank ON rank.user_id = follow.follower_user_id

                {$join_str}

            WHERE 1
              {$where_str}

            ORDER BY rank.rank ASC
            {$offset_string};
            ";


        if(isset($_GET['t'])) {
            echo "query:\n";
            var_dump($following_query);
            echo "values:\n";
            var_dump($values);
            echo "params:\n";
            var_dump($params);
            echo "here here!!";

        }

        $result = $this->fetch($following_query, $values);


        if ($result === false) {
             if(isset($_GET['t'])) echo $this->error;
             return array('error' => 'Could not get top following.');
        }


        return $result;

    }



    protected function generate_limit_offset_str($params, $offset=true) {
   		$limit_offset_str = '';
   		if (!empty($params['limit'])) {
   			$limit_offset_str .= ' LIMIT ' . (int)$params['limit'];
   		}
   		if ($offset && !empty($params['offset'])) {
   			$limit_offset_str .= ' OFFSET ' . (int)$params['offset'];
   		}

   		return $limit_offset_str;
   	}



}





