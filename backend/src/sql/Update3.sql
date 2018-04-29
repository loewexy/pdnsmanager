CREATE TABLE IF NOT EXISTS domainmetadata (
    id INT AUTO_INCREMENT,
    domain_id INT NOT NULL,
    kind VARCHAR(32),
    content TEXT,
    PRIMARY KEY (id)
) Engine=InnoDB;

ALTER TABLE records ADD disabled TINYINT(1) DEFAULT 0;
ALTER TABLE records ADD auth TINYINT(1) DEFAULT 1;

UPDATE options SET value=3 WHERE name='schema_version';