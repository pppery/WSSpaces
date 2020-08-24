<?php

namespace PDP;

use PDP\Validation\PermissionsMatrixValidationCallback;

/**
 * Class LockdownHandler
 *
 * @package PDP
 */
class LockdownHandler {
    /**
     * @var PermissionsMatrix[]
     */
    private $matrices;

    /**
     * LockdownHandler constructor.
     */
    public function __construct() {
        $this->matrices = PermissionsMatrix::getAll();
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
        $namespace_lockdown =& $namespace_lockdown['*'];

        $valid_rights = PermissionsMatrixValidationCallback::getValidRights();

        foreach ( $valid_rights as $right ) {
            $namespace_lockdown[$right] = [];
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