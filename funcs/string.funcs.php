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

/**
* Convert BR tags to nl
*
* @param string The string to convert
* @return string The converted string
*/
function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function promptException($message, $e) {
    echo $e->getMessage().PHP_EOL;
    $message->channel->send(':stop_sign: Exception ocurred on the server side!');
}

function filter_filename($name) {
    // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
    $name = str_replace(array_merge(
        array_map('chr', range(0, 31)),
        array('<', '>', ':', '"', '/', '\\', '|', '?', '*')
    ), '', $name);
    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $name= mb_strcut(pathinfo($name, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($name)) . ($ext ? '.' . $ext : '');
    return $name;
}