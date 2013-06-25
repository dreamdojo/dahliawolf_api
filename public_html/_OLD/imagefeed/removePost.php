<?php
include_once "db.php";

$queryStatment = "UPDATE imageInfo SET posted=1 WHERE imageURL = '".$_REQUEST['imageName']."' OR bigImageURL = '".$_REQUEST['imageName']."'";
mysql_query($queryStatment);
mysql_close($con);
?>
