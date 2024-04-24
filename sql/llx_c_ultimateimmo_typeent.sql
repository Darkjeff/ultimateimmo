

CREATE TABLE `llx_c_ultimateimmo_typeent` (
  `rowid` int(11) NOT NULL,
  `code` varchar(12) NOT NULL,
  `label` varchar(128) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `module` varchar(32) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `llx_c_ultimateimmo_typeent`
  ADD PRIMARY KEY (`rowid`),
  ADD UNIQUE KEY `uk_c_ultimateimmo_typeent` (`code`);

INSERT INTO `llx_c_ultimateimmo_typeent` (`rowid`, `code`, `label`,  `active`, `module`, `position`) VALUES
(1, 'TE_RENTER_1', 'Locataire particulier', 1, NULL, 0),
(2, 'TE_OWNER_1', 'Bailleur privé', 1, NULL, 0),
(3, 'TE_OWNER_2', 'Bailleur public', 0, NULL, 0),
(4, 'TE_OWNER_3', 'Bailleur social', 0, NULL, 0),
(5, 'TE_RENTER_2', 'Locataire en société', 1, NULL, 0);
