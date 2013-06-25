<?php
include_once "db.php";

$picture 	= $_POST['picture'];
$count 		= count($picture);
$success 	= 0;
$fails 		= 0;

//$queryStatment = "INSERT INTO imageInfo (created,imagename,imageURL,dimensionsX,dimensionsY,keyword)
//VALUES ";

for($i=0;$i<$count;$i++)
	{
		//$picture[$i]['src'] = http://distilleryimage2.s3.amazonaws.com/c09dbb5c5a6e11e2ad6322000a9f14f2_7.jpg
		//$picture[$i]['src'] = http://media-cache0.pinterest.com/upload/26458716530301458_WBuxSRtX_b.jpg
		
		$imagename 	= explode("/", $picture[$i]['src']);
		$imagename 	= end($imagename);
		
		$isPinterest 	= preg_match("/pinterest.com/", $picture[$i]['src']);
		$isWebstagram 	= preg_match("/distilleryimage/", $picture[$i]['src']);
		if($isPinterest == true)  
			{
				$big_image_url  = preg_replace("/_b/","_c",$picture[$i]['src']);
				$big_image  = preg_replace("/_b/","_c",$imagename);
			}
		elseif($isWebstagram == true) 
			{
				$big_image_url  = preg_replace("/_6/","_7",$picture[$i]['src']);
				$big_image  = preg_replace("/_6/","_7",$imagename);
			}
		
		$image 		= file_get_contents($picture[$i]['src']);
		$image_big	= file_get_contents($big_image_url);
		
		file_put_contents("upload/".$imagename, $image);
		file_put_contents("upload/".$big_image, $image_big);

		$queryStatment = "INSERT INTO imageInfo (created,imagename,imageURL,bigImageURL,baseurl,dimensionsX,dimensionsY,keyword)
			VALUES ";
	
		$queryStatment .= " (CURDATE(),'".$picture[$i]["alt"]."', '".$imagename."', '".$big_image."', '{$picture[$i]['src']}','{$picture[$i]['dimensionsX']}','{$picture[$i]['dimensionsy']}','{$picture[$i]['keywords']}') ";
		/*if($i<($count-1)) 
		{
			$queryStatment.=",";
		}*/
		$result = mysql_query($queryStatment) or die(mysql_error());
		if (!$result) {
			$fails++;
		}
		else
			$success++;
	}

echo $success." rows are successful inserted and ".$fails ." rows are failure"; // outputs third employee's name

mysql_close($con);
?>