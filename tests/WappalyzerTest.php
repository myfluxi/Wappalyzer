<?php

use PHPUnit\Framework\TestCase;
use MadeITBelgium\Wappalyzer\Wappalyzer;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
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
        $jar = new CookieJar;
        $cookieJar = $jar->fromArray(['laravel_session' => 'ABC'], 'localhost');
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/app.json')),
            new Response(200, [
                'cache-control' => 'no-store, no-cache, must-revalidate',
                'content-encoding' => 'gzip',
                'content-type' => 'text/html; charset="UTF-8"',
                'date' => 'Tue, 17 Jul 2018 12:31:08 GMT',
                'expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
                'link' => '<https://localhost/wp-json/>; rel="https://api.w.org/"',
                'link' => '<https://localhost/>; rel=shortlink',
                'pragma' => 'no-cache',
                'status' => '200',
                'vary' => 'Accept-Encoding,Cookie',
                'x-powered-by' => 'PHP/7.2.7',
            ], file_get_contents(__DIR__ . '/site.html')),
        ]);
        
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler, 'cookies' => $cookieJar]);
        
        $wappalyzer = new Wappalyzer('http://localhost', $client);
        $this->assertEquals([
            'url' => 'http://localhost',
            'language' => 'nl-BE',
            'detected' => [
                'Font Awesome' => [
                    'cats' => [17],
                    'html' => [
                        '<link[^>]* href=[^>]+(?:([\d.]+)/)?(?:css/)?font-awesome(?:\.min)?\.css\;version:\1',
                        '<link[^>]* href="https://use\.fontawesome\.com/releases/v([^>]+)/css/\;version:\1',
                        '<script[^>]* src=[^>]+fontawesome(?:\.js)?',
                    ],
                    'icon' => 'Font Awesome.png',
                    'website' => 'http://fontawesome.io',
                    'detected' => true,
                ],
                'PHP' => [
                    'cats' => [27],
                    'cookies' => ['PHPSESSID' => ''],
                    'headers' => [
                        'Server' => 'php/?([\d.]+)?\;version:\1',
                        'X-Powered-By' => '^php/?([\d.]+)?\;version:\1',
                    ],
                    'icon' => 'PHP.svg',
                    'url' => '\.php(?:$|\?)',
                    'website' => 'http://php.net',
                    'detected' => true,
                    'version' => '7.2.7',
                    'cpe' => 'cpe:/a:php:php'
                ],
                'WordPress' => [
                    'cats' => [
                        0 => 1,
                        1 => 11,
                    ],
                    'html' => [
                        0 => '<link rel=["\']stylesheet["\'] [^>]+/wp-(?:content|includes)/',
                        1 => '<link[^>]+s\d+\.wp\.com',
                    ],
                    'icon' => 'WordPress.svg',
                    'implies' => [
                        0 => 'PHP',
                        1 => 'MySQL'
                    ],
                    'js' => [
                        'wp_username' => ''
                    ],
                    'meta' => ['generator' => '^WordPress ?([\d.]+)?\;version:\1'],
                    'script' => '/wp-(?:content|includes)/',
                    'website' => 'https://wordpress.org',
                    'detected' => true,
                    'version' =>  '4.9.7',
                    'headers' => [
                        'link' => 'rel="https://api\.w\.org/"'
                    ],
                ],
                'Yoast SEO' => [
                    'cats' => [ 54 ],
                    'html' => [ 
                        '<!-- This site is optimized with the Yoast (?:WordPress )?SEO plugin v([\d.]+) -\;version:\1',
                    ],
                    'icon' => 'Yoast SEO.png',
                    'implies' => 'WordPress',
                    'website' => 'http://yoast.com',
                    'detected' => 1,
                    'version' => '7.8',
                ],
                'MySQL' => [
                    'cats' => [ 34 ],
                    'icon' => 'MySQL.svg',
                    'website' => 'http://mysql.com',
                    'cpe' => 'cpe:/a:mysql:mysql',
                ],
                'Laravel' => [
                    'cats' => [18],
                    'cookies' => [
                        'laravel_session' => ''
                    ],
                    'icon' => 'Laravel.svg',
                    'implies' => 'PHP',
                    'js' => [
                        'Laravel' => ''
                    ],
                    'website' => 'https://laravel.com',
                    'detected' => true,
                    'cpe' => 'cpe:/a:laravel:laravel'
                ]
            ]
        ], $wappalyzer->analyze('http://localhost'));
    }

    public function testHtmlFile()
    {
        $jar = new CookieJar;
        $cookieJar = $jar->fromArray(['laravel_session' => 'ABC'], 'localhost');
        $mock = new MockHandler([
            new Response(200, [
                'cache-control' => 'no-store, no-cache, must-revalidate',
                'content-encoding' => 'gzip',
                'content-type' => 'text/html; charset="UTF-8"',
                'date' => 'Tue, 17 Jul 2018 12:31:08 GMT',
                'expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
                'link' => '<https://localhost/wp-json/>; rel="https://api.w.org/"',
                'link' => '<https://localhost/>; rel=shortlink',
                'pragma' => 'no-cache',
                'status' => '200',
                'vary' => 'Accept-Encoding,Cookie',
                'x-powered-by' => 'PHP/7.2.7',
            ], file_get_contents(__DIR__ . '/site.html')),
        ]);
        
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler, 'cookies' => $cookieJar]);
        
        $wappalyzer = new Wappalyzer(__DIR__ . '/app.json', $client);
        $this->assertEquals([
            'url' => 'http://localhost',
            'language' => 'nl-BE',
            'detected' => [
                'Font Awesome' => [
                    'cats' => [17],
                    'html' => [
                        '<link[^>]* href=[^>]+(?:([\d.]+)/)?(?:css/)?font-awesome(?:\.min)?\.css\;version:\1',
                        '<link[^>]* href="https://use\.fontawesome\.com/releases/v([^>]+)/css/\;version:\1',
                        '<script[^>]* src=[^>]+fontawesome(?:\.js)?',
                    ],
                    'icon' => 'Font Awesome.png',
                    'website' => 'http://fontawesome.io',
                    'detected' => true,
                ],
                'PHP' => [
                    'cats' => [27],
                    'cookies' => ['PHPSESSID' => ''],
                    'headers' => [
                        'Server' => 'php/?([\d.]+)?\;version:\1',
                        'X-Powered-By' => '^php/?([\d.]+)?\;version:\1',
                    ],
                    'icon' => 'PHP.svg',
                    'url' => '\.php(?:$|\?)',
                    'website' => 'http://php.net',
                    'detected' => true,
                    'version' => '7.2.7',
                    'cpe' => 'cpe:/a:php:php'
                ],
                'WordPress' => [
                    'cats' => [
                        0 => 1,
                        1 => 11,
                    ],
                    'html' => [
                        0 => '<link rel=["\']stylesheet["\'] [^>]+/wp-(?:content|includes)/',
                        1 => '<link[^>]+s\d+\.wp\.com',
                    ],
                    'icon' => 'WordPress.svg',
                    'implies' => [
                        0 => 'PHP',
                        1 => 'MySQL'
                    ],
                    'js' => [
                        'wp_username' => ''
                    ],
                    'meta' => ['generator' => '^WordPress ?([\d.]+)?\;version:\1'],
                    'script' => '/wp-(?:content|includes)/',
                    'website' => 'https://wordpress.org',
                    'detected' => true,
                    'version' =>  '4.9.7',
                    'headers' => [
                        'link' => 'rel="https://api\.w\.org/"'
                    ],
                ],
                'Yoast SEO' => [
                    'cats' => [ 54 ],
                    'html' => [ 
                        '<!-- This site is optimized with the Yoast (?:WordPress )?SEO plugin v([\d.]+) -\;version:\1',
                    ],
                    'icon' => 'Yoast SEO.png',
                    'implies' => 'WordPress',
                    'website' => 'http://yoast.com',
                    'detected' => 1,
                    'version' => '7.8',
                ],
                'MySQL' => [
                    'cats' => [ 34 ],
                    'icon' => 'MySQL.svg',
                    'website' => 'http://mysql.com',
                    'cpe' => 'cpe:/a:mysql:mysql',
                ],
                'Laravel' => [
                    'cats' => [18],
                    'cookies' => [
                        'laravel_session' => ''
                    ],
                    'icon' => 'Laravel.svg',
                    'implies' => 'PHP',
                    'js' => [
                        'Laravel' => ''
                    ],
                    'website' => 'https://laravel.com',
                    'detected' => true,
                    'cpe' => 'cpe:/a:laravel:laravel'
                ]
            ]
        ], $wappalyzer->analyze('http://localhost'));
    }
}
