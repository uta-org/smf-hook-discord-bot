<?php

require_once __DIR__.'/../libs/php-thread/Thread.php';

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use PHPHtmlParser\Dom;
use PHPHtmlParser\CurlInterface;

/*
function getLoop($response, $browser, $page) {
	$loop = React\EventLoop\Factory::create();

	$loop->addPeriodicTimer(10, function () use($response, $page) {
	    // var_dump($response->headers());
        // $browser->close();
        // $loop->stop();

        echo $page->content();
	});

	return $loop;
}
*/

function internalGetContent($page, $callback) {
	sleep(7);
	$callback($page->content());
}

function getContents($url, &$contents) {
        $puppeteer = new Puppeteer(['read_timeout' => 20]);
        $browser = $puppeteer->launch([
                'args' => ['--no-sandbox', '--disable-setuid-sandbox']
        ]);

        $page = $browser->newPage();
        $response = $page->goto($url, [
    		'timeout' => 15000, // In milliseconds
		]);

		// test to see if threading is available
		if( ! Thread::isAvailable() ) {
		    die( 'Threads not supported' );
		}

		// create 2 thread objects
		$t1 = new Thread( 'internalGetContent' );

		// start them
		$t1->start($page, function($c) use(&$contents) {
			$contents = $c;
		});
		$browser->close();

		return $t1;
}

function getDomFromContents($url) {
	$contents = "";
	$t1 = getContents($url, $contents);

	while( $t1->isAlive() ) {
		echo "Waiting for DOM...".PHP_EOL;
		sleep(1);
	}

	$dom = new Dom;
	$dom->load($contents);

	return $dom;
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
            ],
            // 'proxy' => 'http://202.56.203.40:80'
        ])->getBody();
    }
}