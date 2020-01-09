<?php

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use PHPHtmlParser\Dom;
use PHPHtmlParser\CurlInterface;

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
            runCrawler($db, $client, $last_instance_id, $message);

            // TODO: ???
            /*
			$sqlNews = "INSERT INTO smf_discord_instances (channel_id, smf_url, board_id) VALUES (?, ?, ?)";
			$stmt = $db->prepare($sqlInstances);
			$stmt->execute([$id, $smf_url, $board_id]);
			*/

            $message->channel->send('Listening to channel #'.$message->channel->name.'!');
    	} else {
            // TODO: This isn't showing until everything ends...
            $msg = ':stop_sign: You are already listening to a channel, please use `$set channel` to focus on a new channel!';
    		$message->channel->send($msg);

            $last_instance_id = $row["id"];
            runCrawler($db, $client, $last_instance_id, $message);
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

function runCrawler($db, $client, $instance_id, $message) {
    $channel = $message->channel;
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
	$channelInstance = getChannelById($client, $instance_data['channel_id']);

	// TODO: Create infinite loop to crawl data, but first create an example where the last topic is output
    runLoop($crawl_url, $message, $channelInstance);
}

/*
// [1]
$loop = React\EventLoop\Factory::create();

// [2]
$loop->addPeriodicTimer(5 * 60, function () {
    echo "Tick\n";
});
*/

function runLoop($url, $message, $channelInstance) {
    getDomFromContents($url, $message, true, function($dom) use($url, $message, $channelInstance) {
        $divs = $dom->find("div");    
        $tables = $dom->find("table");

    
        if(count($divs) != count($tables)) 
        {
            $channelInstance->send(':stop_sign: Error ocurred on the server side!');
            echo 'Div count is not the same of table count! ('.count($divs).' != '.count($tables).') on '.$url.PHP_EOL;
            return;
        }

        $data = array();

        $j = 0;
        for ($i = 0; $i < count($divs); $i++) 
        {
            // TODO: Get id from link (discord_bot_news), then if id from last new is less than the actual id then continue
            $div = $divs[$i];
            $table = $tables[$i];

            $title_a = $div->find("a")[0];
            $table_contents = $table->find("font")[0];
        
            // This will be used later
            $new_url = $title_a->getAttribute('href');

            $matches = array();
            preg_match('/\d+ (Enero|Febrero|Marzo|Abril|Mayo|Junio|Julio|Agosto|Septiembre|Octubre|Noviembre|Diciembre) \d+, \d+:\d+/', $table_contents->text, $matches);

            $published_date = $matches[0];
            $user_element = $table_contents->find("a")[0];

            $data[$j]["title"] = $title_a->find("b")[0]->text;
            $data[$j]["url"] = $new_url;
            $data[$j]["description"] = br2nl($div->find("font")[0]->innerHtml);
            $data[$j]["published_at"] = $published_date;
            $data[$j]["user_url"] = $user_element->getAttribute("href");
            $data[$j]["username"] = $user_element->text;

            ++$j;
        }

        echo PHP_EOL.print_r($data, true).PHP_EOL;

        for ($k = 0; $k < $j; $k++) 
        {
            // TODO: Batch downloads
            $new_url = $data[$k]["url"];
            getDomFromUrl($new_url, $message, function($dom) use($new_url, $data, $k, $channelInstance) {
                echo "Getting dom from new (".$new_url.")...".PHP_EOL;
                $avatar = getAvatar($dom);

                $original_newurl = getOriginalUrl($dom); // Only used for screenshot
                $screenshot_url = getScreenshot($original_newurl);

                $data["avatar"] = $avatar;
                $data["screenshot"] = $screenshot_url;
                sendMessageFromData($channelInstance, $data, $k);
            });
        }
    });
}

function sendMessageFromData($channel, $adata, $index) {
    $data = $adata[$index];

    $data["description"] = transformDescription($data);
    $data["footer"] = "Noticia publicada **".$data[$k]["published_at"]."** por [".$data[$k]["username"]."](".$data[$k]["user_url"].")".PHP_EOL.PHP_EOL;

    echo "Sending message for index '".$index."'...".PHP_EOL;

    // sendMessage($channel, $title, $description, $image, $author, $author_avatar, $footer, $url);
    sendMessage($channel, $data["title"], $data["description"], $data["screenshot"], $data["username"], $data["avatar"], $data["footer"], $data["url"]);

    // TODO: Send to database
}

function transformDescription($data) {
    return $data["description"].PHP_EOL."[Leer más](".$data["url"].")";
}

function getDomFromUrl($new_url, $message, $callback) {
    getDomFromContents($new_url, $message, false, function($dom) use($callback, $new_url) {
        // echo "Dom obtained in run.funcs.php:".PHP_EOL.PHP_EOL.print_r($dom, true).PHP_EOL;

        $filename = substr($new_url, strrpos($new_url, '/') + 1);
        $filename = filter_filename($filename);

        file_put_contents("html/".$filename, $dom->outerHtml);
        $callback($dom);
    });
}

function getAvatar($dom) {
    return $dom->find('.avatar')[0]->getAttribute('src');
}

function getOriginalUrl($dom) {
    return $dom->find('.post')[0]->find('a[target="_blank"]')->getAttribute('href');
}

function getScreenshot($url) {
    $relative_url = 'screenshots/'. uniqid(rand(), true) . '.png';
    $filename = __DIR__ . '/../public_html/'.$relative_url;

    echo "Creating screenshot for url (".$url.")...".PHP_EOL;
    $page = launchPuppeteer(null);
    $page->goto($url);
    $page->screenshot(['path' => $filename]);

    $browser->close();

    return 'https://api.z3nth10n.net/'.$relative_url;
}

/*

:notepad_spiral: [Google Cloud puede decir adiós si no supera en cuota a Amazon Web Services o ...](http://google.com/)

Una de las luchas más interesantes que se están dando den la informática actual está muy lejos del escritorio. Se da en la nube, y enfrenta a tres colosos de la computación, como son Amazon, con Amazon Web Services (AWS), Microsoft, con Azure, y Google con Google Cloud.

El orden en que los hemos mencionado corresponde con el lugar que los tres ocupan en cuota de mercado en el campo de los servidores. La posición actual de Google en el sector no gustaba en el seno de la compañía al...

[Leer más](http://google.com)
Noticia publicada **Hoy a las 01:00** por [wolfbcn](http://google.com)

===========================================================================

*/