<?php

use PHPUnit\Framework\TestCase;
use MadeITBelgium\Wappalyzer\Wappalyzer;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class WappalyzerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testHtml()
    {
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/app.json')),
            new Response(200, [], '<html lang="nl"><link src="/bitrix/js/"></html>'),
        ]);
        
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        
        $wappalyzer = new Wappalyzer('http://localhost', $client);
        $this->assertEquals([
            'language' => 'nl',
            'url' => 'http://localhost',
            'detected' => [
                '1C-Bitrix' => [
                    'cats' => [1],
                    'headers' => [
                        'Set-Cookie' => 'BITRIX_',
                        'X-Powered-CMS' => 'Bitrix Site Manager'
                    ],
                    'html' => '(?:<link[^>]+components/bitrix|(?:src|href)="/bitrix/(?:js|templates))',
                    'icon' => '1C-Bitrix.png',
                    'implies' => 'PHP',
                    'script' => '1c-bitrix',
                    'website' => 'http://www.1c-bitrix.ru',
                    'detected' => true,
                ],
                'PHP' => [
                    'cats' => [27],
                    'cookies' => ['PHPSESSID' => ''],
                    'headers' => [
                        'Server' => 'php/?([\d.]+)?\;version:\1',
                        'X-Powered-By' => '^php/?([\d.]+)?\;version:\1'
                    ],
                    'icon' => 'PHP.svg',
                    'url' => '\.php(?:$|\?)',
                    'website' => 'http://php.net',
                ],
            ],
        ], $wappalyzer->analyze('http://localhost'));
    }
}
