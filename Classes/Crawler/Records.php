<?php

namespace WEBcoast\VersatileCrawler\Crawler;


use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\IndexedSearch\Indexer;
use WEBcoast\VersatileCrawler\Controller\QueueController;
use WEBcoast\VersatileCrawler\Domain\Model\Item;
use WEBcoast\VersatileCrawler\Queue\Manager;

class Records extends FrontendRequestCrawler
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * PageTree constructor.
     */
    public function __construct()
    {
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    /**
     * @param array $configuration
     * @param array $rootConfiguration
     *
     * @return boolean
     */
    public function fillQueue(array $configuration, array $rootConfiguration = null)
    {
        if ($rootConfiguration === null) {
            $rootConfiguration = $configuration;
        }

        // fake the frontend group list
        if (!isset($GLOBALS['TSFE'])) {
            $GLOBALS['TSFE'] = new \stdClass();
            $GLOBALS['TSFE']->gr_list = '0,-1';
        }

        $page = $this->pageRepository->getPage_noCheck($configuration['pid']);
        $storagePages = [$configuration['record_storage_page']];
        $this->getStoragePagesRecursively(
            $configuration['record_storage_page'],
            $storagePages,
            ((int)$configuration['record_storage_page_recursive'] === 0 ? null : (int)$configuration['record_storage_page_recursive'] - 1)
        );

        // fetch records
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            $configuration['table_name']
        );
        $query = $connection->createQueryBuilder()->select('*')->from($configuration['table_name']);
        $query->where($query->expr()->in('pid', $storagePages));
        if (isset($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField']) && isset($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['translationSource'])) {
            $query->andWhere(
                $query->expr()->orX(
                    $query->expr()->eq($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField'], 0),
                    $query->expr()->eq($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField'], -1),
                    $query->expr()->andX(
                        $query->expr()->gt($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField'], 0),
                        $query->expr()->eq(
                            $GLOBALS['TCA'][$configuration['table_name']]['ctrl']['translationSource'],
                            0
                        )
                    )
                )
            );
        }
        $query->setRestrictions(new FrontendRestrictionContainer());
        // allow changing the query in sub classes
        $this->alterQuery($query);

        $result = true;
        if ($statement = $query->execute()) {
            $statement->setFetchMode(\PDO::FETCH_ASSOC);
            $queueManager = GeneralUtility::makeInstance(Manager::class);
            $languages = GeneralUtility::intExplode(',', $configuration['languages']);
            foreach ($statement as $record) {
                // no language field is set or the record is set to "all languages", index it in all languages
                if (!isset($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField']) || (int)$record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField']] === -1) {
                    foreach ($languages as $language) {
                        $data = [
                            'page' => $page['uid'],
                            'sys_language_uid' => $language,
                            'rootConfigurationId' => $rootConfiguration['uid'],
                            'record_uid' => $record['uid']
                        ];
                        // add an item for the default language
                        $item = new Item(
                            $configuration['uid'],
                            md5(serialize($data)),
                            Item::STATE_PENDING,
                            '',
                            $data
                        );
                        $result = $result && $queueManager->addOrUpdateItem($item);
                    }
                } elseif ((int)$record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField']] === 0) {
                    // if the record has language "0", add this and all possible translations
                    if (in_array(0, $languages)) {
                        $data = [
                            'page' => $page['uid'],
                            'sys_language_uid' => 0,
                            'rootConfigurationId' => $rootConfiguration['uid'],
                            'record_uid' => $record['uid']
                        ];
                        // add an item for the default language
                        $item = new Item(
                            $configuration['uid'],
                            md5(serialize($data)),
                            Item::STATE_PENDING,
                            '',
                            $data
                        );
                        $result = $result && $queueManager->addOrUpdateItem($item);
                    }
                    // check overlays in other languages
                    foreach ($languages as $language) {
                        if ((int)$language !== 0) {
                            $recordOverlay = $this->pageRepository->getRecordOverlay(
                                $configuration['table_name'],
                                $record,
                                $language
                            );
                            // if there is an overlay
                            if (isset($recordOverlay['_LOCALIZED_UID'])) {
                                $data = [
                                    'page' => $page['uid'],
                                    'sys_language_uid' => $language,
                                    'rootConfigurationId' => $rootConfiguration['uid'],
                                    'record_uid' => $record['uid']
                                ];
                                // add an item for the default language
                                $item = new Item(
                                    $configuration['uid'],
                                    md5(serialize($data)),
                                    Item::STATE_PENDING,
                                    '',
                                    $data
                                );
                                $result = $result && $queueManager->addOrUpdateItem($item);
                            }
                        }
                    }
                } elseif ((int)$record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField']] > 0 && (int)$record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['translationSource']] === 0) {
                    // if there is a record in a foreign language without a parent, add only this language
                    $data = [
                        'page' => $page['uid'],
                        'sys_language_uid' => $record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['languageField']],
                        'rootConfigurationId' => $rootConfiguration['uid'],
                        'record_uid' => $record['uid']
                    ];
                    // add an item for the default language
                    $item = new Item(
                        $configuration['uid'],
                        md5(serialize($data)),
                        Item::STATE_PENDING,
                        '',
                        $data
                    );
                    $result = $result && $queueManager->addOrUpdateItem($item);
                }
            }
        }

        return $result;
    }

    protected function getStoragePagesRecursively($pageUid, &$storagePages, $level)
    {
        if ($level === null || $level > 0) {
            $query = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            $query->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
            $query->select('uid')->from('pages')
                ->where(
                    $query->expr()->eq('pid', $pageUid),
                    $query->expr()->eq('sys_language_uid', 0) // Translated pages can not be used as record storage page
                );
            if ($statement = $query->execute()) {
                foreach ($statement as $subPage) {
                    $storagePages[] = $subPage['uid'];
                    $this->getStoragePagesRecursively(
                        $subPage['uid'],
                        $storagePages,
                        ($level === null ? null : $level - 1)
                    );
                }
            }
        }
    }

    protected function alterQuery(&$query)
    {
        // just do nothing here
    }

    public function isIndexingAllowed(Item $item, TypoScriptFrontendController $typoScriptFrontendController)
    {
        $data = $item->getData();
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            QueueController::CONFIGURATION_TABLE
        );
        $configurationResult = $connection->select(
            ['*'],
            QueueController::CONFIGURATION_TABLE,
            ['uid' => $item->getConfiguration()]
        );
        $configuration = $configurationResult->fetch();
        if (!is_array($configuration)) {
            throw new \RuntimeException(
                sprintf('The configuration with the id %d could not be fetched', $item->getConfiguration())
            );
        }

        $isAllowed = true;
        if ($data['page'] !== (int)$typoScriptFrontendController->id || $data['sys_language_uid'] !== GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId()) {
            $isAllowed = false;
        }
        if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) < VersionNumberUtility::convertVersionNumberToInteger('10.0.0')) {
            $queryString = $this->buildQueryString($item, $configuration);
            $queryParameters = GeneralUtility::explodeUrl2Array($queryString);
            $cacheHashCalculator = GeneralUtility::makeInstance(CacheHashCalculator::class);
            $cacheHashParameters = $cacheHashCalculator->getRelevantParameters($queryString);
            foreach ($cacheHashParameters as $parameter => $value) {
                if (!isset($typoScriptFrontendController->cHash_array[$parameter]) || $typoScriptFrontendController->cHash_array[$parameter] !== $value) {
                    $isAllowed = false;
                }
            }
            if ($queryParameters['cHash'] !== $typoScriptFrontendController->cHash) {
                $isAllowed = false;
            }
        }

        return $isAllowed;
    }

    protected function buildQueryString(Item $item, array $configuration)
    {

        $data = $item->getData();
        $queryString = 'id=' . $data['page'] . '&L=' . $data['sys_language_uid'];
        if (strpos($configuration['query_string'], '&') !== 1) {
            $queryString .= '&';
        }
        $queryString .= $configuration['query_string'];
        $queryString = str_replace('{field:uid}', $data['record_uid'], $queryString);
        // generate cache hash
        $cacheHashCalculator = GeneralUtility::makeInstance(CacheHashCalculator::class);
        $cacheHash = $cacheHashCalculator->generateForParameters($queryString);
        $queryString .= '&cHash=' . $cacheHash;

        return $queryString;
    }

    /**
     * @param \WEBcoast\VersatileCrawler\Domain\Model\Item $item
     *
     * @return int
     */
    public function getRecordUid(Item $item)
    {
        $data = $item->getData();

        return ((int)$data['record_uid'] > 0 ? (int)$data['record_uid'] : 0);
    }

    public function enrichIndexData(Item $item, TypoScriptFrontendController $typoScriptFrontendController, Indexer &$indexer)
    {
        $data = $item->getData();
        $configuration = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            QueueController::CONFIGURATION_TABLE
        )->select(['*'], QueueController::CONFIGURATION_TABLE, ['uid' => $item->getConfiguration()])->fetch();
        if (is_array($configuration) && isset($configuration['table_name'])) {
            $record = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
                $configuration['table_name']
            )->select(['*'], $configuration['table_name'], ['uid' => $data['record_uid']])->fetch();
            if (is_array($record)) {
                if (isset($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['crdate']) && $record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['crdate']]) {
                    $indexer->conf['crdate'] = $record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['crdate']];
                }
                if (isset($GLOBALS['TCA'][$configuration['table_name']]['ctrl']['tstamp']) && $record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['tstamp']]) {
                    $indexer->conf['mtime'] = $record[$GLOBALS['TCA'][$configuration['table_name']]['ctrl']['tstamp']];
                }
            }
        }
    }
}
