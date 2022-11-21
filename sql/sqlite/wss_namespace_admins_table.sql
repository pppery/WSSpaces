CREATE TABLE /*_*/wss_namespace_admins (
      namespace_id INT NOT NULL,
      admin_user_id INT NOT NULL,
      PRIMARY KEY ( namespace_id, admin_user_id ),
      FOREIGN KEY ( namespace_id )
          REFERENCES /*_*/wss_namespaces( namespace_id )
          ON DELETE CASCADE
) /*$wgDBTableOptions*/;