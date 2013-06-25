<?php
include_once 'includes/database.php';

class Posts
	{
		//insert_is_fav
    	function isFavorite($postID, $userID)	{
			global $config,$conn;
			
			$query = "select count(*) as total from posts_fav where USERID='".mysql_real_escape_string(intval(cleanit($userID)))."' AND PID='".mysql_real_escape_string($postID)."'"; 
			$executequery = $conn->execute($query);
			$totalu = intval($executequery->fields['total']);
			
			return $totalu;
		}
			
		//insert_favcount
		function getFavoriteCount($postID)	{
			global $config,$conn;
			
			$query = "select count(*) as total from posts_fav where PID='".mysql_real_escape_string($postID)."'"; 
			$executequery = $conn->execute($query);
			$totalu = intval($executequery->fields['total']);
			
			return $totalu;
		}
			
		//insert_repincount
		function getNumberOfRePosts($postID)	{
			global $config,$conn;
			
			$query = "select count(*) as total from posts where REPID='".mysql_real_escape_string($postID)."' and active='1'"; 
			$executequery = $conn->execute($query);
			$totalu = intval($executequery->fields['total']);
			
			return $totalu;
		}
		
		//update_last_viewed
		function updatPostLastViewed($postID)	{
			global $conn;
			
			$query = "UPDATE posts SET last_viewed='".time()."' WHERE PID='".mysql_real_escape_string($postID)."'";
			$executequery=$conn->execute($query);
		}
		
		//update_viewcount
		function addPostViewCount($postID)	{
			global $conn, $config;
			
			$points_view = intval($config['points_view']);
			
			$query = "UPDATE posts SET viewcount=viewcount+1, points=points+$points_view WHERE PID='".mysql_real_escape_string($postID)."'";
			$executequery=$conn->execute($query);
		}
	
		//update_compoints
		function addCompPoints($postID)	{
			global $conn, $config;
			
			$points_com = intval($config['points_com']);
			
			$query = "UPDATE posts SET points=points+$points_com WHERE PID='".mysql_real_escape_string($postID)."'";
			$executequery=$conn->execute($query);
		}

		//update_likepointsadd
		function addLikePoints($postID)	{
			global $conn, $config;
			
			$points_like = intval($config['points_like']);
			
			$query = "UPDATE posts SET points=points+$points_like WHERE PID='".mysql_real_escape_string($postID)."'";
			$executequery=$conn->execute($query);
		}

		//update_likepointsrem
		function deductLikePoints($postID)	{
			global $conn, $config;
			
			$points_like = intval($config['points_like']);
			
			$query = "UPDATE posts SET points=points-$points_like WHERE PID='".mysql_real_escape_string($postID)."'";
			$executequery=$conn->execute($query);
		}
			
		//update_repinpoints
		function addRepinPoints($postID)	{
			global $conn, $config;
			
			$points_repin = intval($config['points_repin']);
			
			$query = "UPDATE posts SET points=points+$points_repin WHERE PID='".mysql_real_escape_string($postID)."'";
			$executequery=$conn->execute($query);
		}

		//insert_board_pics
		function getPostPics()	{
			global $config,$conn;
			
			$query = "SELECT pic FROM posts WHERE active='1' order by rand() limit 9"; 
			$results = $conn->execute($query);
			$returnthis = $results->getrows();
			
			return $returnthis;
		}
		
		
		//insert_board_pics_count
		function getNumberOfPosts()	{
			global $config,$conn;
			
			$query = "select count(*) as total from posts WHERE active='1' "; 
			$executequery = $conn->execute($query);
			$tt = $executequery->fields['total'];	
			
			return intval($tt);
		}
		
		//delete_pic
		function deletePost($postID, $userID)	{
				global $config,$conn;
				
				$queryd = "select PID, BID, REPID, pic from posts where USERID='".mysql_real_escape_string($userID)."' AND PID='".mysql_real_escape_string($postID)."'"; 
				$executequeryd = $conn->execute($queryd);
				
				$DPID 	= intval($executequeryd->fields['PID']);	
				$DBID 	= intval($executequeryd->fields['BID']);
				$REPID 	= intval($executequeryd->fields['REPID']);
				$pic 	= $executequeryd->fields['pic'];
				
				if($DPID > 0)	{
					$query = "DELETE FROM activity WHERE PID='".mysql_real_escape_string($DPID)."'";
					$conn->Execute($query);	
					
					$query = "DELETE FROM comments WHERE PID='".mysql_real_escape_string($DPID)."'";
					$conn->Execute($query);	
					
					$query = "DELETE FROM posts_fav WHERE PID='".mysql_real_escape_string($DPID)."'";
					$conn->Execute($query);	
					
					$query = "DELETE FROM posts_reports WHERE PID='".mysql_real_escape_string($DPID)."'";
					$conn->Execute($query);
						
					if($REPID > 0)	{
						$query = "DELETE FROM posts WHERE PID='".mysql_real_escape_string($DPID)."'";
						$conn->Execute($query);	
					}
					else	{
						$query = "select count(*) as total from posts WHERE (REPID='".mysql_real_escape_string($DPID)."' OR OID='".mysql_real_escape_string($DPID)."')"; 
						$executequery=$conn->execute($query);
						$tt = $executequery->fields['total'];
					
						if($tt == "0")	{
							delete_pic_images($pic);	
					
							$query = "DELETE FROM posts WHERE PID='".mysql_real_escape_string($DPID)."'";
							$conn->Execute($query);	
						}
						else	{
							$query = "INSERT INTO posts_delete SET PID='".mysql_real_escape_string($DPID)."', pic='".mysql_real_escape_string($pic)."'";
							$conn->Execute($query);	
					
							$query = "DELETE FROM posts WHERE PID='".mysql_real_escape_string($DPID)."'";
							$conn->Execute($query);
					
							$query = "UPDATE posts SET OID='0',OUSERID='0',REPIN='0',REPID='0' WHERE (REPID='".mysql_real_escape_string($DPID)."' OR OID='".mysql_real_escape_string($DPID)."')";
							$conn->Execute($query);	
						}//($tt == "0")
					}//if($REPID > 0)
				}//if($DPID > 0)
		}//delete_pic($DPID)
    }
?>