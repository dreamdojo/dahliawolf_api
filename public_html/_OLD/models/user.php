<?php
include_once 'includes/database.php';

class User
	{
    	function session_verification()	{
			if ($_SESSION[USERID] != "")	{
					if (is_numeric($_SESSION[USERID]))	return true;
					else	return false;
				}
			else	return false;
		}
	
		function verify_email_username($usernametocheck)	{
			global $config,$conn;
			$query = "select count(*) as total from members where username='".mysql_real_escape_string($usernametocheck)."' limit 1"; 
			$executequery = $conn->execute($query);
			$totalu = $executequery->fields[total];
			if ($totalu >= 1)	return false;
			else	return true;
		}

		function verify_email_unique($emailtocheck)	{
			global $config,$conn;
			$query = "select count(*) as total from members where email='".mysql_real_escape_string($emailtocheck)."' limit 1"; 
			$executequery = $conn->execute($query);
			$totalemails = $executequery->fields[total];
			if ($totalemails >= 1)	return false;
			else	return true;
		}

		function create_slrememberme() {
				$key = md5(uniqid(rand(), true));
				global $conn;
				$sql="update members set remember_me_time='".date('Y-m-d H:i:s')."', remember_me_key='".$key."' WHERE username='".mysql_real_escape_string($_SESSION[USERNAME])."'";
				$conn->execute($sql);
				setcookie('slrememberme', gzcompress(serialize(array($_SESSION[USERNAME], $key)), 9), time()+60*60*24*30);
		}
		
		function destroy_slrememberme($username) {
			if (strlen($username) > 0) {
				global $conn;
				$sql="update members set remember_me_time=NULL, remember_me_key=NULL WHERE username='".mysql_real_escape_string($username)."'";
				$conn->execute($sql);
			}
			setcookie ("slrememberme", "", time() - 3600);
		}
		
		function update_your_viewed ($a)	{
				global $conn;
				$query = "UPDATE members SET yourviewed  = yourviewed  + 1 WHERE USERID='".mysql_real_escape_string($a)."'";
				$executequery=$conn->execute($query);
		}
			
		function update_you_viewed($a)	{
			  global $conn;
			  $query = "UPDATE members SET youviewed = youviewed + 1 WHERE USERID='".mysql_real_escape_string($a)."'";
			  $executequery=$conn->execute($query);
		}

		function update_viewcount_profile($a)	{
			  global $conn;
			  $query = "UPDATE members SET profileviews = profileviews + 1 WHERE USERID='".mysql_real_escape_string($a)."'";
			  $executequery=$conn->execute($query);
		}

		function insert_get_member_profilepicture($var)	{
			  $results = $var['profilepicture'];
			  if ($results == "")	
				  return "noprofilepicture.gif";
			  else	
				  return $results;
		}
		
		function insert_get_member_profilepicture2($var)	{
			  global $conn;
			  $query="SELECT profilepicture FROM members WHERE USERID='".mysql_real_escape_string($var[USERID])."' limit 1";
			  $executequery=$conn->execute($query);
			  $results = $executequery->fields['profilepicture'];
			  if ($results == "")
				  return "noprofilepicture.gif";
			  else
				  return $results;
		}
		
		function insert_get_flname($a)		{
			  global $config,$conn;
			  $query = "select fname,lname from members WHERE USERID='".mysql_real_escape_string($_SESSION['USERID'])."' limit 1"; 
			  $results = $conn->execute($query);
			  $returnthis = $results->getrows();
			  return $returnthis;
		}

		function findProfilePicByEmail($mail) {
			$q = "SELECT profilepicture FROM members WHERE email = '".$mail."' limit 1";
			$res = mysql_query($q);
			while($row = mysql_fetch_array($res))	{
				$prof = 'http://www.dahliawolf.com/mpics/'.$row['profilepicture'];
			}
			return $prof; 
		}
		
		function findUsernameByEmail($theemail)	{
			$query=mysql_query("SELECT username FROM members WHERE email='".mysql_real_escape_string($theemail)."' limit 1");
			while($user=mysql_fetch_array($query)){
				$username = $user['username'];
			}
			return $username;
		}
	
		function delete_pic_delete_user($DPID)	{
				global $config,$conn;
				$queryd = "select PID, BID, REPID, pic from posts where PID='".mysql_real_escape_string($DPID)."'"; 
				$executequeryd = $conn->execute($queryd);
				$DPID = intval($executequeryd->fields['PID']);	
				$DBID = intval($executequeryd->fields['BID']);
				$REPID = intval($executequeryd->fields['REPID']);
				$pic = $executequeryd->fields['pic'];
				if($DPID > 0)	{
					$query = "DELETE FROM activity WHERE PID='".mysql_real_escape_string($DPID)."'";
					$conn->Execute($query);	
					if($DBID > 0)	{
						$query = "UPDATE boards SET pincount=pincount-1 WHERE pincount>'0' AND BID='".mysql_real_escape_string($DBID)."'";
						$conn->Execute($query);	
					}
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
						}
					}
				}
			}

		function delete_user($USERID)
			{
				global $config,$conn;
				if($USERID > 0)	{
					$query = "select profilepicture from members where USERID='".mysql_real_escape_string($USERID)."' limit 1"; 
					$executequery = $conn->execute($query);
					$delpp = $executequery->fields['profilepicture'];
					
					if($delpp != "")	{
						$del1=$config['mdir']."/".$delpp;
						if(file_exists($del1))
							unlink($del1);
						
						$del2=$config['mdir']."/thumbs/".$delpp;
						if(file_exists($del2))
							unlink($del2);
					
						$del3=$config['mdir']."/o/".$delpp;
						if(file_exists($del3))
							unlink($del3);
					}
					
					$query = "SELECT PID FROM posts WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$results = $conn->execute($query);
					$returnthis = $results->getrows();
					$vtotal = count($returnthis);
					for($i=0;$i<$vtotal;$i++)	{
						$DPID = intval($returnthis[$i]['PID']);
						if($DPID > 0)	{
							delete_pic_delete_user($DPID);
						}
					}
					
					$query = "DELETE FROM activity WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM boards WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM categories_subscribe WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM comments WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM followb WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM followm WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM followm WHERE ISFOL='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);		
					$query = "DELETE FROM members WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM members_passcode WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM members_verifycode WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM posts_fav WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
					$query = "DELETE FROM posts_reports WHERE USERID='".mysql_real_escape_string($USERID)."'";
					$conn->execute($query);
				}
			}
    }
?>