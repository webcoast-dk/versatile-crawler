<?php

namespace WEBcoast\VersatileCrawler\Crawler;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use WEBcoast\VersatileCrawler\Domain\Model\Item;
use WEBcoast\VersatileCrawler\Frontend\IndexHook;
use WEBcoast\VersatileCrawler\Queue\Manager;

abstract class FrontendRequestCrawler implements CrawlerInterface
{
    abstract public function isIndexingAllowed(Item $item, TypoScriptFrontendController $typoScriptFrontendController);

    abstract public function buildRequestUrl(Item $item, array $configuration);

    public function processQueueItem(Item $item, array $configuration)
    {
        $url = $this->buildRequestUrl($item, $configuration);
        $hash = md5($item->getIdentifier() . time());
        $queueManager = GeneralUtility::makeInstance(Manager::class);
        $queueManager->prepareItemForProcessing($item->getConfiguration(), $item->getIdentifier(), $hash);
        $curlHandle = curl_init();
        curl_setopt_array(
            $curlHandle,
            [
                CURLOPT_URL => $url,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HTTPHEADER => [IndexHook::HASH_HEADER . ': ' . $hash]
            ]
        );
        $content = curl_exec($curlHandle);
        $response = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        if ($response['http_code'] >= 200 && $response['http_code'] <= 300) {
            $item->setState(Item::STATE_SUCCESS);
        } else {
            $result = json_decode($content, true);
            $item->setState(Item::STATE_ERROR);
            if (is_array($result)) {
                $item->setMessage($result['message']);
            }
        }

        return true;
    }
}
