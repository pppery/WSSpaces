CREATE TABLE /*_*/wss_permissions (
    namespace INT NOT NULL,
    user_group VARCHAR(255) NOT NULL,
    user_right VARCHAR(255) NOT NULL,
    PRIMARY KEY ( namespace, user_group, user_right )
) /*$wgDBTableOptions*/;