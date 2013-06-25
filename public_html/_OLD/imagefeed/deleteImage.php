<?php
include_once "db.php";

$picture 	= $_POST['picture'];
$count 		= count($picture);
$success 	= 0;
$fails 		= 0;

//$queryStatment = "INSERT INTO imageInfo (created,imagename,imageURL,dimensionsX,dimensionsY,keyword) VALUES ";

for($i=0;$i<$count;$i++)
	{
		$queryStatment = "DELETE FROM imageInfo WHERE imageURL=";
		$imagesrc = explode("/",$picture[$i]['src']);
		$imagesrc = end($imagesrc);
		$queryStatment .= "'{$imagesrc}'";
		var_dump($queryStatment);
		
		$result = mysql_query($queryStatment);
		if (!$result) {
			$fails++;
		}
		else
			$success++;
	}

echo $success." rows are successful inserted and ".$fails ." rows are failure"; // outputs third employee's name
mysql_close($con);
?>