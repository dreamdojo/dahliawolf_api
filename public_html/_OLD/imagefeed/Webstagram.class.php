<?php
include_once('simple_html_dom.php');

class Webstagram{
	private $webstagramURL = "";
	private $localFileURL = "";
	private $pageHTML = "";
	private $user = "";
	private $pinboardImages = array();
	private $_links = array();
	private $_covers = array();
	private $_thumbs = array(); #An array of arrays.
	private $_search = array();

	/**
	* Get the users pinboard data from Pinterest & store in class variables.
	*/
	/*public function scrapeUser($user){
		$this->user = $user;
		$this->webstagramURL = "http://www.pinterest.com/" . $this->user . "/";
		$this->localFileURL = "" . $this->user . "Pinterest.html";
		$html = $this->loadHTML();
	}
	*/
	
	/**
	* Get the searching results from Pinterest & store in class variables.
	*/
	public function scrapeKeywords($keywords, $page = 0){
		//$this->user = $user;
		//http://search.stagram.com/search?q=fashion&s=12 PAGE 1
		//http://search.stagram.com/search?q=fashion&s=24 PAGE 2
		$this->webstagramURL = "http://search.stagram.com/search?q=" . $keywords ."&s=".$page;
		$this->localFileURL = "" . $keywords . "-".$page."-Instagram.html";
		$html = $this->loadHTML();
	}

	/**
	* Pulls the user's data from Pinterest if there is no locally stored copy available or if the local file is over 1hr old.
	*/
	private function loadHTML(){		
		date_default_timezone_set('UTC');

		if(!file_exists($this->localFileURL)){
			$this->updateWebstagramFile();
		}
		else if (strtotime("-1 hour") >= filemtime($this->localFileURL)) {
			//If the file is over an hour old, pull a new copy from Pinterest
	        		$this->updateWebstagramFile();
		}else{
			//Else, load content from locally cached file.
			$this->pageHTML = file_get_contents($this->localFileURL);
		}

		$this->parseHTML();
	}

	private function updateWebstagramFile(){
		$fh = fopen($this->localFileURL, 'w') or die("Could not open local HTML file.");	
  		$this->pageHTML = file_get_contents($this->webstagramURL);
  		fwrite($fh, $this->pageHTML);
	}

	private function parseHTML(){
		$html = new simple_html_dom();
		$html->load($this->pageHTML);

		# retrieve all of the pinboards
		$pinBoards = $html->find(".photo_each");

		foreach($pinBoards as $board) {
			#Loads the cover shots
			/*foreach ($board->find("h3 a") as $link ) {
		    		$this->_links[] = "http://www.webstagram.com" . $link->href;
		    	}

		    	#Loads the cover shots
			foreach ($board->find(".cover img") as $cover ) {
		    		$this->_covers[] = array("src" => $cover->src
							,"alt" => $cover->alt);
		    	}

			#Loads the thumbnails
			$tempThumbs = array();
			foreach ($board->find(".thumbs img") as $thumbs) {
		    		$tempThumbs[] = $thumbs->src;
		    	}
		    	$this->_thumbs[] = $tempThumbs;
			*/
			
			#Loads the search picture
			foreach ($board->find(".modal_open_x img") as $search ) {
					$this->_search[] = array("src" => $search->src
							,"alt" => $search->alt);

		    	}
			
		}
	}


	public function getCovers(){
		return $this->_covers;
	}

	public function getThumbs(){
		return $this->_thumbs;
	}

	public function getLinks(){
		return $this->_links;
	}
	public function getSearch(){
		return $this->_search;
	}
}
?>