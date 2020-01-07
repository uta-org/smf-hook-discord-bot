<?php

// require_once __DIR__.'/../libs/php-thread/Thread.php';

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use PHPHtmlParser\Dom;
use PHPHtmlParser\CurlInterface;


function getLoop($page, $browser, $callback) {
	$loop = React\EventLoop\Factory::create();

	$loop->addPeriodicTimer(6, function () use($page, $browser, $callback) {
        $callback($page->content());
        $loop->stop();
        $browser->close();
	});

	return $loop;
}


/*function internalGetContent($page, $callback) {
	sleep(7);
	$callback($page->content());
}*/

function getContents($url, $callback) {
        $puppeteer = new Puppeteer(['read_timeout' => 20]);
        $browser = $puppeteer->launch([
                'args' => ['--no-sandbox', '--disable-setuid-sandbox']
        ]);

        $page = $browser->newPage();
        $response = $page->goto($url, [
    		'timeout' => 15000, // In milliseconds
		]);

		getLoop($page, $browser, $callback)->run();
}

function getDomFromContents($url, $callback) {
	getContents($url, function($contents) {
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
            ],
            // 'proxy' => 'http://202.56.203.40:80'
        ])->getBody();
    }
}