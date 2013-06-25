<?php
include_once 'includes/database.php';

class Arcade
	{
		/* PULL FROM JSON FILE */
		public function update()
			{
				$content = $this->getGames();
				global $config;
				$filename = $config['JsonFile']['arcade'];
				file_put_contents($filename, $content);
				var_dump($filename);
			}
		
		public function display()
			{
				global $config;
				$filename = $config['JsonFile']['arcade'];
				$content = file_get_contents($filename);
				echo $content;
			}
			
		private function auditUserPoints($userID)
			{
				$db = new Database();
				/*
				SELECT person,
				SUM(IF(vote = "yes", 1,0)) AS `yes_votes`,
				SUM(IF(vote = "no", 1,0)) AS `no_votes`,
				COUNT(vote) AS `total`
				FROM votes
				GROUP BY person
				ORDER BY yes_votes DESC
				
				
				$sql = "SELECT count(points)
						FROM 
							`arcade_leaderboard` 
						WHERE 
							user_id = $userID";
				$result = $db->Select("arcade_leaderboard", $sql);
				*/
				return $result;
			}
		/* END PULL FROM JSON FILE */
			
		public function getGames($nameFilter = "", $gameDomain = "", $orderBy = "", $orderOrder = "ASC", $startLimit = 0, $endLimit = 50, $active = 2)
			{
				$db = new Database();
				
				$addSQL = " id > 0"; 
				if($nameFilter != "") $addSQL .= " AND game_name LIKE \"%$nameFilter%\"";
				if($gameDomain != "") $addSQL .= " AND game_domain LIKE \"%$gameDomain%\"";
				if($active == 0) 	  $addSQL .= " AND active = 0 ";
				elseif($active == 1)  $addSQL .= " AND active = 1 ";
					
				if($orderBy == "") $orderBy = "date_created";
				
				if( ($startLimit == "") || ($startLimit == 0) ) $startLimit = 0; 
				if( ($endLimit == "") || ($endLimit == 0) ) $endLimit = 50; 
								
				$sql = "SELECT 
							id,
							game_name,
							game_thumb,
							game_domain,
							game_link,
							the_height,
							the_width,
							keyword,
							category,
							active,
							times_played,
							date_created
						FROM 
							`arcade_games` 
						WHERE 
							$addSQL
						ORDER BY
							$orderBy $orderOrder
						LIMIT $startLimit, $endLimit
						";
				$result = $db->Select("arcade_games", $sql);
				
				return $result;
			}
		
		public function getGameDetails($gameID, $increment = 1, $userID = "", $ip = "")
			{
				$db = new Database();
				
				//CHECK TO SEE IF THE GAME ID EXISTS
				//temporary - use PDO
				$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
				mysql_select_db("dahlia_db", $con);
				
				$getIDR = mysql_query("SELECT id FROM `arcade_games` WHERE id=$gameID");
				$getID 	= @mysql_fetch_array($getIDR);
				
				if($getID['id'] > 0) {
				
					if($increment == 1) $this->incrementTimesPlayed($gameID);
					
					$sql = "SELECT 
								id,
								game_name,
								game_thumb,
								game_domain,
								game_link,
								the_height,
								the_width,
								keyword,
								category,
								active,
								times_played,
								date_created
							FROM 
								`arcade_games` 
							WHERE 
								id = $gameID";
							
					$result = $db->Select("arcade_games", $sql);
					
					///////////////////////////// IF THE USER IS LOGGED IN THEN
					if( ($userID != "") && ($userID != "null") ) 	{
						$sql = "INSERT INTO `arcade_leaderboard` 
						SET user_id 		= \"".$userID."\",
							game_id 		= \"".$gameID."\",
							points_earned 	= \"0\",
							minutes_played 	= \"0\",
							ip_address 		= \"".$ip."\",
							date_created 	= NOW()
						";
					
						//$result = $db->Insert("arcade_leaderboard", $sql);
						mysql_query($sql);
						$sess_id 	= mysql_insert_id();
						
						$result['game_session_id'] = $sess_id;
					}
					/////////////////////////////////////////////////////////
				} else {
					$result = array();
					$result['error_message'] = "No Game Found";
				}
				return $result;
			}
			
		
		############################################################
		public function addCategory($categoryName)
			{
				if($categoryName != "") {
					$sql = "INSERT INTO `arcade_categories` SET category_name 	= \"".$categoryName."\"";
					
					//temporary - use PDO
					$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
					mysql_select_db("dahlia_db", $con);
					mysql_query($sql);
					$category_id 	= mysql_insert_id();
					//end temp
					
					$resultArray = array('category_id' => $category_id);
					return $resultArray;
				}
				else {
					$resultArray = array('error_message' => "No category name set.");
					return $resultArray;
				}
			}
		
		public function updateCategory($categoryID, $categoryName)
			{
				if($categoryID > 0) {
					if($categoryName != "") {
						$sql = "UPDATE `arcade_categories` SET category_name = \"".$categoryName."\" WHERE id=$categoryID";
						
						//temporary - use PDO
						$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
						mysql_select_db("dahlia_db", $con);
						mysql_query($sql);
						//end temp
						
						$resultArray = array('message' => "Category Updated.");
						return $resultArray;
					} else {
						$resultArray = array('error_message' => "No Category Name set.");
						return $resultArray;
					}
				}
				else {
					$resultArray = array('error_message' => "No Category ID set.");
					return $resultArray;
				}
			}
		
		public function deleteCategory($categoryID)
			{
				if($categoryID > 0) {
					$sql = "DELETE FROM `arcade_categories` WHERE id=$categoryID";
					
					//temporary - use PDO
					$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
					mysql_select_db("dahlia_db", $con);
					mysql_query($sql);
					//end temp
					
					$resultArray = array('message' => "Category Deleted.");
					return $resultArray;
				}
				else {
					$resultArray = array('error_message' => "No Category ID set.");
					return $resultArray;
				}
			}
		
		public function getAllCategories()
			{
				$db = new Database();
				$sql = "SELECT * FROM `arcade_categories`";
				$result = $db->Select("arcade_categories", $sql);
					
				return $result;
			}
		
		public function getCategoryName($categoryID)
			{
				if($categoryID > 0) {
					$db = new Database();
					$sql = "SELECT * FROM `arcade_categories` WHERE id=$categoryID";
					$result = $db->Select("arcade_categories", $sql);
						
					return $result;
				}
			}
		
		public function getCategoryID($categoryName)
			{
				if($categoryName != "") {
					$db = new Database();
					$sql = "SELECT * FROM `arcade_categories` WHERE category_name=\"".trim($categoryName)."\"";
					$result = $db->Select("arcade_categories", $sql);
						
					return $result;
				}
			}
			
		############################################################	
		public function addGame($gameID = 0, $gameArray)
			{
				$db = new Database();
				
				if($gameArray['game_name'] != "") {
					$sql = "INSERT INTO `arcade_games` 
							SET game_name 	= \"".$gameArray['game_name']."\",
								game_thumb 	= \"".$gameArray['game_thumb']."\",
								game_domain = \"".$gameArray['game_domain']."\",
								game_link 	= \"".$gameArray['game_link']."\",
								the_height 	= \"".$gameArray['height']."\",
								the_width 	= \"".$gameArray['width']."\",
								keyword 	= \"".$gameArray['keyword']."\",
								category 	= \"".$gameArray['category']."\",
								active 		= \"".$gameArray['active']."\",
								times_played = 0,
								date_created = NOW()
							";
					//temporary - use PDO
					$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
					mysql_select_db("dahlia_db", $con);
					mysql_query($sql);
					$game_id 	= mysql_insert_id();
					//end temp
					$resultArray = array('game_id' => $game_id);
				} else {
					$resultArray = array('error_message' => "Game Name can't be empty");
				}
				
				return $resultArray;
			}
		
		public function editGame($gameID, $gameArray)
			{
				$db = new Database();
				
				//CHECK TO SEE IF THE GAME ID EXISTS
				$getIDR = mysql_query("SELECT id FROM `arcade_games` WHERE id=$gameID");
				$getID 	= @mysql_fetch_array($getIDR);
				
				if($getID['id'] > 0) {
					$sql = "UPDATE `arcade_games` 
							SET game_name 	= \"".$gameArray['game_name']."\",
								game_thumb 	= \"".$gameArray['game_thumb']."\",
								game_domain = \"".$gameArray['game_domain']."\",
								game_link 	= \"".$gameArray['game_link']."\",
								the_height 	= \"".$gameArray['height']."\",
								the_width 	= \"".$gameArray['width']."\",
								keyword 	= \"".$gameArray['keyword']."\",
								category 	= \"".$gameArray['category']."\",
								active 		= \"".$gameArray['active']."\"
							WHERE 
								id = $gameID";
					
					$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
					mysql_select_db("dahlia_db", $con);
					mysql_query($sql);
					//$result = $db->Update("arcade_games", $sql);
					$resultArray = array('message' => "Game $gameID Updated.");
				} else {
					$resultArray = array('error_message' => "Game Not Found.");
				}
				return $resultArray;
			}
		
		public function deleteGame($gameID)
			{
				$db = new Database();
				
				//CHECK TO SEE IF THE GAME ID EXISTS
				$getIDR = mysql_query("SELECT id FROM `arcade_games` WHERE id=$gameID");
				$getID 	= @mysql_fetch_array($getIDR);
				
				if($getID['id'] > 0) {
					$sql = "DELETE FROM `arcade_games` 
							WHERE id = $gameID";
					//$result = $db->Delete("arcade_games", $sql);
					
					$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
					mysql_select_db("dahlia_db", $con);
					mysql_query($sql);
					
					$resultArray = array('message' => "Game $gameID Deleted.");
				} else {
					$resultArray = array('error_message' => "Game Not Found.");
				}
				return $resultArray;
			}
		
		public function incrementTimesPlayed($gameID)
			{
				$db = new Database();
				
				$sql = "UPDATE `arcade_games` 
						SET times_played =  times_played+1	
						WHERE 
							id = $gameID";
				
				$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
				mysql_select_db("dahlia_db", $con);
				mysql_query($sql);
				//$result = $db->Update("arcade_games", $sql);
				$resultArray = array('message' => "Times Played Updated.");
				return $resultArray;
			}
			
		
		public function getLeaderboard($gameID = 0, $startLimit = 0, $endLimit = 10)
			{
				$db = new Database();
				
				//$addSQL = " user_id <> \"\" ";
				if($gameID > 0) $addSQL .= " game_id=$gameID";
				else $addSQL .= " game_id=0";
				
				$sql = "SELECT 
							user_id,
							total_points,
							minutes_played
						FROM 
							`arcade_user_points` 
						WHERE 
							$addSQL
						ORDER BY
							total_points DESC
						LIMIT $startLimit, $endLimit
						";
				
				$result = $db->Select("arcade_user_points", $sql);
				
				return $result;
			}
		
		
		public function getPlayerPoints($userID, $gameID = 0)
			{
				//CHECK TO SEE IF THE GAME ID EXISTS
				$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
				mysql_select_db("dahlia_db", $con);
				$getIDR = mysql_query("SELECT user_id FROM `arcade_user_points` WHERE user_id=\"$userID\"") or die(mysql_error());
				$getID 	= mysql_fetch_array($getIDR);
				$playerID = $getID['user_id'];
				
				if($playerID != "") {
					$db = new Database();
					
					$addSQL = " user_id = \"$userID\" ";
					if($gameID > 0) $addSQL .= " AND game_id=$gameID";
					else $addSQL .= " AND game_id=0";
					
					$sql = "SELECT 
								user_id,
								total_points,
								minutes_played
							FROM 
								`arcade_user_points` 
							WHERE 
								$addSQL
							";
					
					$result = $db->Select("arcade_user_points", $sql);
					
				} else {
					$result = array('error_message' => "Player Not Found.");
				}
				return $result;
			}
			
		public function postToLeaderboard($gameID, $dataArray, $userID)
			{
				//calculate points
				if($dataArray['game_session_id'] > 0) {
					//$db = new Database();
					//$result = $db->Insert("arcade_leaderboard", $sql);
					
					//temporary - use PDO
					$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
					mysql_select_db("dahlia_db", $con);
					mysql_query($sql);
					//end temp
					
					$sql = "UPDATE `arcade_leaderboard` 
							SET date_stopped 	= NOW()
							WHERE id=".$dataArray['game_session_id']."
							";
					mysql_query($sql);
								
					$sql = "SELECT UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date_created) FROM `arcade_leaderboard` WHERE id=".$dataArray['game_session_id']."";
					$res = mysql_query($sql);
					$que = mysql_fetch_array($res);
					//echo $que[0]." Seconds<br>";
					////////////////////////////////////////	
				
					$minutesPlayed = $que[0]/60;
					//echo $minutesPlayed." Minutes Played<br>";
					$hourlyRate = array(
						"1000",
						"950",
						"903",
						"857",
						"815",
						"774",
						"735",
						"698",
						"663",
						"630"
					);
					
					$div = (int) ($minutesPlayed/60);
					//$mod = $minutesPlayed%60;
					$number = explode('.',($minutesPlayed / 60));
					$mod = ".".$number[1];
					
					$i = 0;
					$pointsEarned = 0;
					if($div >= 1) {
						while($i <= $div) {
							if($i > 9) $pointsEarned = $pointsEarned + (1 * $hourlyRate[9]); 
							else $pointsEarned = $pointsEarned + (1 * $hourlyRate[$i]);
							$i++;
						}
					}
					$pointsEarned = $pointsEarned + ($mod * $hourlyRate[$i]);
					$pointsEarned = floor($pointsEarned);
					//echo $mod." Remainder<br>";
					//echo $pointsEarned." Points Earned<br>";
					////////////////////////
					
					$sql = "UPDATE `arcade_leaderboard` 
							SET
								points_earned 	= \"".$pointsEarned."\",
								minutes_played 	= \"".$minutesPlayed."\"
							WHERE id=".$dataArray['game_session_id']."
							";
					mysql_query($sql);
					
					########################################################
					//UPDATE TOTAL POINTS
					//CHECK TO SEE IF THE USER ID EXISTS
					$getIDR = mysql_query("SELECT user_id FROM `arcade_user_points` WHERE user_id=\"$userID\"");
					$getID 	= mysql_fetch_array($getIDR);
					
					$getIDR2 = mysql_query("SELECT user_id FROM `arcade_user_points` WHERE user_id=\"$userID\" AND game_id=$gameID");
					$getID2 	= mysql_fetch_array($getIDR2);
					//END
					
					if($getID['user_id'] == "") {
						mysql_query("INSERT INTO `arcade_user_points` SET user_id=\"$userID\"");
					}
					if($getID2['user_id'] == "") {
						mysql_query("INSERT INTO `arcade_user_points` SET user_id=\"$userID\", game_id=$gameID");
					}
						//UPDATE GLOBALLY
						$sql = "UPDATE `arcade_user_points` 
								SET 
									total_points 	=  total_points+".$pointsEarned.",
									minutes_played 	=  minutes_played+".$minutesPlayed."
								WHERE 
									user_id = \"$userID\" AND game_id=0";
						mysql_query($sql);
						
						//UPDATE PER GAME
						$sql = "UPDATE `arcade_user_points` 
								SET 
									total_points 	=  total_points+".$pointsEarned.",
									minutes_played 	=  minutes_played+".$minutesPlayed."
								WHERE 
									user_id = \"$userID\" AND game_id=$gameID";
						mysql_query($sql);
					///END UPDATE TOTAL POINTS
					mysql_close($con);
					
					//UPDATE ON MAGENTO shop_production -> rewards_points_index (2 columns (_usable & _active)
					//$DBTYPE = 'mysql';
					//$DBHOST = 'localhost';
					//$DBUSER = 'shop_production';
					//$DBPASSWORD = 'orMicntKNP';
					//$DBNAME = 'dahlia_pinmeweb2';
					
					//temporary - use PDO
					//$con = mysql_connect('localhost', $DBUSER, $DBPASSWORD);
					//mysql_select_db($DBNAME, $con);
					//mysql_query($sql);
					//end temp
					return $pointsEarned;
				}
			}
		
		
		
	}
?>
