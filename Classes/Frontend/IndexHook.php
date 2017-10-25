<?php

namespace WEBcoast\VersatileCrawler\Frontend;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\IndexedSearch\Indexer;
use WEBcoast\VersatileCrawler\Controller\QueueController;
use WEBcoast\VersatileCrawler\Crawler\FrontendRequestCrawler;
use WEBcoast\VersatileCrawler\Domain\Model\Item;
use WEBcoast\VersatileCrawler\Queue\Manager;
use WEBcoast\VersatileCrawler\Utility\TypeUtility;

class IndexHook implements SingletonInterface
{
    const HASH_HEADER = 'X-Versatile-Crawler-Hash';

    public function indexPage(&$incomingParameters)
    {
        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $typoScriptFrontendController */
        $typoScriptFrontendController = &$incomingParameters['pObj'];
        $typoScriptFrontendController->debug = false;

        $request = GeneralUtility::makeInstance(ServerRequestFactory::class)->fromGlobals();

        if ($request->hasHeader(self::HASH_HEADER) && strcmp($request->getHeader(self::HASH_HEADER), '') !== 0) {
            try {
                $queueManager = GeneralUtility::makeInstance(Manager::class);
                $hash = $request->getHeader(self::HASH_HEADER);
                if (is_array($hash)) {
                    $hash = array_shift($hash);
                }
                $itemResult = $queueManager->getItemForProcessing($hash);
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

    public function processIndexing(Item $item, TypoScriptFrontendController &$typoScriptFrontendController, FrontendRequestCrawler $crawler)
    {
        $data = $item->getData();
        $indexer = GeneralUtility::makeInstance(Indexer::class);
        $indexer->conf = [];
        $indexer->conf['id'] = $typoScriptFrontendController->id;
        $indexer->conf['type'] = $typoScriptFrontendController->type;
        $indexer->conf['sys_language_uid'] = $typoScriptFrontendController->sys_language_uid;
        $indexer->conf['MP'] = $typoScriptFrontendController->MP;
        $indexer->conf['gr_list'] = $typoScriptFrontendController->gr_list;
        $indexer->conf['cHash'] = $typoScriptFrontendController->cHash;
        $indexer->conf['cHash_array'] = $typoScriptFrontendController->cHash_array;
        $indexer->conf['crdate'] = $typoScriptFrontendController->page['crdate'];
        $indexer->conf['page_cache_reg1'] = $typoScriptFrontendController->page_cache_reg1;
        $indexer->conf['rootline_uids'] = [];
        foreach ($typoScriptFrontendController->config['rootLine'] as $rlkey => $rldat) {
            $indexer->conf['rootline_uids'][$rlkey] = $rldat['uid'];
        }
        $indexer->conf['content'] = $typoScriptFrontendController->content;
        $indexer->conf['indexedDocTitle'] = $typoScriptFrontendController->convOutputCharset(
            !empty($typoScriptFrontendController->altPageTitle) ? $typoScriptFrontendController->altPageTitle : $typoScriptFrontendController->indexedDocTitle
        );
        $indexer->conf['metaCharset'] = $typoScriptFrontendController->metaCharset;
        $indexer->conf['mtime'] = isset($typoScriptFrontendController->register['SYS_LASTCHANGED']) ? $typoScriptFrontendController->register['SYS_LASTCHANGED'] : $typoScriptFrontendController->page['SYS_LASTCHANGED'];
        $indexer->conf['index_externals'] = $typoScriptFrontendController->config['config']['index_externals'];
        $indexer->conf['index_descrLgd'] = $typoScriptFrontendController->config['config']['index_descrLgd'];
        $indexer->conf['index_metatags'] = isset($typoScriptFrontendController->config['config']['index_metatags']) ? $typoScriptFrontendController->config['config']['index_metatags'] : true;
        $indexer->conf['recordUid'] = $crawler->getRecordUid($item);
        $indexer->conf['freeIndexUid'] = (isset($data['rootConfigurationId']) ? $data['rootConfigurationId'] : 0);
        $indexer->conf['freeIndexSetId'] = 0;

        // use this to override `crdate` and `mtime` and other information (used for record indexing)
        $crawler->enrichIndexData($item, $typoScriptFrontendController, $indexer);

        $indexer->init();
        $indexer->indexTypo3PageContent();
    }
}
