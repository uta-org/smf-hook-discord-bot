<?php

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use PHPHtmlParser\Dom;
use PHPHtmlParser\CurlInterface;

// Use this function all the time we need to launch puppeteer
function launchPuppeteer($args) {
    $puppeteer = !isset($args) ? new Puppeteer : new Puppeteer($args);
    $browser = $puppeteer->launch([
               'args' => ['--no-sandbox', '--disable-setuid-sandbox'],
               'headless' => true
    ]);

    return $browser;
}

function getLoop($page, $browser, $callback) {
	$loop = React\EventLoop\Factory::create();

	$loop->addPeriodicTimer(10, function () use($page, $browser, $callback, $loop) {
		$contents = $page->content();

		// echo $contents;
        $callback($contents);
        $loop->stop();
        $browser->close();
	});

	return $loop;
}

function getContents($url, $callback) {
        $browser = launchPuppeteer(['read_timeout' => 20]);

        $page = $browser->newPage();
        $response = $page->goto($url, [
    		'timeout' => 15000, // In milliseconds
		]);

        echo "Waiting 10 seconds to Cloudflare for url '".$url."'...".PHP_EOL;
		getLoop($page, $browser, $callback)->run();
}

function getDomFromContents($url, $callback) {
	getContents($url, function($contents) use($callback) {
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
