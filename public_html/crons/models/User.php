<?
class User extends db {
	public function get_daily_summary_users($date) {
		$query = '
			SELECT user_username.user_id, user_username.email_address AS email, user_username.first_name, user_username.last_name
				, IFNULL(posts.posts, 0) AS posts, IFNULL(likes.likes, 0) AS likes, IFNULL(comments.comments, 0) AS comments, IFNULL(follows.follows, 0) AS follows, IFNULL(points.points, 0) AS points
			FROM user_username
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS posts
					FROM posting
					WHERE DATE(posting.created) = :date
					GROUP BY posting.user_id
				) AS posts ON user_username.user_id = posts.user_id
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS likes
					FROM posting_like
						INNER JOIN posting ON posting_like.posting_id = posting.posting_id
					WHERE DATE(posting_like.created) = :date
					GROUP BY posting.user_id
				) AS likes ON user_username.user_id = likes.user_id
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS comments
					FROM comment
						INNER JOIN posting ON comment.posting_id = posting.posting_id
					WHERE DATE(comment.created) = :date
					GROUP BY posting.user_id
				) AS comments ON user_username.user_id = comments.user_id
				LEFT JOIN (
					SELECT follow.user_id, COUNT(*) AS follows
					FROM follow
					WHERE DATE(created) = :date
					GROUP BY follow.user_id
				) AS follows ON user_username.user_id = follows.user_id
				LEFT JOIN (
					SELECT user_point.user_id, SUM(points) AS points
					FROM user_point
					WHERE DATE(created) = :date
					GROUP BY user_point.user_id
				) AS points ON user_username.user_id = points.user_id
			WHERE user_username.daily_notifications = 1
			ORDER BY user_username.user_id
		';
		$values = array(
			':date' => $date
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			return NULL;
		}
		$rows = $result->fetchAll();

		return $rows;
	}

	public function get_summary_users($interval, $date) {
		$interval = ucwords(strtolower($interval));

		$intervals = array(
			'Daily' => 'DAY'
			, 'Weekly' => 'WEEK'
			, 'Monthly' => 'MONTH'
		);

		if (empty($intervals[$interval])) {
			return NULL;
		}
		$interval_keyword = $intervals[$interval];

		$query = '
			SELECT  user_username.user_id, user_username.email_address AS email, user_username.first_name, user_username.last_name
				, IFNULL(posts.posts, 0) AS posts
				, IFNULL(likes.likes, 0) AS likes
				, IFNULL(comments.comments, 0) AS comments
				, IFNULL(follows.follows, 0) AS follows
				, IFNULL(points.points, 0) AS points
				, (
					SELECT COUNT(*)
					FROM user_username AS u
					WHERE
						u.points > user_username.points
				) + 1 AS rank
			FROM user_username
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS posts
					FROM posting
					WHERE posting.created BETWEEN DATE_SUB(:date, INTERVAL 1 ' . $interval_keyword . ') AND :date
					GROUP BY posting.user_id
				) AS posts ON user_username.user_id = posts.user_id
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS likes
					FROM posting_like
						INNER JOIN posting ON posting_like.posting_id = posting.posting_id
					WHERE posting_like.created BETWEEN DATE_SUB(:date, INTERVAL 1 ' . $interval_keyword . ') AND :date
					GROUP BY posting.user_id
				) AS likes ON user_username.user_id = likes.user_id
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS comments
					FROM comment
						INNER JOIN posting ON comment.posting_id = posting.posting_id
					WHERE comment.created BETWEEN DATE_SUB(:date, INTERVAL 1 ' . $interval_keyword . ') AND :date
					GROUP BY posting.user_id
				) AS comments ON user_username.user_id = comments.user_id
				LEFT JOIN (
					SELECT follow.user_id, COUNT(*) AS follows
					FROM follow
					WHERE follow.created BETWEEN DATE_SUB(:date, INTERVAL 1 ' . $interval_keyword . ') AND :date
					GROUP BY follow.user_id
				) AS follows ON user_username.user_id = follows.user_id
				LEFT JOIN (
					SELECT user_point.user_id, SUM(points) AS points
					FROM user_point
					WHERE user_point.created BETWEEN DATE_SUB(:date, INTERVAL 1 ' . $interval_keyword . ') AND :date
					GROUP BY user_point.user_id
				) AS points ON user_username.user_id = points.user_id
			WHERE user_username.notification_interval = :interval
			ORDER BY user_username.user_id ASC
		';
		$values = array(
			':date' => $date
			, ':interval' => $interval
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			echo $this->debug();
			return NULL;
		}
		$rows = $result->fetchAll();

		return $rows;
	}

    public function get_associates() {
        $values = Array();
        $q = "
            SELECT user_id, email_address, username
            FROM user_username
            WHERE associate = 1
        ";

        $result = $this->run($q, $values);
        $rows = $result->fetchAll();

        return $rows;
    }
    public function get_associate_progress($user_id) {
        $values = Array();
        $q = "
            SELECT
                (SELECT COUNT(posting.posting_id) as total FROM posting WHERE user_id = ". $user_id." AND created > SUBDATE(NOW(),2) AND created < (DATE(NOW()))) AS posts
                , (SELECT COUNT(posting_repost.posting_repost_id) as total FROM posting_repost WHERE repost_user_id = ". $user_id." AND created_at > SUBDATE(NOW(),2) AND created_at < (DATE(NOW()))) AS reposts
                , (SELECT COUNT(follow.follow_id) as total FROM follow WHERE follower_user_id = ". $user_id." AND created > SUBDATE(NOW(),2) AND created < (DATE(NOW()))) AS follows
                , (SELECT COUNT(posting_like.posting_like_id) as total FROM posting_like WHERE user_id = ". $user_id." AND created > SUBDATE(NOW(),2) AND created < (DATE(NOW()))) AS likes
                , (SELECT COUNT(comment.comment_id) as total FROM comment WHERE user_id = ". $user_id." AND created_at > SUBDATE(NOW(),2) AND created_at < (DATE(NOW()))) AS comments
        ";

        $result = $this->run($q, $values);
        $rows = $result->fetchAll();
        return $rows[0];
    }
}
?>
