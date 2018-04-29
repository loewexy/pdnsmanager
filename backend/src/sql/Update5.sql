ALTER DATABASE CHARACTER SET utf8 COLLATE = utf8_general_ci;

ALTER TABLE `domainmetadata` 
    ADD INDEX domainmetadata_idx (domain_id,kind);

ALTER TABLE `domains` 
    DROP PRIMARY KEY, 
    DROP INDEX name_index, 
    ADD PRIMARY KEY(`id`), 
    ADD UNIQUE INDEX name_index (name), 
    CHANGE COLUMN notified_serial notified_serial int(10) unsigned NULL;

ALTER TABLE `permissions` 
    DROP FOREIGN KEY permissions_ibfk_1, 
    DROP FOREIGN KEY permissions_ibfk_2,
    DROP INDEX domain, 
    DROP PRIMARY KEY, 
    CHANGE `userid` `user_id` INT(11) NOT NULL,
    CHANGE `domain` `domain_id` INT(11) NOT NULL;

ALTER TABLE `permissions`
    ADD CONSTRAINT permissions_ibfk_1 FOREIGN KEY(user_id) REFERENCES `users`(id), 
    ADD CONSTRAINT permissions_ibfk_2 FOREIGN KEY(domain_id) REFERENCES `domains`(id), 
    ADD PRIMARY KEY(`domain_id`,`user_id`), 
    ADD INDEX permissions_ibfk_3 (domain_id), 
    COLLATE=utf8_general_ci;

ALTER TABLE `records` 
    DROP FOREIGN KEY records_ibfk_1, 
    DROP INDEX rec_name_index, 
    DROP INDEX nametype_index, 
    DROP PRIMARY KEY,
    ADD PRIMARY KEY(`id`), 
    DROP INDEX domain_id;

ALTER TABLE `records`
    ADD CONSTRAINT records_ibfk_1 FOREIGN KEY(domain_id) REFERENCES `domains`(id), 
    ADD INDEX nametype_index (name,type), 
    ADD INDEX domain_id (domain_id), 
    ADD INDEX ordername (ordername), 
    CHANGE COLUMN content content varchar(64000) NULL, 
    CHANGE COLUMN type type varchar(10) NULL, 
    ADD COLUMN ordername varchar(255) NULL AFTER disabled, 
    CHANGE COLUMN prio prio int(11) NULL, 
    CHANGE COLUMN auth auth tinyint(1) NULL DEFAULT '1';

ALTER TABLE `remote` 
    DROP FOREIGN KEY remote_ibfk_1, 
    DROP INDEX record, 
    DROP PRIMARY KEY,
    ADD PRIMARY KEY(`id`); 

ALTER TABLE `remote`
    ADD CONSTRAINT remote_ibfk_1 FOREIGN KEY(record) REFERENCES `records`(id), 
    ADD INDEX remote_ibfk_2 (record), 
    COLLATE=utf8_general_ci;

ALTER TABLE `users` 
    DROP PRIMARY KEY, 
    ADD PRIMARY KEY(`id`), 
    CHANGE COLUMN password password varchar(255) NULL AFTER type, 
    ADD COLUMN backend varchar(50) NOT NULL AFTER name, 
    COLLATE=utf8_general_ci;

CREATE TABLE `comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `type` varchar(10) NOT NULL,
    `modified_at` int(11) NOT NULL,
    `account` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
    `comment` text CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cryptokeys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain_id` int(11) NOT NULL,
    `flags` int(11) NOT NULL,
    `active` tinyint(1) DEFAULT NULL,
    `content` text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `supermasters` (
    `ip` varchar(64) NOT NULL,
    `nameserver` varchar(255) NOT NULL,
    `account` varchar(40) CHARACTER SET utf8 NOT NULL,
    PRIMARY KEY (`ip`, `nameserver`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `tsigkeys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `algorithm` varchar(50) DEFAULT NULL,
    `secret` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

UPDATE options SET value=5 WHERE name='schema_version';