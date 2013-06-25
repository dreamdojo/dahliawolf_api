<?php
include_once 'includes/database.php';

class Utility
	{
		function escape($data)		{
			if (ini_get('magic_quotes_gpc'))	$data = stripslashes($data);
			return mysql_real_escape_string($data);
		}
		
		function verify_valid_email($emailtocheck)
			{	
				if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $emailtocheck))	return false;
				else	return true;
			}
		
		function cleanit($text)
			{
				return htmlentities(strip_tags(stripslashes($text)), ENT_COMPAT, "UTF-8");
			}

		function getimg($url)	{
				$doc = new DOMDocument();
				@$doc->loadHTML($url);
				$tags = $doc->getElementsByTagName('img');
				foreach ($tags as $tag) {
				   return $tag->getAttribute('src');
				}
		}
		
		function non_repeat($min, $max, $count)	{
				if ($max - $min < $count) { 
					return false; 
				} 
				$arr = range($min, $max);
				shuffle($arr);
				$nonrepeatarray = array_slice($arr, 0, $count);
				return $nonrepeatarray;
		}

		function strip_mq_gpc($arg)	{
			  if (get_magic_quotes_gpc())	{
				$arg = str_replace('"',"'",$arg);
				$arg = stripslashes($arg);
				return $arg;
			  }
			  else	{
				$arg = str_replace('"',"'",$arg);
				return $arg;
			  }
		}

		function generateCode($length)	{
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
			$code = "";
			$clen = strlen($chars) - 1;
			while (strlen($code) < $length) {
				$code .= $chars[mt_rand(0,$clen)];
			}
			return $code;
		}
		
		function getCurrentPageUrl()	{
			 static $pageURL = '';
			 if(empty($pageURL))	{
				  $pageURL = 'http';
				  if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')$pageURL .= 's';
				  $pageURL .= '://';
				  if($_SERVER['SERVER_PORT'] != '80')$pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
				  else $pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			 }
			 return $pageURL;
		}

		function download_photo($url, $saveto)	{
			global $config;
			if (!curlSaveToFile($url, $saveto))	{
				if (!secondarysave($url, $saveto))	{
					return false;
				}
				return true;
			}
			return true;
		}

		function download_photo_new($url, $saveto, $sourceurl)	{
			global $config;
			include("functions/curl.php");
			$curl = &new Curl_HTTP_Client();
			$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13";
			$curl->set_user_agent($useragent);
			$curl->set_referrer($url);
			$cookies_file = $config['basedir']."/temporary/cookies.txt";
			$curl->store_cookies($cookies_file);
			$html = $curl->fetch_url($url);
			if(file_exists($saveto))	{
				unlink($saveto);
			}
			$fh = fopen($saveto, 'x');
			fwrite($fh, $html);
			fclose($fh);
			if(file_exists($saveto))	{
				return true;
			}
			else	{
				return false;
			}
		}

		function secondarysave( $url, $local )	{
			$ch = curl_init($url);
			$fp = fopen($local, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
		
			if( filesize($local) > 10 ) {
				return true;
			}
		
			return false;
		}

    	function curlSaveToFile( $url, $local )	{
			$ch = curl_init();
			$fh = fopen($local, 'w');
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FILE, $fh);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_VERBOSE, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_NOPROGRESS, true);
			curl_setopt($ch, CURLOPT_USERAGENT, '"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.11) Gecko/20071204 Ubuntu/7.10 (gutsy) Firefox/2.0.0.11');
			curl_exec($ch);
		
			if( curl_errno($ch) ) {
				return false;
			}
		
			curl_close($ch);
			fclose($fh);
		
			if( filesize($local) > 10 ) {
				return true;
			}
		
			return false;
		}
		
		// note this wrapper function exists in order to circumvent PHP's 
		//strict obeying of HTTP error codes.  In this case, Facebook 
		//returns error code 400 which PHP obeys and wipes out 
		//the response.
		function curl_get_file_contents($URL) {
		  $c = curl_init();
		  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($c, CURLOPT_URL, $URL);
		  $contents = curl_exec($c);
		  $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
		  curl_close($c);
		  if ($contents) return $contents;
		  else return FALSE;
		}
	
		function insert_get_time_to_days_ago($a)
			{
				global $lang;
				$currenttime = time();
				$timediff = $currenttime - $a[time];
				$oneday = 60 * 60 * 24;
				$dayspassed = floor($timediff/$oneday);
				if ($dayspassed == "0")
				{
					$mins = floor($timediff/60);
					if($mins == "0")
					{
						$secs = floor($timediff);
						if($secs == "1")
						{
							return $lang['112'];
						}
						else
						{
							return $secs." ".$lang['113'];
						}
					}
					elseif($mins == "1")
					{
						return $lang['114'];
					}
					elseif($mins < "60")
					{
						return $mins." ".$lang['115'];
					}
					elseif($mins == "60")
					{
						return $lang['116'];
					}
					else
					{
						$hours = floor($mins/60);
						return "$hours ".$lang['117'];
					}
				}
				else
				{
					if ($dayspassed > "30")
					{
						return date("F j, Y",$a[time]);
					}
					else
					{
						if($dayspassed == "1")
						{
							return "$dayspassed ".$lang['119'];
						}
						else
						{
							return "$dayspassed ".$lang['118'];
						}
					}
				}
			}
		
		function insert_get_google_url($a)
			{
				global $conn, $config;
				$skey = stripslashes($a['key']);
				$sshort = stripslashes($a['short']);
				$gee_url = $config['baseurl']."/pin/".$skey;
				if($skey != "")
				{
					if($sshort == "")
					{
						$takenurl =  @file_get_contents("http://www.taken.to/geeurl.php?url=".$gee_url);
						if($takenurl != "")
						{
							$sshort = str_replace("http://www.taken.to/", "", $takenurl);
							if($sshort != "")
							{
								$query = "UPDATE posts SET short='".mysql_real_escape_string($sshort)."' WHERE pkey='".mysql_real_escape_string($skey)."'";
								$conn->execute($query);
								$rme = 	"http://www.taken.to/".$sshort;
							}
							else
							{
								$rme = 	$gee_url;	
							}
						}
						else
						{
							$rme = 	$gee_url;
						}
						
					}
					else
					{
						$rme = 	"http://www.taken.to/".$sshort;
					}
				}
				else
				{
					$rme = 	$gee_url;
				}
				return $rme;
			}
		
		function follow_redirect($url)
			{
			   $redirect_url = null;
			   if(function_exists("curl_init"))
			   {
				  $ch = curl_init($url);
				  curl_setopt($ch, CURLOPT_HEADER, true);
				  curl_setopt($ch, CURLOPT_NOBODY, true);
				  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				  $response = curl_exec($ch);
				  curl_close($ch);
			   }
			   else
			   {
				  $url_parts = parse_url($url);
				  $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80));
				  $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n";
				  $request .= 'Host: ' . $url_parts['host'] . "\r\n";
				  $request .= "Connection: Close\r\n\r\n";
				  fwrite($sock, $request);
				  $response = fread($sock, 2048);
				  fclose($sock);
			   }
			   $header = "Location: ";
			   $pos = strpos($response, $header);
			   if($pos === false)
			   {
				  return false;
			   }
			   else
			   {
				  $pos += strlen($header);
				  $redirect_url = substr($response, $pos, strpos($response, "\r\n", $pos)-$pos);
				  return $redirect_url;
			   }
			}
    }
?>