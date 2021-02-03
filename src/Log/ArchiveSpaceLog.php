<?php

namespace WSS\Log;

use WSS\Space;

class ArchiveSpaceLog extends Log {
    /**
     * @var string
     */
    private $space;

    /**
     * ArchiveSpaceLog constructor.
     *
     * @param Space $space
     */
    public function __construct( Space $space ) {
        $this->space = $space;
        parent::__construct( \Title::newFromText( $space->getKey() ), 'archive' );
    }

    /**
     * Returns the parameters to enter into this log.
     *
     * @return array
     */
    public function getParameters(): array {
        return [
            '4::namespace' => $this->space->getKey()
        ];
    }
}