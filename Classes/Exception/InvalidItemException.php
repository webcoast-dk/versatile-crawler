<?php

namespace WEBcoast\VersatileCrawler\Exception;

use Throwable;
use WEBcoast\VersatileCrawler\Domain\Model\Item;

class InvalidItemException extends \Exception
{
    protected Item $item;

    public function __construct(Item $item, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->item = $item;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }
}
