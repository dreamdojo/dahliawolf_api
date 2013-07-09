<?php
error_reporting(0);
require('includes/config.php');
require('models/database.php');
require('models/Posting.php'); // Added by Dev
$db = new Posting( "mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass );


$userId = '369';

if(isset($_POST['submit'])){
    $hdnuserId = $_POST['hdnusrid'];
    $parentid = $_POST['parentid'];
    $postid = $_POST['postid'];
    $productid = $_POST['productid'];
    $namex = $_POST['namex'];
    $comments = $_POST['comments'];
    
    $cmnttable = 'comment';
    $cmntdata=array(); 
    
    $cmntdata['parent_id'] = $parentid;
    $cmntdata['user_id'] = $hdnuserId;
    $cmntdata['post_id'] = $postid;
    $cmntdata['product_id'] = $productid;
    $cmntdata['name'] = $namex;
    $cmntdata['description'] = $comments;
    
    $db->insert($cmnttable, $cmntdata);
}


$postid = $_GET['postid'];
$parameters = array(
                 'conditions' => array(
                                    'posting_id' => $postid
                                )
                    );


$getPostInfo = $db->getPostDetails($parameters);
$datarow = $postingRow['data'];

$rows = count($getPostInfo['data']);

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

<table border='0' width="100%" cellpadding="3">
    <tr>
<?php 
    foreach($getPostInfo['data'] as $key => $value){
        //echo '<pre>'; print_r($getPostInfo['data']);
        $posting_id = $value['posting_id'];
        $image_id = $value['image_id'];
        $total_likes = $value['total_likes'];
        $total_votes = $value['total_votes'];
                
       if (array_key_exists($image_id, $imgArr)) {
           $imagename = $imgArr[$image_id];
           // echo $imagename;
?>        
        <td>
               <table width="60%" border='1'>
                   <tr><td colspan="2" align="center">
                            <img src="uploads/<?php echo $imagename; ?>" height="220px" width="200px"><br><br>
                            <span onclick="imgstats('like','<?php echo $posting_id; ?>','<?php echo $image_id; ?>');" style="cursor:pointer; color:blue;">Like</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span onclick="imgstats('up','<?php echo $posting_id; ?>','<?php echo $image_id; ?>');" style="cursor:pointer; color:blue;">Vote Up</span>&nbsp;&nbsp;&nbsp;
                            <span onclick="imgstats('down','<?php echo $posting_id; ?>','<?php echo $image_id; ?>');" style="cursor:pointer; color:blue;">Vote Down</span><br><br>
                       </td></tr>
                   <tr><td width="50%"><div id="<?php echo $posting_id; ?>_totallikes"><?php echo $total_likes; ?> </div>Likes</td>  <td width="50%"><div id="<?php echo $posting_id; ?>_totalvotes"><?php echo $total_votes; ?>  </div>Votes</td></tr>
                   <tr><td>&nbsp;</td><td>
                           <span onclick="showcommentbox('0','<?php echo $posting_id; ?>','<?php echo $image_id; ?>');" style="cursor:pointer; color:blue;">Click to Comment</span>
                       </td></tr>
               </table>
        </td>
        
  <?php    
       }
    }
    ?>      
        
    </tr>
</table>
<br>
<div id="displaycomments" style="display: block; float: left; padding:20px;">
    <h2>Comments</h2>
<?php

$cmnttable = 'comment';
$where=array();
$where['post_id'] = $postid;
$where['parent_id'] = '0';


$commentsRow = $db->get_all( $cmnttable, $where, $fields='*' );
foreach($commentsRow as $key =>$row){
    //print_r($row);
    getThrdComments($row,$postid,$db); 
}


function getThrdComments($row,$postid,$db) { 
    global $db;
    $cmtId = $row['comment_id'];
    $prdId = $row['product_id'];
 echo "<li class='comment'>";   
 echo "<div class='aut'>Commented By ".$row['user_id']."</div>";
 echo "<div class='aut'>Title: ".$row['name']."</div>";  
 echo "<div class='comment-body'>Comment: ".$row['description']."</div>";  
 echo "<div class='timestamp'>Posted On:".$row['created']."</div>"; 
 echo '<span onclick="showcommentbox('.$cmtId.','.$postid.','.$prdId.');" style="cursor:pointer; color:blue;">Reply</span>';
 
 /*<!-- <span onclick="showcommentbox('<?php echo $cmtId; ?>','<?php echo $postid; ?>','<?php echo $prdId; ?>');">Replyxxx</span>-->
 */
 //print_r($row);
 $cmnttable = 'comment';
$where=array();
$where['post_id'] = $postid;
$where['parent_id'] = $cmtId;

 $commentsRow = $db->get_all( $cmnttable, $where, $fields='*' );
 
 echo "<ul>";  
 foreach($commentsRow as $key =>$row){    
    getThrdComments($row,$postid,$db); 
    //echo '<pre>'; print_r($commentsRow);echo '</pre>';
}
echo "</ul>"; 
echo "</li>"; 
 
}





?>
</div>    



<br><br>
<form name="commentsform" id="commentsform" action="" method="POST" >
<input type="hidden" name="hdnusrid" id="hdnusrid" value="<?php echo $userId; ?>" />
<div style="display: none; float: left;" id="respond">
    <input type="hidden" name="parentid" id="parentid" value="">
    <input type="hidden" name="postid" id="postid" value="">
    <input type="hidden" name="productid" id="productid" value="">
    
<table width="50%" border="1">
    <tr><td colspan="2"><h3>Leave a Comment</h3></td></tr>
    <tr><td valign="top">Name</td><td> <input type="text" name="namex" id="namex" value=""></td></tr>
    <tr><td valign="top">Comments</td><td><textarea name="comments" id="comments" rows="7" cols="30"></textarea></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" name="submit" id="submit" value="Submit"></td></tr>
</table>    
</div>
</form>



<script type="text/javascript">
function ajaxRequest(){
 var activexmodes=["Msxml2.XMLHTTP", "Microsoft.XMLHTTP"] //activeX versions to check for in IE
 if (window.ActiveXObject){ //Test for support for ActiveXObject in IE first (as XMLHttpRequest in IE7 is broken)
  for (var i=0; i<activexmodes.length; i++){
   try{
    return new ActiveXObject(activexmodes[i]);
   }
   catch(e){
    //suppress error
   }
  }
 }
 else if (window.XMLHttpRequest) // if Mozilla, Safari etc
  return new XMLHttpRequest();
 else
  return false;
}


function imgstats(act,postid,imgid){    
            var mypostrequest=new ajaxRequest();
            mypostrequest.onreadystatechange=function(){
                    if (mypostrequest.readyState==4){
                            if (mypostrequest.status==200 || window.location.href.indexOf("http")==-1){		   		
                                    var response = mypostrequest.responseText;	                                    
                                    //alert(response);
                                    var respArr = response.split('|');
                                    var imgid = respArr[1];
                                    var msg = respArr[2];
                                    if(msg == 'likes'){
                                        document.getElementById(imgid+'_totallikes').innerHTML = respArr[0];
                                    }else if(msg == 'votes'){
                                        document.getElementById(imgid+'_totalvotes').innerHTML = respArr[0];
                                    }

                            }else{
                                    alert("An error has occured making the request");
                            }
                    }
            }

            var userid = document.getElementById('hdnusrid').value;
            var parameters="act="+act+"&userid="+userid+"&postid="+postid+"&imgid="+imgid;	
            //alert(parameters);
            mypostrequest.open("POST", "postingstats.php", true);
            mypostrequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            mypostrequest.send(parameters);
    
}

function showcommentbox(parid,postid,imgid)    {
    document.getElementById('respond').style.display = "block";    
    document.getElementById('parentid').value = parid;
    document.getElementById('postid').value = postid;
    document.getElementById('productid').value = imgid;
}


</script>
<br><br><br>
<style>
html, body, div, h1, h2, h3, h4, h5, h6, ul, ol, dl, li, dt, dd, p, blockquote,  
pre, form, fieldset, table, th, td { margin: 0; padding: 0; }  
  
body {  
font-size: 14px;  
line-height:1.3em;  
}  
  
a, a:visited {  
outline:none;  
color:#7d5f1e;  
}  
  
.clear {  
clear:both;  
}  
  
#wrapper {  
 width:480px;  
 margin:0px auto;  
 padding:15px 0px;  
}  
  
.comment {  
 padding:5px;  
 border:2px solid #7d5f1e;  
 margin-top:15px;  
 list-style:none;  
}  
  
.aut {  
 font-weight:bold;  
}  
  
.timestamp {  
 font-size:85%;  
 float:rightright;  
}  
  
#comment_form {  
 margin-top:15px;  
}  
  
#comment_form input {  
 font-size:1.2em;  
 margin:0 0 10px;  
 padding:3px;  
 display:block;  
 width:100%;  
}  
  
#comment_body {  
 display:block;  
 width:100%;  
 height:150px;  
}  
  
#submit_button {  
 text-align:center;   
 clear:both;  
}  
</style>
