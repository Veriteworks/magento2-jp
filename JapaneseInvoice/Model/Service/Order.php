<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Service;

class Order
{
    /**
     * @var mixed
     */
    private $source;

    /**
     * @var array
     */
    private $handlers = [];

    /**
     * Constructor
     *
     * @param array $handlers
     */
    public function __construct(
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    /**
     * Set source
     *
     * @param mixed $source source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Collect total
     *
     * @return array
     */
    public function collectTotal()
    {
        $result = [];

        foreach ($this->handlers as $handler) {
            $handler->setSource($this->source)->collect($result);
        }

        return $result;
    }
}
