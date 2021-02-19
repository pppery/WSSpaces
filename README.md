# WSSpaces

WSSpaces is a comprehensive space management system developed for MediaWiki. It enables
users to dynamically define namespaces. This document describes the extensions capabilities,
options and usage.

## Changelog

* 1.0 - Initial release
* 2.0 - Fix issue where SemanticMediaWiki properties were not available for dynamic namespaces. This update also
changes the `WSSpacesBeforeInitializeSpace` hook (see documentation of the hook below).

## Installation

* Download and place the files in a directory called `WSSpaces` in your `extension/` folder.
* Add the following code at the **top** (otherwise it will not work) of your `LocalSettings.php`:

```php
wfLoadExtension( "WSSpaces" );
```

* Run the update script (`update.php`), which will automatically create the necessary database tables
  that this extension needs.
* Possibly running `composer update` is required.

## Configuration

WSSpaces has one configuration variable.

* `$wgWSSpacesEnableSpaceArchiving` (boolean, default: true) Whether to allow archiving of spaces

## Hooks

WSSpaces defines several hooks to alter or extend its behaviour.

### `WSSpacesAfterCreateSpace`

```php
public static function onWSSpacesAfterCreateSpace( \WSS\Space $space ) {}
```

Gets called once directly after a space has been created.

### `WSSpacesBeforeInitializeSpace`

```php
public static function onWSSpacesBeforeInitializeSpace( int $namespace_id, string $namespace_key ) {}
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