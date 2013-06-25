<?php
include_once 'includes/database.php';

class Miss
	{
    	function insert_is_folm($a)	{
			global $config,$conn;
			$USERID = $a['USERID'];
			$SID = intval(cleanit($_SESSION['USERID']));
			$query = "select count(*) as total from followm where USERID='".mysql_real_escape_string($SID)."' AND ISFOL='".mysql_real_escape_string($USERID)."'"; 
			$executequery = $conn->execute($query);
			$totalu = intval($executequery->fields['total']);
			return $totalu;
		}
			
		function insert_is_folb($a)	{
			global $config,$conn;
			$BID = $a['BID'];
			$SID = intval(cleanit($_SESSION['USERID']));
			$query = "select count(*) as total from followb where USERID='".mysql_real_escape_string($SID)."' AND ISFOLBID='".mysql_real_escape_string($BID)."'"; 
			$executequery = $conn->execute($query);
			$totalu = intval($executequery->fields['total']);
			return $totalu;
		}
			
		function insert_get_categories($a)	{
			global $config,$conn;
			$query = "select CATID,name,seo from categories order by name asc"; 
			$results = $conn->execute($query);
			$returnthis = $results->getrows();
			return $returnthis;
		}
			
		function insert_get_static($var)	{
			global $conn;
			$query="SELECT value FROM static WHERE ID='".mysql_real_escape_string($var['ID'])."'";
			$executequery=$conn->execute($query);
			$returnme = $executequery->fields['value'];
			return $returnme;
		}
    }
?>