<?php

namespace WEBcoast\VersatileCrawler\Scheduler;


use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;

class ProcessTaskAdditionalFieldProvider implements AdditionalFieldProviderInterface
{
    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array                                                     $taskInfo        Values of the fields from the
     *                                                                                   add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask                    $task            The task object being edited.
     *                                                                                   Null when adding a task!
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler
     *                                                                                   backend module
     *
     * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' =>
     *               '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];

        // configuration field
        $fieldId = 'versatileCrawler_processTask_numberOfItemsPerRun';
        $fieldName = 'tx_scheduler[' . $fieldId . ']';

        if ($schedulerModule->CMD === 'edit' && $task instanceof processTask && !isset($taskInfo[$fieldId])) {
            $taskInfo[$fieldId] = $task->numberOfItemsPerRun;
        }

        $additionalFields[$fieldId] = [
            'code' => '<input type="text" class="form-control" name="' . $fieldName . '" id="' . $fieldId . '" size="10" value="' . $taskInfo[$fieldId] . '"" />',
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.processTask.numberOfItemsPerRun'
        ];

        return $additionalFields;
    }

    /**
     * Validates the additional fields' values
     *
     * @param array                                                     $submittedData   An array containing the data
     *                                                                                   submitted by the add/edit task
     *                                                                                   form
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler
     *                                                                                   backend module
     *
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
    {
        $isValid = true;
        if (isset($submittedData['versatileCrawler_processTask_numberOfItemsPerRun']) && strcmp(
                $submittedData['versatileCrawler_processTask_numberOfItemsPerRun'],
                ''
            ) !== 0 && (int)$submittedData['versatileCrawler_processTask_numberOfItemsPerRun'] < 1) {
            $schedulerModule->addMessage(
                $this->getLanguageService()->sL(
                    'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.processTask.error.numberOfItemsPerRun'
                ),
                FlashMessage::ERROR
            );
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array                                  $submittedData An array containing the data submitted by the
     *                                                              add/edit task form
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task          Reference to the scheduler backend module
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        $task->numberOfItemsPerRun = $submittedData['versatileCrawler_processTask_numberOfItemsPerRun'];
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected final function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
