<?php
$con = mysql_connect('localhost', 'dahlia_db', 'Jgv9EZ3G.WE6');
if (!$con) {
    die('Could not connect: ' . mysql_error());
}
//var_dump($_POST);
mysql_select_db("dahlia_db", $con);

?>