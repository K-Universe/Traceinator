<?php

namespace K_Universe\Tracenator;

use DOMDocument;
use DOMElement;
use Exception;
use K_Universe\Tracenator\Models\URLModel;
use SimpleXMLElement;

class GenerateSitemap {
    const NAMESPACE = 'http://www.w3.org/2000/xmlns/';
    const SITEMAP_SCHEMA = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    const IMAGE_SCHEMA = 'http://www.google.com/schemas/sitemap-image/1.1';
    const ARTICLE_SCHEMA = 'http://www.google.com/schemas/sitemap-news/0.9';
    const VIDEOS_SCHEMA = 'http://www.google.com/schemas/sitemap-video/1.1';
    const XHTML_SCHEMA = 'http://www.w3.org/1999/xhtml';
    private string $siteUrl;
    private bool $generateIndex;
    private array $urls = [];
    private string $version = '1.0';
    private string $encoding = 'UTF-8';
    private bool $useSimpleXML = true;

    public function __construct(bool $generateIndex = false, string $siteUrl = null, string $version = null, string $encoding = null) {
        if (function_exists('simplexml_load_string') === false)
            $this->useSimpleXML = false;

        if ($siteUrl)
            $this->siteUrl = $siteUrl;
        if ($version)
            $this->version = $version;
        if ($encoding)
            $this->encoding = $encoding;

        $this->generateIndex = $generateIndex;
    }

    /**
     * @throws Exception
     */
    public function addUrl(array $url, string $siteindexName = 'index'): void {
        $this->checkUrl($url);
        if (!isset($this->siteUrl)):
            if (filter_var($url['location'], FILTER_VALIDATE_URL) === false)
                throw new Exception("URL " . $url['location'] . " is not valid");
            $url_info = parse_url($url['location']);
            $this->siteUrl = $url_info['scheme'] . '://' . $url_info['host'];
        endif;
        if ($this->generateIndex)
            $this->urls[$siteindexName][] = new URLModel($url);
        else
            $this->urls[] = new URLModel($url);
    }

    /**
     * @param array $url
     * @return void
     * @throws Exception
     */
    private function checkUrl(array &$url): void {
        if (!isset($url['location']))
            throw new Exception('Location is required');
        if (!isset($url['priority']))
            $url['priority'] = 0.5;
        if (!isset($url['lastModified']))
            $url['lastModified'] = null;
        if (!isset($url['changeFrequency']))
            $url['changeFrequency'] = null;
    }

    public function export(string $dir = null, string $name = null): bool {

        if (empty($this->urls))
            return false;
        if ($this->generateIndex) :
            $this->generateIndex($dir, $name);
        else:
            if ($this->useSimpleXML)
                try {
                    $this->generateFileWithSimpleXML($this->urls, 'url', $name, $dir);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            else
                $this->generateFileWithSimpleXML($this->urls, 'url', $name, $dir);
        endif;

        return true;
    }

    private function generateIndex(string $dir = null, string $name = null): void {
        $sitemaps = [];
        try {
            foreach ($this->urls as $name => $urls) {
                if ($this->useSimpleXML)
                    $sitemaps[] = $this->generateFileWithSimpleXML($urls, 'url', $name, $dir);

                else
                    $sitemaps[] = $this->generateFileBasic($urls, 'url', $name);
            }

            if ($this->useSimpleXML)
                try {
                    $this->generateFileWithSimpleXML($sitemaps, 'index', dir: $dir);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            else
                $this->generateFileBasic($sitemaps, 'index');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @throws Exception
     */
    private function generateFileWithSimpleXML(array $data, string $type, string $name = 'index', string $dir = null): array {
        $dom = new DOMDocument($this->version, $this->encoding);
        $dom->formatOutput = true;

        switch ($type):
            case 'url':
                $urlset = $dom->createElement('urlset');
                $urlset->setAttributeNS(self::NAMESPACE, 'xmlns', self::SITEMAP_SCHEMA);
                $dom->appendChild($urlset);
                foreach ($data as $url) :
                    if (Helper::isDuplicate($url->getLocation(), $urlset)) continue;
                    $url->setAsChild($urlset, $dom);
                endforeach;
                $fileName = 'sitemap' . ($this->generateIndex ? "-$name" : '') . '.xml';
                break;
            case 'index':
                if (empty(array_column($data, 'location')) || !str_contains(array_column($data, 'location')[0], '/sitemap'))
                    throw new Exception('Data are not a valid Sitemaps array');

                $sitemapindex = $dom->createElement('sitemapindex');
                $sitemapindex->setAttributeNS(self::NAMESPACE, 'xmlns', self::SITEMAP_SCHEMA);
                foreach ($data as $sitemap) :
                    $domSitemap = $dom->createElement('sitemap');
                    $domSitemap->appendChild($dom->createElement('loc', $sitemap['location']));
                    if ($sitemap['lastModified'])
                        $domSitemap->appendChild($dom->createElement('lastmod', $sitemap['lastModified']));
                    $sitemapindex->appendChild($domSitemap);
                endforeach;
                $dom->appendChild($sitemapindex);
                $fileName = 'sitemap.xml';
                break;
            default:
                throw new Exception('Invalid type');
        endswitch;

        if ($dir):
            if (!is_dir($dir))
                mkdir($dir, 0640, true);
            $filePath = rtrim($dir, '/') . '/' . $fileName;
        else:
            $filePath = rtrim(basepath(), '/') . '/' . $fileName;
        endif;
        if ($dom->save($filePath)):
            $content = file_get_contents($filePath);
            $cleanedContent = Helper::removeEmptyLines($content);
            file_put_contents($filePath, $cleanedContent);
            return [
                'location' => rtrim($this->siteUrl, '/') . '/' . $fileName,
                'lastModified' => date('c')
            ];
        endif;

        throw new Exception('Error while saving XML file');
    }


    /**
     * @throws Exception
     */
    private function generateFileBasic(array $urls, string $type, string $name = 'index'): string {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

        switch ($type):
            case 'url':
                $xml .= "<urlset xmlns=\"" . self::NAMESPACE . "\">";
                foreach ($urls as $url) :
                    $xml .= "<url>";
                    $xml .= '<loc>' . $url['location'] . "</loc>";
                    $xml .= '<priority>' . $url['priority'] . '</priority>';
                    if ($url['lastModified'])
                        $xml .= '<lastmod>' . $url['lastModified'] . '</lastmod>';
                    if ($url['changeFrequency'])
                        $xml .= '<changefreq>' . $url['changeFrequency'] . '</changefreq>';
                    $xml .= '</url>';
                endforeach;
                $xml .= '</urlset>';

                $fileName = 'sitemap' . ($this->generateIndex ? "-$name" : '') . '.xml';
                break;
            case 'sitemap':
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                $xml .= "<sitemapindex xmlns=\"" . self::NAMESPACE . "\">";
                foreach ($this->urls as $name => $urls) :
                    $xml .= '<sitemap>';
                    $xml .= '<loc>' . $this->siteUrl . '/sitemap-' . $name . '.xml</loc>';
                    $xml .= '</sitemap>';
                endforeach;
                $xml .= '</sitemapindex>';

                $fileName = 'sitemap_index.xml';
                break;
            default:
                throw new Exception('Invalid type');
        endswitch;

        $file = fopen(rtrim(basepath(), '/') . '/' . $fileName, 'w');
        fwrite($file, $xml);
        fclose($file);

        return $xml;
    }
}