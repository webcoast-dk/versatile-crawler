<?php

namespace WEBcoast\VersatileCrawler\Scheduler;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\ProgressProviderInterface;
use WEBcoast\VersatileCrawler\Controller\CrawlerController;
use WEBcoast\VersatileCrawler\Queue\Manager;

class ProcessTask extends AbstractBaseTask implements ProgressProviderInterface
{
    public $numberOfItemsPerRun = 100;

    /**
     * This is the main method that is called when a task is executed
     * It MUST be implemented by all classes inheriting from this one
     * Note that there is no error handling, errors and failures are expected
     * to be handled and logged by the client implementations.
     * Should return TRUE on successful execution, FALSE on error.
     *
     * @return bool Returns TRUE on successful execution, FALSE on error
     */
    public function execute(): bool
    {
        $crawlerController = GeneralUtility::makeInstance(CrawlerController::class);

        return $crawlerController->processQueue($this->numberOfItemsPerRun);
    }

    /**
     * Gets the progress of a task.
     *
     * @return float Progress of the task as a two decimal precision float. f.e. 44.87
     */
    public function getProgress()
    {
        $queueManager = GeneralUtility::makeInstance(Manager::class);
        $totalItems = $queueManager->countAllItems();
        $finishedItems = $queueManager->countFinishedItems();

        if ($totalItems === 0) {
            return 0;
        }

        return $finishedItems / $totalItems * 100;
    }

    public function getAdditionalInformation()
    {
        $queueManager = GeneralUtility::makeInstance(Manager::class);
        if ($queueManager->countAllItems() === 0) {
            return 'There are no items in the queue.';
        }

        $totalItems = $queueManager->countAllItems();
        $finishedItems = $queueManager->countFinishedItems();
        $successfulItems = $queueManager->countSuccessfulItems();
        $failedItems = $queueManager->countFailedItems();

        return sprintf(
            $this->getLanguageService()->sL(
                'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.processTask.additionalInformation.numbers'
            ),
            $totalItems,
            $totalItems - $finishedItems,
            $finishedItems,
            $successfulItems,
            $failedItems
        );
    }
}
