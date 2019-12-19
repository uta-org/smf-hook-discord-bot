<?php

function startsWith($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
}

function endsWith($string, $endString) 
{ 
    $len = strlen($endString); 
    if ($len == 0) { 
        return true; 
    } 
    return (substr($string, -$len) === $endString); 
}

function getParams($raw_command) {
	return array_slice(explode(" ", $raw_command), 1);
}

function concatSqlWhere($channels) {
    $str = "";

    for ($i = 0; $i < count($channels); $i++) { 
        $str .= "channel_id = ?";

        if($i + 1 < count($channels))
            $str .= " OR ";
    }

    return $str;
}

function isUrl($text) {
    $regex = "((https?|ftp)\:\/\/)?"; // SCHEME 
    $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass 
    $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP 
    $regex .= "(\:[0-9]{2,5})?"; // Port 
    $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path 
    $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query 
    $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

    if(preg_match("/^$regex$/", $text)) {
        return true;
    } else {
        return false;
    }
}

function appendSlash($str) {
	if(endsWith($str, '/'))
		return $str;

	return $str.'/';
}

function nl2br2($string) {
    $string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
    return $string;
}