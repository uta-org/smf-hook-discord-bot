<?php

$i = 0;

function getResult($db, $table_name, $condition, $data, $columns = '*') 
{
	// global $i, $debug;
	global $i;

	$sql = 'SELECT '.$columns.' FROM '.$table_name.' WHERE '.$condition;

	/*
	if($debug)
		echo "[".$i."] Executing SQL clause: '".getFullSql($sql, $data)."'!";
	*/

	$stmt = $db->prepare($sql);
	// $stmt->bindParam(1, $user_id, PDO::PARAM_STR);
	$stmt->execute($data);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	++$i;
	return $row;	
}

// Used to re-hook bot
function getInstanceIdFromChannelId($db, $channel_id) 
{

	$stmt = $db->prepare('SELECT id FROM smf_discord_news WHERE channel_id=?');
	$stmt->bindParam(1, $channel_id, PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	return @$row['id'];
}


function getFullSql($sql, $data) {
	$count = 0;
	$sql = preg_replace_callback( '/\?/', function($match) use($data, &$count) {
	    return $data[$count++] . ' ' . "\n";
	}, $sql);

	return $sql;
}