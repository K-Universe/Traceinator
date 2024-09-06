<?php

namespace K_Universe\Tracenator;

use K_Universe\Tracenator\Models\URLModel;
use PHPUnit\Framework\TestCase;

class GenerateSitemapTest extends TestCase {

    public function testAddUrl() {
        $generateSitemap = new GenerateSitemap();
        $url = [
            'location' => 'https://example.com',
            'lastmod' => '2021-01-01',
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];
        $generateSitemap->addUrl($url);
        $urls = $generateSitemap->getUrls();
        $this->assertNotEmpty($urls);
        foreach ($urls as $url) {
            $this->assertInstanceOf(URLModel::class, $url);
        }
    }

    public function testExport() {
        $generateSitemap = new GenerateSitemap();
        $url = [
            'location' => 'https://example.com',
            'lastmod' => '2021-01-01',
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];
        $generateSitemap->addUrl($url);
        $this->assertTrue($generateSitemap->export());
    }
}
