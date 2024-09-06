<?php

namespace K_Universe\Tracenator\Models;

use DateTime;
use DOMDocument;
use DOMElement;
use Exception;

class VideoModel {
    protected string $title;
    protected string $thumbnail_url;
    protected string $description;
    protected string $content_url;
    protected string $player_url;

    // Optional properties
    protected int $duration;
    protected DateTime $expiration_date;
    protected float $rating;
    protected int $view_count;
    protected DateTime $publication_date;
    protected bool $family_friendly;
    protected array $restriction;
    protected array $platform;
    protected bool $requires_subscription;
    protected string $uploader;
    protected bool $live;
    protected string $tag;

    public function __construct(string $title, string $thumbnail_url, string $description, string $content_url = null, string $player_url = null, int $duration = null, string $expiration_date = null, float $rating = null, int $view_count = null, string $publication_date = null, bool $family_friendly = null, array $restriction = null, array $platform = null, bool $requires_subscription = null, string $uploader = null, bool $live = null, string $tag = null) {
        if (!$content_url && !$player_url)
            throw new Exception('Content URL or Player URL is required');
        $this->title = $title;

        if (filter_var($thumbnail_url, FILTER_VALIDATE_URL) === false)
            throw new Exception("URL '" . $thumbnail_url . "' is not valid");
        $this->thumbnail_url = $thumbnail_url;
        $this->description = $description;
        if ($content_url)
            $this->content_url = $content_url;
        if ($player_url)
            $this->player_url = $player_url;
        if ($duration && $duration > 0)
            $this->duration = $duration;
        if ($expiration_date):
            $this->expiration_date = new DateTime($expiration_date);
            if ($this->expiration_date->format('U') < time())
                unset($this->expiration_date);
        endif;
        if ($rating && $rating >= 0 && $rating <= 5)
            $this->rating = $rating;
        if ($view_count && $view_count >= 0)
            $this->view_count = $view_count;
        if ($publication_date)
            $this->publication_date = new DateTime($publication_date);
        if ($family_friendly !== null)
            $this->family_friendly = $family_friendly;
        if ($restriction):
            if (!is_array($restriction))
                throw new Exception('Restriction must be an array');
            if (!isset($restriction['relationship']) || !isset($restriction['country']))
                throw new Exception('Restriction must have relationship and country keys');
            if (!in_array($restriction['relationship'], ['allow', 'deny']))
                throw new Exception('Restriction relationship must be either allow or deny');
            $this->restriction = $restriction;
        endif;
        if ($platform):
            if (!is_array($platform))
                throw new Exception('Platform must be an array');
            if (!isset($platform['relationship']) || !isset($platform['platform']))
                throw new Exception('Platform must have relationship and platform keys');
            if (!in_array($platform['relationship'], ['allow', 'deny']))
                throw new Exception('Restriction relationship must be either allow or deny');
            if (array_diff(explode(' ', $platform['platform']), ['web', 'mobile', 'tv']))
                throw new Exception('Platform key must include web, mobile, and tv');
            $this->platform = $platform;
        endif;
        if ($requires_subscription !== null)
            $this->requires_subscription = $requires_subscription;
        if ($uploader)
            $this->uploader = $uploader;
        if ($live !== null)
            $this->live = $live;
        if ($tag)
            $this->tag = strtolower($tag);
    }

    public function setAsChild(DOMElement $parent, DOMDocument &$dom): void {
        $video = $dom->createElement('video:video');

        $video->appendChild($dom->createElement('video:thumbnail_loc', $this->thumbnail_url));
        $video->appendChild($dom->createElement('video:title', $this->title));
        $video->appendChild($dom->createElement('video:description', $this->description));

        $optionalElements = [
            'content_loc' => $this->content_url ?? null,
            'player_loc' => $this->player_url ?? null,
            'duration' => $this->duration ?? null,
            'expiration_date' => isset($this->expiration_date) ? $this->expiration_date->format('c') : null,
            'rating' => $this->rating ?? null,
            'view_count' => $this->view_count ?? null,
            'publication_date' => $this->publication_date?->format('c'),
            'family_friendly' => $this->family_friendly !== null ? ($this->family_friendly ? 'yes' : 'no') : null,
            'requires_subscription' => $this->requires_subscription !== null ? ($this->requires_subscription ? 'yes' : 'no') : null,
            'uploader' => $this->uploader ?? null,
            'live' => $this->live !== null ? ($this->live ? 'yes' : 'no') : null,
            'tag' => $this->tag ?? null,
        ];

        foreach ($optionalElements as $tag => $value)
            if ($value !== null)
                $video->appendChild($dom->createElement("video:$tag", $value));

        if ($this->restriction) {
            $restriction = $dom->createElement("video:restriction", $this->restriction['country']);
            $restriction->setAttribute('relationship', $this->restriction['relationship']);
            $video->appendChild($restriction);
        }
        if ($this->platform) {
            $platform = $dom->createElement("video:platform", $this->platform['platform']);
            $platform->setAttribute('relationship', $this->platform['relationship']);
            $video->appendChild($platform);
        }

        $parent->appendChild($video);
    }
}