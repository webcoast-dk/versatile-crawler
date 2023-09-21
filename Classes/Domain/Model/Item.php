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
    protected array $data;

    /**
     * Item constructor.
     *
     * @param integer        $configuration
     * @param string|integer $identifier
     * @param integer        $state
     * @param string         $message
     * @param array          $data
     */
    public function __construct($configuration, $identifier, $state = self::STATE_PENDING, $message = '', array $data = [])
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

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }
}
