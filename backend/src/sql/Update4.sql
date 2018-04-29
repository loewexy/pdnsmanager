ALTER TABLE permissions DROP FOREIGN KEY permissions_ibfk_2;

RENAME TABLE user TO users;

ALTER TABLE permissions CHANGE user userid INT(11);

ALTER TABLE permissions
    ADD CONSTRAINT permissions_ibfk_2 FOREIGN KEY (userid) REFERENCES users (id) ON DELETE CASCADE;

ALTER TABLE users ADD CONSTRAINT UNIQUE KEY user_name_index (name);

UPDATE options SET value=4 WHERE name='schema_version';