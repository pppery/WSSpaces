<?php

namespace PDP\Validation;

use MediaWiki\MediaWikiServices;
use PDP\NamespaceRepository;

class PermissionsMatrixValidationCallback extends AbstractValidationCallback {
    /**
     * Returns an array of valid user rights.
     *
     * @return array|string[]
     * @throws \ConfigException
     */
    public static function getValidRights() {
        $rights = \User::getAllRights();
        $valid_rights = MediaWikiServices::getInstance()
            ->getMainConfig()
            ->get( 'PDPValidRights' );

        return empty( $valid_rights ) ? $rights : array_intersect($rights, $valid_rights);
    }

    /**
     * Returns an array of valid user groups.
     *
     * @return array
     * @throws \ConfigException
     */
    public static function getValidUserGroups() {
        $groups = \User::getAllGroups();
        $valid_groups = MediaWikiServices::getInstance()
            ->getMainConfig()
            ->get( 'PDPValidUserGroups' );

        return [ "*" ] + ( empty( $valid_groups ) ? $groups : array_intersect($groups, $valid_groups) );
    }

    /**
     * Validates whether the given value is allowed for the given field.
     *
     * @param string $field The name of the field. Can be any arbitrary string.
     * @param string|array $value The value given to the field.
     * @param array $form_data Other data given to the form.
     * @return bool|string
     * @throws \ConfigException
     */
    public function validateField( string $field, $value, array $form_data ) {
        if ( $field !== "checkmatrix" ) {
            return false;
        }

        $space = explode('/', \RequestContext::getMain()->getTitle()->getText())[1] ?? null;

        $namespace_repository = new NamespaceRepository();
        $namespaces = $namespace_repository->getNamespaces();

        if ($space !== null && !in_array($space, $namespaces)) {
            return false;
        }

        $valid_rights = self::getValidRights();
        $valid_groups = self::getValidUserGroups();

        foreach ($value as $checked) {
            list($right, $group) = explode('-', $checked);

            if (empty($right) || empty($group)) {
                return false;
            }

            if (!in_array($right, $valid_rights) || !in_array($group, $valid_groups)) {
                return false;
            }
        }

        return true;
    }
}