<?php
session_start();

//include_once 'includes/config.php';
include_once 'includes/function.php';

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

if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'arcade') {
	
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
}
?>