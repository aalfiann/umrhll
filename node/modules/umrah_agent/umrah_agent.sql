SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for umrah_agent
-- ----------------------------
DROP TABLE IF EXISTS `umrah_agent`;
CREATE TABLE `umrah_agent` (
  `ID` varchar(50) NOT NULL,
  `Fullname` varchar(50) NOT NULL,
  `StatusID` int(11) NOT NULL,
  `Custom_id` varchar(255) DEFAULT NULL,
  `Extend` text,
  `Created_at` datetime NOT NULL,
  `Created_by` varchar(50) NOT NULL,
  `Updated_at` datetime DEFAULT NULL,
  `Updated_by` varchar(50) DEFAULT NULL,
  `Updated_sys` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `StatusID` (`StatusID`),
  KEY `Created_at` (`Created_at`),
  KEY `Created_by` (`Created_by`),
  KEY `Custom_id` (`Custom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET FOREIGN_KEY_CHECKS=1;