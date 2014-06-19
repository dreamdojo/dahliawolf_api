<?php
    class God extends _Model
    {
        public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
        {
            parent::__construct($db_host, $db_user, $db_password, $db_name );
        }

        public function getData($params = array())
        {
            $values = array();


            $members = "
                SELECT COUNT(*) AS total
                FROM dahliawolf_v1_2013.user_username
      			";
            $t_members = "
                SELECT COUNT(*) AS today
                FROM admin_offline_v1_2013.user
                WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY);
                ";
            $a_members = "
                SELECT COUNT(DISTINCT user_id) AS dau
                FROM admin_offline_v1_2013.login_instance
                WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY);
                ";

            $posts = "
                SELECT COUNT(*) AS total
                FROM dahliawolf_v1_2013.posting
      			";

            $t_posts = "
                SELECT COUNT(*) AS today
                FROM dahliawolf_v1_2013.posting
                WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY);
      			";

            $u_posts = "
                SELECT COUNT(DISTINCT user_id) AS perday
                FROM dahliawolf_v1_2013.posting
                WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY);
      			";

            $c_posts = "
                SELECT posting.user_id
                FROM dahliawolf_v1_2013.posting
                WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY);
                GROUP BY posting.user_id
                ORDER BY user_id DESC
      			";

            $likes = "
                SELECT COUNT(*) AS total
                FROM dahliawolf_v1_2013.posting_like
      			";
            $t_likes = "
                SELECT COUNT(*) AS today
                FROM dahliawolf_v1_2013.posting_like
                WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY);
      			";

            try {
                $data['members'] = $this->fetch($members, $values);
                $data['members'][0]['today'] = $this->fetch($t_members, $values)[0]['today'];
                $data['members'][0]['dau'] = $this->fetch($a_members, $values)[0]['dau'];
                $data['posts'] = $this->fetch($posts, $values);
                $data['posts'][0]['today'] = $this->fetch($t_posts, $values)[0]['today'];
                $data['posts'][0]['distinct'] = $this->fetch($u_posts, $values)[0]['perday'];
                $data['posts'][0]['counts'] = $this->fetch($c_posts, $values);
                $data['likes'] = $this->fetch($likes, $values);
                $data['likes'][0]['today'] = $this->fetch($t_likes, $values)[0]['today'];
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }

        public function getAssociateData($username) {
            $values = array();

            $q = "
                SELECT user_username.user_id AS userid
                FROM dahliawolf_v1_2013.user_username
                WHERE user_username.username = '".$username."'"
            ;

            $user_id = $this->fetch($q, $values)[0]['userid'];

            $posts = "
                SELECT COUNT(*) AS total
                FROM dahliawolf_v1_2013.posting
                WHERE posting.user_id = ".$user_id;

            $t_posts = "
                SELECT COUNT(*) AS today
                FROM dahliawolf_v1_2013.posting
                WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY) AND posting.user_id = ".$user_id;

            $w_posts = "
                SELECT posting.posting_id,image.source, image.imagename, CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url, DATE_FORMAT(posting.created, '%M %D') AS date
                FROM dahliawolf_v1_2013.posting
                LEFT JOIN dahliawolf_v1_2013.image ON image.id = posting.image_id
                WHERE posting.created > DATE_SUB(NOW(), INTERVAL 7 DAY) AND posting.user_id = ".$user_id."
                ORDER BY posting.created DESC
                ";

            $d_posts = "
                SELECT COUNT(*) AS daily, DATE_FORMAT(created, '%M %D') AS date
                FROM dahliawolf_v1_2013.posting
                WHERE created > DATE_SUB(NOW(), INTERVAL 7 DAY) AND posting.user_id = ".$user_id."
                GROUP BY DATE(posting.created)
                ORDER BY posting.created DESC
                ";


            $refs = "
                SELECT COUNT(*) AS referral
                FROM dahliawolf_v1_2013.referral
                WHERE referral.user_id = ".$user_id;

            $likes = "
                SELECT COUNT(*) AS total
                FROM dahliawolf_v1_2013.posting_like
                WHERE posting_like.user_id = ".$user_id;

            $w_likes = "
                SELECT posting_like.posting_id, CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url, DATE_FORMAT(posting_like.created, '%M %D') AS date
                FROM dahliawolf_v1_2013.posting_like
                LEFT JOIN dahliawolf_v1_2013.posting ON posting_like.posting_id = posting.posting_id
                LEFT JOIN dahliawolf_v1_2013.image ON image.id = posting.image_id
                WHERE posting_like.created > DATE_SUB(NOW(), INTERVAL 7 DAY) AND posting_like.user_id = ".$user_id."
                ORDER BY posting.created DESC
                ";

            $d_likes = "
                SELECT COUNT(*) AS daily, DATE_FORMAT(created, '%M %D') AS date
                FROM dahliawolf_v1_2013.posting_like
                WHERE created > DATE_SUB(NOW(), INTERVAL 7 DAY) AND posting_like.user_id = ".$user_id."
                GROUP BY DATE(posting_like.created)
                ORDER BY posting_like.created DESC
                ";

            $follows = "
                SELECT COUNT(DISTINCT user_id) AS total
                FROM dahliawolf_v1_2013.follow
                WHERE follow.follower_user_id = ".$user_id." AND follow.user_id IS NOT NULL";

            $w_follows = "
                SELECT follow.user_id, user_username.avatar, user_username.username, user_username.user_id, DATE_FORMAT(follow.created, '%M %D') AS date
                FROM dahliawolf_v1_2013.follow
                LEFT JOIN user_username ON user_username.user_id = follow.user_id
                WHERE created > DATE_SUB(NOW(), INTERVAL 7 DAY) AND follow.follower_user_id = ".$user_id." AND follow.user_id IS NOT NULL";

            $d_follows = "
                SELECT COUNT(DISTINCT user_id) AS daily, DATE_FORMAT(created, '%M %D') AS date
                FROM dahliawolf_v1_2013.follow
                WHERE created > DATE_SUB(NOW(), INTERVAL 7 DAY) AND follow.follower_user_id = ".$user_id."
                GROUP BY DATE(follow.created)
                ORDER BY follow.created DESC
            ";

            $reposts = "
                SELECT COUNT(*) AS total
                FROM dahliawolf_v1_2013.posting_repost
                WHERE posting_repost.repost_user_id = ".$user_id;

            $w_reposts = "
                SELECT posting_repost.posting_id, CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url, DATE_FORMAT(posting_repost.created_at, '%M %D') AS date
                FROM dahliawolf_v1_2013.posting_repost
                    LEFT JOIN dahliawolf_v1_2013.posting ON posting_repost.posting_id = posting.posting_id
                    LEFT JOIN dahliawolf_v1_2013.image ON image.id = posting.image_id
                WHERE posting_repost.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) AND posting_repost.repost_user_id = ".$user_id."
                ORDER BY posting_repost.created_at DESC
                ";
            $d_reposts = "
                SELECT COUNT(*) AS daily, DATE_FORMAT(created_at, '%M %D') AS date
                FROM dahliawolf_v1_2013.posting_repost
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) AND posting_repost.repost_user_id = ".$user_id."
                GROUP BY DATE(posting_repost.created_at)
                ORDER BY posting_repost.created_at DESC
                ";

            $comments = "
                SELECT COUNT(*) AS total
                FROM dahliawolf_v1_2013.comment
                WHERE comment.user_id = ".$user_id;

            $w_comments = "
                SELECT comment.posting_id, comment.comment, CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url, DATE_FORMAT(comment.created_at, '%M %D') AS date
                FROM dahliawolf_v1_2013.comment
                    LEFT JOIN dahliawolf_v1_2013.posting ON comment.posting_id = posting.posting_id
                    LEFT JOIN dahliawolf_v1_2013.image ON image.id = posting.image_id
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) AND  comment.user_id = ".$user_id;
            $d_comments = "
                SELECT COUNT(*) AS daily, DATE_FORMAT(created_at, '%M %D') AS date
                FROM dahliawolf_v1_2013.comment
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) AND  comment.user_id = ".$user_id."
                GROUP BY DATE(comment.created_at)
                ORDER BY comment.created_at DESC
                ";

            $moneys = "
                SELECT SUM(commission.commission) AS total
                FROM offline_commerce_v1_2013.commission
                WHERE commission.user_id = ".$user_id;

            try {
                $data['posts'] = $this->fetch($posts, $values);
                $data['posts']['title'] = 'posts';
                //$data['posts'][0]['today'] = $this->fetch($t_posts, $values)[0]['today'];
                $data['posts'][0]['week'] = $this->fetch($w_posts, $values);
                $data['posts'][0]['daily'] = $this->fetch($d_posts, $values);

                $data['likes'] = $this->fetch($likes, $values);
                $data['likes'][0]['week'] = $this->fetch($w_likes, $values);
                $data['likes'][0]['daily'] = $this->fetch($d_likes, $values);
                $data['likes']['title'] = 'likes';

                $data['follows']['title'] = 'follows';
                $data['follows'][0]['total'] = $this->fetch($follows, $values)[0]['total'];
                $data['follows'][0]['week'] = $this->fetch($w_follows, $values);
                $data['follows'][0]['daily'] = $this->fetch($d_follows, $values);

                $data['reposts']['title'] = 'reposts';
                $data['reposts'][0]['total'] = $this->fetch($reposts, $values)[0]['total'];
                $data['reposts'][0]['week'] = $this->fetch($w_reposts, $values);
                $data['reposts'][0]['daily'] = $this->fetch($d_reposts, $values);

                $data['comments']['title'] = 'comments';
                $data['comments'][0]['total'] = $this->fetch($comments, $values)[0]['total'];
                $data['comments'][0]['week'] = $this->fetch($w_comments, $values);
                $data['comments'][0]['daily'] = $this->fetch($d_comments, $values);

                $data['referrals']['title'] = 'referrals';
                $data['referrals'][0]['total'] = $this->fetch($refs, $values)[0]['referral'];

                $data['commision'] = $this->fetch($moneys, $values);
                $data['commision']['title'] = 'commision';

                return $data;
            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }
        }
    }
?>