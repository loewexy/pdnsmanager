ALTER TABLE `remote` 
    DROP FOREIGN KEY remote_ibfk_1; 

ALTER TABLE records MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE remote MODIFY record BIGINT;

ALTER TABLE `remote`
    ADD CONSTRAINT remote_ibfk_1 FOREIGN KEY(record) REFERENCES `records`(id);

UPDATE options SET value=7 WHERE name='schema_version';
