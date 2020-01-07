<?php

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use PHPHtmlParser\Dom;
use PHPHtmlParser\CurlInterface;

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

function getContents($url) {
        $puppeteer = new Puppeteer(['read_timeout' => 20]);
        $browser = $puppeteer->launch([
                'args' => ['--no-sandbox', '--disable-setuid-sandbox']
        ]);

        $page = $browser->newPage();
        $response = $page->goto($url, [
    		'timeout' => 15000, // In milliseconds
		]);

        sleep(7);

        // getLoop($response, $browser, $page)->run();
        $contents = $page->content();

        // var_dump($response->headers());

		$browser->close();
        return $contents;
}

function getDomFromContents($url) {
	$contents = getContents($url);
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