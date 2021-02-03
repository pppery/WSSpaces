<?php


namespace WSS;

use Wikimedia\Rdbms\ResultWrapper;

/**
 * Class WSSPager
 *
 * @package WSS
 */
abstract class Pager extends \TablePager {
    /**
     * Pre-processes the results from the database to remove any results
     * the user should not be able to see.
     *
     * @param ResultWrapper $results
     * @throws \ConfigException
     */
    public function preprocessResults( $results ) {
        // We cannot use type checking in the function's signature, because it must be compatible with the interface
        assert( $results instanceof ResultWrapper );

        $res_pointer =& ResultWrapper::unwrap( $results );

        $valid_rows = [];
        foreach ( $res_pointer as $row ) {
            $space = Space::newFromConstant( $row["namespace_id"] );
            if ( $space->canEdit() ) {
                $valid_rows[] = $row;
            }
        }

        $res_pointer = $valid_rows;
    }

    /**
     * @inheritDoc
     */
    public function getTableClass() {
        return parent::getTableClass() . ' wss-table';
    }
}