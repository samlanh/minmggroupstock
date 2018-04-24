<?php 
	$protocol = ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1')? 'http':'http';
	header("Location: ".$protocol."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."public");
?>