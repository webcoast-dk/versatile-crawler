<?php

namespace WEBcoast\VersatileCrawler\Crawler;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\VersatileCrawler\Controller\QueueController;
use WEBcoast\VersatileCrawler\Queue\Manager;
use WEBcoast\VersatileCrawler\Utility\TypeUtility;

class Meta implements QueueInterface
{
    public function fillQueue(array $configuration, ?array $rootConfiguration = null): bool
    {
        if ($rootConfiguration === null) {
            $rootConfiguration = $configuration;
        }

        $queueManager = GeneralUtility::makeInstance(Manager::class);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            QueueController::CONFIGURATION_TABLE
        );
        $query = $connection->createQueryBuilder()->from(QueueController::CONFIGURATION_TABLE, 'c')
            ->from('tx_versatilecrawler_domain_model_configuration_mm', 'm')
            ->select('c.*');
        $query->where(
            'm.uid_foreign=c.uid',
            $query->expr()->eq('m.uid_local', $configuration['uid'])
        );
        $result = true;
        if ($statement = $query->executeQuery()) {
            foreach ($statement->fetchAllAssociative() as $subConfiguration) {
                $className = TypeUtility::getClassForType($subConfiguration['type']);
                $crawler = GeneralUtility::makeInstance($className);
                if (!$crawler instanceof QueueInterface) {
                    throw new \RuntimeException(
                        sprintf(
                            'The registered crawler class "%s" must implement "%s"',
                            $className,
                            QueueInterface::class
                        )
                    );
                }
                $result = $result && $crawler->fillQueue($subConfiguration, $rootConfiguration);
                // delete old queue entries
                $queueManager->cleanUpOldItems($GLOBALS['EXEC_TIME'], $subConfiguration['uid']);
            }
        } else {
            $result = false;
        }

        return $result;
    }
}
