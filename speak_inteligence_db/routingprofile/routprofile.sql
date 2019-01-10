/*
SQLyog Ultimate v11.42 (64 bit)
MySQL - 5.7.18 : Database - speakintelligentRouting
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`speakintelligentRouting` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `speakintelligentRouting`;

/*Table structure for table `tblRoutingCategory` */

DROP TABLE IF EXISTS `tblRoutingCategory`;

CREATE TABLE `tblRoutingCategory` (
  `RoutingCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `CompanyID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Order` int(11) DEFAULT NULL,
  PRIMARY KEY (`RoutingCategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `tblRoutingProfile` */

DROP TABLE IF EXISTS `tblRoutingProfile`;

CREATE TABLE `tblRoutingProfile` (
  `RoutingProfileID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `SelectionCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RoutingPolicy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  PRIMARY KEY (`RoutingProfileID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `tblRoutingProfileCategory` */

DROP TABLE IF EXISTS `tblRoutingProfileCategory`;

CREATE TABLE `tblRoutingProfileCategory` (
  `RoutingProfileCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `RoutingProfileID` int(11) DEFAULT NULL,
  `RoutingCategoryID` int(11) DEFAULT NULL,
  `Order` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`RoutingProfileCategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=latin1;

/*Table structure for table `tblRoutingProfileToCustomer` */

DROP TABLE IF EXISTS `tblRoutingProfileToCustomer`;

CREATE TABLE `tblRoutingProfileToCustomer` (
  `RoutingProfileToCustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `RoutingProfileID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `TrunkID` int(11) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`RoutingProfileToCustomerID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

/* Procedure structure for procedure `prc_getAssignRoutingProfileByAccount` */

/*!50003 DROP PROCEDURE IF EXISTS  `prc_getAssignRoutingProfileByAccount` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAssignRoutingProfileByAccount`(IN `p_companyid` INT,
	IN `p_level` VARCHAR(1),
	IN `p_trunkid` INT,
	IN `p_AccountIds` TEXT,
	IN `p_services` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
			
	IF (p_level="S") THEN
		SELECT DISTINCT a.AccountID,a.AccountName,d.Name,s.ServiceName,b.ServiceID,c.RoutingProfileID  FROM  speakintelligentRM.tblAccount a 
		
		LEFT JOIN speakintelligentRM.tblAccountService b  ON a.AccountID=b.AccountID
		LEFT JOIN speakintelligentRM.tblService s ON b.ServiceID=s.ServiceID
		
		LEFT JOIN speakintelligentRouting.tblRoutingProfileToCustomer c ON a.AccountID=c.AccountID AND s.ServiceID=c.ServiceID
		LEFT JOIN speakintelligentRouting.tblRoutingProfile d ON c.RoutingProfileID=d.RoutingProfileID
		
		WHERE a.`Status`=1 AND a.AccountType=1 AND a.CompanyId=p_companyid  AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
		AND a.IsCustomer=1 AND a.IsReseller=1 AND s.`Status`=1 AND (p_services='' OR FIND_IN_SET(s.ServiceID,p_services) != 0 )
		
		ORDER BY
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
		 END DESC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN NAME
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN NAME
		 END DESC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameASC') THEN ServiceName
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameDESC') THEN ServiceName
		 END DESC
						 
		 LIMIT p_RowspPage OFFSET v_OffSet_; 
		 
		 SELECT COUNT(*) AS `totalcount`  FROM  speakintelligentRM.tblAccount a 
		
		LEFT JOIN speakintelligentRM.tblAccountService b  ON a.AccountID=b.AccountID
		LEFT JOIN speakintelligentRM.tblService s ON b.ServiceID=s.ServiceID
		
		LEFT JOIN speakintelligentRouting.tblRoutingProfileToCustomer c ON a.AccountID=c.AccountID AND s.ServiceID=c.ServiceID
		LEFT JOIN speakintelligentRouting.tblRoutingProfile d ON c.RoutingProfileID=d.RoutingProfileID
		
		WHERE a.`Status`=1 AND a.AccountType=1 AND a.CompanyId=p_companyid  AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
		AND a.IsCustomer=1 AND a.IsReseller=1 AND s.`Status`=1 AND (p_services='' OR FIND_IN_SET(s.ServiceID,p_services) != 0 );
		
						
        ELSEIF (p_level="A") THEN
             SELECT DISTINCT a.AccountID,a.AccountName,d.Name, c.RoutingProfileID
		FROM  speakintelligentRM.tblAccount a 				
		LEFT JOIN speakintelligentRouting.tblRoutingProfileToCustomer c ON a.AccountID=c.AccountID
		LEFT JOIN speakintelligentRouting.tblRoutingProfile d ON c.RoutingProfileID=d.RoutingProfileID				
		WHERE a.IsCustomer=1 AND a.IsReseller=1 AND a.`Status`=1 AND a.AccountType=1 AND a.CompanyId=p_companyid AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
		ORDER BY
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
		 END DESC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN NAME
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN NAME
		 END DESC
		 LIMIT p_RowspPage OFFSET v_OffSet_;
		 
		 SELECT COUNT(*) AS `totalcount`
			FROM  speakintelligentRM.tblAccount a 				
			LEFT JOIN speakintelligentRouting.tblRoutingProfileToCustomer c ON a.AccountID=c.AccountID
			LEFT JOIN speakintelligentRouting.tblRoutingProfile d ON c.RoutingProfileID=d.RoutingProfileID				
			WHERE a.IsCustomer=1 AND a.IsReseller=1 AND a.`Status`=1 AND a.AccountType=1 AND a.CompanyId=p_companyid 
			AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 );
		 
        ELSEIF (p_level="T") THEN
             SELECT DISTINCT a.AccountID,a.AccountName,d.Name,s.Trunk,s.TrunkID, c.RoutingProfileID
		FROM  speakintelligentRM.tblAccount a 
		
		JOIN speakintelligentRM.tblCustomerTrunk b  ON b.AccountID =a.AccountID
		LEFT JOIN speakintelligentRM.tblTrunk s ON b.TrunkID=s.TrunkID		
					
		LEFT JOIN speakintelligentRouting.tblRoutingProfileToCustomer c ON a.AccountID=c.AccountID AND s.TrunkID=c.TrunkID
		LEFT JOIN speakintelligentRouting.tblRoutingProfile d ON c.RoutingProfileID=d.RoutingProfileID	
					
		WHERE a.IsCustomer=1 AND a.IsReseller=1 AND a.`Status`=1 AND a.AccountType=1 AND a.CompanyId=p_companyid AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
		AND (p_trunkid='' OR FIND_IN_SET(s.TrunkID,p_trunkid) != 0 )
		ORDER BY
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
		 END DESC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN NAME
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN NAME
		 END DESC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkASC') THEN Trunk
		 END ASC,
		 CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkDESC') THEN Trunk
		 END DESC
						 
		 LIMIT p_RowspPage OFFSET v_OffSet_; 
		 
		 SELECT COUNT(*) AS `totalcount` FROM  speakintelligentRM.tblAccount a 
		
			JOIN speakintelligentRM.tblCustomerTrunk b  ON b.AccountID =a.AccountID
			LEFT JOIN speakintelligentRM.tblTrunk s ON b.TrunkID=s.TrunkID		
						
			LEFT JOIN speakintelligentRouting.tblRoutingProfileToCustomer c ON a.AccountID=c.AccountID AND s.TrunkID=c.TrunkID
			LEFT JOIN speakintelligentRouting.tblRoutingProfile d ON c.RoutingProfileID=d.RoutingProfileID	
						
			WHERE a.IsCustomer=1 AND a.IsReseller=1 AND a.`Status`=1 AND a.AccountType=1 AND a.CompanyId=p_companyid AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
			AND (p_trunkid='' OR FIND_IN_SET(s.TrunkID,p_trunkid) != 0 );
        ELSE
             SELECT DISTINCT a.AccountID,a.AccountName,d.Name,t.Trunk, c.RoutingProfileID
		FROM  speakintelligentRM.tblAccount a 
		JOIN speakintelligentRM.tblTrunk t
		LEFT JOIN speakintelligentRM.tblCustomerTrunk ct ON a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID				
		LEFT JOIN speakintelligentRouting.tblRoutingProfileToCustomer c ON a.AccountID=c.AccountID
		LEFT JOIN speakintelligentRouting.tblRoutingProfile d ON c.RoutingProfileID=d.RoutingProfileID				
		WHERE  a.IsCustomer=1 AND a.IsReseller=1 AND a.`Status`=1 AND a.AccountType=1 AND a.CompanyId=p_companyid AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 );
        END IF;
				
END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
