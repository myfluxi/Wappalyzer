<?php

namespace MadeITBelgium\Wappalyzer;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * MadeITBelgium Wappalyzer PHP Library.
 *
 * @version    1.0.0
 *
 * @copyright  Copyright (c) 2018 Made I.T. (https://www.madeit.be)
 * @author     Tjebbe Lievens <tjebbe.lievens@madeit.be>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-3.txt    LGPL
 */
class Wappalyzer
{
    private $apps;
    private $categories;
    
    private $detected = [];
    private $client = null;
    
    public function __construct($app, $client = null)
    {
        if ($client === null) {
            $this->client = new Client(['cookies' => true]);
        } else {
            $this->client = $client;
        }
        
        // Remote file load 
        if (filter_var($app, FILTER_VALIDATE_URL) !== false && $this->client !== false) {
            $response = $this->client->request("GET", $app);
            if ($response->getStatusCode() == 200) {
                $appData = (string)$response->getBody();
            } else {
                throw new Exception('Cannot fetch Wappalizer data.');
            }
        } else {
            // No client available or local file
            $appData = file_get_contents($app);
        }
        
        $appParsed = json_decode($appData, true);
        $this->apps = $appParsed['apps'];
        $this->categories = $appParsed['categories'];
    }
    
    public function analyze($url)
    {
        $startTime = time();
        
        $scripts = [];
        $cookies = [];
        $js = [];
        
        if ($this->client === false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);

            $response = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $header_size);
            $html = substr($response, $header_size);

            $headers = $this->getHeadersFromResponse($headers);
        } else {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() == 200) {
                $html = (string) $response->getBody();
                $headers = $response->getHeaders();
                
                $cookieJar = $this->client->getConfig('cookies');
                $cookies = $cookieJar->toArray();
            } else {
                throw new Exception("Can't load URL.");
            }
        }
        
        // Additional information
        $language = null;

        if ($html) {
            if (!is_string($html)) {
                $html = '';
            }
            
            preg_match('/<html[^>]*[: ]lang="([a-z]{2}((-|_)[A-Z]{2})?)"/i', $html, $matches);
            
            $language = $matches && count($matches) > 0 ? $matches[1] : null;
        }
        
        foreach ($this->apps as $appName => $app) {
            $this->apps[$appName] = isset($this->detected[$appName]) ? $this->detected[$appName] : $app;
            
            $this->analyzeUrl($appName, $app, $url);
            
            $this->analyzeHtml($appName, $app, $html);
            $this->analyzeMeta($appName, $app, $html);
            
            $this->analyzeHeaders($appName, $app, $headers);
            $this->analyzeScripts($appName, $app, $html);
            $this->analyzeCookies($appName, $app, $cookies);
        }
        
        $apps = $this->detected;
        $apps = $this->resolveExcludes($apps);
        
        $implies = $this->resolveImplies($apps, $url);
        
        foreach ($implies as $appName => $app) {
            $this->detected[$appName] = $app;
            
            $this->analyzeUrl($appName, $app, $url);
            $this->analyzeHtml($appName, $app, $html);
            $this->analyzeMeta($appName, $app, $html);
            $this->analyzeHeaders($appName, $app, $headers);
            $this->analyzeScripts($appName, $app, $html);
            $this->analyzeCookies($appName, $app, $cookies);
        }
        
        return [
            'url' => $url,
            'language' => $language,
            'detected' => $this->detected,
        ];
    }
    
    public function getHeadersFromResponse($response)
    {
        $headers = [];

        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list($key, $value) = explode(': ', $line);

                $headers[strtolower($key)] = $value;
            }
        }

        return $headers;
    }
    
    /**
    * Enclose string in array
    */
    public function asArray($value)
    {
        return is_array($value) ? $value : [ $value ];
    }

    /**
    * Parse apps.json patterns
    */
    public function parsePatterns($patterns, $escape = true)
    {
        if (!$patterns) {
            return [];
        }

        $parsed = [];

        // Convert string to object containing array containing string
        if (is_string($patterns) || is_array($patterns)) {
            $patterns = $this->asArray($patterns);

            foreach ($patterns as $key => $pattern) {
                $attrs = [];

                foreach (explode('\\;', $pattern) as $i => $attr) {
                    if ($i) {
                        // Key value pairs
                        $attr = explode(":", $attr);

                        if (count($attr) > 1) {
                            $attrs[array_shift($attr)] = $attr;
                        }
                    } else {
                        $attrs['string'] = $attr;

                        try {
                            if ($escape) {
                                $attrs['regex'] = str_replace('/', '\/', $attr); // Escape slashes in regular expression
                            } else {
                                $attrs['regex'] = $attr;
                            }
                        } catch (Exception $e) {
                            $attrs['regex'] = '';
                        }
                    }
                }

                $parsed[$key] = $attrs;
            }
        }
        
        return $parsed;
    }

    /**
    * Parse JavaScript patterns
    */
    public function parseJsPatterns()
    {
        foreach (array_keys($this->apps) as $appName) {
            if ($this->apps[$appName]->js) {
                $this->jsPatterns[$appName] = $this->parsePatterns($this->apps[$appName]->js);
            }
        }
    }

    public function resolveExcludes($apps)
    {
        $excludes = [];

        // Exclude app in detected apps only
        foreach ($this->detected as $appName => $app) {
            if (isset($app['excludes'])) {
                $excludes = array_merge($excludes, $this->asArray($app['excludes']));
            }
        }

        // Remove excluded applications
        foreach ($excludes as $appName) {
            if (array_key_exists($appName, $this->apps)) {
                unset($this->apps[$appName]);
            }
            if (array_key_exists($appName, $this->detected)) {
                unset($this->detected[$appName]);
            }
            
            if (array_key_exists($appName, $apps)) {
                unset($apps[$appName]);
            }
        }
        
        return $apps;
    }

    public function resolveImplies($apps, $url)
    {
        $checkImplies = true;
        $implies = [];

        // Implied applications
        // Run several passes as implied apps may imply other apps
        while ($checkImplies) {
            $checkImplies = false;

            foreach ($apps as $appName => $app) {
                if (isset($app['implies'])) {
                    foreach ($this->asArray($app['implies']) as $implied) {
                        $implied = $this->parsePatterns($implied)[0];

                        if (!isset($this->apps[$implied['string']])) {
                            continue;
                        }

                        if (!in_array($implied['string'], array_keys($implies)) && !in_array($implied['string'], array_keys($this->detected))) {
                            $implies[$implied['string']] = $this->apps[$implied['string']];

                            $checkImplies = true;
                        }
                    }
                }
            }
        }
        
        return $implies;
    }

    /**
    * Analyze URL
    */
    public function analyzeUrl($appName, $app, $url)
    {
        if (!isset($app['url'])) {
            return;
        }
        
        $patterns = $this->parsePatterns($app['url']);

        if (count($patterns) == 0) {
            return;
        }

        foreach ($patterns as $pattern) {
            if (preg_match('~' . $pattern['regex'] . '~i', $url)) {
                $this->addDetected($appName, $app, $pattern, 'url', $url->canonical);
            }
        }
    }

    /**
    * Analyze HTML
    */
    public function analyzeHtml($appName, $app, $html)
    {
        if (!isset($app['html'])) {
            return;
        }
        
        $patterns = $this->parsePatterns($app['html'], false);

        if (count($patterns) == 0) {
            return;
        }

        foreach ($patterns as $pattern) {
            if (@preg_match('~' . $pattern['regex'] . '~i', $html)) {
                $this->addDetected($appName, $app, $pattern, 'html', $html);
            }
        }
    }

    /**
    * Analyze meta tag
    */
    public function analyzeMeta($appName, $app, $html)
    {
        if (!isset($app['meta'])) {
            return;
        }
        
        $regex = "/<meta[^>]+>/i";
        $patterns = $this->parsePatterns($app['meta']);
        
        $matches;
        if ($patterns && (preg_match_all($regex, $html, $matches))) {
            foreach ($matches as $matchs) {
                foreach ($matchs as $match) {
                    foreach ($patterns as $key => $meta) {
                        $r = '/(?:name|property)=["\']' . $key . '["\']/i';

                        if (preg_match($r, $match)) {
                            preg_match("/content=(\"|')([^\"']+)(\"|')/i", $match, $content);

                            foreach ($patterns as $patternKey => $pattern) {
                                if ($patternKey == $key && $content && count($content) === 4 && preg_match('~' . $pattern['regex'] . '~i', $content[2])) {
                                    $this->addDetected($appName, $app, $pattern, 'meta', $content[2], $meta);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
    * Analyze response headers
    */
    public function analyzeHeaders($appName, $app, $headers)
    {
        if (!isset($app['headers'])) {
            return;
        }
        
        $patterns = $this->parsePatterns($app['headers']);

        if (count($patterns) == 0) {
            return;
        }
        
        foreach ($patterns as $headerName => $pattern) {
            $headerName = strtolower($headerName);

            if (in_array($headerName, array_keys($headers))) {
                $headerValue = $headers[$headerName];
                if(is_array($headerValue)) {
                    $headerValue = $headerValue[0];
                }
                if (preg_match('~' . $pattern['regex'] . '~i', $headerValue)) {
                    $this->addDetected($appName, $app, $pattern, 'headers', $headerValue, $headerName);
                }
            }
        }
    }

    /**
    * Analyze script tag
    */
    public function analyzeScripts($appName, $app, $html)
    {
        if (!isset($app['script'])) {
            return;
        }
        
        $patterns = $this->parsePatterns($app['script']);

        if (count($patterns) == 0) {
            return;
        }
        
        $regex = "/<script[^>]+>/i";
        
        $matches;
        if ($patterns && (preg_match_all($regex, $html, $matches))) {
            foreach ($matches as $matchs) {
                foreach ($matchs as $match) {
                    foreach ($patterns as $pattern) {
                        $r = '~src=["\']' . $pattern['regex'] . '["\']~i';
                        
                        if (preg_match($r, $match)) {
                            $this->addDetected($appName, $app, $pattern, 'script', $match);
                        }
                    }
                }
            }
        }
    }

    /**
    * Analyze cookies
    */
    public function analyzeCookies($appName, $app, $cookies)
    {
        if (!isset($app['cookies'])) {
            return;
        }
        $patterns = $this->parsePatterns($app['cookies']);

        if (count($patterns) == 0) {
            return;
        }
        
        foreach ($patterns as $patternName => $pattern) {
            $patternName = strtolower($patternName);
            foreach($cookies as $cookie) {
                if($patternName === strtolower($cookie['Name'])) {
                    if (preg_match('/' . $pattern['regex'] . '/i', $cookie['Value'])) {
                        $this->addDetected($appName, $app, $pattern, 'cookies', $cookie, $cookie['Name']);
                    }
                }
            }
        }
    }

    /**
    * Analyze JavaScript variables
    */
    /*
    public function analyzeJs($app, $results)
    {
        $promises = [];

        foreach (array_keys($results) as $string) {
            if (! function_exists($results[$string])) {
                foreach (array_keys($results[$string]) as $index) {
                    $pattern = $this->jsPatterns[$app->name][$string][$index];
                    $value = $results[$string][$index];

                    if ($pattern && $pattern->regex->test($value)) {
                        $this->addDetected($app, $pattern, 'js', $value);
                    }
                }
            }
        }

        return $promises;
    }
    */

    /**
    * Mark application as detected, set confidence and version
    */
    public function addDetected($appName, $app, $pattern, $type, $value, $key = null)
    {
        $this->apps[$appName]['detected'] = true;
        
        // Detect version number
        if (isset($pattern['version'])) {
            $versions = [];
            $version  = $pattern['version'];
            
            if (preg_match('~' . $pattern['regex'] . '~i', $value, $matches)) {
                if (isset($matches[1])) {
                    $version = trim($matches[1]);
                    
                    if (strlen($version) > 0 && !in_array($version, $versions)) {
                        $versions[] = $version;
                    }
                }

                if (count($versions)) {
                    $this->apps[$appName]['version'] = $versions[count($versions) - 1];
                }
            }
        }
        $this->detected[$appName] = $this->apps[$appName];
    }
}
