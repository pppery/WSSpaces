<?php

namespace WSS\Log;

use WSS\Space;

class UnarchiveSpaceLog extends Log {
    /**
     * @var string
     */
    private $space;

    /**
     * UnarchiveSpaceLog constructor.
     *
     * @param Space $space
     */
    public function __construct( Space $space ) {
        $this->space = $space;
        parent::__construct( \Title::newFromText( $space->getName() ), 'unarchive' );
    }

    /**
     * Returns the parameters to enter into this log.
     *
     * @return array
     */
    public function getParameters(): array {
        return [
            '4::namespace' => $this->space->getName()
        ];
    }
}