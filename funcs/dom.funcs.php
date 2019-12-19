<?php

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

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