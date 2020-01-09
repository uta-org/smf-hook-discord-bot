<?php

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use PHPHtmlParser\Dom;
use PHPHtmlParser\CurlInterface;

$puppeteerInstance = null;
$browserInstance = null;
$loopInstance = null;
$pageInstance = null;

// Use this function all the time we need to launch puppeteer
function launchPuppeteer($args, $disable = false) {
    global $puppeteerInstance, $browserInstance, $pageInstance, $queue;

    // bugfix: Don't create instance if it's already created!
    if(isset($puppeteerInstance) && isset($browserInstance) && isset($pageInstance)) {
        echo "Reusing instance...".PHP_EOL;
        return $pageInstance;
    }

    $arg = $disable ? ['--no-sandbox', '--disable-setuid-sandbox'] : ['--no-sandbox'];
    $puppeteerInstance = !isset($args) ? new Puppeteer : new Puppeteer($args);
    $browserInstance = $puppeteerInstance->launch([
               'args' => $arg,
               'headless' => true
    ]);

    $pageInstance = $browserInstance->newPage();

    return $pageInstance;
}

function getLoop($page, $callback) {
    global $loopInstance; 

	$loopInstance = React\EventLoop\Factory::create();

	$loopInstance->addPeriodicTimer(10, function () use($page, $callback) {
		$contents = $page->content();
        echo "Getting DOM with ".strlen($contents)." bytes".PHP_EOL;

        $callback($contents);
	});

	return $loopInstance;
}

function getContents($url, $message, $disable, $callback) {
    try {
        $page = launchPuppeteer(['read_timeout' => 20], $disable);
        $page->goto($url, [
        	'timeout' => 15000, // In milliseconds
    	]);
    
        echo "Waiting 10 seconds to Cloudflare for url '".$url."'...".PHP_EOL;

        if(!isset($loopInstance))
		  getLoop($page, $callback)->run();
    } catch(Exception $e) {
        // promptException($message, $e);
        echo $e->getMessage().PHP_EOL;
    }
}

function getDomFromContents($url, $message, $disable, $callback) {
	getContents($url, $message, $disable, function($contents) use($callback) {
		$dom = new Dom;
		$dom->load($contents);
		$callback($dom);
	});
}

// Not working on Forums with Cloudflare protection
function getDom($url) {
	$dom = new Dom;
	$dom->loadFromUrl($url, [], new ParseClient());

	return $dom;
}

class ParseClient implements CurlInterface
{
    public function get($url) : string
    {
        $client = new GuzzleHttp\Client();

        return $client->request('GET', $url, [
            'headers' => [
                'User-Agent' => 'testing/1.0',
            ]
        ])->getBody();
    }
}
