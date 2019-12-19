<?php

$i = 0;

function getResult($db, $table_name, $condition, $data, $columns = '*') 
{
	global $i;

	$sql = 'SELECT '.$columns.' FROM '.$table_name.' WHERE '.$condition;

	echo "[".$i."] Executing SQL clause: '".$sql."'!";
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