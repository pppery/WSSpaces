CREATE TABLE /*_*/wss_namespaces (
      namespace_id INT NOT NULL PRIMARY KEY,
      namespace_name INT NOT NULL UNIQUE,
      description VARCHAR(1024) NOT NULL,
      creator_id INT NOT NULL,
      created_on BIGINT NOT NULL,
      archived BOOLEAN NOT NULL DEFAULT FALSE
) /*$wgDBTableOptions*/;