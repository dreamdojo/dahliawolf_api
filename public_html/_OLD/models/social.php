<?php
include_once 'includes/database.php';

class Social
	{
		
		public function get_facebook_cookie($app_id, $application_secret)	{
			$args = array();
			parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
			ksort($args);
			$payload = '';
			
			foreach ($args as $key => $value) {
			  if ($key != 'sig') {
				$payload .= $key . '=' . $value;
			  }
			}
			
			if (md5($payload . $application_secret) != $args['sig']) {
			  return null;
			}
			
			return $args;
		}
		
		public function publishToFacebook($app_id, $app_secret, $fb_id, $scriptolution_msg, $scriptolution_link, $scriptolution_picture, $scriptolution_caption) 
			{
				global $config, $conn;
				if($config['enable_fc'] == "1")
					{
						if($_SESSION['FB'] == "1")
							{
								$USERID = intval($_SESSION['USERID']);
								if($USERID > 0)
									{
										$queryd = "select post_fb from members where USERID='".mysql_real_escape_string($USERID)."'"; 
										$executequeryd = $conn->execute($queryd);
										$post_fb = intval($executequeryd->fields['post_fb']);
										if($post_fb == "1")
											{	
												require_once $config['basedir'].'/src/facebook.php'; 
												$facebook = new Facebook(array(
														appId 	=> $app_id,
														secret 	=> $app_secret,
														cookie 	=> true));
												if(is_null($facebook)) {}
												else 
													{
														try 
															{
																$code 			= $_SESSION['facebook_code'];
																$access_token 	= $_SESSION['facebook_access_token'];
						
																$post_id = $facebook->api('/' . $fb_id . '/feed/', 'post', array(
																'access_token' 	=> $_SESSION['facebook_access_token'],
																'message' 		=> $scriptolution_msg,
																'link' 			=> $scriptolution_link,
																'picture'  		=> $scriptolution_picture,
																'caption' 		=> $scriptolution_caption
																));
															   return $post_id;
															   
															   //mail("solomonacosta@ymail.com", "Success", "Facebook Post Succeeded");
															}
														catch (FacebookApiException $e) 
															{
																//mail("solomonacosta@ymail.com", "Failure", "$e - ".$code." - ". $access_token ." - $app_id, $app_secret, $fb_id, $scriptolution_msg, $scriptolution_link, $scriptolution_picture, $scriptolution_caption");
															}
													}
											}
									}
							}
					}
			}
		
		function facebookVerifyAccessToken($app_id, $app_secret, $code)
			{
				$app_id = $app_id;
				$app_secret = $app_secret; 
				$my_url = $config['baseurl']."/";
				   
				// known valid access token stored in a database 
				$access_token = $_SESSION['facebook_access_token'];
			  
				//$code = $_REQUEST["code"];
				  
				// If we get a code, it means that we have re-authed the user 
				//and can get a valid access_token. 
				if (isset($code)) {
				  $token_url="https://graph.facebook.com/oauth/access_token?client_id="
					. $app_id . "&redirect_uri=" . urlencode($my_url) 
					. "&client_secret=" . $app_secret 
					. "&code=" . $code . "&display=popup";
				  $response = file_get_contents($token_url);
				  $params = null;
				  parse_str($response, $params);
				  $access_token = $params['access_token'];
				}
			  
					  
				// Attempt to query the graph:
				$graph_url = "https://graph.facebook.com/me?"
				  . "access_token=" . $access_token;
				$response = curl_get_file_contents($graph_url);
				$decoded_response = json_decode($response);
				  
				//Check for errors 
				if ($decoded_response->error) {
				// check to see if this is an oAuth error:
				  if ($decoded_response->error->type== "OAuthException") {
					// Retrieving a valid access token. 
					$dialog_url= "https://www.facebook.com/dialog/oauth?"
					  . "client_id=" . $app_id 
					  . "&redirect_uri=" . urlencode($my_url);
					echo("<script> top.location.href='" . $dialog_url 
					. "'</script>");
				  }
				  else {
					echo "other error has happened";
				  }
				} 
				else {
				// success
				  echo("success" . $decoded_response->name);
				   return $access_token;
				}
			}
    
    }
?>