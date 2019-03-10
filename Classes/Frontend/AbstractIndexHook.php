<?php

namespace WEBcoast\VersatileCrawler\Frontend;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use WEBcoast\VersatileCrawler\Controller\QueueController;
use WEBcoast\VersatileCrawler\Crawler\FrontendRequestCrawler;
use WEBcoast\VersatileCrawler\Domain\Model\Item;
use WEBcoast\VersatileCrawler\Queue\Manager;
use WEBcoast\VersatileCrawler\Utility\TypeUtility;

abstract class AbstractIndexHook implements SingletonInterface
{
    const HASH_HEADER = 'X-Versatile-Crawler-Hash';

    public function indexPage(&$incomingParameters)
    {
        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $typoScriptFrontendController */
        $typoScriptFrontendController = &$incomingParameters['pObj'];

        $hashHeader = null;
        // find the hash header
        foreach($_SERVER as $headerName => $headerValue) {
            if (strtolower($headerName) === 'http_' . strtolower(str_replace('-', '_', self::HASH_HEADER))) {
                $hashHeader = $headerValue;
                break;
            }
        }
        if (is_array($hashHeader)) {
            $hashHeader = array_shift($hashHeader);
        }

        if ($hashHeader !== null && strcmp($hashHeader, '') !== 0) {
            try {
                $queueManager = GeneralUtility::makeInstance(Manager::class);
                $itemResult = $queueManager->getItemForProcessing($hashHeader);
                $itemRecord = $itemResult->fetch();
                $result = [
                    'state' => 'success',
                    'message' => ''
                ];
                if (!is_array($itemRecord)) {
                    throw new \RuntimeException('No item found for processing.');
                }
                $item = $queueManager->getFromRecord($itemRecord);
                $configurationResult = $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable(QueueController::CONFIGURATION_TABLE)
                    ->select(['*'], QueueController::CONFIGURATION_TABLE, ['uid' => $item->getConfiguration()]);
                $configuration = $configurationResult->fetch();
                if (!is_array($configuration)) {
                    throw new \RuntimeException(
                        sprintf(
                            'The configruation record with the id %d could not be fetched',
                            $item->getConfiguration()
                        )
                    );
                }
                $className = TypeUtility::getClassForType($configuration['type']);
                $crawler = GeneralUtility::makeInstance($className);
                if (!$crawler instanceof FrontendRequestCrawler) {
                    throw new \RuntimeException(
                        sprintf(
                            'The class "%s" must extend "%s", to be used for frontend indexing.',
                            get_class($crawler),
                            FrontendRequestCrawler::class
                        )
                    );
                }
                if (!$crawler->isIndexingAllowed($item, $typoScriptFrontendController)) {
                    throw new \RuntimeException('The indexing was denied. This should not happen.');
                }
                $this->processIndexing($item, $typoScriptFrontendController, $crawler);
                $item->setState(Item::STATE_SUCCESS);
            } catch (\Exception $e) {
                if (isset($item) && $item instanceof Item) {
                    $item->setState(Item::STATE_ERROR);
                }
                $result['state'] = 'error';
                $result['message'] = $e->getMessage();
                header(HttpUtility::HTTP_STATUS_500);
            } finally {
                header('Content-type: application/json');
                $typoScriptFrontendController->content = json_encode($result);
            }
        }
    }

    abstract public function processIndexing(Item $item, TypoScriptFrontendController &$typoScriptFrontendController, FrontendRequestCrawler $crawler);
}
