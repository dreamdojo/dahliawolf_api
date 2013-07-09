<?php
error_reporting(0);
require('includes/config.php');
require('models/database.php');
require('models/Posting.php'); // Added by Dev
$db = new Posting( "mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass );


$act = $_POST['act'];
$userid = $_POST['userid'];
$postid = $_POST['postid'];
$imgid = $_POST['imgid'];

//echo '<pre>'; print_r($_POST);
/*$act = 'up'; // like up  down
$imgid = '1'; // 2
$postid = '1'; // 2
$userid = '369'; // 2*/

$liketypeid = '2'; // 1 Internal; 2 Facebook; 3 Twitter; 4 Pinterest

$table = 'posting';
$likestable = 'posting_likes';
$votestable = 'vote';

$parameters = array(
                 'conditions' => array(
                                    'posting_id' => $postid
                                )
                    );


$postingRow = $db->getPostDetails($parameters);
$datarow = $postingRow['data'];
//echo '<pre>';
//print_r($postingRow);

if(count($datarow)>0){
    $postingArr= $datarow[0];
    $old_total_likes = $postingArr['total_likes'];
    $old_total_votes = $postingArr['total_votes'];
}

if($act == 'like'){
    $likestbldata=array();
    $likestbldata['user_id'] = $userid;
    $likestbldata['post_id'] = $postid;
    $likestbldata['like_type_id'] = $liketypeid;
    $likestbldata['image_id'] = $imgid;
    
    $db->insert($likestable, $likestbldata) ;
            
            
    $total_likes = ($old_total_likes + 1);
    $msg = $total_likes.'|'.$postid.'|likes';
}else{
    $total_likes = $old_total_likes;
}

if($act == 'up'){
    $votetbldata=array();
    $votetbldata['user_id'] = $userid;
    $votetbldata['post_id'] = $postid;    
    $votetbldata['image_id'] = $imgid;
    
    $db->insert($votestable, $votetbldata) ;
    
    $total_votes = ($old_total_votes + 1);
    $msg = $total_votes.'|'.$postid.'|votes';
}elseif($act == 'down'){
    $total_votes = ($old_total_votes - 1);
    $msg = $total_votes.'|'.$postid.'|votes';
}else{
    $total_votes = $old_total_votes;
}




$whereArr = array();
$whereArr['image_id'] = $imgid;

$curTime = date('Y-m-d H:i:s');
$postParams = array();
$postParams['created'] = $curTime;
$postParams['total_likes'] = $total_likes;
$postParams['total_votes'] = $total_votes;

$params = array();
$params['data'] = $postParams;
$params['where'] = $whereArr;



$arr = $db->addPostLike( $params) ;
//print_r($arr);
if( ($arr['success'] == '1') && ($arr['data'] == '1') ){
    echo $msg;
}
?>