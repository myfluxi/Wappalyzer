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
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testHtml()
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
                'x-github-request-id' => 'DA9A:AF74:3CF1A7:47D586:615EFC90'
            ], file_get_contents(__DIR__ . '/site.html')),
        ]);
        
        
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler, 'cookies' => $cookieJar]);
        
        $wappalyzer = new Wappalyzer($client);
        $this->assertEquals([
            'url' => 'https://www.madeit.be/',
            'language' => 'nl-BE',
            'detected' => [
                'Font Awesome' => [
                    'cats' => [17],
                    'detected' => true,
                    "description" => "Font Awesome is a font and icon toolkit based on CSS and Less.",
                    "html" => [
                        "<link[^>]* href=[^>]+(?:([\\d.]+)/)?(?:css/)?font-awesome(?:\\.min)?\\.css\\;version:\\1",
                        "<link[^>]* href=[^>]*kit\\-pro\\.fontawesome\\.com/releases/v([0-9.]+)/\\;version:\\1"
                    ],
                    "icon" => "font-awesome.svg",
                    "js" => [
                        "FontAwesomeCdnConfig" => "",
                        "___FONT_AWESOME___" => ""
                    ],
                    "pricing" => [
                        "low",
                        "freemium",
                        "recurring"
                    ],
                    "scriptSrc" => [
                        "(?:F|f)o(?:n|r)t-?(?:A|a)wesome(?:.*?([0-9a-fA-F]{7,40}|[\\d]+(?:.[\\d]+(?:.[\\d]+)?)?)|)",
                        "\\.fontawesome\\.com/([0-9a-z]+).js"
                    ],
                    "website" => "https://fontawesome.com/"
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
                    'cpe' => 'cpe:/a:php:php',
                    "description" => "PHP is a general-purpose scripting language used for web development.",
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
                    'meta' => [
                        'generator' => '^WordPress(?: ([\d.]+))?\;version:\1',
                        "shareaholic:wp_version" => ""
                    ],
                    'website' => 'https://wordpress.org',
                    'detected' => true,
                    'version' =>  '4.9.7',
                    'headers' => [
                        'link' => 'rel="https://api\.w\.org/"',
                        'X-Pingback' => '/xmlrpc\.php$',
                    ],
                    "cpe" => "cpe:/a:wordpress:wordpress",
                    "description" => "WordPress is a free and open-source content management system written in PHP and paired with a MySQL or MariaDB database. Features include a plugin architecture and a template system.",
                    "pricing" => [
                        "low",
                        "recurring",
                        "freemium"
                    ],
                    "saas" => true,
                    "scriptSrc" => [
                        "/wp-(?:content|includes)/",
                        "wp-embed\\.min\\.js"
                    ],
                ],
                'Yoast SEO' => [
                    'cats' => [ 54, 87 ],
                    "description" => "Yoast SEO is a search engine optimisation plugin for WordPress and other platforms.",
                    "dom" => [
                        "script.yoast-schema-graph" => [
                            "attributes" => [
                                "class" => ""
                            ]
                        ]
                    ],
                    "html" => "<!-- This site is optimized with the Yoast (?:WordPress )?SEO plugin v([\\d.]+) -\\;version:\\1",
                    "icon" => "Yoast SEO.png",
                    "requires" => "WordPress",
                    "website" => "https://yoast.com",
                    'detected' => true,
                    'version' => '7.8',
                ],
                'MySQL' => [
                    'cats' => [ 34 ],
                    'icon' => 'MySQL.svg',
                    'website' => 'http://mysql.com',
                    'cpe' => 'cpe:/a:mysql:mysql',
                    "description" => "MySQL is an open-source relational database management system.",
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
                    'cpe' => 'cpe:/a:laravel:laravel',
                    "description" => "Laravel is a free, open-source PHP web framework.",
                ],
                'GitHub Pages' => [
                    'cats' => [
                        0 => 62,
                    ],
                    'description' => 'GitHub Pages is a static site hosting service.',
                    'headers' => [
                        'Server' => '^GitHub\\.com$',
                        'X-GitHub-Request-Id' => '',
                    ],
                    'icon' => 'GitHub.svg',
                    'implies' => 'Ruby on Rails',
                    'url' => '^https?://[^/]+\\.github\\.io',
                    'website' => 'https://pages.github.com/',
                    'detected' => true,
                ],
                'Ruby on Rails' => [
                    'cats' => [
                        0 => 18,
                    ],
                    'cookies' => [
                        '_session_id' => '\\;confidence:75',
                    ],
                    'cpe' => 'cpe:/a:rubyonrails:rails',
                    'description' => 'Ruby on Rails is a server-side web application framework written in Ruby under the MIT License.',
                    'headers' => [
                        'Server' => 'mod_(?:rails|rack)',
                        'X-Powered-By' => 'mod_(?:rails|rack)',
                    ],
                    'icon' => 'Ruby on Rails.png',
                    'implies' => 'Ruby',
                    "js" => [
                        "ReactOnRails" => "",
                      "__REACT_ON_RAILS_EVENT_HANDLERS_RAN_ONCE__" => ""
                    ],
                    'meta' => [
                        'csrf-param' => '^authenticity_token$\\;confidence:50',
                    ],
                    'scriptSrc' => '/assets/application-[a-z\\d]{32}/\\.js\\;confidence:50',
                    'website' => 'https://rubyonrails.org',
                ],
                'Ruby' => [
                    'cats' => [
                        0 => 27,
                    ],
                    'cpe' => 'cpe:/a:ruby-lang:ruby',
                    'description' => 'Ruby is an open-source object-oriented programming language.',
                    'headers' => [
                        'Server' => '(?:Mongrel|WEBrick|Ruby)',
                    ],
                    'icon' => 'Ruby.png',
                    'website' => 'http://ruby-lang.org',
                ],
            ]
        ], $wappalyzer->analyze('https://www.madeit.be/'));
    }

    public function testHtmlFile()
    {
        $jar = new CookieJar;
        $cookieJar = $jar->fromArray(['laravel_session' => 'ABC'], 'localhost');
        $js = ['adroll_adv_id', 'foo', 'bar'];
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
        $client = new Client(['handler' => $handler, 'cookies' => $cookieJar, 'js' => $js]);
        
        $wappalyzer = new Wappalyzer($client);
        $this->assertEquals([
            'url' => 'https://www.madeit.be/',
            'language' => 'nl-BE',
            'detected' => [
                'Font Awesome' => [
                    'cats' => [17],
                    'detected' => true,
                    "description" => "Font Awesome is a font and icon toolkit based on CSS and Less.",
                    "html" => [
                        "<link[^>]* href=[^>]+(?:([\\d.]+)/)?(?:css/)?font-awesome(?:\\.min)?\\.css\\;version:\\1",
                        "<link[^>]* href=[^>]*kit\\-pro\\.fontawesome\\.com/releases/v([0-9.]+)/\\;version:\\1"
                    ],
                    "icon" => "font-awesome.svg",
                    "js" => [
                        "FontAwesomeCdnConfig" => "",
                        "___FONT_AWESOME___" => ""
                    ],
                    "pricing" => [
                        "low",
                        "freemium",
                        "recurring"
                    ],
                    "scriptSrc" => [
                        "(?:F|f)o(?:n|r)t-?(?:A|a)wesome(?:.*?([0-9a-fA-F]{7,40}|[\\d]+(?:.[\\d]+(?:.[\\d]+)?)?)|)",
                        "\\.fontawesome\\.com/([0-9a-z]+).js"
                    ],
                    "website" => "https://fontawesome.com/"
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
                    'cpe' => 'cpe:/a:php:php',
                    "description" => "PHP is a general-purpose scripting language used for web development.",
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
                    'meta' => [
                        'generator' => '^WordPress(?: ([\d.]+))?\;version:\1',
                        "shareaholic:wp_version" => ""
                    ],
                    'website' => 'https://wordpress.org',
                    'detected' => true,
                    'version' =>  '4.9.7',
                    'headers' => [
                        'link' => 'rel="https://api\.w\.org/"',
                        'X-Pingback' => '/xmlrpc\.php$',
                    ],
                    "cpe" => "cpe:/a:wordpress:wordpress",
                    "description" => "WordPress is a free and open-source content management system written in PHP and paired with a MySQL or MariaDB database. Features include a plugin architecture and a template system.",
                    "pricing" => [
                        "low",
                        "recurring",
                        "freemium"
                    ],
                    "saas" => true,
                    "scriptSrc" => [
                        "/wp-(?:content|includes)/",
                        "wp-embed\\.min\\.js"
                    ],
                ],
                'Yoast SEO' => [
                    'cats' => [ 54, 87 ],
                    "description" => "Yoast SEO is a search engine optimisation plugin for WordPress and other platforms.",
                    "dom" => [
                        "script.yoast-schema-graph" => [
                            "attributes" => [
                                "class" => ""
                            ]
                        ]
                    ],
                    "html" => "<!-- This site is optimized with the Yoast (?:WordPress )?SEO plugin v([\\d.]+) -\\;version:\\1",
                    "icon" => "Yoast SEO.png",
                    "requires" => "WordPress",
                    "website" => "https://yoast.com",
                    'detected' => true,
                    'version' => '7.8',
                ],
                'MySQL' => [
                    'cats' => [ 34 ],
                    'icon' => 'MySQL.svg',
                    'website' => 'http://mysql.com',
                    'cpe' => 'cpe:/a:mysql:mysql',
                    "description" => "MySQL is an open-source relational database management system.",
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
                    'cpe' => 'cpe:/a:laravel:laravel',
                    "description" => "Laravel is a free, open-source PHP web framework.",
                ],
                'AdRoll' => [
                    'cats' => [
                        0 => 36,
                        1 => 77,
                    ],
                    'description' => 'AdRoll is a digital marketing technology platform that specializes in retargeting.',
                    'icon' => 'AdRoll.svg',
                    'js' => [
                        'adroll_adv_id' => '',
                        'adroll_pix_id' => '',
                    ],
                    'pricing' => [
                        0 => 'low',
                        1 => 'recurring',
                    ],
                    'saas' => true,
                    'scriptSrc' => '(?:a|s)\\.adroll\\.com',
                    'website' => 'http://adroll.com',
                    'detected' => true,
                ]
            ]
        ], $wappalyzer->analyze('https://www.madeit.be/'));
    }
}
