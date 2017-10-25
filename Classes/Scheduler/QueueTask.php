<?php

namespace WEBcoast\VersatileCrawler\Scheduler;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\VersatileCrawler\Controller\QueueController;

class QueueTask extends AbstractBaseTask
{
    /**
     * @var array
     */
    public $configurations;

    /**
     * This is the main method that is called when a task is executed
     * It MUST be implemented by all classes inheriting from this one
     * Note that there is no error handling, errors and failures are expected
     * to be handled and logged by the client implementations.
     * Should return TRUE on successful execution, FALSE on error.
     *
     * @return bool Returns TRUE on successful execution, FALSE on error
     */
    public function execute()
    {
        $queueController = GeneralUtility::makeInstance(QueueController::class);

        return $queueController->fillQueue($this->configurations);
    }

    public function getAdditionalInformation()
    {
        $configurationNames = [];

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            QueueController::CONFIGURATION_TABLE
        );
        foreach ($this->configurations as $configurationUid) {
            $configurationResult = $connection->select(
                ['*'],
                QueueController::CONFIGURATION_TABLE,
                ['uid' => $configurationUid]
            );
            $configuration = $configurationResult->fetch();
            if (is_array($configuration) && isset($configuration['title'])) {
                $configurationNames[] = $configuration['title'];
            }
        }

        return sprintf(
            $this->getLanguageService()->sL(
                'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.queueTask.additionalInformation.selectedConfigurations'
            ),
            implode(', ', $configurationNames)
        );
    }
}
