# WSSpaces

WSSpaces is a comprehensive space management system developed for MediaWiki. It enables
users to dynamically define namespaces. This document describes the extensions capabilities,
options and usage.

## Configuration

WSSpaces has two configuration variable.

* `$wgWSSpacesEnableSpaceArchiving` (boolean, default: true) Whether to allow archiving of spaces
* `$wgWSSpacesAutoAddAdminsToUserGroups` (boolean, default: false) Whether to automatically add space admins to a user group. Eg. An admin for a space with id 50000 will get added to a group called '50000Admin'. Will additionally add admins to a general 'SpaceAdmin' group that can be used to assign rights to all space admins.
* `$wgWSSpacesAllowNoAdmins` (boolean, default: false) If set to true, this will disable the validation check that at least one admin must be set for this space. This is not recommended.

To enable Semantic MediaWiki for the created namespace, place the following code inbetween the initialization of Semantic MediaWiki and WSSpaces in LocalSettings.php:

```
// NOTE: This is not very nice, and another solution is needed, but it suffices for now
for ($i = 50000; $i < 55000; $i++) {
	$smwgNamespacesWithSemanticLinks[$i] = true;
}
```

## Hooks

WSSpaces defines several hooks to alter or extend its behaviour.

### `WSSpacesAfterCreateSpace`

```php
public static function onWSSpacesAfterCreateSpace( \WSS\Space $space ) {}
```

Gets called once directly after a space has been created. NOTE: The space has not been initialized with the Wiki at this point. Therefore, you cannot create a page in this namespace (use a job instead).

### `WSSpacesBeforeInitializeSpace`

```php
public static function onWSSpacesBeforeInitializeSpace( \WSS\Space $space ) {}
```

Gets called on each page load after the space has been initialized into `$wgCanonicalNamespaces`.

### `WSSpacesCustomApiExceptionHandler`

```php
public static function onWSSpacesCustomApiExceptionHandler( \ApiUsageException $exception ) {}
```

Gets called whenever an ApiUsageException occurs when using the WSSpaces API. Allows for custom handling
of the exception.

## Rights

WSSpaces defines several rights.

> Please note that administrators of a space are always able to edit the details of that
> space, regardless of whether or not they have been assigned any of the rights below.

### `wss-edit-all-spaces`

Whether the user can edit all spaces or not, regardless of whether or not they are a space administrator of those
spaces.

### `wss-add-space`

Whether the user can add new spaces to the wiki or not.

### `wss-archive-space`

Whether the user can archive existing spaces or not. This right does not affect the behaviour or
`$wgWSSpacesEnableSpaceArchiving`.

### `wss-view-spaces-overview`

Whether the user is able to view the overview of spaces or not.

## API modules

WSSpaces defines several API modules. The documentation of these API modules can be found through
the API sandbox or by going to `/api.php` on the wiki. For your reference, the following API modules are
available:

* `addspace`
* `archivespace`
* `unarchivespace`
* `editspace`

Furthermore, the following API list (`?action=query`) modules are available:

* `spaces`