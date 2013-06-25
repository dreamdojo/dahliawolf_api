<?
$url = 'http://api.dahliawolf.com/imagefeed/getImages.php';
$fields = array(
            'start_limit'  => 0,
			'end_limit'  => 20,
			'posted' => 0,
			'type'	 => 'all'
        );
//posted 0 = all
//posted 1 = posted
//posted 2 = not posted		

//type = all
//type = webstagram
//type = pinterest
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if(curl_exec($ch) === false)
{
    echo 'Curl error: ' . curl_error($ch);
}

$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result);
print_r($res);
?>