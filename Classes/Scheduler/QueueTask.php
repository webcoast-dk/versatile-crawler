<?php

namespace WEBcoast\VersatileCrawler\Scheduler;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use WEBcoast\VersatileCrawler\Controller\QueueController;

class QueueTask extends AbstractTask
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
}
