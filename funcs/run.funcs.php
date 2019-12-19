<?php

function startListening($db, $client, $message, $params) {
    try {
    	if(null === @$params[0]) {
    		$message->channel->send(':stop_sign: Empty argument #0 passed!');
    		return;
    	}

    	if(null === @$params[1]) {
    		$message->channel->send(':stop_sign: Empty argument #1 passed!');
    		return;
    	}

    	if(!isUrl(@$params[0])) {
    		$message->channel->send(':stop_sign: Param #0 is not a valid url!');
    		return;
    	}

    	if(!is_int(@$params[1])) {
    		$message->channel->send(':stop_sign: Param #1 is not an integer!');
    		return;
    	}

    	$id = $message->channel->getId();
    	$smf_url = $params[0];
    	$board_id = $params[1];

    	// TODO: Check if subforum exists, and url is still valid

    	// Before adding anything to the database checks if the bot is already listening to a channel
    	// If not, then add it to the database

    	$ids = getAllChannelIds($client);
    	// `isAnyChannelAlreadyListened` func
    	$isEmpty = !getResult($db, 'smf_discord_instances', concatSqlWhere($ids), $ids, 'id');

    	if($isEmpty) {
			$sqlInstances = "INSERT INTO smf_discord_instances (channel_id, smf_url, board_id) VALUES (?, ?, ?)";
			$stmt = $db->prepare($sqlInstances);
			$stmt->execute([$id, $smf_url, $board_id]);

			/*
			$last_instance_id = $db->lastInsertId();

			$sqlNews = "INSERT INTO smf_discord_instances (channel_id, smf_url, board_id) VALUES (?, ?, ?)";
			$stmt = $db->prepare($sqlInstances);
			$stmt->execute([$id, $smf_url, $board_id]);
			*/
    	} else {
    		$message->channel->send(':stop_sign: You are already listening to a channel, please use `$set channel` to focus on a new channel!');
    	}

        $message->channel->send('Listening to channel #'.$message->channel->name.'!');
        // $message->channel->send('You need to contigure this by using `$listen-board <url> <board_id>`.');
        // $message->channel->send('Example: `$listen-board https://foro.elhacker.net/ 34`');
    }
    catch(Exception $e) {
        promptException($message);
    }
    
}

// Used when starting to run once `listenChannel` method is executed
function getChannelById($client, $id) {
    foreach ($client->channels->all() as $channel) 
    {
        if($channel->getId() == $id)
            return $channel;
    }

    return null;
}

function getAllChannelIds($client) {
	$ids = array();

    foreach ($client->channels->all() as $channel) 
    {
        $ids[] = $channel->getId();
    }

    return $ids;
}

function runCrawler($db, $client, $instance_id) {
	$instance_data = getResult($db, 'smf_discord_instances', 'id=?', $instance_id);

	$smf_url = $instance_data['smf_url'];
	$board_id = $instance_data['board_id'];

	$crawl_url = appendSlash($smf_url).'-b'.$board_id.'.0';
	$channel = getChannelById($client, $instance_data['channel_id']);

	// TODO: Create infinite loop to crawl data, but first create an example where the last topic is output
    runLoop($crawl_url, $channel);	
}

function runLoop($url, $channel) {

}

/*
function getCachedChannels($client) {
    foreach ($client->channels->all() as $channel) 
    {
        echo "Channel: ".$channel->name." [".$channel->getId()."]".PHP_EOL;
    }
}
*/

function promptException($message) {
    $message->channel->send(':stop_sign: Exception ocurred on the server side!');
}