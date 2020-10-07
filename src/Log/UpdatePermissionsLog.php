<?php

namespace WSS\Log;

use WSS\NamespaceRepository;
use WSS\PermissionsMatrix;
use WSS\Space;

class UpdatePermissionsLog extends Log {
    /**
     * @var string
     */
    private $old;

    /**
     * @var string
     */
    private $new;

    /**
     * @var string
     */
    private $name;

    /**
     * UpdateSpaceLog constructor.
     *
     * @param $old_matrix
     * @param PermissionsMatrix $new_matrix
     * @throws \MWException
     * @throws \ConfigException
     */
    public function __construct( PermissionsMatrix $old_matrix, PermissionsMatrix $new_matrix ) {
        {
            $old_matrix->setStringMode();
            $old_matrix_permissions = iterator_to_array( $old_matrix );
        }

        {
            $new_matrix->setStringMode();
            $new_matrix_permissions = iterator_to_array( $new_matrix );
        }

        $this->old = $old_matrix_permissions === [] ? "(none)" : implode( ", ", $old_matrix_permissions );
        $this->new = $new_matrix_permissions === [] ? "(none)" : implode( ", ", $new_matrix_permissions );

        $matrix_namespace = $new_matrix->getNamespaceConstant();
        $space = Space::newFromConstant( $matrix_namespace );

        if ( $space instanceof Space ) {
            $this->name = $space->getName();
            $type = "permissions";
        } else {
            $namespace_repository = new NamespaceRepository();
            $namespaces = $namespace_repository->getAllNamespaces();

            $this->name = $namespaces[$matrix_namespace];

            $type = "core-permissions";
        }

        parent::__construct( \Title::newFromText( $this->name ), $type );

        // Clean up for the others
        $old_matrix->setStringMode(false);
        $new_matrix->setStringMode(false);
    }

    /**
     * Returns the parameters to enter into this log.
     *
     * @return array
     */
    public function getParameters(): array {
        return [
            '4::namespace' => $this->name,
            '5::old_permissions' => $this->old,
            '6::new_permissions' => $this->new
        ];
    }
}