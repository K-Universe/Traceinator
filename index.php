<?php

require_once 'vendor/autoload.php';

$urls = [
    [
        'location' => 'https://www.example.com/',
        'priority' => 1,
        'lastModified' => '2021-10-01',
        'changeFrequency' => 'daily',
        'alternates' => [
            [
                'location' => 'https://www.example.com/en',
                'hreflang' => 'en'
            ],
            [
                'location' => 'https://www.example.com/es',
                'hreflang' => 'es'
            ]
        ],
        'article' => [
            'author' => 'John Doe',
            'language' => 'en',
            'publish_date' => '2021-10-01',
            'title' => 'Example Article'
        ],
        'videos' => [
            [
                'title' => 'Example Video',
                'description' => 'This is an example video',
                'thumbnail_url' => 'https://www.example.com/images/thumbnail.jpg',
                'content_url' => 'https://www.example.com/videos/video.mp4',
                'player_url' => 'https://www.example.com/videos/player',
                'duration' => 60,
                'expiration_date' => '2022-10-01',
                'rating' => 4.5,
                'view_count' => 1000,
                'publication_date' => '2021-10-01',
                'family_friendly' => true,
                'restriction' => [
                    'relationship' => 'allow',
                    'country' => 'IE GB US CA'
                ],
                'platform' => [
                    'relationship' => 'allow',
                    'platform' => 'tv mobile'
                ],
                'requires_subscription' => false,
                'uploader' => 'John Doe',
                'live' => false,
                'tag' => 'example'
            ]
        ],
        'images' => [
            'https://www.example.com/images/image-1.jpg',
            'https://www.example.com/images/image-2.jpg',
        ]
    ],
    [
        'location' => 'https://www.example.com/about-us',
        'priority' => 0.9,
        'lastModified' => '2021-10-01',
        'changeFrequency' => 'never'
    ],
    [
        'location' => 'https://www.example.com/company',
        'priority' => 0.8,
        'lastModified' => '2021-10-01',
        'changeFrequency' => 'never'
    ],
    [
        'location' => 'https://www.example.com/contact',
        'priority' => 0.9
    ],
    [
        'location' => 'https://www.example.com/products',
        'priority' => 0.7
    ],
    [
        'location' => 'https://www.example.com/products/product-1',
        'priority' => 0.5
    ],
    [
        'location' => 'https://www.example.com/products/product-2',
        'priority' => 0.5
    ],
    [
        'location' => 'https://www.example.com/products/product-3',
        'priority' => 0.5
    ]
];

$images = [
    [
        'location' => 'https://www.example.com/',
        'priority' => 1,
        'lastModified' => '2021-10-01',
        'changeFrequency' => 'daily',
        'image' => [
            [
                'location' => 'https://www.example.com/images/image-1.jpg',
            ],
            [
                'location' => 'https://www.example.com/images/image-2.jpg',
            ]
        ]
    ],
    [
        'location' => 'https://www.example.com/about-us',
        'priority' => 0.9,
        'lastModified' => '2021-10-01',
        'changeFrequency' => 'never'
    ],
    [
        'location' => 'https://www.example.com/company',
        'priority' => 0.8,
        'lastModified' => '2021-10-01',
        'changeFrequency' => 'never'
    ],
    [
        'location' => 'https://www.example.com/contact',
        'priority' => 0.9
    ],
    [
        'location' => 'https://www.example.com/products',
        'priority' => 0.7
    ],
    [
        'location' => 'https://www.example.com/products/product-1',
        'priority' => 0.5
    ],
    [
        'location' => 'https://www.example.com/products/product-2',
        'priority' => 0.5
    ],
    [
        'location' => 'https://www.example.com/products/product-3',
        'priority' => 0.5
    ]
];


$siteMap = new K_Universe\Tracenator\GenerateSitemap(false);
foreach ($urls as $url) {
    $siteMap->addUrl($url);
}
foreach ($images as $url) {
    $siteMap->addUrl($url, 'images');
}
$siteMap->export();