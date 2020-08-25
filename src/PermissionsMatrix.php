<?php

namespace PDP;

use MediaWiki\MediaWikiServices;

class PermissionsMatrix implements \Iterator, \Countable {
    private $index = 0;

    /**
     * An array that holds per-namespace, per-group permissions in a matrix form.
     *
     * It has the following form:
     *
     * [
     *  [ 'group' => <group>, 'right' => <right> ]
     *  [ 'group' => <group>, 'right' => <right> ]
     * ]
     *
     * @var array[]
     */
    private $permissions;

    /**
     * The namespace constant these permissions belong to.
     *
     * @var int
     */
    private $namespace_constant;

    /**
     * Whether or not 'string mode' is enabled.
     *
     * @var bool
     */
    private $string_mode = false;

    /**
     * PermissionsMatrix constructor.
     *
     * @param array $permissions
     * @param string $namespace_constant
     */
    private function __construct( array $permissions, $namespace_constant = null ) {
        $this->permissions        = $permissions;
        $this->namespace_constant = $namespace_constant;
    }

    /**
     * Creates a new PermissionsMatrix from form data.
     *
     * @param array $form_data The data given to the PermissionsMatrix form.
     * @param string $matrix_field The name of the field for the matrix, or empty for root.
     * @return PermissionsMatrix
     */
    public static function newFromFormData( array $form_data, $matrix_field =  null ): PermissionsMatrix {
        $data = $matrix_field ? $form_data[$matrix_field] : $form_data;
        $data = array_unique( $data );

        $permissions = array_map( 'self::stringToArray', $data);

        return new PermissionsMatrix( $permissions );
    }

    /**
     * Creates a new PermissionsMatrix from the permissions assigned to the given namespace constant.
     *
     * @param int $namespace_constant
     * @return PermissionsMatrix
     */
    public static function newFromNamespaceConstant( $namespace_constant ): PermissionsMatrix {
        $database = wfGetDB( DB_REPLICA );
        $rows = $database->select(
            'pdp_permissions',
            [ 'user_group', 'user_right' ],
            [ 'namespace' => $namespace_constant ]
        );

        $result = [];

        foreach ( $rows as $row ) {
            $right = $row->user_right;
            $group = $row->user_group;

            $result[] = [ 'group' => $group, 'right' => $right ];
        }

        return new PermissionsMatrix( $result, $namespace_constant );
    }

    /**
     * Returns an array of PermissionsMatrix objects for every namespace.
     *
     * @return PermissionsMatrix[]
     */
    public static function getAll(): array {
        return array_map( "self::newFromNamespaceConstant", self::getNamespaces() );
    }

    /**
     * Sets/overwrites the namespace constant these permissions belong to.
     *
     * @param int $namespace_constant
     */
    public function setNamespaceConstant( int $namespace_constant ) {
        $this->namespace_constant = $namespace_constant;
    }

    /**
     * Turns on 'string mode' for the iterator. This means the iterator will return strings of the form
     * "<right>-<group>" instead of arrays.
     *
     * @param bool $value
     */
    public function setStringMode( bool $value = true ) {
        $this->string_mode = $value;
    }

    /**
     * Returns the namespace constant these permissions belong to.
     *
     * @return int
     */
    public function getNamespaceConstant(): int {
        return $this->namespace_constant;
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current() {
        return $this->string_mode ?
            self::arrayToString( $this->permissions[$this->index] ) :
            $this->permissions[$this->index];
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() {
        ++$this->index;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        return isset( $this->permissions[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        $this->index = 0;
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count() {
        return count( $this->permissions );
    }

    /**
     * Converts the given string/formfield to an array. A matrix form field has the following form:
     *
     * "<right>-<group>"
     *
     * @param string $field
     * @return array
     */
    private static function stringToArray( string $field ): array {
        list( $right, $group ) = explode( '-', $field );
        return [ 'group' => $group, 'right' => $right ];
    }

    /**
     * Converts the given array to a string/formfield.
     *
     * @param array $array
     * @return string
     */
    private static function arrayToString( array $array ): string {
        $group = $array['group'];
        $right = $array['right'];

        return "$right-$group";
    }

    /**
     * Returns an array of namespace constants.
     *
     * @return array|mixed
     * @throws \ConfigException
     */
    private static function getNamespaces() {
        $config = MediaWikiServices::getInstance()->getMainConfig();
        $namespaces = [ NS_MAIN => 'Main' ] + $config->get( 'CanonicalNamespaceNames' );
        $namespaces += \ExtensionRegistry::getInstance()->getAttribute( 'ExtensionNamespaces' );

        if ( is_array( $config->get( 'ExtraNamespaces' ) ) ) {
            $namespaces += $config->get( 'ExtraNamespaces' );
        }

        return array_flip( $namespaces );
    }
}