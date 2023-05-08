<?php
declare(strict_types=1);

namespace CommunityEngineering\JapaneseInvoice\Model\Service;

class Creditmemo
{
    private $source;

    private $handlers = [];

    public function __construct(
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function collectTotal()
    {
        $result = [];

        foreach ($this->handlers as $handler) {
            $handler->setSource($this->source)->collect($result);
        }

        return $result;
    }
}
