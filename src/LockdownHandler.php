<?php

namespace WSS;

use WSS\Validation\PermissionsMatrixValidationCallback;

/**
 * Class LockdownHandler
 *
 * @package WSS
 */
class LockdownHandler {
    /**
     * @var PermissionsMatrix[]
     */
    private $matrices;

    /**
     * LockdownHandler constructor.
     * @param PermissionsMatrix[] $matrices
     */
    public function __construct( array $matrices ) {
        $this->matrices = $matrices;
    }

    /**
     * Set the lockdown constraints.
     * @throws \ConfigException
     */
    public function setLockdownConstraints() {
        $this->setGlobalConstraints();

        foreach ( $this->matrices as $matrix ) {
            $this->setLockdownConstraintsForPermissionsMatrix( $matrix );
        }
    }

    /**
     * Sets constraints that apply to all namespaces.
     * @throws \ConfigException
     */
    private function setGlobalConstraints() {
        $namespace_lockdown =& $this->getNamespaceLockdownPointer();

        $spaces = ( new NamespaceRepository() )->getSpaces( true );
        $valid_rights = PermissionsMatrixValidationCallback::getValidRights();

        foreach ( $spaces as $space ) {
            foreach ( $valid_rights as $right ) {
                $namespace_lockdown[$space][$right] = [];
            }
        }
    }

    /**
     * Sets the lockdown constraints for the given PermissionsMatrix.
     *
     * @param PermissionsMatrix $permissions
     */
    private function setLockdownConstraintsForPermissionsMatrix( PermissionsMatrix $permissions ) {
        $namespace_constant = $permissions->getNamespaceConstant();
        $namespace_lockdown =& $this->getNamespaceLockdownPointer();
        $namespace_lockdown =& $namespace_lockdown[$namespace_constant];

        foreach ( $permissions as $permission ) {
            $group = $permission['group'];
            $right = $permission['right'];

            $namespace_lockdown[$right][] = $group;
        }
    }

    /**
     * Returns a pointer to the namespace lockdown variable.
     */
    private function &getNamespaceLockdownPointer() {
        global $wgNamespacePermissionLockdown;
        return $wgNamespacePermissionLockdown;
    }
}