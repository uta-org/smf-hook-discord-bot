<?php

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use PHPHtmlParser\Dom;
use PHPHtmlParser\CurlInterface;

function getContents($url) {
        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch([
                'args' => ['--no-sandbox', '--disable-setuid-sandbox']
        ]);

        $page = $browser->newPage();
        $page->goto($url);

        $contents = $page->content();
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