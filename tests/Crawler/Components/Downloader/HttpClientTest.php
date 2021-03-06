<?php

namespace Tests\Crawler\Components\Downloader;

use Tests\TestCase;

class HttpClientTest extends TestCase
{
    public function testDownload()
    {
        $downloader = $this->container->make('Downloader');
        $response = $downloader->download('http://www.baidu.com', 'GET');

        $this->assertInstanceOf(\Crawler\Components\Parser\ParserInterface::class, $response);
    }
}