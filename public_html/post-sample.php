<?php
error_reporting(0);
require('includes/config.php');
require('models/database.php');
require('models/Posting.php'); // Added by Dev
$db = new Posting( "mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass );


$userId = '369';





$getAllPosts = $db->allPosts();
//echo '<pre>'; print_r($getAllPosts); echo '</pre>';
$rows = count($getAllPosts['data']);

$imgArr = array();
$imgRow = $db->get('image', ' ', '*');        
//echo '<pre>'; print_r($imgRow); 
foreach($imgRow as $key => $value){
    $imgid = $value['id'];
    $imgname = $value['imagename'];
    
    $imgArr[$imgid] = $imgname;
}     

?>
<a href="index.php">All Images</a> | <a href="post_image.php">Post Images</a> | 
<br><br><br>


    



<br><br><br>

<table border='0' width="60%" cellpadding="3">
    
<?php 
    foreach($getAllPosts['data'] as $key => $value){
        //echo '<pre>'; print_r($getAllPosts['data']);
        $posting_id = $value['posting_id'];
        $image_id = $value['image_id'];
        $total_likes = $value['total_likes'];
        $total_votes = $value['total_votes'];
                
       if (array_key_exists($image_id, $imgArr)) {
           $imagename = $imgArr[$image_id];
           // echo $imagename;
?>        
        
                   <tr>
                       <td align="center">
                            <a href="posting_info.php?postid=<?php echo $posting_id; ?>" target="_blank"><img src="uploads/<?php echo $imagename; ?>" height="50px" width="50px"></a>
                       </td>
                       <td align="center">
                            <?php echo $imagename; ?>
                       </td>
                   </tr>
        
        
  <?php    
       }
    }
    ?>      
        
    
</table>