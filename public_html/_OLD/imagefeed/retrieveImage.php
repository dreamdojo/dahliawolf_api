<?php
include_once "db.php";

if($_REQUEST['limit'] > 0) $limit = $_REQUEST['limit'];
if($_REQUEST['posted'] > 0) 
	{
		if($_REQUEST['posted'] == 1) $_REQUEST['posted'] = 1;
		else if($_REQUEST['posted'] == 2) $_REQUEST['posted'] = 0;
		$posted = "AND posted=". $_REQUEST['posted'];
	}
if($_REQUEST['type'] != "") 
	{
		if($_REQUEST['type'] == "webstagram") $typesql = " AND baseurl LIKE \"%distilleryimage%\" ";
		if($_REQUEST['type'] == "pinterest") $typesql = "AND baseurl LIKE \"%pinterest.com%\" ";
	}
$queryStatment = "SELECT * FROM imageInfo WHERE id > 0 ".$posted." ".$typesql." ORDER BY id DESC ".$limit;
$results = mysql_query($queryStatment) or die(mysql_error());
$pinterestItems = array();


while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
	
    array_push($pinterestItems, array(
        'src' => $row['imageURL'],
        'alt' => $row['imagename'],
		'keywords' => $row['keyword']
        )
    );
}

mysql_close($con);
?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<style>
.pinterest{
	postion:relative;
	float : left;
}

</style>

<h3>Repository</h3>
<form>
<!--<input type="text" name="search-text" id="search-text" value="<?=$search_text?>">
<input type="button" id="searchbn" value="Search"><br>
-->
<input type="button" id="savebn" value="Delete Selected">
[<a href="index.php">New Search</a>]
<br>
[<a href="">All</a>]
[<a href="?type=pinterest">All Pinterest</a>]
[<a href="?type=webstagram">All Webstagram</a>]
<br />
<b>PINTEREST</b> :: 
[<a href="?posted=1&type=pinterest">Posted</a>]
[<a href="?posted=2&type=pinterest">Not Posted</a>]
<br />
<b>WEBSTAGRAM</b> :: 
[<a href="?posted=1&type=webstagram">Posted</a>]
[<a href="?posted=2&type=webstagram">Not Posted</a>]

<script>
	var pictures = new Array()
	var i=0;
	$("#searchbn").click(function(){
		var searchString = $("#search-text").attr('value');
		console.log(searchString);
		$(".pinterest").each(function(){
			if($(this).find('img').attr('alt').indexOf(searchString) == -1)//Delete What were chosen;
			{
				$(this).hide();
			}
			else
				$(this).show();
		});
	});

	$("#savebn").click(function(){
		$(".pinterest").each(function(){
			if($(this).find('input').is(':checked'))//Delete What were chosen;
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
				
				$(this).hide();
			}	
		});
	$.ajax({
  	url: "http://api.dahliawolf.com/imagefeed/deleteImage.php",
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
	
	
	if(is_array($pinterestItems)) {
        
	foreach ($pinterestItems as $pinterestItem)
		{
			$imageName = "upload/".$pinterestItem['src'];
			if ($size = @getimagesize($imageName)) { 
			?><div class="pinterest">
			<img src="<?= $imageName ?>" alt="<?= $pinterestItem['alt'] ?>" dimensionsX="<?= $size[0] ?>" dimensionsY="<?= $size[1] ?>" keywords="<?= $keywords ?>">
			
			<br>
			<input type="checkbox">discard it
	
			</div>
		<?php
		}
		}
	}
?>

</div>

</form>

