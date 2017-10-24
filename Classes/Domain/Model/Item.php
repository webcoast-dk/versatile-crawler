<?php

namespace WEBcoast\VersatileCrawler\Domain\Model;


use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Item extends AbstractEntity
{
    const STATE_PENDING = 0;
    const STATE_IN_PROGRESS = 10;
    const STATE_SUCCESS = 20;
    const STATE_ERROR = 21;

    /**
     * @var integer
     */
    protected $configuration;
    /**
     * @var string|integer
     */
    protected $identifier;
    /**
     * @var int
     */
    protected $state;
    /**
     * @var string
     */
    protected $message;
    /**
     * @var array
     */
    protected $data;

    /**
     * Item constructor.
     *
     * @param integer        $configuration
     * @param string|integer $identifier
     * @param integer        $state
     * @param string         $message
     * @param array          $data
     */
    public function __construct($configuration, $identifier, $state = self::STATE_PENDING, $message = '', $data = [])
    {
        $this->configuration = $configuration;
        $this->identifier = $identifier;
        $this->state = $state;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
