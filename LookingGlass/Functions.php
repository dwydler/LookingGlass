<?php

/**
* Execute a 'host' command against given host:
* Host is a simple utility for performing DNS lookups
*
* @return string
*   Return the client ip address
*/
function get_client_ip() {
	if( !empty($_SERVER['HTTP_CF_CONNECTING_IP']) ) {
		return $_SERVER["HTTP_CF_CONNECTING_IP"];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
		return  $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif (!empty($_SERVER['REMOTE_ADDR']) ) {
		return $_SERVER['REMOTE_ADDR'];
	}
	else {
		return $_SERVER['HTTP_CLIENT_IP'];
	}
}

?>