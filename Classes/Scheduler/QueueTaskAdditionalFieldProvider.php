<?php

namespace WEBcoast\VersatileCrawler\Scheduler;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;

class QueueTaskAdditionalFieldProvider implements AdditionalFieldProviderInterface
{
    const CONFIGURATION_TABLE = 'tx_versatilecrawler_domain_model_configuration';

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
        $fieldId = 'versatileCrawler_queueTask_configuration';
        $fieldName = 'tx_scheduler[' . $fieldId . '][]';

        if ($schedulerModule->CMD === 'edit' && $task instanceof QueueTask && !isset($taskInfo[$fieldId])) {
            $taskInfo[$fieldId] = $task->configurations;
        }
        $fieldOptions = $this->getConfigurationOptions($taskInfo);

        $additionalFields[$fieldId] = [
            'code' => '<select class="form-control" name="' . $fieldName . '" id="' . $fieldId . '" size="10" multiple="multiple">' . $fieldOptions . '</select>',
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.queueTask.configuration'
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
        if (empty($submittedData['versatileCrawler_queueTask_configuration'])) {
            $schedulerModule->addMessage(
                $this->getLanguageService()->sL(
                    'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.queueTask.error.emptyConfiguration'
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
        $task->configurations = $submittedData['versatileCrawler_queueTask_configuration'];
    }

    /**
     * @param array $taskInfo
     *
     * @return string
     */
    protected function getConfigurationOptions($taskInfo)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            self::CONFIGURATION_TABLE
        );
        $configurations = $connection->select(['uid', 'title'], self::CONFIGURATION_TABLE, [], [], ['title' => 'ASC']);
        $options = [];
        foreach ($configurations as $configuration) {
            $options[] = '<option value="' . $configuration['uid'] . '" ' . (is_array($taskInfo['versatileCrawler_queueTask_configuration']) && in_array($configuration['uid'], $taskInfo['versatileCrawler_queueTask_configuration']) ? ' selected="selected"' : '') . ' >' . $configuration['title'] . '</option>';
        }

        return implode('', $options);
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected final function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
