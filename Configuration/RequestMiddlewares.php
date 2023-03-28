<?php

return [
    'frontend' => [
        'versatile-crawler/indexing-middleware' => [
            'target' => \WEBcoast\VersatileCrawler\Middleware\IndexingMiddleware::class,
            'after' => [
                'typo3/cms-frontend/content-length-headers',
            ],
            // 'before' => [
            //     'typo3/cms-frontend/response-propagation',
            // ],
        ],
    ],
];
