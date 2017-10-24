<?php

namespace WEBcoast\VersatileCrawler\Crawler;


use WEBcoast\VersatileCrawler\Domain\Model\Item;

interface CrawlerInterface
{
    /**
     * @param \WEBcoast\VersatileCrawler\Domain\Model\Item $item
     * @param array                                        $configuration
     *
     * @return boolean
     */
    public function processQueueItem(Item $item, array $configuration);
}
