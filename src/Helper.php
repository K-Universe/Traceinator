<?php

namespace K_Universe\Tracenator;

use DOMElement;

class Helper {
    public static function removeEmptyLines(string $content): string {
        return preg_replace('/^\h*\v+/m', '', $content);
    }

    public static function isDuplicate(string $location, DOMElement $urlset): bool {
        foreach ($urlset->getElementsByTagName('loc') as $loc) {
            if ($loc->nodeValue === $location)
                return true;
        }
        return false;
    }
}