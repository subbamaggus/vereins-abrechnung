-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 15. Nov 2025 um 10:11
-- Server-Version: 8.0.43-0ubuntu0.24.04.2
-- PHP-Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `accounting`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_attribute`
--

CREATE TABLE `account_attribute` (
  `id` int NOT NULL,
  `mandant_id` int NOT NULL,
  `name` varchar(20) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_attribute_item`
--

CREATE TABLE `account_attribute_item` (
  `id` int NOT NULL,
  `mandant_id` int NOT NULL,
  `name` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `attribute_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_depot`
--

CREATE TABLE `account_depot` (
  `id` int NOT NULL,
  `mandant_id` int NOT NULL,
  `name` varchar(30) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_depot_value`
--

CREATE TABLE `account_depot_value` (
  `id` int NOT NULL,
  `depot_id` int NOT NULL,
  `mandant_id` int NOT NULL,
  `value` int NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_item`
--

CREATE TABLE `account_item` (
  `id` int NOT NULL,
  `mandant_id` int NOT NULL,
  `name` text COLLATE latin1_general_cs NOT NULL,
  `value` int NOT NULL,
  `date` date NOT NULL,
  `user` int NOT NULL,
  `file` text COLLATE latin1_general_cs
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_item_attribute_item`
--

CREATE TABLE `account_item_attribute_item` (
  `id` int NOT NULL,
  `mandant_id` int NOT NULL,
  `attribute_item_id` int NOT NULL,
  `item_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_mandant`
--

CREATE TABLE `account_mandant` (
  `id` int NOT NULL,
  `name` text COLLATE latin1_general_cs NOT NULL,
  `apikey` text COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_mandant_user`
--

CREATE TABLE `account_mandant_user` (
  `id` int NOT NULL,
  `mandant_id` int NOT NULL,
  `user_id` int NOT NULL,
  `privilege` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account_user`
--

CREATE TABLE `account_user` (
  `id` int NOT NULL,
  `email` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `name` text CHARACTER SET latin1 COLLATE latin1_general_cs,
  `vorname` text CHARACTER SET latin1 COLLATE latin1_general_cs,
  `password` varchar(256) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `login_hash` varchar(256) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `account_attribute`
--
ALTER TABLE `account_attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `account_attribute_item`
--
ALTER TABLE `account_attribute_item`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `account_depot`
--
ALTER TABLE `account_depot`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `account_depot_value`
--
ALTER TABLE `account_depot_value`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `account_item`
--
ALTER TABLE `account_item`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `account_item_attribute_item`
--
ALTER TABLE `account_item_attribute_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attribute_item_id` (`attribute_item_id`,`item_id`,`mandant_id`) USING BTREE;

--
-- Indizes für die Tabelle `account_mandant`
--
ALTER TABLE `account_mandant`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `account_mandant_user`
--
ALTER TABLE `account_mandant_user`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `account_user`
--
ALTER TABLE `account_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `account_attribute`
--
ALTER TABLE `account_attribute`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_attribute_item`
--
ALTER TABLE `account_attribute_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_depot`
--
ALTER TABLE `account_depot`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_depot_value`
--
ALTER TABLE `account_depot_value`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_item`
--
ALTER TABLE `account_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_item_attribute_item`
--
ALTER TABLE `account_item_attribute_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_mandant`
--
ALTER TABLE `account_mandant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_mandant_user`
--
ALTER TABLE `account_mandant_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `account_user`
--
ALTER TABLE `account_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
