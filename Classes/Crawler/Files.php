<?php

namespace WEBcoast\VersatileCrawler\Crawler;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\IndexedSearch\Indexer;
use WEBcoast\VersatileCrawler\Controller\CrawlerController;
use WEBcoast\VersatileCrawler\Domain\Model\Item;
use WEBcoast\VersatileCrawler\Queue\Manager;

class Files implements CrawlerInterface, QueueInterface
{
    const TABLE_SYS_STORAGE = 'sys_file_storage';
    const TABLE_CONFIGURATION_FILE_STORAGE_MM = 'tx_versatilecrawler_domain_model_configuration_file_storage_mm';
    /**
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * PageTree constructor.
     */
    public function __construct()
    {
        $this->storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
    }

    /**
     * @param array $configuration
     * @param array $rootConfiguration
     *
     * @return boolean
     * @throws DBALException
     */
    public function fillQueue(array $configuration, array $rootConfiguration = null)
    {
        if ($rootConfiguration === null) {
            $rootConfiguration = $configuration;
        }

        // Find storage records from the configuration
        /** @var ResourceStorage[] $storages */
        $storages = [];
        $storageQuery = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_CONFIGURATION_FILE_STORAGE_MM);
        $storageQuery->select('s.uid as storageId')->from(self::TABLE_CONFIGURATION_FILE_STORAGE_MM, 'r')
            ->join('r', self::TABLE_SYS_STORAGE, 's', 'r.uid_foreign = s.uid')
            ->where($storageQuery->expr()->eq('r.uid_local', (int)$configuration['uid']))
            ->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        if ($statement = $storageQuery->execute()) {
            foreach ($statement as $storageRecord) {
                $storages[] = $this->storageRepository->findByUid($storageRecord['storageId']);
            }
        }

        // For each storage collect files
        /** @var File[] $filesToIndex */
        $filesToIndex = [];
        foreach ($storages as $storage) {
            $folder = $storage->getRootLevelFolder();
            if (!empty($configuration['file_extensions'])) {
                $allowedFileExtensions = GeneralUtility::trimExplode(',', $configuration['file_extensions']);
                $folder->setFileAndFolderNameFilters(
                    [function ($itemName, $itemIdentifier, $parentIdentifier, $notUsed, $driver) use ($allowedFileExtensions) {
                        $allowed = -1;
                        foreach ($allowedFileExtensions as $fileExtension) {
                            if (fnmatch('*.' . $fileExtension, $itemIdentifier)) {
                                $allowed = true;
                                break;
                            }
                        }

                        return $allowed;
                    }]);
            }
            $filesToIndex = array_merge($filesToIndex, $folder->getFiles(0, 0, Folder::FILTER_MODE_USE_OWN_AND_STORAGE_FILTERS, true, 'name'));
        }

        $queueManager = GeneralUtility::makeInstance(Manager::class);
        $languages = GeneralUtility::intExplode(',', $configuration['languages']);
        $result = true;
        foreach ($filesToIndex as $file) {
            foreach ($languages as $languageId) {
                $data = [
                    'fileId' => $file->getUid(),
                    'sys_language_uid' => $languageId,
                    'rootConfigurationId' => $rootConfiguration['uid']
                ];
                $item = new Item($configuration['uid'], md5(serialize($data)), Item::STATE_PENDING, '', $data);
                $result = $result && $queueManager->addOrUpdateItem($item);
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function processQueueItem(Item $item, array $configuration)
    {
        $hash = md5($item->getIdentifier() . time());
        $queueManager = GeneralUtility::makeInstance(Manager::class);
        $queueManager->prepareItemForProcessing($item->getConfiguration(), $item->getIdentifier(), $hash);
        $rootConfiguration = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(CrawlerController::CONFIGURATION_TABLE)->select(
            ['*'],
            CrawlerController::CONFIGURATION_TABLE,
            ['uid' => $item->getData()['rootConfigurationId']],
            [],
            [],
            1
        )->fetch(\PDO::FETCH_ASSOC);
        /** @var File $file */
        $file = GeneralUtility::makeInstance(FileRepository::class)->findByUid($item->getData()['fileId']);

        $tmpFile = $file->getForLocalProcessing(false);

        $rootPage = $this->getRootPage($configuration['pid']);
        $indexer = GeneralUtility::makeInstance(Indexer::class);
        $indexer->conf['index_externals'] = true;
        $indexer->conf['gr_list'] = implode(',', [0, -1]);
        $indexer->conf['sys_language_uid'] = $item->getData()['sys_language_uid'];
        $indexer->conf['id'] = $rootPage;
        $indexer->conf['recordUid'] = $file->getUid();
        $indexer->conf['rootline_uids'][0] = $rootPage;
        $indexer->conf['freeIndexUid'] = $rootConfiguration['indexing_configuration'];
        $indexer->conf['freeIndexSetId'] = 0;
        $indexer->init();
        $indexer->indexRegularDocument($file->getPublicUrl(), false, $tmpFile);

        $item->setState(Item::STATE_SUCCESS);

        return true;
    }

    protected function getRootPage($pageId)
    {
        $pagesQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $pagesQueryBuilder->select('uid', 'pid')->from('pages');
        $pagesQueryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));
        $domainQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_domain');
        $domainQueryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));
        $domainQueryBuilder->count('uid')->from('sys_domain');
        do {
            $domainQueryBuilder->where(
                $domainQueryBuilder->expr()->eq('pid', $pageId)
            );
            $domainStatement = $domainQueryBuilder->execute();
            if ($domainStatement->fetchColumn(0) > 0) {
                return $pageId;
            }

            $pagesQueryBuilder->where(
                $pagesQueryBuilder->expr()->eq('uid', $pageId)
            );
            $pagesStatement = $pagesQueryBuilder->execute();
            $page = $pagesStatement->fetch(\PDO::FETCH_ASSOC);
        } while ($page && $page['pid'] > 0 && $pageId = $page['pid']);

        return $pageId;
    }
}
