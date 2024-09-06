<?php

namespace K_Universe\Tracenator\Models;

use DateTime;
use DOMDocument;
use DOMElement;

class ArticleModel {
    protected string $author;
    protected string $language;
    protected DateTime $publish_date;
    protected string $title;

    public function __construct(string $author, string $language, string $publish_date, string $title) {
        $this->author = $author;
        $this->language = $language;
        $this->publish_date = new DateTime($publish_date);
        $this->title = $title;
    }

    public function setAsChild(DOMElement &$parent, DOMDocument $dom): void {
        $news = $dom->createElement('news:news');
        $publication = $dom->createElement('news:publication');
        $publication->appendChild($dom->createElement('news:name', $this->author));
        $publication->appendChild($dom->createElement('news:language', $this->language));
        $news->appendChild($dom->createElement('news:publication_date', $this->publish_date->format('Y-m-d')));
        $news->appendChild($dom->createElement('news:title', $this->title));
        $news->appendChild($publication);
        $parent->appendChild($news);
    }
}