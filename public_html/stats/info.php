<?php

if( isset($_GET['print']) ) phpinfo();

echo "<br><br>";
echo "<pre>";


echo  ini_get("error_log") . "\n";
echo  ini_get("display_errors") . "\n";


print_r(ini_get_all());
echo "</pre>";

error_log("WTF php!!!!! 0.o");


?>
