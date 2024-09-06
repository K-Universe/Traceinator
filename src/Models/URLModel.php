<?php

namespace K_Universe\Tracenator\Models;

use DOMDocument;
use DOMElement;
use Exception;
use InvalidArgumentException;
use K_Universe\Tracenator\GenerateSitemap;

class URLModel {
    protected string $location;
    protected float $priority;
    protected string $lastModified;
    protected string $changeFrequency;
    protected array $images;
    protected array $videos;
    protected array $alternates;
    protected ArticleModel $article;

    public function __construct(array $attributes) {
        if (!isset($attributes['location']))
            throw new Exception('Location is missing in URL data');
        if (!filter_var($attributes['location'], FILTER_VALIDATE_URL))
            throw new InvalidArgumentException('Invalid URL');
        $this->location = $attributes['location'];
        if (!isset($attributes['priority']))
            throw new Exception('Location is missing in URL data');
        $this->priority = floatval($attributes['priority']);
        $this->lastModified = $attributes['lastModified'] ?? '';
        $this->changeFrequency = $attributes['changeFrequency'] ?? '';
        if (isset($attributes['images']))
            $this->setImages($attributes['images']);
        if (isset($attributes['article']))
            $this->setArticle($attributes['article']);
        if (isset($attributes['videos']))
            $this->setVideos($attributes['videos']);
        if (isset($attributes['alternates']))
            $this->setAlternates($attributes['alternates']);
    }

    /**
     * Images have an limit of 1000
     *
     * @param string $image URL link to image
     * @return void
     */
    public function addImage(string $image): void {
        if (filter_var($image, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL');
        }
        if (!isset($this->images)) $this->images = [];
        if (count($this->images) >= 1000)
            return;
        $this->images[] = $image;
    }

    public function getArticle(DOMElement $parent, DOMDocument &$dom): void {
        $this->article->setAsChild($parent, $dom);
    }

    public function setArticle(array $article): void {
        if (!isset($article['author']))
            throw new Exception('Author is missing in article data');
        if (!isset($article['language']))
            throw new Exception('Language is missing in article data');
        if (!isset($article['publish_date']))
            throw new Exception('Publish date is missing in article data');
        if (!isset($article['title']))
            throw new Exception('Title is missing in article data');
        $this->article = new ArticleModel($article['author'], $article['language'], $article['publish_date'], $article['title']);
    }

    public function setAsChild(DOMElement &$parent, DOMDocument $dom) {
        $domUrl = $dom->createElement('url');
        $domUrl->appendChild($dom->createElement('loc', $this->location));
        $domUrl->appendChild($dom->createElement('priority', number_format($this->priority, 1)));
        if (isset($this->lastModified) && $this->lastModified != "")
            $domUrl->appendChild($dom->createElement('lastmod', $this->lastModified));
        if (isset($this->changeFrequency) && $this->changeFrequency != "")
            $domUrl->appendChild($dom->createElement('changefreq', $this->changeFrequency));

        if ($this->hasImages()):
            $dom->getElementsByTagName('urlset')[0]->setAttribute('xmlns:image', GenerateSitemap::IMAGE_SCHEMA);
            $this->getImages($domUrl, $dom);
        endif;

        if ($this->hasArticle()):
            $dom->getElementsByTagName('urlset')[0]->setAttribute('xmlns:news', GenerateSitemap::ARTICLE_SCHEMA);
            $this->getArticle($domUrl, $dom);
        endif;

        if ($this->hasVideos()):
            $dom->getElementsByTagName('urlset')[0]->setAttribute('xmlns:video', GenerateSitemap::VIDEOS_SCHEMA);
            $this->getVideos($domUrl, $dom);
        endif;

        if ($this->hasAlternates()):
            $dom->getElementsByTagName('urlset')[0]->setAttribute('xmlns:xhtml', GenerateSitemap::XHTML_SCHEMA);
            $this->getAlternates($domUrl, $dom);
        endif;

        $parent->appendChild($domUrl);
    }

    /**
     * @return bool Returns true if the URL has images
     */
    public function hasImages(): bool {
        return !empty($this->images);
    }

    public function getImages(DOMElement $parent, DOMDocument &$dom): void {
        foreach ($this->images as $image) {
            $imageElement = $dom->createElement('image:image');
            $imageElement->appendChild($dom->createElement('image:loc', $image));
            $parent->appendChild($imageElement);
        }
    }

    /**
     * @param array $images Array with URL links to videos
     * @return void
     */
    public function setImages(array $images): void {
        $this->images = [];
        if (!empty($images))
            foreach ($images as $image):
                if (filter_var($image, FILTER_VALIDATE_URL) === false) continue;
                $this->images[] = $image;
            endforeach;
    }

    public function hasArticle(): bool {
        return isset($this->article);
    }

    public function hasVideos(): bool {
        return !empty($this->videos);
    }

    public function getVideos(DOMElement $parent, DOMDocument &$dom): void {
        foreach ($this->videos as $video) {
            $video->setAsChild($parent, $dom);
        }
    }

    public function setVideos(array $videos): void {
        $this->videos = [];
        if (!empty($videos))
            foreach ($videos as $video)
                $this->videos[] = new VideoModel($video['title'], $video['thumbnail_url'], $video['description'], $video['content_url'] ?? null, $video['player_url'] ?? null, $video['duration'] ?? null, $video['expiration_date'] ?? null, $video['rating'] ?? null, $video['view_count'] ?? null, $video['publication_date'] ?? null, $video['family_friendly'] ?? null, $video['restriction'] ?? null, $video['platform'] ?? null, $video['requires_subscription'] ?? null, $video['uploader'] ?? null, $video['live'] ?? null, $video['tag'] ?? null);
    }

    public function hasAlternates(): bool {
        return !empty($this->alternates);
    }

    public function getAlternates(DOMElement $parent, DOMDocument &$dom): void {
        foreach ($this->alternates as $alternate) {
            $xhtml = $dom->createElement('xhtml:link');
            $xhtml->setAttribute('rel', 'alternate');
            $xhtml->setAttribute('hreflang', $alternate['hreflang']);
            $xhtml->setAttribute('href', $alternate['location']);
            $parent->appendChild($xhtml);
        }
    }

    public function setAlternates(array $alternates): void {
        $this->alternates = [];
        if (!empty($alternates))
            foreach ($alternates as $alternate)
                $this->alternates[] = $alternate;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function addVideo(array $video): void {
        $this->videos[] = new VideoModel(...$video);
    }
}