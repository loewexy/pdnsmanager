CREATE TABLE IF NOT EXISTS remote (
    id int(11) NOT NULL AUTO_INCREMENT,
    record int(11) NOT NULL,
    description varchar(255) NOT NULL,
    type varchar(20) NOT NULL,
    security varchar(2000) NOT NULL,
    nonce varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY record (record)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

ALTER TABLE `remote`
    ADD CONSTRAINT `remote_ibfk_1` FOREIGN KEY (`record`) REFERENCES `records` (`id`);

CREATE TABLE IF NOT EXISTS options (
    name varchar(255) NOT NULL,
    value varchar(2000) DEFAULT NULL,
    PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO options(name,value) VALUES ('schema_version', 1);