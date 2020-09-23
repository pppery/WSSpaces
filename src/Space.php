<?php

namespace PDP;

use User;

class Space {
    const DEFAULT_NAMESPACE_CONSTANT = 0;

    /**
     * @var User
     */
    private $namespace_owner;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $display_name;

    /**
     * @var int
     */
    private $namespace_id;

    /**
     * @var int
     */
    private $talkspace_id;

    /**
     * @var string
     */
    private $namespace_name;

    /**
     * @var bool
     */
    private $is_archived;

    /**
     * @var array
     */
    private $namespace_administrators;

    /**
     * Space constructor.
     *
     * @param string $namespace_name The canonical name of the namespace.
     * @param int $namespace_id The ID of the namespace. MUST be an even number.
     * @param string $display_name The display name of the namespace. Currently unused.
     * @param string $description The description of the namespace.
     * @param User $namespace_owner The owner of the namespace.
     * @param bool $is_archived Whether or not the space is archived.
     * @param array $namespace_administrators The administrators of this namespace.
     */
    private function __construct(
        string $namespace_name,
        int $namespace_id,
        string $display_name,
        string $description,
        User $namespace_owner,
        bool $is_archived = false,
        array $namespace_administrators = []
    ) {
        if ( $namespace_id % 2 !== 0 ) {
            throw new \InvalidArgumentException( "Namespace ID must be an even number; '$namespace_id' is not even'" );
        }

        if ( empty( $namespace_name ) ) {
            throw new \InvalidArgumentException( "Namespace name must not be empty." );
        }

        if ( !ctype_alpha( $namespace_name ) ) {
            throw new \InvalidArgumentException( "A namespace name can only consist of letters, therefore $namespace_name is invalid." );
        }

        $namespace_name = ucfirst( $namespace_name );

        $this->namespace_name  = $namespace_name;
        $this->namespace_id    = $namespace_id;
        $this->talkspace_id    = $namespace_id + 1;
        $this->is_archived     = $is_archived;

        $this->setDisplayName( $display_name );
        $this->setDescription( $description );
        $this->setOwner( $namespace_owner );
        $this->setSpaceAdministrators( $namespace_administrators );
    }

    /**
     * Returns a new space object from the given namespace name.
     *
     * @param string $namespace_name
     * @return bool|Space
     */
    public static function newFromName( string $namespace_name ) {
        $database = wfGetDB( DB_REPLICA );
        $namespace = $database->select(
            'pdp_namespaces',
            [ 'namespace_id', 'display_name', 'description', 'creator_id', 'archived' ],
            [ 'namespace_name' => $namespace_name ]
        );

        if ( $namespace->numRows() === 0 ) {
            return false;
        }

        $namespace = $namespace->current();
        $user = User::newFromId( $namespace->creator_id );

        if ( !$user instanceof User ) {
            throw new \InvalidArgumentException( "Invalid creator_id '{$namespace->creator_id}'" );
        }

        $namespace_administrators = array_map( function( $row ): string {
            return User::newFromId( $row->admin_user_id )->getName();
        }, iterator_to_array( $database->select(
            'pdp_namespace_admins',
            [ 'admin_user_id' ],
            [ 'namespace_id' => $namespace->namespace_id ]
        ) ) );

        return new Space(
            $namespace_name,
            $namespace->namespace_id,
            $namespace->display_name,
            $namespace->description,
            $user,
            $namespace->archived,
            $namespace_administrators
        );
    }

    /**
     * Returns a new space object from the given values.
     *
     * @param string $namespace_name
     * @param string $display_name
     * @param string $description
     * @param User $user
     * @return Space
     */
    public static function newFromValues(
        string $namespace_name,
        string $display_name,
        string $description,
        User $user
    ): Space {
        $space = self::newFromName( $namespace_name );

        if ( $space instanceof Space ) {
            return $space;
        }

        return new Space(
            $namespace_name,
            self::DEFAULT_NAMESPACE_CONSTANT,
            $display_name,
            $description,
            $user
        );
    }

    /**
     * Returns a new space object from the given namespace constant.
     *
     * @param int $namespace_constant
     * @return bool|Space
     */
    public static function newFromConstant( int $namespace_constant ) {
        $database = wfGetDB( DB_REPLICA );
        $namespace = $database->select(
            'pdp_namespaces',
            [ 'namespace_name', 'display_name', 'description', 'creator_id', 'archived' ],
            [ 'namespace_id' => $namespace_constant ]
        );

        if ( $namespace->numRows() === 0 ) {
            return false;
        }

        $namespace = $namespace->current();
        $user = User::newFromId( $namespace->creator_id );

        if ( !$user instanceof User ) {
            throw new \InvalidArgumentException( "Invalid creator_id '{$namespace->creator_id}'" );
        }

        return new Space(
            $namespace->namespace_name,
            $namespace_constant,
            $namespace->display_name,
            $namespace->description,
            $user,
            $namespace->archived
        );
    }

    /**
     * Returns the canonical name of this namespace.
     *
     * @return string
     */
    public function getName(): string {
        return ucfirst( $this->namespace_name );
    }

    /**
     * Returns the id of this space.
     *
     * @return int
     */
    public function getId(): int {
        if ( !$this->exists() ) {
            throw new \BadFunctionCallException( "Cannot call getId on a space that does not exist." );
        }

        return $this->namespace_id;
    }

    /**
     * Returns the talkspace id of this space.
     *
     * @return int
     */
    public function getTalkId(): int {
        if ( !$this->exists() ) {
            throw new \BadFunctionCallException( "Cannot call getTalkId on a space that does not exist." );
        }

        return $this->talkspace_id;
    }

    /**
     * Returns an array of administrators of this space.
     *
     * @return array
     */
    public function getSpaceAdministrators(): array {
        return $this->namespace_administrators;
    }

    /**
     * Returns true if and only if the Space is archived.
     *
     * @return bool
     */
    public function isArchived(): bool {
        return $this->is_archived;
    }

    /**
     * Returns the display name of this space.
     *
     * @return string
     */
    public function getDisplayName(): string {
        return ucfirst( $this->display_name );
    }

    /**
     * Returns the description of this space.
     *
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * Returns the owner of this space.
     *
     * @return User
     */
    public function getOwner(): User {
        return $this->namespace_owner;
    }

    /**
     * Sets the display name for this Space.
     *
     * @param string $display_name
     */
    public function setDisplayName( string $display_name ) {
        if ( empty( $display_name ) ) {
            throw new \InvalidArgumentException( "Display name must not be empty." );
        }

        $this->display_name = $display_name;
    }

    /**
     * Sets the description for this Space.
     *
     * @param string $description
     */
    public function setDescription( string $description ) {
        if ( empty( $description ) ) {
            throw new \InvalidArgumentException( "Description must not be empty." );
        }

        $this->description = $description;
    }

    /**
     * Sets the owner of this Space.
     *
     * @param User $owner
     */
    public function setOwner( User $owner ) {
        if ( $owner->isAnon() ) {
            throw new \InvalidArgumentException( "A namespace cannot be owned by an anonymous user." );
        }

        $this->namespace_owner = $owner;
    }

    /**
     * Sets the administrators of this space.
     *
     * @param array $administrators
     */
    public function setSpaceAdministrators( array $administrators ) {
        $this->namespace_administrators = $administrators;
    }

    /**
     * Sets the archived status of this Space.
     *
     * @param bool $is_archived
     */
    public function setArchived( bool $is_archived = true ) {
        $this->is_archived = $is_archived;
    }

    /**
     * Returns true if and only if the given space exists.
     *
     * @return bool
     */
    public function exists(): bool {
        $database = wfGetDB(DB_MASTER);
        $result = $database->select(
            'pdp_namespaces',
            ['namespace_id'],
            ['namespace_id' => $this->namespace_id]
        );

        return $result->numRows() > 0 && $this->namespace_id !== self::DEFAULT_NAMESPACE_CONSTANT;
    }

    /**
     * Returns true if and only if the current logged in user can edit this space.
     *
     * @return bool
     */
    public function canEdit(): bool {
        return in_array( \RequestContext::getMain()->getUser()->getName(), $this->namespace_administrators ) ||
            in_array( 'pdp-edit-all-spaces', \RequestContext::getMain()->getUser()->getRights() );
    }
}