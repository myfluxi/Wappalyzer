# PHP Wappalyzer library

[![Build Status](https://travis-ci.org/madeITBelgium/Wappalyzer.svg?branch=master)](https://travis-ci.org/madeITBelgium/Wappalyzer)
[![Coverage Status](https://coveralls.io/repos/github/madeITBelgium/Wappalyzer/badge.svg?branch=master)](https://coveralls.io/github/madeITBelgium/Wappalyzer?branch=master)
[![Latest Stable Version](https://poser.pugx.org/madeITBelgium/Wappalyzer/v/stable.svg)](https://packagist.org/packages/madeITBelgium/Wappalyzer)
[![Latest Unstable Version](https://poser.pugx.org/madeITBelgium/Wappalyzer/v/unstable.svg)](https://packagist.org/packages/madeITBelgium/Wappalyzer)
[![Total Downloads](https://poser.pugx.org/madeITBelgium/Wappalyzer/d/total.svg)](https://packagist.org/packages/madeITBelgium/Wappalyzer)
[![License](https://poser.pugx.org/madeITBelgium/Wappalyzer/license.svg)](https://packagist.org/packages/madeITBelgium/Wappalyzer)


This library is a PHP version Fork of the Wappalyzer utility that uncovers the technologies used on websites. It detects content management systems, eCommerce platforms, web servers, JavaScript frameworks, analytics tools and many more.

# Installation

Require this package in your `composer.json` and update composer.

```php
"madeitbelgium/wappalyzer": "~1.0"
```

# Documentation
## Usage
```php
use MadeITBelgium\Wappalyzer\Wappalyzer;
$wappalyzer = new Wappalyzer('https://raw.githubusercontent.com/madeITBelgium/Wappalyzer/master/src/apps.json');
$wappalyzer->analyze('http://www.example.com');
```

## Usage Laravel Facade
```php
$analyze = Wappalyzer::analyze('http://www.example.com');
```

The complete documentation can be found at: [http://www.madeit.be/](http://www.madeit.be/)


# Support
Support github or mail: tjebbe.lievens@madeit.be

# Contributing
Please try to follow the psr-2 coding style guide. http://www.php-fig.org/psr/psr-2/

# License
This package is licensed under LGPL. You are free to use it in personal and commercial projects. The code can be forked and modified, but the original copyright author should always be included!