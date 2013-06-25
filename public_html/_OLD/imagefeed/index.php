<?php include_once "db.php"; ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<style>
.pinterest{
	postion:relative;
	float : left;
}
</style>


<?php

	if($_REQUEST['type'] == "") $_REQUEST['type'] = "pinterest";
	
	if(isset($_REQUEST['search-text']))
		{	
			$search_text = $_REQUEST['search-text'];
			$keywords = str_replace(' ','+',$search_text);
			
			if($_GET['type'] == "pinterest")
				{
					include_once('Pinterest.class.php');
					$pinterest = new Pinterest();

					$pinterest->scrapeKeywords($keywords);
					$pinterestItems = $pinterest->getSearch();
				}
			else if($_GET['type'] == "webstagram")
				{
					include_once('Webstagram.class.php');
					$webstagram = new Webstagram();
					$webstagram->scrapeKeywords($keywords, $_REQUEST['s']);
					$pinterestItems = $webstagram->getSearch();
				}
			else { echo "Set Scrape Type"; }
			
		}
	else
		{
			//$pinterest->scrapeUser("username");
			//$pinterestItems = $pinterest->getCovers();
		}

	//$pinterestCovers = $pinterest->getCovers();
	//$pinterestThumbs = $pinterest->getThumbs();
	//$pinterestLinks = $pinterest->getLinks();
	//$pinterestSearch = $pinterest->getSearch();

$perPage = 12;
if($_REQUEST['s'] == "") $_REQUEST['s'] = 0;
$webstagramNext = $_REQUEST['s'] + $perPage;
$webstagramPrev = $_REQUEST['s'] - $perPage;	
?>
<h3>Image Search</h3>
<form>
<input type="radio" name="type" value="webstagram" <?php if($_REQUEST['type'] == "webstagram") echo "checked"; ?> /> Webstagram 
<input type="radio" name="type" value="pinterest" <?php if($_REQUEST['type'] == "pinterest") echo "checked"; ?> /> Pinterest 
<input type="text" name="search-text" value="<?=$search_text?>" placeholder="Enter Keywords">
<input type="submit" id="searchbn" value="Search"><br>
<input type="button" id="savebn" value="Save To DH Repository">
[<a href="retrieveImage.php">View Repository</a>]<br />



<?php if($_REQUEST['type'] == "webstagram") { ?>
<?php if($_REQUEST['s'] >= $perPage) { ?>
[<a href="?type=<?= $_REQUEST['type'] ?>&s=<?= $webstagramPrev ?>&search-text=<?= $_REQUEST['search-text'] ?>">Previous</a>] 
<?php } ?>
[<a href="?type=<?= $_REQUEST['type'] ?>&s=<?= $webstagramNext ?>&search-text=<?= $_REQUEST['search-text'] ?>">Next</a>]
<?php } ?>
<br>

<script>
	$(function () {
    $('.checkall').click(function () {
        $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
    });
});

	var pictures = new Array()
	var i=0;
	$("#savebn").click(function(){
		$(".pinterest").each(function(){
			if(!$(this).find('input').is(':checked'))
			{
				pictures[i] = new Array();
				$element = $(this).find('img');
				pictures[i]={'src':$element.attr('src')
						,'alt':$element.attr('alt')
					    ,'dimensionsX':$element.attr('dimensionsX')
					    ,'dimensionsy':$element.attr('dimensionsy')
						,'keywords':$element.attr('keywords')
				};
				i++;
			}	
		});
	$.ajax({
  	url: "http://api.dahliawolf.com/imagefeed/saveImage.php",
  	data: {'picture':pictures},
  	type: 'post',
  		success: function(data) {
    		console.log(data);
  		}
	});
	alert("Images saved to repository");
	location.reload();
	//$(".results").hide().fadeIn('fast');
	//console.log(pictures);
	//console.log(pictures.length);
	i=0;
	pictures.length=0;
	});
	

</script>

<div id="results">
<fieldset>
<input type="checkbox" class="checkall"> Check all
<br>
<?php
	if(is_array($pinterestItems)) {
	foreach ($pinterestItems as $pinterestItem)
		{
			$doesExist = doesExist($pinterestItem['src']);
			if($doesExist == 0) {
			if ($size = @getimagesize($pinterestItem['src'])) { ?>
			<div class="pinterest">
			<img src="<?= $pinterestItem['src'] ?>" alt="<?=$pinterestItem['alt']?>" dimensionsX="<?=$size[0]?>" dimensionsY="<?=$size[1]?>" keywords="<?=$keywords?>">
			
			<br>
			<div><input type="checkbox"> discard it</div>
	
			</div>
		<?
			} else {
				//NOT OK
			}
		}
		}
		}
function doesExist($imageName)
	{
		$query = mysql_query("SELECT id FROM imageInfo WHERE baseurl='".$imageName."'");
		$result = mysql_fetch_array($query);
		
		if($result['id'] > 0) return 1;
		else return 0;
	}
?>
</fieldset>
</div>
</form>