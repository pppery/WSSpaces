<?php

namespace WSS\Log;

use WSS\Space;

class UpdateSpaceLog extends Log {
    /**
     * @var Space
     */
    private $space;

    /**
     * UpdateSpaceLog constructor.
     *
     * @param Space $old_space
     * @param Space $new_space
     */
    public function __construct( Space $old_space, Space $new_space ) {
        $new_admins = $new_space->getSpaceAdministrators();
        $old_admins = $old_space->getSpaceAdministrators();

        // We use sort and then "===" to ignore changes in the order of the elements.

        sort( $old_admins );
        sort( $new_admins );

        if ( $old_admins !== $new_admins ) {
            // Administrators were changed in this update
            $old_admins_comment = $old_admins === [] ? "(none)" : implode( ", ", $old_admins );
            $new_admins_comment = $new_admins === [] ? "(none)" : implode( ", ", $new_admins );

            $this->setComment( wfMessage( 'wss-update-log-changed-admins', $old_admins_comment, $new_admins_comment )->plain() );
        }
        
        $this->space = $new_space;

        parent::__construct( \Title::newFromText( $new_space->getKey() ), 'update' );
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