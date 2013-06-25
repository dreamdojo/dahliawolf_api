<?php
session_start();

//include_once 'includes/config.php';
include_once 'includes/function.php';
$functionArray = array();

if (isset($_REQUEST['api'])) {
	
	if($_REQUEST['api'] == 'admin') {
		$functionArray = array(
			""
			);
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'arcade') {
		$functionArray = array(
			"getGames",
			"getGameDetails",
			"addGame",
			"editGame",
			"deleteGame",
			"getLeaderboard",
			"postToLeaderboard",
			"getPlayerPoints",
			"addCategory",
			"updateCategory",
			"deleteCategory",
			"getAllCategories",
			"getCategoryName",
			"getCategoryID"
		);
		
		include_once 'controllers/ArcadeController.php';
		$arcadeController = new ArcadeController();
		
		if (in_array($_REQUEST['function'], $functionArray)) {
			$sendArray = array();
			foreach($_REQUEST AS $key=>$val)
				{
					if( ($key != "api" ) && ($key != "function" ) && ($key != "game_id" ) ) $sendArray[$key] = $val;
					if($key == "game_id") 		$entityId = $val;
					if($key == "user_id") 		$userId = $val;
					if($key == "active") 		$active = $val;
					if($key == "increment") 	$increment = $val;
					if($key == "nameFilter") 	$nameFilter = $val;
					if($key == "gameDomain") 	$gameDomain = $val;
					if($key == "orderBy")	 	$orderBy = $val;
					if($key == "orderOrder") 	$orderOrder = $val;
					if($key == "startLimit") 	$startLimit = $val;
					if($key == "endLimit") 		$endLimit = $val;
					if($key == "category_name") $category_name = $val;
					if($key == "category_id") 	$category_id = $val;
					if($key == "ip") 			$ip = $val;
				}
			//print_r($sendArray);
			
			if($_REQUEST['function'] == "getLeaderboard")	$finalResult = $arcadeController -> $_REQUEST['function']($entityId, $startLimit, $endLimit);
			elseif($_REQUEST['function'] == "getGames")		$finalResult = $arcadeController -> $_REQUEST['function']($nameFilter, $gameDomain, $orderBy, $orderOrder, $startLimit, $endLimit, $active);
			elseif($_REQUEST['function'] == "getGameDetails")	$finalResult = $arcadeController -> $_REQUEST['function']($entityId, $increment, $userId, $ip);
			elseif($_REQUEST['function'] == "getPlayerPoints")	$finalResult = $arcadeController -> $_REQUEST['function']($userId, $entityId);
			elseif( ($_REQUEST['function'] == "addCategory") || ($_REQUEST['function'] == "updateCategory") || ($_REQUEST['function'] == "deleteCategory") || ($_REQUEST['function'] == "getAllCategories") || ($_REQUEST['function'] == "getCategoryName") || ($_REQUEST['function'] == "getCategoryID") )  $finalResult = $arcadeController -> $_REQUEST['function']($category_id, $category_name);
			else $finalResult = $arcadeController -> $_REQUEST['function']($entityId, $sendArray, $userId);
			
			echo $finalResult;
		} else {
			resultArray(FALSE, "Function doesn't exist!");
		}
	#######################################################################
	#######################################################################
	} else if($_REQUEST['api'] == 'comments') {
		$functionArray = array(
			"getComments",
			"getTotalComments"
		);
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'communication') {
		$functionArray = array(
			"sendMail"
		);
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'formatting') {
		$functionArray = array(
			"replaceSpaceWithDashes",
			"formatPrice",
			"getSourceDomain",
			"getHost",
			"seoCleanTitles"
		);	
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'geography') {
		$functionArray = array(
			"countryCodeToCountryName"
		);
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'image') {
		$functionArray = array(
			"resizeImage",
			"resizeImage2",
			"generateVideoThumbs",
			"deleteImage"
		);
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'magento') {
		
		$functionArray = array(
			"createUser",
			"login",
			"addPointsPerComment",
			"addPointsPerImageUpload",
			"addPointsPerImageShare",
			"showWolfBadge",
			"addOneTimePoints",
			"getUserAchievementLevel",
			"getUserPoints",
			"getRank",
			"getTopUsers",
			"sendEmailofPoints"
		);	
		
		if (in_array($_REQUEST['function'], $functionArray)) {
			include_once 'models/'.$_REQUEST['api'].'.php';
			$magento = new magento();
			
			if($_REQUEST['function'] == "createUser") {
				
				$finalResult = $magento -> $_REQUEST['function']($_REQUEST['theemail'], $_REQUEST['thepassword'], $_REQUEST['thefirstname'], $_REQUEST['thelastname']);
				
			} elseif($_REQUEST['function'] == "login") {
				
				$finalResult = $magento -> $_REQUEST['function']($_REQUEST['username'], $_REQUEST['validate']);
			
			} elseif( ($_REQUEST['function'] == "addPointsPerComment") || ($_REQUEST['function'] == "addPointsPerImageUpload") || ($_REQUEST['function'] == "addPointsPerImageShare") || ($_REQUEST['function'] == "showWolfBadge") || ($_REQUEST['function'] == "getUserAchievementLevel") || ($_REQUEST['function'] == "getUserPoints") ) {
				$finalResult = $magento -> $_REQUEST['function']($_REQUEST['username']);
			
			} elseif($_REQUEST['function'] == "addOneTimePoints") {
				
				$finalResult = $magento -> $_REQUEST['function']($_REQUEST['username'], $_REQUEST['custom_action_code'], $_REQUEST['points']);
			
			} elseif($_REQUEST['function'] == "getRank") {
				
				$finalResult = $magento -> $_REQUEST['function']($_REQUEST['blop']);
			
			} elseif($_REQUEST['function'] == "getTopUsers") {
				
				$finalResult = $magento -> $_REQUEST['function']($_REQUEST['howmany']);
			
			} elseif($_REQUEST['function'] == "sendEmailofPoints") {
				
				$finalResult = $magento -> $_REQUEST['function']($_REQUEST['theemail'], $_REQUEST['howmany']);
			}
			
			return $finalResult;
			
		} else resultArray(FALSE, "Function doesn't exist!");
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'miss') {
		$functionArray = array(
			""
			);
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'posts') {
		$functionArray = array(
			"isFavorite",
			"getFavoriteCount",
			"getNumberOfRePosts",
			"updatPostLastViewed",
			"addPostViewCount",
			"addCompPoints",
			"addLikePoints",
			"deductLikePoints",
			"addRepinPoints",
			"getPostPics",
			"getNumberOfPosts",
			"deletePost",
			"getSEOFriendlyTitle"
		);	
		
		if( ($_REQUEST['function'] == "isFavorite") || ($_REQUEST['function'] == "deletePost") ) {
				
			$finalResult = $magento -> $_REQUEST['function']($_REQUEST['postID'], $_REQUEST['userID']);
		
		} elseif( ($_REQUEST['function'] == "getPostPics") || ($_REQUEST['function'] == "getNumberOfPosts") )	{
			
			$finalResult = $magento -> $_REQUEST['function']();
		
		} else {
			
			$finalResult = $magento -> $_REQUEST['function']($_REQUEST['postID']);
		}
		
		return $finalResult;
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'social') {
		$functionArray = array(
			"get_facebook_cookie",
			"publishToFacebook",
			"facebookVerifyAccessToken");
	#######################################################################
	#######################################################################		
	} else if($_REQUEST['api'] == 'user') {
		$functionArray = array(
			"verify_email_username",
			"verify_email_unique",
			"create_slrememberme",
			);
	#######################################################################
	#######################################################################	
	} else if($_REQUEST['api'] == 'utility') {
		$functionArray = array(
			""
			);
	#######################################################################
	#######################################################################	
	}
	
} else {
	echo "No API Set";
}
?>