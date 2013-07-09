<?php
error_reporting(0);
require('includes/config.php');
require('models/database.php');
require('models/Posting.php'); // Added by Dev
$db = new Posting( "mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass );


$userId = '369';
if(isset($_POST['submit'])){
    if (!empty($_FILES)) {
            $tempFile = $_FILES['postimage']['tmp_name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/postings/uploads/';
            if($_FILES['postimage']['error'] != 4){
                $_FILES['postimage']['name'] = time().$_FILES['postimage']['name'];
                $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['postimage']['name']);
                

                $fileTypes = array('jpg','jpeg','gif','png','JPG','JPEG','GIF','PNG'); // File extensions
                $fileParts = pathinfo($_FILES['postimage']['name']);
                
                $filename = $userId.'_'.time().'.'.$fileParts['extension'];
                $targetFile =  str_replace('//','/',$targetPath) . $filename;
                
                if (in_array($fileParts['extension'],$fileTypes)) {                    
                       move_uploaded_file($tempFile,$targetFile);
                       chmod($targetFile,0777);
                        //echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
echo '<br>'.$filename.' Uploaded successfully<br>';

$curDate = date('Y-m-d');

$imgParams = array();
$imgParams['created'] = $curDate;
$imgParams['imagename'] = $filename;


//$db = new Posting( "mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass );
$db->insert( 'image', $imgParams);
$image_id = $db->insert_id;

$curTime = date('Y-m-d H:i:s');
$postParams = array();
$postParams['created'] = $curTime;
$postParams['image_id'] = $image_id;


$params = array();
$params['data'] = $postParams;
$db->addPost( $params );




                } 
            }else{
	$filename = $hiddenimg;
            } 
}else{
	$filename = $hiddenimg;
            }
  

    
}
?>
<a href="index.php">All Images</a> | <a href="post_image.php">Post Images</a> | 
<br><br><br>
<form action="" method="post" enctype="multipart/form-data">
<label for="file">Post Image:</label>
<input type="file" name="postimage" id="postimageid"><br>
<input type="submit" name="submit" value="Submit">
</form>

