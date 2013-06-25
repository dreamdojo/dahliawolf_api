<?php
function resultArray($success, $result) {
	$ret = array('success' => ($success == TRUE ? TRUE : FALSE), 'data' => $result);
	return $ret;
}
?>
