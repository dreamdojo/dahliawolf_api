<?php
include_once 'includes/database.php';

class Seo
	{
		######## xml.php
		function xml_seotitle_from_videoid($a)	{
			global $config,$conn;
			$query="select title from posts where PID='".mysql_real_escape_string($a)."'";
			$executequery=$conn->execute($query);
			return seo_clean_titles($executequery->fields[title]);
		}
		##############################################
		
		function insert_seo_clean_titles($a)	{
			$title2 = explode(" ", $a['title']);
			$i = 0;
			foreach($title2 as $line)	{
				if($i < 15)	{
					$title .= $line."-";
					$i++;
				}
			}
			$title = str_replace(array(":", ".", "^", "*", ",", ";", "~", "[", "]", "<", ">", "\\", "/", "=", "+", "%"),"", $title);
			$last = substr($title, -1);
			if($last == "-")	{
				$title = substr($title, 0, -1);
			}
			$title = str_replace(" ", "-", $title);
			return $title;
		}
			
		function seo_clean_titles($a)	{
			$title2 = explode(" ", $a);
			$i = 0;
			foreach($title2 as $line)	{
				if($i < 15)	{
					$title .= $line."-";
					$i++;
				}
			}
			$title = str_replace(array(":", ".", "^", "*", ",", ";", "~", "[", "]", "<", ">", "\\", "/", "=", "+", "%"),"", $title);
			$last = substr($title, -1);
			if($last == "-")	{
				$title = substr($title, 0, -1);
			}
			$title = str_replace(" ", "-", $title);
			return $title;
		}

    }
?>