<?php

namespace WEBcoast\VersatileCrawler\Queue;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\VersatileCrawler\Domain\Model\Item;

class Manager implements SingletonInterface
{
    const QUEUE_TABLE = 'tx_versatilecrawler_domain_model_queue_item';

    /**
     * Add or update a item for a given configuration and identifier.
     * Sets state to PENDING, and clear message and data.
     *
     * @param \WEBcoast\VersatileCrawler\Domain\Model\Item $item
     *
     * @return bool
     */
    public function addOrUpdateItem(Item $item)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);
        $count = $connection->count(
            '*',
            self::QUEUE_TABLE,
            ['configuration' => $item->getConfiguration(), 'identifier' => $item->getIdentifier()]
        );
        if ($count === 1) {
            $changedRows = $connection->update(
                self::QUEUE_TABLE,
                ['tstamp' => time(), 'state' => Item::STATE_PENDING, 'message' => '', 'data' => $item->getData(), 'hash' => ''],
                ['configuration' => $item->getConfiguration(), 'identifier' => $item->getIdentifier()]
            );

            return $changedRows === 1;
        } else {
            $insertedRows = $connection->insert(
                self::QUEUE_TABLE,
                [
                    'configuration' => $item->getConfiguration(),
                    'identifier' => $item->getIdentifier(),
                    'tstamp' => time(),
                    'state' => Item::STATE_PENDING,
                    'message' => '',
                    'data' => $item->getData()
                ]
            );

            return $insertedRows === 1;
        }
    }

    /**
     * Updates the state, message and data of a given
     *
     * @param \WEBcoast\VersatileCrawler\Domain\Model\Item $item
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public function updateState($item)
    {
        if ($item->getState() !== Item::STATE_SUCCESS && $item->getState() !== Item::STATE_ERROR) {
            throw new \RuntimeException('The state can only be changed to SUCCESS or ERROR', 1508491824);
        }
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);
        $changedRows = $connection->update(
            self::QUEUE_TABLE,
            ['state' => $item->getState(), 'message' => $item->getMessage(), 'hash' => ''],
            ['configuration' => $item->getConfiguration(), 'identifier' => $item->getIdentifier()]
        );

        return $changedRows === 1;
    }

    /**
     * @param int $timestamp
     * @param int $configuration
     */
    public function cleanUpOldItems(int $timestamp, int $configuration)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);
        $query = $connection->createQueryBuilder()->delete(self::QUEUE_TABLE);
        $query->where(
            $query->expr()->lt('tstamp', $timestamp),
            $query->expr()->eq('configuration', $configuration)
        )->executeStatement();
    }

    /**
     * Return DBAL statement container all pending items
     *
     * @param int $limit Limit of items to be fetched
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function getPendingItems($limit = null)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->select(
            ['*'],
            self::QUEUE_TABLE,
            ['state' => Item::STATE_PENDING],
            [],
            ['tstamp' => 'ASC'],
            $limit !== null ? (int)$limit : 0
        )->fetchAllAssociative();
    }

    public function getAllItems()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->select(
            ['*'],
            self::QUEUE_TABLE,
            [],
            [],
            ['tstamp' => 'ASC']
        )->fetchAllAssociative();
    }

    public function getFinishedItems()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);
        $query = $connection->createQueryBuilder()->select('*')->from(self::QUEUE_TABLE);
        $query->where(
            $query->expr()->or(
                'state=' . Item::STATE_SUCCESS,
                'state=' . Item::STATE_ERROR
            )
        );
        $query->orderBy('tstamp', 'ASC');

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getSuccessfulItems()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->select(
            ['*'],
            self::QUEUE_TABLE,
            ['state' => Item::STATE_SUCCESS],
            [],
            ['tstamp' => 'ASC']
        );
    }

    public function getFailedItems()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->select(
            ['*'],
            self::QUEUE_TABLE,
            ['state' => Item::STATE_ERROR],
            [],
            ['tstamp' => 'ASC']
        );
    }

    public function countAllItems(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->count(
            '*',
            self::QUEUE_TABLE,
            []
        );
    }

    public function countFinishedItems(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);
        $query = $connection->createQueryBuilder()->count('*')->from(self::QUEUE_TABLE);
        $query->where(
            $query->expr()->orX(
                'state=' . Item::STATE_SUCCESS,
                'state=' . Item::STATE_ERROR
            )
        );

        return $query->executeQuery()->fetchOne();
    }

    public function countSuccessfulItems(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);
        $query = $connection->createQueryBuilder()->count('*')->from(self::QUEUE_TABLE);
        $query->where(
            $query->expr()->eq('state',  Item::STATE_SUCCESS)
        );

        return $query->executeQuery()->fetchOne();
    }

    public function countFailedItems(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);
        $query = $connection->createQueryBuilder()->count('*')->from(self::QUEUE_TABLE);
        $query->where(
            $query->expr()->eq('state',  Item::STATE_ERROR)
        );

        return $query->executeQuery()->fetchOne();
    }

    public function prepareItemForProcessing($configuration, $identifier, $hash)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->update(
            self::QUEUE_TABLE,
            ['hash' => $hash, 'state' => Item::STATE_IN_PROGRESS],
            ['configuration' => $configuration, 'identifier' => $identifier, 'state' => Item::STATE_PENDING]
        );
    }

    /**
     * @param string $hash
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function getItemForProcessing($hash)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->select(['*'], self::QUEUE_TABLE, ['hash' => $hash, 'state' => Item::STATE_IN_PROGRESS])->fetchAssociative();
    }

    public function removeQueueItem(Item $item)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::QUEUE_TABLE);

        return $connection->delete(self::QUEUE_TABLE, ['identifier' => $item->getIdentifier()]);
    }

    public function getFromRecord($record): Item
    {
        return new Item(
            $record['configuration'],
            $record['identifier'],
            $record['state'],
            $record['message'],
            json_decode($record['data'], true)
        );
    }
}
