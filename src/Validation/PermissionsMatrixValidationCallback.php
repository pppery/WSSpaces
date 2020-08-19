<?php

namespace PDP\Validation;

use MediaWiki\MediaWikiServices;

class PermissionsMatrixValidationCallback implements ValidationCallback {
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

        return empty( $valid_groups ) ? $groups : array_intersect($groups, $valid_groups);
    }

    /**
     * Returns an array of valid namespaces.
     *
     * @return array
     * @throws \ConfigException
     */
    public static function getValidSpaces(): array {
        try {
            $config = MediaWikiServices::getInstance()->getMainConfig();
            $namespaces = [ NS_MAIN => 'Main' ] + $config->get( 'CanonicalNamespaceNames' );
            $namespaces += \ExtensionRegistry::getInstance()->getAttribute( 'ExtensionNamespaces' );

            if ( is_array( $config->get( 'ExtraNamespaces' ) ) ) {
                $namespaces += $config->get( 'ExtraNamespaces' );
            }
        } catch(\Exception $e) {
            return [];
        }

        foreach ( $namespaces as $constant => &$name ) {
            $name = str_replace( '_', ' ', $name );
        }

        $valid_namespaces = MediaWikiServices::getInstance()
                ->getMainConfig()
                ->get( 'PDPValidNamespaces' );

        return empty( $valid_namespaces ) ? $namespaces : array_intersect($namespaces, $valid_namespaces);
    }

    /**
     * Validates whether the given value is allowed for the given field.
     *
     * @param string $field The name of the field. Can be any arbitrary string.
     * @param string|array $value The value given to the field.
     * @param array $form_data Other data given to the form.
     * @return bool|string
     */
    public function validateField( string $field, $value, array $form_data ) {
        if ( $field !== "checkmatrix" ) {
            return false;
        }

        try {
            $space = explode('/', \RequestContext::getMain()->getTitle()->getText())[1] ?? null;

            if ($space !== null && !in_array($space, self::getValidSpaces())) {
                return false;
            }

            $valid_rights = self::getValidRights();
            $valid_groups = self::getValidUserGroups();
        } catch(\Exception $e) {
            return false;
        }

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