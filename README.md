# Tracenator

Tracenator is a PHP library designed to generate sitemaps in XML format.

## Features

- Generate XML sitemaps for your website.
- Support for multiple types of sitemaps including URL, index, and more.
- Avoids duplicate URL entries.
- Supports additional elements like images, videos, articles and different languages in sitemaps.
- Automatically sets site url

## Installation

You can install Tracenator via Composer. Run the following command in your project directory:

```sh 
composer require k_universe/tracenator
```

## Usage

Here is an example of how to generate a URL sitemap:

```php
<?php

$siteMap = new K_Universe\Tracenator\GenerateSitemap();
foreach ($urls as $url) {
    $siteMap->addUrl([
        'location' => 'https://example.com/',
        'priority' => 0.8,
        'images' => [
            'https://example.com/image.jpg',
            'https://example.com/image-1.jpg',
        ],
        'alternates' => [
            [
                'href' => 'https://example.com/de/',
                'hreflang' => 'de',
            ],
            [
                'href' => 'https://example.com/cs/',
                'hreflang' => 'cs',
            ]
        ],
        'article' => [
            'author' => 'John Doe',
            'publication_date' => '2021-01-01',
            'title' => 'Example Article',
            'description' => 'This is an example article.',
        ],
        'videos' => [
            [
                'thumbnail_url' => 'https://example.com/video.jpg',
                'title' => 'Example Video',
                'description' => 'This is an example video.',
                // either content_url or player_url is required
                'content_url' => 'https://example.com/video.mp4',
                'player_url' => 'https://example.com/videoplayer.php?video=123',
                // optional parameters
                'duration' => '600',
                'expiration_date' => '2021-11-05T19:20:30+08:00',
                'rating' => 4.2,
                'view_count' => 1000,
                'publication_date' => '2007-11-05T19:20:30+08:00',
                'family_friendly' => true,
                'restriction' => [
                    'relationship' => 'allow', // only allow or denied
                    'value' => 'IE GB US CA',
                ],
                'platform' => [
                    'relationship' => 'allow', // only allow or denied
                    'platform' => 'tv mobile web' // only available values spaced
                ],
                'requires_subscription' => true,
                'uploader' => [
                    'info' => 'https://example.com/user/123',
                    'name' => 'John Doe',
                ],
                'live' => true
            ],
        ],
    ]);
}
// generates sitemap in base directory
$siteMap->export();
```

### GenerateSitemap

This class is used to generate sitemaps. It has the following methods:

- `addUrl(array $urlData)`: Adds a URL to the sitemap. The URL data is an associative array. Needs to be in format above
- `export(string $dir = null, string $name = null)`: Exports the sitemap to an XML file in the base directory of the
  project. You can specify output directory and file name.