<?php

namespace WEBcoast\VersatileCrawler\Crawler;


interface QueueInterface
{
    /**
     * @param array $configuration
     * @param array $rootConfiguration
     *
     * @return boolean
     */
    public function fillQueue(array $configuration, array $rootConfiguration = null);
}
