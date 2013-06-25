<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>

<?php
	include_once('Pinterest.class.php');
	$pinterest = new Pinterest();
	
	if(isset($_GET['search-text']))
		{	
			$search_text = $_GET['search-text'];
			$keywords = str_replace(' ','+',$search_text);
			$pinterest->scrapeKeywords($keywords);
			$pinterestItems = $pinterest->getSearch();
		}
	else
		{
			$pinterest->scrapeUser("username");
			$pinterestItems = $pinterest->getCovers();
		}

	//$pinterestCovers = $pinterest->getCovers();
	//$pinterestThumbs = $pinterest->getThumbs();
	//$pinterestLinks = $pinterest->getLinks();
	//$pinterestSearch = $pinterest->getSearch();
?>

<form>
<input type="text" name="search-text" value="<?=$search_text?>">
<input type="submit" id="searchbn" value="Search"><br>
<input type="button" id="savebn" value="save"><br>

<script>
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
	console.log(pictures);
	console.log(pictures.length);
	i=0;
	pictures.length=0;
	});
</script>

<div id="results">
<?php
	foreach ($pinterestItems as $pinterestItem)
		{
			?><div class="pinterest">
			<?($size = getimagesize($pinterestItem['src']))?>
			<img src="<?=$pinterestItem['src']?>" alt="<?= $pinterestItem['alt']?>" dimensionsX="<?=$size[0]?>" dimensionsY="<?=$size[1]?>" keywords="<?=$keywords?>">
			
			<br>
			<input type="checkbox"> discard it
	
			</div>
		<?
		}
?>
</div>
</form>

<?php
$link = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
echo 'Connected successfully';
mysql_close($link);
?>
