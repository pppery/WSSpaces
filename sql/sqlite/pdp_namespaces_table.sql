CREATE TABLE /*_*/pdp_namespaces (
      namespace_id INT NOT NULL PRIMARY KEY,
      namespace_name INT NOT NULL UNIQUE,
      display_name VARCHAR(24) NOT NULL,
      description VARCHAR(1024) NOT NULL,
      creator_id INT NOT NULL,
      created_on BIGINT NOT NULL,
      archived BOOLEAN NOT NULL DEFAULT FALSE
) /*$wgDBTableOptions*/;