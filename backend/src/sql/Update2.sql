ALTER TABLE permissions
    DROP FOREIGN KEY permissions_ibfk_1;

ALTER TABLE permissions
    DROP FOREIGN KEY permissions_ibfk_2;

ALTER TABLE permissions
    ADD CONSTRAINT permissions_ibfk_1 FOREIGN KEY (domain) REFERENCES domains (id) ON DELETE CASCADE;

ALTER TABLE permissions
    ADD CONSTRAINT permissions_ibfk_2 FOREIGN KEY (user) REFERENCES user (id) ON DELETE CASCADE;

ALTER TABLE remote
    DROP FOREIGN KEY remote_ibfk_1;

ALTER TABLE remote
    ADD CONSTRAINT remote_ibfk_1 FOREIGN KEY (record) REFERENCES records (id) ON DELETE CASCADE;

ALTER TABLE records
    ADD CONSTRAINT records_ibfk_1 FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE;
    
UPDATE options SET value=2 WHERE name='schema_version';