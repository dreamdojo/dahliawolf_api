<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>DahliaWolf API v1.0</title>
</head>

<body>

<?php 
//AVAILABLE FUNCTIONS
//getGames
//getGameDetails
//addGame
//editGame
//deleteGame
//getLeaderboard
//postToLeaderboard
//getPlayerPoints

$url 		= 'http://api.dahliawolf.com/api.php';
$api 		= "arcade";

####################################################
####################################################
//GAME ADMIN
//$function 	= "getGames";
//$nameFilter = "";
//$gameDomain = "";
//$orderBy 	= "";
//$orderOrder = "DESC";
//$startLimit	= 0;
//$endLimit	= 50;
//$active		= 2; //0 = not active, 1 = active, 2 = all

//$function 	= "getGameDetails";
//$game_id	= 3;
//$increment  = 1;

//$function 	= "addGame";
//$active		= 0;
//$gameName	= "Test Game Name 7";
//$gameThumb  = "http://www.gamesgames.com/image.jpg";
//$gameDomain	= "www.gamesgames.com";
//$gameLink	= "http://www.gamesgames.com/gamelink.html5";

//$function 	= "editGame";
//$game_id	= 8;
//$active		= 1;
//$gameName	= "Test Game Name 8";
//$gameThumb  = "http://www.gamesgames.com/image.jpg";
//$gameDomain	= "www.gamesgames.com";
//$gameLink	= "http://www.gamesgames.com/gamelink.html5";

//$function 	= "deleteGame";
//$game_id	= 9;

/*
$fields = array(
            'api'  			=> $api,
			'function'  	=> $function,
			'game_id'  		=> $game_id,
			'increment'  	=> $increment,
			'active'  		=> $active,
			'nameFilter'	=> $nameFilter,
			'gameDomain'	=> $gameDomain,
			'orderBy'	 	=> $orderBy,
			'orderOrder'	=> $orderOrder,
			'startLimit'	=> $startLimit,
			'endLimit'	  	=> $endLimit,
			'game_name'  	=> $gameName,
			'game_thumb'  	=> $gameThumb,
			'game_domain' 	=> $gameDomain,
			'game_link'	 	=> $gameLink
        );
*/
####################################################



####################################################		
####################################################
//LEADERBOARD
//$function 	= "postToLeaderboard";
//$game_id	= 3;
//$user_id	= 10;
//$points_earned	= 100;
//$minutes_played	= 60;

//$function 	= "getLeaderboard";
//$game_id	= 3; //optional
//$startLimit	= 0;
//$endLimit	= 10;

$function 	= "getPlayerPoints";
//$game_id	= 3;  //optional
$user_id	= 10;

$fields = array(
            'api'  			 => $api,
			'function'  	 => $function,
			'game_id'  		 => $game_id,
			'user_id'	  	 => $user_id,
			'startLimit'	 => $startLimit,
			'endLimit'	  	 => $endLimit,
			'points_earned'  => $points_earned,
			'minutes_played' => $minutes_played,
			'ip_address'	 => $_SERVER['REMOTE_ADDR']
        );

####################################################


///////////////////////////////////////////////////////////////////////////
###########################################################################
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');
//echo $url."?".$fields_string;
//echo "<br><br>";

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if(curl_exec($ch) === false) echo 'Curl error: ' . curl_error($ch);

$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result);

//FINAL OUTPUT
echo "<pre>";
print_r($res);
?>
</body>
</html>