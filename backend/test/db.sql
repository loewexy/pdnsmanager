-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. Dez 2019 um 23:22
-- Server-Version: 5.7.23-0ubuntu0.16.04.1
-- PHP-Version: 7.0.30-0ubuntu0.16.04.1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE `comments`, `cryptokeys`, `domainmetadata`, `domains`, `options`, `permissions`, `records`, `remote`, `supermasters`, `tsigkeys`, `users`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `pdnsnew`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `modified_at` int(11) NOT NULL,
  `account` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `comment` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cryptokeys`
--

DROP TABLE IF EXISTS `cryptokeys`;
CREATE TABLE `cryptokeys` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `flags` int(11) NOT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `content` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `domainmetadata`
--

DROP TABLE IF EXISTS `domainmetadata`;
CREATE TABLE `domainmetadata` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `kind` varchar(32) DEFAULT NULL,
  `content` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `domains`
--

DROP TABLE IF EXISTS `domains`;
CREATE TABLE `domains` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `master` varchar(128) DEFAULT NULL,
  `last_check` int(11) DEFAULT NULL,
  `type` varchar(6) NOT NULL,
  `notified_serial` int(10) UNSIGNED DEFAULT NULL,
  `account` varchar(40) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `domains`
--

INSERT INTO `domains` (`id`, `name`, `master`, `last_check`, `type`, `notified_serial`, `account`) VALUES
(1, 'example.com', NULL, NULL, 'MASTER', NULL, NULL),
(2, 'slave.example.net', '12.34.56.78', NULL, 'SLAVE', NULL, NULL),
(3, 'foo.de', NULL, NULL, 'NATIVE', NULL, NULL),
(4, 'bar.net', NULL, NULL, 'MASTER', NULL, NULL),
(5, 'baz.org', NULL, NULL, 'MASTER', NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `options`
--

DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
  `name` varchar(255) NOT NULL,
  `value` varchar(2000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `options`
--

INSERT INTO `options` (`name`, `value`) VALUES
('schema_version', '7');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `domain_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `permissions`
--

INSERT INTO `permissions` (`domain_id`, `user_id`) VALUES
(1, 2),
(2, 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `records`
--

DROP TABLE IF EXISTS `records`;
CREATE TABLE `records` (
  `id` bigint(20) NOT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `content` varchar(64000) DEFAULT NULL,
  `ttl` int(11) DEFAULT NULL,
  `prio` int(11) DEFAULT NULL,
  `change_date` int(11) DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT '0',
  `ordername` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `records`
--

INSERT INTO `records` (`id`, `domain_id`, `name`, `type`, `content`, `ttl`, `prio`, `change_date`, `disabled`, `ordername`, `auth`) VALUES
(1, 1, 'test.example.com', 'A', '12.34.56.78', 86400, 0, 1521645110, 0, NULL, 1),
(2, 1, 'sdfdf.example.com', 'TXT', 'foo bar baz', 60, 10, 1522321931, 0, NULL, 1),
(3, 1, 'foo.example.com', 'AAAA', '::1', 86400, 0, 1522321902, 0, NULL, 1),
(4, 3, 'foo.de', 'A', '9.8.7.6', 86400, 0, 1522321989, 0, NULL, 1),
(5, 1, 'example.com', 'SOA', 'ns1.example.com hostmaster.example.com 2018041300 3600 900 604800 86400', 86400, NULL, 1523629358, 0, NULL, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `remote`
--

DROP TABLE IF EXISTS `remote`;
CREATE TABLE `remote` (
  `id` int(11) NOT NULL,
  `record` bigint(20) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `security` varchar(2000) NOT NULL,
  `nonce` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `remote`
--

INSERT INTO `remote` (`id`, `record`, `description`, `type`, `security`, `nonce`) VALUES
(1, 1, 'Password Test', 'password', '$2y$10$abocd6jj/Tw4jzDtqTnjreNzwcerzkXwoVc.JvZBoZ6p0grEKDWoW', NULL),
(2, 4, 'Key Test', 'key', '-----BEGIN PUBLIC KEY-----\nMIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA5mu3aH90uSXY9sVLgVSz\nKj4FEctrpFDPyVC4ufbJa/44fuLABFe+IizgZUheNBBO7FjpLJYvsL24o6TEeht4\no5j0KHrRHXqp4WQuAL3ZREv/AhNaOC9/xyjoGwUkKkdC2bIfh0J/ACkezxvUrPsh\nbzhzY+co/M9PqlgTbjKjvlv/pRj2dSp98FzUme3HCh7Nn1EOM3yPMtaKNA9Qkkz1\noalfR3xmJjIanoS9zcK77/yyQ8VwI//CgxvnpnWbORZG0B9W2ZBoI8Bj4zprbbFG\nKNmrb403wfDijYF3MXpSMjKvJ5YVuZsn35EWIi5tqFc0oV7Ryy9nBHzKeoYN7Szs\nrXIS5+ZcQDLuN+pqJ7ByVaw4aVn85py8IdO0IYD5xeKd1i0iqm+KSoFTS1jiNSZu\n6iVl4odixWtW7oPLYBbd/vD2F7Ua5cLd12Rs+6kEVtlpnIf7txyFQL4QHYJxB7fI\ny+m70mfufVvKbFh/mHkhe+Arv71ERDMfAV3AD8++axLqYfU/LLFzanjwIBctAA9a\nj++G0lwl1adURwnBeq8+YrMU4/wg9efquKXLR40dU9nkMJOm5tPm+XHt4o3wio4X\n2FqnD57I7qJCWVc00HtpeWno5vHL+eJu0TdxjBuYXnQfwa1z9pWvGaoBtg7tyHgv\ng7YZJzF1MW5N9ZqnkdFJVEsCAwEAAQ==\n-----END PUBLIC KEY-----', NULL),
(3, 1, 'Key Test 2', 'key', '-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCrJ/UoQoN5rO1nwrWBNDr3TgPB\nkm6UmN/B6NY7RXcYTJOFEP6iWqTj9Pw8aT8/DSn2uTMeQK6kWNUAWmRaylQI2QHQ\ndPtrI6piTpjvKm+KbR+n3e4QJ/zOcg06cHYJJiyhPjfC12j3ZxINOV3LDbEKq4s0\nHxMGYZHPu+UezapeeQIDAQAB\n-----END PUBLIC KEY-----', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `supermasters`
--

DROP TABLE IF EXISTS `supermasters`;
CREATE TABLE `supermasters` (
  `ip` varchar(64) NOT NULL,
  `nameserver` varchar(255) NOT NULL,
  `account` varchar(40) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tsigkeys`
--

DROP TABLE IF EXISTS `tsigkeys`;
CREATE TABLE `tsigkeys` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `algorithm` varchar(50) DEFAULT NULL,
  `secret` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `backend` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `name`, `backend`, `type`, `password`) VALUES
(1, 'admin', 'native', 'admin', '$2y$10$9iIDHWgjY0pEsz8pZLXPx.gkMNDxTMzb7U0Um5hUGjKmUUHWQNXcW'),
(2, 'user', 'native', 'user', '$2y$10$MktCI4XcfD0FpIFSkxex6OVifnIw3Nqw6QJueWmjVte99wx6XGBoq'),
(3, 'configuser', 'config', 'user', NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_name_type_idx` (`name`,`type`),
  ADD KEY `comments_order_idx` (`domain_id`,`modified_at`);

--
-- Indizes für die Tabelle `cryptokeys`
--
ALTER TABLE `cryptokeys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domainidindex` (`domain_id`);

--
-- Indizes für die Tabelle `domainmetadata`
--
ALTER TABLE `domainmetadata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domainmetadata_idx` (`domain_id`,`kind`);

--
-- Indizes für die Tabelle `domains`
--
ALTER TABLE `domains`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`name`);

--
-- Indizes für die Tabelle `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`domain_id`,`user_id`),
  ADD KEY `permissions_ibfk_2` (`user_id`);

--
-- Indizes für die Tabelle `records`
--
ALTER TABLE `records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nametype_index` (`name`,`type`),
  ADD KEY `domain_id` (`domain_id`),
  ADD KEY `ordername` (`ordername`);

--
-- Indizes für die Tabelle `remote`
--
ALTER TABLE `remote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remote_ibfk_1` (`record`);

--
-- Indizes für die Tabelle `supermasters`
--
ALTER TABLE `supermasters`
  ADD PRIMARY KEY (`ip`,`nameserver`);

--
-- Indizes für die Tabelle `tsigkeys`
--
ALTER TABLE `tsigkeys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `namealgoindex` (`name`,`algorithm`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cryptokeys`
--
ALTER TABLE `cryptokeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `domainmetadata`
--
ALTER TABLE `domainmetadata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `domains`
--
ALTER TABLE `domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `records`
--
ALTER TABLE `records`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `remote`
--
ALTER TABLE `remote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT für Tabelle `tsigkeys`
--
ALTER TABLE `tsigkeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `records`
--
ALTER TABLE `records`
  ADD CONSTRAINT `records_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `remote`
--
ALTER TABLE `remote`
  ADD CONSTRAINT `remote_ibfk_1` FOREIGN KEY (`record`) REFERENCES `records` (`id`);
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
