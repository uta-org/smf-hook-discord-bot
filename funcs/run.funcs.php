<?php

use PHPHtmlParser\Dom;

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

    	if(!is_numeric(@$params[1])) {
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
        $where_clause = concatSqlWhere($ids);

        $row = getResult($db, 'smf_discord_instances', $where_clause, $ids, 'id');
    	$isEmpty = !$row;

    	if($isEmpty) {
			$sqlInstances = "INSERT INTO smf_discord_instances (channel_id, smf_url, board_id) VALUES (?, ?, ?)";
			$stmt = $db->prepare($sqlInstances);
			$stmt->execute([$id, $smf_url, $board_id]);

			$last_instance_id = $db->lastInsertId();
            runCrawler($db, $client, $message->channel, $last_instance_id);

            /*
			$sqlNews = "INSERT INTO smf_discord_instances (channel_id, smf_url, board_id) VALUES (?, ?, ?)";
			$stmt = $db->prepare($sqlInstances);
			$stmt->execute([$id, $smf_url, $board_id]);
			*/

            $message->channel->send('Listening to channel #'.$message->channel->name.'!');
    	} else {
    		$message->channel->send(':stop_sign: You are already listening to a channel, please use `$set channel` to focus on a new channel!');

            // $stmt = $db->query("SELECT LAST_INSERT_ID() FROM smf_discord_instances");
            // $last_id = $stmt->fetchColumn();

            $last_instance_id = $row["id"];
            runCrawler($db, $client, $message->channel, $last_instance_id);
    	}
    }
    catch(Exception $e) {
        promptException($message, $e);
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
    // TODO: Use PHP Linq

	$ids = array();

    foreach ($client->channels->all() as $channel) 
    {
        $ids[] = $channel->getId();
    }

    return $ids;
}

function runCrawler($db, $client, $channel, $instance_id) {
    // global $loop;

	$instance_data = getResult($db, 'smf_discord_instances', 'id=?', $instance_id);

    if(!$instance_data) {
        $channel->send(":stop_sign: Can't run the crawler because can't get instance from id (".$instance_id.")!");
        return;        
    }

	$smf_url = $instance_data['smf_url'];
	$board_id = $instance_data['board_id'];

    // https://foro.elhacker.net/SSI.php?ssi_function=boardNews;board=34;start=1;limit=5;length=500
    // $crawl_url = appendSlash($smf_url).'-b'.$board_id.'.0';
    $crawl_url = appendSlash($smf_url).'SSI.php?ssi_function=boardNews;board='.$board_id.';start=1;limit=5;length=500';
	$channel = getChannelById($client, $instance_data['channel_id']);

	// TODO: Create infinite loop to crawl data, but first create an example where the last topic is output
    runLoop($crawl_url, $channel);
}

/*
// [1]
$loop = React\EventLoop\Factory::create();

// [2]
$loop->addPeriodicTimer(5 * 60, function () {
    echo "Tick\n";
});
*/

function runLoop($url, $channel) {
    $dom = new Dom;
    $dom->loadFromUrl($url);

    $divs = $dom->find("div");    
    $tables = $dom->find("table");

    $data = array();

    if(count($divs) != count($tables)) 
    {
        $channel->send(':stop_sign: Error ocurred on the server side!');
        echo 'Div count is not the same of table count! ('.count($divs).' != '.count($tables).') on '.$url.PHP_EOL;
        return;
    }

    $j = 0;
    for ($i=0; $i < count($divs); $i++) 
    {
        // TODO: Get id from link (discord_bot_news), then if id from last new is less than the actual id then continue

        $div = $divs[$i];
        $table = $tables[$i];

        $title_a = $div->find("a")[0];
        $table_contents = $table->find("font")[0]->find("b")[0];

        $published_date = $table_contents->text;
        $published_date .= $table_contents->nextSibling()->text;

        $user_element = $table_contents->nextSibling();

        $data[$j]["title"] = $title_a->text;
        $data[$j]["url"] = $title_a->getAttribute('href');
        $data[$j]["description"] = br2nl($div->find("font")[0]->innerHtml);
        $data[$j]["published_at"] = $published_date;
        $data[$j]["user_url"] = $user_element->getAttribute("href");
        $data[$j]["username"] = $user_element->text;

        ++$j;
    }

    for ($k=0; $k < $j; $k++) 
    { 
        echo "[".$k."] Publishing new! Data: ".PHP_EOL.print_r($data[$k], true).PHP_EOL.PHP_EOL;

        $new = ":notepad_spiral: [".$data[$k]["title"]."](".$data[$k]["url"].")".PHP_EOL.PHP_EOL;
        $new .= $data[$k]["description"].PHP_EOL;
        $new .= "[Leer más](".$data[$k]["url"].")".PHP_EOL;
        $new .= "Noticia publicada **".$data[$k]["published_at"]."** por [".$data[$k]["username"]."](".$data[$k]["user_url"].")".PHP_EOL.PHP_EOL;
        $new .= "-----------------------------";

        $channel->send($new);   
    }

}

/*

:notepad_spiral: [Google Cloud puede decir adiós si no supera en cuota a Amazon Web Services o ...](http://google.com/)

Una de las luchas más interesantes que se están dando den la informática actual está muy lejos del escritorio. Se da en la nube, y enfrenta a tres colosos de la computación, como son Amazon, con Amazon Web Services (AWS), Microsoft, con Azure, y Google con Google Cloud.

El orden en que los hemos mencionado corresponde con el lugar que los tres ocupan en cuota de mercado en el campo de los servidores. La posición actual de Google en el sector no gustaba en el seno de la compañía al...

[Leer más](http://google.com)
Noticia publicada **Hoy a las 01:00** por [wolfbcn](http://google.com)

===========================================================================

*/

/*
function getCachedChannels($client) {
    foreach ($client->channels->all() as $channel) 
    {
        echo "Channel: ".$channel->name." [".$channel->getId()."]".PHP_EOL;
    }
}
*/

function promptException($message, $e) {
    echo $e->getMessage().PHP_EOL;
    $message->channel->send(':stop_sign: Exception ocurred on the server side!');
}