<?php
include_once 'includes/database.php';

class Comments
	{
    	function insert_get_comments($a)	{
			global $config,$conn;
			
			$query = "select A.username, A.fname, A.lname, A.profilepicture, B.COMID, B.comment from members A, comments B WHERE B.PID='".mysql_real_escape_string($a['PID'])."' AND A.USERID=B.USERID order by B.COMID desc limit 2"; 
			$results = $conn->execute($query);
			$returnthis = $results->getrows();
			
			return $returnthis;
		}
		
		function insert_get_total_comments($a)	{
			global $config,$conn;
			
			$query = "select count(*) as total from members A, comments B WHERE B.PID='".mysql_real_escape_string($a['PID'])."' AND A.USERID=B.USERID"; 
			$executequery=$conn->execute($query);
			$tt = $executequery->fields['total'];
			
			return $tt;
		}
    }
?>