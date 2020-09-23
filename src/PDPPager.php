<?php


namespace PDP;

/**
 * Class PDPPager
 *
 * @package PDP
 */
abstract class PDPPager extends \TablePager {
    /**
     * This should return an HTML string
     * representing the result row $row. Rows will be concatenated and
     * returned by getBody()
     *
     * @param array|\stdClass $row Database row
     * @return string
     */
    public function formatRow( $row ) {
        $this->mCurrentRow = $row;
        $namespace = $row->namespace_name;

        $space = Space::newFromName( $namespace );

        if ( !$space->canEdit() ) {
            return '';
        }

        return parent::formatRow( $row );
    }
}