<?php
include_once 'models/arcade.php';

class ArcadeController
	{
		public function getGames($nameFilter = "", $gameDomain = "", $orderBy = "", $orderOrder = "DESC", $startLimit = 0, $endLimit = 50, $active = 2)
			{
				$arcade = new Arcade();
        		$games = $arcade->getGames($nameFilter, $gameDomain, $orderBy, $orderOrder, $startLimit, $endLimit, $active);
				$games = json_encode($games);
				
				return $games; 
            }
		
		public function getGameDetails($entityId, $increment = 1, $userID = "", $ip = "")
			{
				$arcade = new Arcade();
        		$games = $arcade->getGameDetails($entityId, $increment, $userID, $ip);
				$games = json_encode($games);
				
				return $games; 
            }
		
		public function addGame($entityId = 0, $dataArray)
			{
				$arcade = new Arcade();
        		$games = $arcade->addGame($entityId, $dataArray);
				
				$games = json_encode($games);
				return $games; 
            }
		
		public function editGame($entityId, $dataArray)
			{
				$arcade = new Arcade();
        		$games = $arcade->editGame($entityId, $dataArray);
				$games = json_encode($games);
				
				return $games; 
            }
		
		public function deleteGame($entityId)
			{
				$arcade = new Arcade();
        		$games = $arcade->deleteGame($entityId);
				$games = json_encode($games);
				
				return $games; 
            }
		
		public function getLeaderboard($gameID = 0, $startLimit = 0, $endLimit = 10)
			{
				$arcade = new Arcade();
        		$games = $arcade->getLeaderboard($gameID, $startLimit, $endLimit);
				$games = json_encode($games);
				
				return $games; 
            }
		
		public function postToLeaderboard($gameID, $dataArray, $userID)
			{
				$arcade = new Arcade();
        		$games = $arcade->postToLeaderboard($gameID, $dataArray, $userID);
				$games = json_encode($games);
				
				return $games; 
            }
		
		public function getPlayerPoints($userID, $gameID = 0)
			{
				$arcade = new Arcade();
        		$games = $arcade->getPlayerPoints($userID, $gameID);
				$games = json_encode($games);
				
				return $games; 
            }
		
		########################################################
		public function addCategory($categoryID = 0, $categoryName ="")
			{
				$arcade = new Arcade();
        		$games = $arcade->addCategory($categoryName);
				$games = json_encode($games);
				
				return $games;
			}
		
		public function updateCategory($categoryID = 0, $categoryName ="")
			{
				$arcade = new Arcade();
        		$games = $arcade->updateCategory($categoryID, $categoryName);
				$games = json_encode($games);
				
				return $games;
			}
		
		public function deleteCategory($categoryID = 0, $categoryName ="")
			{
				$arcade = new Arcade();
        		$games = $arcade->deleteCategory($categoryID);
				$games = json_encode($games);
				
				return $games;
			}
		
		public function getAllCategories($categoryID = 0, $categoryName ="")
			{
				$arcade = new Arcade();
        		$games = $arcade->getAllCategories();
				$games = json_encode($games);
				
				return $games;
			}
		
		public function getCategoryName($categoryID = 0, $categoryName ="")
			{
				$arcade = new Arcade();
        		$games = $arcade->getCategoryName($categoryID);
				$games = json_encode($games);
				
				return $games;
			}
		
		public function getCategoryID($categoryID = 0, $categoryName ="")
			{
				$arcade = new Arcade();
        		$games = $arcade->getCategoryID($categoryName);
				$games = json_encode($games);
				
				return $games;
			}
       
	}

?>