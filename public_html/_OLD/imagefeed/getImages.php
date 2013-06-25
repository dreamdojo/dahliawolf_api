<?php
include_once "db.php";

if( ($_REQUEST['start_limit'] > 0) && ($_REQUEST['end_limit'] > 0) ) $limit = " LIMIT ". $_REQUEST['start_limit'].",".$_REQUEST['end_limit'];
if($_REQUEST['posted'] > 0) 
	{
		if($_REQUEST['posted'] == 1) $_REQUEST['posted'] = 1;
		else if($_REQUEST['posted'] == 2) $_REQUEST['posted'] = 0;
		$posted = "AND posted=". $_REQUEST['posted'];
	}
if($_REQUEST['type'] != "") 
	{
		if($_REQUEST['type'] == "webstagram") $typesql = " AND baseurl LIKE \"%distilleryimage%\" ";
		elseif($_REQUEST['type'] == "pinterest") $typesql = "AND baseurl LIKE \"%pinterest.com%\" ";
		else $typesql = "";
	}
$queryStatment = "SELECT * FROM imageInfo WHERE id > 0 ".$posted." ".$typesql." ORDER BY rand() ".$limit;
$results = mysql_query($queryStatment) or die(mysql_error());
$pinterestItems = array();

while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
    $isPinterest 	= preg_match("/pinterest.com/", $row['baseurl']);
	$isWebstagram 	= preg_match("/distilleryimage/", $row['baseurl']);
	if($isPinterest == true)  $typeVal = "pinterest"; 
	elseif($isWebstagram == true) $typeVal = "webstagram"; 
	else $typeVal = "";
	
	array_push($pinterestItems, array(
        'src' 		=> "http://api.dahliawolf.com/imagefeed/upload/".$row['imageURL'],
		'big_src' 	=> "http://api.dahliawolf.com/imagefeed/upload/".$row['bigImageURL'],
        'alt' 		=> $row['imagename'],
		'keywords' 	=> $row['keyword'],
		'type' 		=> $typeVal
        )
    );
}

mysql_close($con);

//echo $pinterestItems;
//echo "<pre>";
//print_r($pinterestItems);
	//$size = getimagesize($pinterestItem['src'])
	//$pinterestItem['src']
	//$pinterestItem['alt']
	//$size[0]
	//$size[1]
	//$keywords

echo json_encode($pinterestItems);

?>
