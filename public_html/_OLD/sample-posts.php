<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>DahliaWolf API v1.0 - Posts</title>
</head>

<body>
<?php 
//AVAILABLE FUNCTIONS
//isFavorite - $postID, $userID
//getFavoriteCount - $postID
//getNumberOfRePosts - $postID
//updatPostLastViewed - $postID
//addPostViewCount - $postID
//addCompPoints - $postID
//addLikePoints - $postID
//deductLikePoints - $postID
//addRepinPoints - $postID
//getPostPics
//getNumberOfPosts
//deletePost - $postID, $userID

$url 		= 'http://api.dahliawolf.com/api.php';
$api 		= "posts";

####################################################
####################################################

$fields = array(
            'api'  					=> $api,
			'function'  			=> $function,
			'postID' 				=> '',
			'userID'	 			=> ''
        );

///////////////////////////////////////////////////////////////////////////
###########################################################################
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');
//echo $url."?".$fields_string;
//echo "<br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if(curl_exec($ch) === false) echo 'Curl error: ' . curl_error($ch);

$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result);

//////////////////////////// FINAL OUTPUT /////////////////////////////////
###########################################################################
echo "<pre>";
print_r($res);
###########################################################################
////////////////////////// END FINAL OUTPUT ///////////////////////////////
?>
</body>
</html>