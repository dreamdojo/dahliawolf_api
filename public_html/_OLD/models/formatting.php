<?php
include_once 'includes/database.php';

class Formatting
	{
    	function insert_get_seo_bname($a)	{
			$bname = $a['bname'];
			$bname = str_replace(" ", "-", $bname);
			echo $bname;
		}
		
		function insert_return_seo_bname($a)	{
			$bname = $a['bname'];
			$bname = str_replace(" ", "-", $bname);
			return $bname;
		}
		
		function seo_bname($a)	{
			$bname = $a;
			$bname = str_replace(" ", "-", $bname);
			return $bname;
		}
		
		function get_price($text)	{
			$text = str_replace("  ", " ", $text);
			$words = explode(" ", $text);
			
			foreach ($words as $item)	{
				$item = str_replace(",", "", $item);
				$wfirst = substr($item, 0, 1);
				
				if($wfirst == "\$")	{
					$item2 = substr($item, 1);
					$theprice = number_format($item2, 2, '.', '');
				}
			}		
			return $theprice;
		}
		
		function insert_get_source_domain($a)	{
			  $dname = $a['dname'];
			  $dname = getHost($dname);
			  $dsub = substr($dname, 0, 4);
			  if(strtolower($dsub) == "www.")	{
				  $dname = substr($dname, 4);
			  }
			  return $dname;
		}
			
		function get_source_domain($dname)	{
			  $dname = getHost($dname);
			  $dsub = substr($dname, 0, 4);
			  if(strtolower($dsub) == "www.")	{
				  $dname = substr($dname, 4);
			  }
			  return $dname;
		}
	
		function getHost($Address) {
		   $parseUrl = parse_url(trim($Address));
		   return trim($parseUrl[host] ? $parseUrl[host] : array_shift(explode('/', $parseUrl[path], 2)));
		} 
    }
?>