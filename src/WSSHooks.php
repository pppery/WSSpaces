<?php

namespace WSS;

use ConfigException;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Session\Session;
use MWException;
use Parser;
use User;
use WSS\UI\WSSUI;

/**
 * Class WSSHooks
 *
 * @package WSS
 */
abstract class WSSHooks {
	// phpcs:ignore
	const TIMEOUT = 12000;

	/**
	 * Hook WSSpaces into the parser.
	 *
	 * @param Parser $parser
	 * @throws MWException
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'spaceadmins', [ self::class, 'renderSpaceAdmins' ] );
		$parser->setFunctionHook( 'spaces', [ self::class, 'renderSpaces' ] );
	}

	/**
	 * Render the output of {{#spaceadmins: namespace}}.
	 *
	 * @param Parser $parser
	 * @param string $namespace
	 * @return string
	 */
	public static function renderSpaceAdmins( Parser $parser, $namespace = '' ) : string {
		// Check if user has the 'wss-view-space-admins' right.
		if ( !\RequestContext::getMain()->getUser()->isAllowed( 'wss-view-space-admins' ) ) {
			return wfMessage( 'wss-permission-denied-spaceadmins' );
		}

		// Validate namespace input.
		if ( $namespace === '' ) {
			return wfMessage( 'wss-api-missing-param-namespace' );
		}
		if ( !ctype_digit( $namespace ) ) {
			return wfMessage( 'wss-pf-invalid-number' );
		}

		// Get all admin ids for a namespace. If no admins are found, return the error message.
		$admin_ids = NamespaceRepository::getNamespaceAdmins( $namespace );

		if ( empty( $admin_ids ) ) {
			return wfMessage( 'wss-pf-no-admins-found', $namespace )->parse();
		}

		// Turn the list of admin ids into User objects
		$admins = array_map( [ User::class, "newFromId" ], $admin_ids );
		$admins = array_filter( $admins, function ( $user ): bool {
			// loadFromDatabase checks if the user actually exists in the database.
			return $user instanceof User && $user->loadFromDatabase();
		} );

		if ( empty( $admins ) ) {
			return wfMessage( 'wss-pf-no-valid-admins', $namespace )->parse();
		}

		// Add all admin names to a comma separated string.
		$admins = array_map( function ( User $user ): string {
			return $user->getName();
		}, $admins );

		// Return the admin list.
		return implode( ",", $admins );
	}

	/**
	 * Render the output of {{#spaces:}}.
	 *
	 * @param Parser $parser
	 * @return string
	 */
	public static function renderSpaces( Parser $parser ) : string {
		$namespace_repository = new NamespaceRepository();
		$spaces = $namespace_repository->getSpaces();

		return implode( ",", $spaces );
	}

	/**
	 * Affect the return value from AuthManager::securitySensitiveOperationStatus().
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SecuritySensitiveOperationStatus
	 *
	 * @param string &$status
	 * @param string $operation
	 * @param Session $session
	 * @param int $timeSinceAuth
	 * @return bool
	 */
	public static function onSecuritySensitiveOperationStatus(
		string &$status,
		string $operation,
		Session $session,
		int $timeSinceAuth
	): bool {
		$security_sensitive_operations = [
			"ws-manage-namespaces",
			"ws-create-namespaces"
		];

		if ( $session->getLoggedOutTimestamp() > 0 ) {
			$status = AuthManager::SEC_FAIL;
		} elseif ( in_array( $operation, $security_sensitive_operations, true ) && $timeSinceAuth > self::TIMEOUT ) {
			$status = AuthManager::SEC_REAUTH;
		} else {
			$status = AuthManager::SEC_OK;
		}

		return true;
	}

	/**
	 * At the end of Skin::buildSidebar().
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar
	 *
	 * @param \Skin $skin
	 * @param array &$bar
	 * @return bool
	 * @throws ConfigException
	 */
	public static function onSkinBuildSidebar( \Skin $skin, &$bar ): bool {
		if ( !WSSUI::isQueued() ) {
			return true;
		}

		$bar[wfMessage( 'wss-sidebar-header' )->plain()][] = [
			'text' => wfMessage( 'wss-add-space-header' ),
			'href' => \Title::newFromText( "AddSpace", NS_SPECIAL )->getFullUrlForRedirect(),
			'id'   => 'wss-add-space-special',
			'active' => ''
		];

		$bar[wfMessage( 'wss-sidebar-header' )->plain()][] = [
			'text' => wfMessage( 'wss-active-spaces-header' ),
			'href' => \Title::newFromText( "ActiveSpaces", NS_SPECIAL )->getFullUrlForRedirect(),
			'id'   => 'wss-manage-space-special',
			'active' => ''
		];

		$bar[wfMessage( 'wss-sidebar-header' )->plain()][] = [
			'text' => wfMessage( 'wss-archived-spaces-header' ),
			'href' => \Title::newFromText( "ArchivedSpaces", NS_SPECIAL )->getFullUrlForRedirect(),
			'id'   => 'wss-archived-spaces-special',
			'active' => ''
		];

		WSSUI::setSidebar( $bar );

		return true;
	}

	/**
	 * Fired when MediaWiki is updated to allow extensions to register updates for the database schema.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 *
	 * @param \DatabaseUpdater $updater
	 * @return bool
	 * @throws MWException
	 */
	public static function onLoadExtensionSchemaUpdates( \DatabaseUpdater $updater ): bool {
		$directory = $GLOBALS['wgExtensionDirectory'] . '/WSSpaces/sql';
		$type = $updater->getDB()->getType();

		// Associative array where the keys are the name of the table and the value the name of the associated SQL file
		$sql_files = [
			"wss_namespaces" => "wss_namespaces_table.sql",
			"wss_namespace_admins" => "wss_namespace_admins_table.sql"
		];

		foreach ( $sql_files as $table => $sql_file ) {
			$path = sprintf( "%s/%s/%s", $directory, $type, $sql_file );

			if ( !file_exists( $path ) ) {
				throw new MWException( wfMessage( 'wss-unsupported-dbms', $type )->parse() );
			}

			$updater->addExtensionTable( $table, $path );
		}

		return true;
	}

	/**
	 * For extensions adding their own namespaces or altering the defaults.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CanonicalNamespaces
	 *
	 * @param array &$namespaces
	 * @return bool
	 *
	 * @throws ConfigException
	 */
	public static function onCanonicalNamespaces( array &$namespaces ): bool {
		$namespace_repository = new NamespaceRepository();
		$spaces = $namespace_repository->getSpaces();
		foreach ( $spaces as $id => $space ) {
			$spaces[$id + 1] = $space . "_talk";
		}

		$namespaces += $spaces;

		return true;
	}

	/**
	 * A hook of the UserMerge extension: If a user is merged, do migrate the admin table accordingly
	 *
	 * @see https://www.mediawiki.org/wiki/Extension:UserMerge/Hooks/UserMergeAccountFields
	 *
	 * @param array &$updateFields The fields as described on the documentation page
	 */
	public static function onUserMergeAccountFields( &$updateFields ) {
		$updateFields []= [ 'wss_namespace_admins', 'admin_user_id', 'options' => [ 'IGNORE' ] ];
	}

	/**
	 * Called when generating the extensions credits, use this to change the tables headers.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ExtensionTypes
	 *
	 * @param array &$extension_types
	 * @return bool
	 */
	public static function onExtensionTypes( array &$extension_types ): bool {
		$extension_types['csp'] = wfMessage( "version-csp" )->parse();
		return true;
	}
}
