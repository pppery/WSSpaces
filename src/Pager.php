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
     * Preprocesses the results from the database to remove any results
     * the user should not be able to see.
     *
     * @param ResultWrapper $results
     */
    public function preprocessResults( $results ) {
        // We cannot use type checking in the function's signature, because it must be compatible with the interface
        assert( $results instanceof ResultWrapper );

        $valid_rows = [];
        foreach( $results->result as &$row ) {
            $space = Space::newFromConstant( $row['namespace_id'] );

            if ( $space->canEdit() ) {
                $valid_rows[] = $row;
            }
        }

        $results->result = $valid_rows;
    }
}