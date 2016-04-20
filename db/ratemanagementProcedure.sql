-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.11 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for RateManagement4
CREATE DATABASE IF NOT EXISTS `RateManagement4` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `RateManagement4`;


-- Dumping structure for table RateManagement4.AccountEmailLog
CREATE TABLE IF NOT EXISTS `AccountEmailLog` (
  `AccountEmailLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `JobId` int(11) DEFAULT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `Emailfrom` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailTo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subject` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Message` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountEmailLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for procedure RateManagement4.fnVendorCurrentRates
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `fnVendorCurrentRates`(IN `p_companyid` INT , IN `p_codedeckID` INT, IN `p_trunkID` INT , IN `p_countryID` INT , IN `p_code` VARCHAR(50) , IN `p_accountID` INT 
    )
BEGIN
 			    
 			    
	
	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			Code varchar(50),
			Description varchar(200),
			Rate float,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
	);
	INSERT INTO tmp_VendorCurrentRates_ 
	  			    
      SELECT DISTINCT tblVendorRate.AccountId, tblRate.Code, tblRate.Description, tblVendorRate.Rate,
	  DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, 
	  tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID

                
      FROM      tblVendorRate  
                INNER JOIN tblAccount   ON tblVendorRate.AccountId = tblAccount.AccountID
                INNER JOIN tblRate  ON tblVendorRate.RateId = tblRate.RateID
                LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
                                                              AND tblVendorRate.AccountId = blockCode.AccountId
                                                              AND tblVendorRate.TrunkID = blockCode.TrunkID
                LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
                                                              AND tblVendorRate.AccountId = blockCountry.AccountId
                                                              AND tblVendorRate.TrunkID = blockCountry.TrunkID
      WHERE     ( EffectiveDate <= NOW() )
                AND tblAccount.CompanyId = p_companyid
                AND tblRate.CodeDeckId = p_codedeckID
                AND tblAccount.IsVendor = 1
                AND tblAccount.Status = 1
                AND tblVendorRate.TrunkID = p_trunkID
                AND ( CHAR_LENGTH(RTRIM(p_code)) = 0
                      OR tblRate.Code LIKE REPLACE(p_code,'*', '%')
                    )
                AND ( p_countryID = ''
                      OR tblRate.CountryID = p_countryID
                    )
                AND blockCode.RateId IS NULL
               AND blockCountry.CountryId IS NULL
                AND ( p_accountID = 0
                      OR ( p_accountID > 0
                           AND tblVendorRate.AccountId = p_accountID
                         )
                    );
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.fnVendorSippySheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `fnVendorSippySheet`(IN `p_AccountID` int, IN `p_Trunks` longtext)
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
            RateID int,
            `Action [A|D|U|S|SA` varchar(50),
            id varchar(10),
            Prefix varchar(50),
            COUNTRY varchar(200),
            Preference int,
            `Interval 1` int,
            `Interval N` int,
            `Price 1` float,
            `Price N` float,
            `1xx Timeout` int,
            `2xx Timeout` INT,
            Huntstop int,
            Forbidden int,
            `Activation Date` varchar(10),
            `Expiration Date` varchar(10),
            AccountID int,
            TrunkID int
    );
    

    call vwVendorCurrentRates(p_AccountID,p_Trunks); 
    
        SELECT NULL AS RateID,
               'A' AS `Action [A|D|U|S|SA]`,
               '' AS id,
               Concat(tblTrunk.Prefix , vendorRate.Code)  AS PREFIX,
               vendorRate.Description AS COUNTRY,
               5 AS Preference,
               CASE
                   WHEN vendorRate.Description LIKE '%gambia%'
                        OR vendorRate.Description LIKE '%mexico%' THEN 60
                   ELSE 1
               END AS `Interval 1`,
               CASE
                   WHEN vendorRate.Description LIKE '%mexico%' THEN 60
                   ELSE 1
               END AS `Interval N`,
               vendorRate.Rate AS `Price 1`,
               vendorRate.Rate AS `Price N`,
               10 AS `1xx Timeout`,
               60 AS `2xx Timeout`,
               0 AS Huntstop,
               CASE WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0) OR (blockCountry.VendorBlockingId IS NOT NULL AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0 ) 
                THEN 1 
                ELSE 0 
                END AS Forbidden,
               'NOW' AS `Activation Date`,
               '' AS `Expiration Date`,
               tblAccount.AccountID,
               tblTrunk.TrunkID
        FROM tmp_VendorSippySheet_ AS vendorRate
        INNER JOIN tblAccount ON vendorRate.AccountId = tblAccount.AccountID
        LEFT OUTER JOIN tblVendorBlocking ON vendorRate.RateID = tblVendorBlocking.RateId
        AND tblAccount.AccountID = tblVendorBlocking.AccountId
        AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
        LEFT OUTER JOIN tblVendorBlocking AS blockCountry ON vendorRate.CountryID = blockCountry.CountryId
        AND tblAccount.AccountID = blockCountry.AccountId
        AND vendorRate.TrunkID = blockCountry.TrunkID
        INNER JOIN tblTrunk ON tblTrunk.TrunkID = vendorRate.TrunkID
        WHERE vendorRate.AccountId = p_AccountID
          AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0
          AND vendorRate.Rate > 0;


     
END//
DELIMITER ;


-- Dumping structure for table RateManagement4.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` longtext COLLATE utf8_unicode_ci,
  `payload` longtext COLLATE utf8_unicode_ci,
  `attempts` int(11) DEFAULT NULL,
  `reserved` int(11) DEFAULT NULL,
  `reserved_at` int(11) DEFAULT NULL,
  `available_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for procedure RateManagement4.prc_ActiveCronJobEmail
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ActiveCronJobEmail`(IN `p_CompanyID` INT)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		tblCronJobCommand.*,
		tblCronJob.*
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Active = 1
	AND tblCronJobCommand.Command != 'activecronjobemail';
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_ArchiveOldCustomerRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldCustomerRate`(IN `p_CustomerIds` longtext, IN `p_TrunkIds` longtext)
BEGIN
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DELETE cr
      FROM tblCustomerRate cr
      INNER JOIN tblCustomerRate cr2
      ON cr2.CustomerID = cr.CustomerID
      AND cr2.TrunkID = cr.TrunkID
      AND cr2.RateID = cr.RateID
      WHERE  FIND_IN_SET(cr.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 AND cr.EffectiveDate <= NOW()
			AND FIND_IN_SET(cr2.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr2.TrunkID,p_TrunkIds) != 0 AND cr2.EffectiveDate <= NOW()
         AND cr.EffectiveDate < cr2.EffectiveDate;

	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
   CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
        INDEX _tmp_ArchiveOldEffectiveRates_CustomerRateID (`CustomerRateID`,`RateID`,`TrunkId`)
	);
	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates2 (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
       INDEX _tmp_ArchiveOldEffectiveRates_CustomerRateID (`CustomerRateID`,`RateID`,`TrunkId`)
	);

    


    
   INSERT INTO _tmp_ArchiveOldEffectiveRates (CustomerRateID,RateID,CustomerID,TrunkID,Rate,EffectiveDate)	
	SELECT
	   cr.CustomerRateID,
	   cr.RateID,
	   cr.CustomerID,
	   cr.TrunkID,
	   cr.Rate,
	   cr.EffectiveDate
	FROM tblCustomerRate cr
	WHERE  FIND_IN_SET(cr.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 
	ORDER BY cr.CustomerID ASC,cr.TrunkID ,cr.RateID ASC,EffectiveDate ASC;
	
	INSERT INTO _tmp_ArchiveOldEffectiveRates2
	SELECT * FROM _tmp_ArchiveOldEffectiveRates;

    DELETE tblCustomerRate
        FROM tblCustomerRate
        INNER JOIN(
        SELECT
            tt.CustomerRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
            ON tt.RowID = t.RowID + 1
            AND  t.CustomerId = tt.CustomerId
			AND  t.TrunkId = tt.TrunkId
            AND t.RateID = tt.RateID
            AND t.Rate = tt.Rate) aold on aold.CustomerRateID = tblCustomerRate.CustomerRateID;

     
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_ArchiveOldRateTableRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldRateTableRate`(IN `p_RateTableId` INT)
BEGIN

 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
 	
 	DELETE rtr
      FROM tblRateTableRate rtr
      INNER JOIN tblRateTableRate rtr2
      ON rtr.RateTableId = rtr2.RateTableId
      AND rtr.RateID = rtr2.RateID
      WHERE  FIND_IN_SET(rtr.RateTableId,p_RateTableId) != 0  AND rtr.EffectiveDate <= NOW()
			AND FIND_IN_SET(rtr2.RateTableId,p_RateTableId) != 0 AND rtr2.EffectiveDate <= NOW()
         AND rtr.EffectiveDate < rtr2.EffectiveDate;
    
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_ArchiveOldVendorRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldVendorRate`(IN `p_AccountIds` longtext
, IN `p_TrunkIds` longtext)
BEGIN
 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  
   DELETE vr
      FROM tblVendorRate vr
      INNER JOIN tblVendorRate vr2
      ON vr2.AccountId = vr.AccountId
      AND vr2.TrunkID = vr.TrunkID
      AND vr2.RateID = vr.RateID
      WHERE  FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0 AND vr.EffectiveDate <= NOW()
			AND FIND_IN_SET(vr2.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr2.TrunkID,p_TrunkIds) != 0 AND vr2.EffectiveDate <= NOW()
         AND vr.EffectiveDate < vr2.EffectiveDate;
	
   DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
   CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  VendorRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
        INDEX _tmp_ArchiveOldEffectiveRates_VendorRateID (`VendorRateID`,`RateID`,`TrunkId`,`CustomerID`)
	);
	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates2 (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  VendorRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
       INDEX _tmp_ArchiveOldEffectiveRates_VendorRateID (`VendorRateID`,`RateID`,`TrunkId`,`CustomerID`)
	);

    


    
   INSERT INTO _tmp_ArchiveOldEffectiveRates (VendorRateID,RateID,CustomerID,TrunkID,Rate,EffectiveDate)	
	SELECT
	   VendorRateID,
	   RateID,
	   AccountId,
	   TrunkID,
	   Rate,
	   EffectiveDate
	FROM tblVendorRate 
	WHERE  FIND_IN_SET(AccountId,p_AccountIds) != 0 AND FIND_IN_SET(TrunkID,p_TrunkIds) != 0 
	ORDER BY AccountId ASC,TrunkID ,RateID ASC,EffectiveDate ASC;
	
	INSERT INTO _tmp_ArchiveOldEffectiveRates2
	SELECT * FROM _tmp_ArchiveOldEffectiveRates;

    DELETE tblVendorRate
        FROM tblVendorRate
        INNER JOIN(
        SELECT
            tt.VendorRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
            ON tt.RowID = t.RowID + 1
            AND  t.CustomerID = tt.CustomerID
			   AND  t.TrunkId = tt.TrunkId
            AND t.RateID = tt.RateID
            AND t.Rate = tt.Rate) aold on aold.VendorRateID = tblVendorRate.VendorRateID;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_BlockVendorCodes
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_BlockVendorCodes`(IN `p_companyid` INT , IN `p_AccountId` VARCHAR(500), IN `p_trunkID` INT, IN `p_CountryIDs` VARCHAR(500), IN `p_Codes` VARCHAR(500), IN `p_Username` VARCHAR(100), IN `p_action` VARCHAR(100), IN `p_isCountry` INT, IN `p_isAllCountry` INT)
BEGIN
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	 
  	IF p_isAllCountry = 1		
	THEN	
		SELECT CONCAT(CountryID) INTO p_CountryIDs  FROM tblCountry;
	END IF;
	 
	IF p_isCountry = 0 AND p_action = 1 
   THEN 

		INSERT INTO tblVendorBlocking (AccountId,RateId,TrunkID,BlockedBy)
			SELECT DISTINCT tblVendorRate.AccountID,tblRate.RateID,tblVendorRate.TrunkID,p_Username
			FROM tblVendorRate
			INNER JOIN tblRate ON tblRate.RateID = tblVendorRate.RateId 
				AND tblRate.CompanyID = p_companyid
			LEFT JOIN tblVendorBlocking ON tblVendorBlocking.AccountId = tblVendorRate.AccountId 
				AND tblVendorBlocking.RateId = tblRate.RateID 
				AND tblVendorBlocking.TrunkID = p_trunkID
					
			WHERE tblVendorBlocking.VendorBlockingId IS NULL AND FIND_IN_SET(tblRate.Code,p_Codes) != 0 AND tblVendorRate.TrunkID = p_trunkID AND FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0 ;
	END IF; 

	IF p_isCountry = 0 AND p_action = 0 
	THEN
		DELETE  tblVendorBlocking
	  	FROM      tblVendorBlocking
	  	INNER JOIN tblVendorRate ON tblVendorRate.RateID = tblVendorBlocking.RateId
			AND tblVendorRate.TrunkID =  p_trunkID
	  	INNER JOIN tblRate ON  tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyid
		WHERE FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0 AND FIND_IN_SET(tblRate.Code,p_Codes) != 0 ;
	END IF;
	
	
	IF p_isCountry = 1 AND p_action = 1 
	THEN
		INSERT INTO tblVendorBlocking (AccountId,CountryId,TrunkID,BlockedBy)
		SELECT DISTINCT tblVendorRate.AccountID,tblRate.CountryID,p_trunkID,p_Username
		FROM tblVendorRate  
		INNER JOIN tblRate ON tblVendorRate.RateId = tblRate.RateID 
			AND tblRate.CompanyID = p_companyid
			AND  FIND_IN_SET(tblRate.CountryID,p_CountryIDs) != 0
		LEFT JOIN tblVendorBlocking ON tblVendorBlocking.AccountId = tblVendorRate.AccountID 
			AND tblRate.CountryID = tblVendorBlocking.CountryId
			AND tblVendorBlocking.TrunkID = p_trunkID
		WHERE tblVendorBlocking.VendorBlockingId IS NULL AND FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0
			AND tblVendorRate.TrunkID = p_trunkID;
		 
	END IF;
	
	
	IF p_isCountry = 1 AND p_action = 0 
	THEN
		DELETE FROM tblVendorBlocking
		WHERE tblVendorBlocking.TrunkID = p_trunkID AND FIND_IN_SET (tblVendorBlocking.AccountId,p_AccountId) !=0 AND FIND_IN_SET(tblVendorBlocking.CountryID,p_CountryIDs) != 0;
	END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_checkAccountIP
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkAccountIP`(IN `p_CompanyID` INT, IN `p_AccountIP` VARCHAR(50))
BEGIN
   
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SELECT AccountID
    FROM tblAccountAuthenticate 
    WHERE CompanyID = p_CompanyID AND  
	 ( FIND_IN_SET(p_AccountIP,CustomerAuthValue)!= 0 OR FIND_IN_SET(p_AccountIP,VendorAuthValue)!= 0 );  
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_checkCustomerCli
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkCustomerCli`(IN `p_CompanyID` INT, IN `p_CustomerCLI` varchar(50))
BEGIN
     
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SELECT AccountID
    FROM tblAccount a
    WHERE a.CompanyId =p_CompanyID AND FIND_IN_SET(p_CustomerCLI,CustomerCLI)!= 0;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_CronJobAllPending
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CronJobAllPending`(IN `p_CompanyID` INT)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	/**PendingUploadCDR**/
    SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CDR'
		AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
	ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CDR'
		AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


   /**PendingInvoiceGenerate**/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID,
	   TBL1.JobLoggedUserID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BI'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BI'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	/**PendingPortaSheet**/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'	
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'	
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


   /**getActiveCronCommand**/
	SELECT
		tblCronJobCommand.Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Status = 1
	AND tblCronJob.Active = 0;


	/**getVosDownloadCommand**/
	
	SELECT
		'vosdownloadcdr' as Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Status = 1
	AND tblCronJob.DownloadActive = 0
	AND tblCronJobCommand.Command = 'vosaccountusage';

	/**PendingBulkMailSend**/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BIS'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BIS'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	/** PortVendorSheet **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'	
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'	
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	/** Pending CDR Recalculate. **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RCC'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RCC'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	
	 

	/** PendingInvoice Usage File Generation. **/


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'INU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'INU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;
	
	/** Pending RatesheetGeneration **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
			AND j.Options like '%"Format":"Rate Sheet"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
			AND j.Options like '%"Format":"Rate Sheet"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	/** Pending InvoiceRegenerate **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BIR'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BIR'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	/** Bulk Lead Email **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BLE'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BLE'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	/** Bulk Account Email **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BAE'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BAE'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	/** Vendor Upload  **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VU'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	 /** Code Deck Upload **/
    SELECT
    	  "CodeDeckUpload",
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			   @prev_JobLoggedUserID  := j.JobLoggedUserID,
 			   @prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'CDU'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'CDU'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;

	   /** invoice reminder **/
    SELECT
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
				@prev_JobLoggedUserID  := j.JobLoggedUserID,
				@prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'IR'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'IR'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;



	/** Customer Sippysheet download **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'	
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'	
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	/** Vendor Sippysheet download **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'	
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	/** Customer VOSsheet download **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Vos 3.2"%'	
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Vos 3.2"%'	
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	/** Vendor VOSsheet download **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Vos 3.2"%'	
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Vos 3.2"%'	
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	/** Generate Rate Table **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'GRT'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'GRT'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;
	
	/** Rate Table Rate Upload **/
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RTU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RTU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;
	
	/**VendorUploadCDR**/
    SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VDR'
		AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
	ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VDR'
		AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;
	
	
	/* Sippyy CDR Download */
	SELECT
		'sippydownloadcdr' as Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Status = 1
	AND tblCronJob.DownloadActive = 0
	AND tblCronJobCommand.Command = 'sippyaccountusage';
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_CronJobGeneratePortaSheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CronJobGeneratePortaSheet`(IN `p_CustomerID` INT , IN `p_trunks` varchar(200) )
BEGIN
	DECLARE v_codedeckid_ INT;
   DECLARE v_ratetableid_ INT;
   DECLARE v_RateTableAssignDate_ DATETIME;
   DECLARE v_NewA2ZAssign_ INT;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
          
        DROP TEMPORARY TABLE IF EXISTS tmp_RateTable_;
        CREATE TEMPORARY TABLE tmp_RateTable_ (
	 			RateId INT,
            Rate DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            ConnectionFee DECIMAL(18,6),
            Trunk VARCHAR(50) ,
            IncludePrefix TINYINT,
            RatePrefix VARCHAR(50),
            AreaPrefix VARCHAR(50),
            INDEX tmp_RateTable_RateId (`RateId`)  
        );
        DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRate_;
        CREATE TEMPORARY TABLE tmp_CustomerRate_ (
            RateId INT ,
            Rate DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            ConnectionFee DECIMAL(18,6),
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            RatePrefix VARCHAR(50),
            AreaPrefix VARCHAR(50),
            INDEX tmp_CustomerRate_RateId (`RateId`)
        );
        DROP TEMPORARY TABLE IF EXISTS tmp_tbltblRate_;
        CREATE TEMPORARY TABLE tmp_tbltblRate_ (
            RateId INT 
        );
        SELECT
            CodeDeckId,
            RateTableID,
            RateTableAssignDate INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
        FROM tblCustomerTrunk
        WHERE 
        FIND_IN_SET(tblCustomerTrunk.TrunkID,p_trunks)!= 0  
        AND tblCustomerTrunk.AccountID = p_CustomerID
        AND tblCustomerTrunk.Status = 1
		  LIMIT 1;
        
      SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
	 	SELECT MAX(EffectiveDate) as EffectiveDate
		FROM 
		tblRateTableRate
		WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() 
		ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
	 	)tbl;
	 
    
        INSERT INTO tmp_RateTable_
             SELECT  RateId ,
                     tblRateTableRate.Rate ,
                     CASE WHEN v_NewA2ZAssign_ = 1 THEN
                         v_RateTableAssignDate_  
                     ELSE
                         tblRateTableRate.EffectiveDate
                     END  AS EffectiveDate,
                     tblRateTableRate.Interval1,
                     tblRateTableRate.IntervalN,
                     ConnectionFee ,
                     tblTrunk.Trunk ,
                     tblCustomerTrunk.IncludePrefix ,
                     tblTrunk.RatePrefix ,
                     tblTrunk.AreaPrefix
             FROM    tblAccount
                     JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                     JOIN tblRateTable ON tblCustomerTrunk.RateTableId = tblRateTable.RateTableId
                     JOIN tblRateTableRate ON tblRateTableRate.RateTableId = tblRateTable.RateTableId
                     JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerTrunk.TrunkId
             WHERE   tblAccount.AccountID = p_CustomerID
                    -- AND tblRateTableRate.Rate > 0
                     And FIND_IN_SET(tblTrunk.TrunkID,p_trunks)!= 0
                     AND ( EffectiveDate <= now() or ( v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW()) )
            ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC;
    
    		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTable4_ as (select * from tmp_RateTable_);	        
         DELETE n1 FROM tmp_RateTable_ n1, tmp_RateTable4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	   AND n1.Trunk = n2.Trunk
		   AND  n1.RateId = n2.RateId;
    
        INSERT INTO tmp_CustomerRate_
                SELECT  RateId ,
                        tblCustomerRate.Rate ,
                        EffectiveDate ,
                        tblCustomerRate.Interval1,
                        tblCustomerRate.IntervalN,
                        ConnectionFee ,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        CASE  WHEN t.RatePrefix is not null 
                        THEN
                            t.RatePrefix
                        ELSE
                            tblTrunk.RatePrefix
                        END AS RatePrefix,
                        CASE  WHEN t.AreaPrefix is not null
                        THEN
                            t.AreaPrefix
                        ELSE
                            tblTrunk.AreaPrefix
                        END AS AreaPrefix
                FROM    tblAccount
                        JOIN tblCustomerRate ON tblAccount.AccountID = tblCustomerRate.CustomerID
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerRate.TrunkId
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                            AND tblCustomerTrunk.TrunkId = tblTrunk.TrunkId
                        LEFT JOIN tblCustomerTrunk ct ON ct.AccountId = tblCustomerRate.CustomerID and ct.TrunkID =tblCustomerRate.RoutinePlan AND ct.RoutinePlanStatus = 1
                        LEFT JOIN tblTrunk t ON t.TrunkID = tblCustomerRate.RoutinePlan 
                        
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblCustomerRate.Rate > 0 
                        And FIND_IN_SET(tblTrunk.TrunkID,p_trunks)!= 0
                        AND EffectiveDate <= now()
               ORDER BY tblCustomerRate.CustomerId,tblCustomerRate.TrunkId,tblCustomerRate.RateID,tblCustomerRate.effectivedate DESC;
               
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRate_);	        
         DELETE n1 FROM tmp_CustomerRate_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	   AND n1.Trunk = n2.Trunk
		   AND  n1.RateId = n2.RateId;
    

    		INSERT INTO tmp_tbltblRate_
			SELECT    RateID
			FROM      tmp_RateTable_
         UNION
         SELECT    RateID
         FROM      tmp_CustomerRate_; 
            
        SELECT distinct 
                Code as `Destination` ,
                Description  as `Description`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.Interval1 
                                ELSE
                                    rt.Interval1
                                END as `First Interval`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.IntervalN 
                                ELSE
                                    rt.IntervalN
                                END as `Next Interval`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    Abs(cr.Rate) 
                                ELSE
                                    Abs(rt.Rate)
                                END as `First Price` ,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    Abs(cr.Rate) 
                                ELSE
                                    Abs(rt.Rate)
                                END as `Next Price`,
               CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                        DATE_FORMAT(cr.EffectiveDate ,'%d/%m/%Y')
                                    ELSE
                                        DATE_FORMAT(rt.EffectiveDate ,'%d/%m/%Y')
                                    END as  `Effective From`,
         CASE WHEN ( CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.Rate
                                ELSE
                                    rt.Rate
                                 	END ) < 0 THEN 'Y' ELSE '' 
								END  'Payback Rate' ,
			 CASE WHEN cr.RateID IS NOT NULL  and cr.ConnectionFee > 0 THEN
									CONCAT('SEQ=', cr.ConnectionFee,'&int1x1@price1&intNxN@priceN')
								ELSE
									CASE WHEN rt.RateID IS NOT NULL  and rt.ConnectionFee > 0 THEN
										CONCAT('SEQ=', rt.ConnectionFee,'&int1x1@price1&intNxN@priceN')
										ELSE
										''
									END
								END as `Formula`
			
            
        FROM    tblRate
                LEFT JOIN tmp_RateTable_ rt ON rt.RateID = tblRate.RateID
                LEFT JOIN tmp_CustomerRate_ cr ON cr.RateID = tblRate.RateID
		 WHERE tblRate.RateID in (SELECT RateID FROM tmp_tbltblRate_);
		 
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_CronJobGeneratePortaVendorSheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CronJobGeneratePortaVendorSheet`(IN `p_AccountID` INT , IN `p_trunks` varchar(200) )
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
	 			RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        INDEX tmp_RateTable_RateId (`RateId`)  
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `RateId`, `Rate`, `EffectiveDate`, `Interval1`, `IntervalN`, `ConnectionFee`
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID 
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0 
                        AND EffectiveDate <= NOW();

      CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);	        
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId;
                        
         
       
       SELECT  tblRate.Code as `Destination`,
               tblRate.Description as `Description` ,
               CASE WHEN tblVendorRate.Interval1 IS NOT NULL
                   THEN tblVendorRate.Interval1
                   ElSE tblRate.Interval1
               END AS `First Interval`,
               CASE WHEN tblVendorRate.IntervalN IS NOT NULL
                   THEN tblVendorRate.IntervalN
                   ElSE tblRate.IntervalN
               END  AS `Next Interval`,
               Abs(tblVendorRate.Rate) as `First Price`,
               Abs(tblVendorRate.Rate) as `Next Price`,
               tblVendorRate.EffectiveDate as `Effective From` ,
               IFNULL(Preference,5) as `Preference`,
               CASE
                   WHEN (blockCode.VendorBlockingId IS NOT NULL AND
                   	FIND_IN_SET(tblVendorRate.TrunkId,blockCode.TrunkId) != 0
                       )OR
                       (blockCountry.VendorBlockingId IS NOT NULL AND
                       FIND_IN_SET(tblVendorRate.TrunkId,blockCountry.TrunkId) != 0
                       ) THEN 1
                   ELSE 0
               END AS `Forbidden`,
               CASE WHEN tblVendorRate.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
               CASE WHEN ConnectionFee > 0 THEN
						CONCAT('SEQ=',ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`
       FROM    tmp_VendorRate_ as tblVendorRate 
               JOIN tblRate on tblVendorRate.RateId =tblRate.RateID
               LEFT JOIN tblVendorBlocking as blockCode
                   ON tblVendorRate.RateID = blockCode.RateId
                   AND blockCode.AccountId = p_AccountID
                   AND tblVendorRate.TrunkID = blockCode.TrunkID
               LEFT JOIN tblVendorBlocking AS blockCountry
                   ON tblRate.CountryID = blockCountry.CountryId
                   AND blockCountry.AccountId = p_AccountID
                   AND tblVendorRate.TrunkID = blockCountry.TrunkID
					LEFT JOIN tblVendorPreference 
						ON tblVendorPreference.AccountId = p_AccountID
						AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
						AND tblVendorPreference.RateId = tblVendorRate.RateId;
                        
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_CustomerBulkRateClear
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerBulkRateClear`(IN `p_AccountIdList` LONGTEXT , IN `p_TrunkId` INT , IN `p_CodeDeckId` int, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(200) , IN `p_CountryId` INT , IN `p_CompanyId` INT)
BEGIN

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 
	delete tblCustomerRate
	from tblCustomerRate
   INNER JOIN ( 
		SELECT c.CustomerRateID
      FROM   tblCustomerRate c
      INNER JOIN tblCustomerTrunk
          ON tblCustomerTrunk.TrunkID = c.TrunkID
          AND tblCustomerTrunk.AccountID = c.CustomerID
          AND tblCustomerTrunk.Status = 1
      INNER JOIN tblRate r
          ON c.RateID = r.RateID and r.CodeDeckId=p_CodeDeckId
		WHERE c.TrunkID = p_TrunkId
			   AND FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 
				AND ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
				AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
				AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) ) 
		) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_CustomerBulkRateUpdate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerBulkRateUpdate`(IN `p_AccountIdList` LONGTEXT , IN `p_TrunkId` INT , IN `p_CodeDeckId` int, IN `p_code` VARCHAR(50) , IN `p_description` VARCHAR(200) , IN `p_CountryId` INT , IN `p_CompanyId` INT , IN `p_Rate` DECIMAL(18, 6) , IN `p_ConnectionFee` DECIMAL(18, 6) , IN `p_EffectiveDate` DATETIME , IN `p_Interval1` INT, IN `p_IntervalN` INT, IN `p_RoutinePlan` INT, IN `p_ModifiedBy` VARCHAR(50))
BEGIN

 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   UPDATE  tblCustomerRate
   INNER JOIN ( 
		SELECT c.CustomerRateID, 
			c.EffectiveDate,
			CASE WHEN ctr.TrunkID IS NOT NULL 
				THEN p_RoutinePlan 
			ELSE NULL
				END AS RoutinePlan
      FROM   tblCustomerRate c
    	INNER JOIN tblCustomerTrunk 
			ON tblCustomerTrunk.TrunkID = c.TrunkID
         AND tblCustomerTrunk.AccountID = c.CustomerID
         AND tblCustomerTrunk.Status = 1
      INNER JOIN tblRate r 
			ON c.RateID = r.RateID and r.CodeDeckId=p_CodeDeckId
		LEFT JOIN tblCustomerTrunk ctr
			ON ctr.TrunkID = c.TrunkID
			AND ctr.AccountID = c.CustomerID
			AND ctr.RoutinePlanStatus = 1
      WHERE  ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
				AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
				AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) ) 
				AND c.TrunkID = p_TrunkId
	         AND FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0
			) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID and cr.EffectiveDate = p_EffectiveDate
		 SET tblCustomerRate.PreviousRate = Rate ,
           tblCustomerRate.Rate = p_Rate ,
			  tblCustomerRate.ConnectionFee = p_ConnectionFee ,
           tblCustomerRate.EffectiveDate = p_EffectiveDate ,
           tblCustomerRate.Interval1 = p_Interval1, 
			  tblCustomerRate.IntervalN = p_IntervalN,
			  tblCustomerRate.RoutinePlan = cr.RoutinePlan,
           tblCustomerRate.LastModifiedBy = p_ModifiedBy ,
           tblCustomerRate.LastModifiedDate = NOW();



        INSERT  INTO tblCustomerRate
                ( RateID ,
                  CustomerID ,
                  TrunkID ,
                  Rate ,
				  		ConnectionFee,
                  EffectiveDate ,
                  Interval1, 
				  		IntervalN ,
				  		RoutinePlan,
                  CreatedDate ,
                  LastModifiedBy ,
                  LastModifiedDate
                )
                SELECT  r.RateID ,
                        r.AccountId ,
                        p_TrunkId ,
                        p_Rate ,
								p_ConnectionFee,
                        p_EffectiveDate ,
                        p_Interval1, 
					    		p_IntervalN,
								RoutinePlan,
                        NOW() ,
                        p_ModifiedBy ,
                        NOW()
                FROM    ( SELECT    RateID,Code,AccountId,CompanyID,CodeDeckId,Description,CountryID,RoutinePlan
                          FROM      tblRate ,
                                    (  SELECT   a.AccountId,
													CASE WHEN ctr.TrunkID IS NOT NULL 
														THEN p_RoutinePlan 
														ELSE NULL
													END AS RoutinePlan
                                       FROM tblAccount a 
													INNER JOIN tblCustomerTrunk 
														ON TrunkID = p_TrunkId
                                       	AND a.AccountID    = tblCustomerTrunk.AccountID
                                          AND tblCustomerTrunk.Status = 1
													LEFT JOIN tblCustomerTrunk ctr
														ON ctr.TrunkID = p_TrunkId
														AND ctr.AccountID = a.AccountID
														AND ctr.RoutinePlanStatus = 1
												WHERE FIND_IN_SET(a.AccountID,p_AccountIdList) != 0
                                    ) a
                        ) r
                        LEFT OUTER JOIN ( SELECT DISTINCT
                                                    RateID ,
                                                    c.CustomerID as AccountId ,
                                                    c.TrunkID,
                                                    c.EffectiveDate
                                          FROM      tblCustomerRate c
														INNER JOIN tblCustomerTrunk 
															ON tblCustomerTrunk.TrunkID = c.TrunkID
                                          	AND tblCustomerTrunk.AccountID = c.CustomerID
                                             AND tblCustomerTrunk.Status = 1
                                          WHERE FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 AND c.TrunkID = p_TrunkId
                                        ) cr ON r.RateID = cr.RateID
                                                AND r.AccountId = cr.AccountId
                                                AND r.CompanyID = p_CompanyId    
                                                and cr.EffectiveDate = p_EffectiveDate
                WHERE   r.CompanyID = p_CompanyId
						AND r.CodeDeckId=p_CodeDeckId 
                  AND ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
						AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
						AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) )
      				AND cr.RateID IS NULL; 
      				
      CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId);

    	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_CustomerRateClear
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerRateClear`(IN `p_CustomerRateId` int)
BEGIN
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
  DELETE FROM tblCustomerRate
    WHERE CustomerRateId = p_CustomerRateId;
    
 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_CustomerRateUpdateBySelectedRateId
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerRateUpdateBySelectedRateId`(IN `p_CompanyId` INT, IN `p_AccountIdList` LONGTEXT , IN `p_RateIDList` LONGTEXT , IN `p_TrunkId` VARCHAR(100) , IN `p_Rate` DECIMAL(18, 6) , IN `p_ConnectionFee` DECIMAL(18, 6) , IN `p_EffectiveDate` DATETIME , IN `p_Interval1` INT, IN `p_IntervalN` INT, IN `p_RoutinePlan` INT, IN `p_ModifiedBy` VARCHAR(50))
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	UPDATE  tblCustomerRate
	INNER JOIN ( 
		SELECT c.CustomerRateID,c.EffectiveDate,
			CASE WHEN ctr.TrunkID IS NOT NULL 
				THEN p_RoutinePlan 
			ELSE 0
				END AS RoutinePlan
      FROM   tblCustomerRate c
			
         INNER JOIN tblCustomerTrunk ON tblCustomerTrunk.TrunkID = c.TrunkID
         	AND tblCustomerTrunk.AccountID = c.CustomerID
            AND tblCustomerTrunk.Status = 1
            AND c.EffectiveDate = p_EffectiveDate
			LEFT JOIN tblCustomerTrunk ctr
				ON ctr.TrunkID = c.TrunkID
				AND ctr.AccountID = c.CustomerID
				AND ctr.RoutinePlanStatus = 1
            ) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID and cr.EffectiveDate = p_EffectiveDate
		SET     
			tblCustomerRate.PreviousRate = Rate ,
         tblCustomerRate.Rate = p_Rate ,
			tblCustomerRate.ConnectionFee = p_ConnectionFee,
         tblCustomerRate.EffectiveDate = p_EffectiveDate ,
         tblCustomerRate.Interval1 = p_Interval1, 
			tblCustomerRate.IntervalN = p_IntervalN,
         tblCustomerRate.LastModifiedBy = p_ModifiedBy ,
			tblCustomerRate.RoutinePlan = cr.RoutinePlan,
         tblCustomerRate.LastModifiedDate = NOW()
			WHERE tblCustomerRate.TrunkID = p_TrunkId
         AND FIND_IN_SET(tblCustomerRate.RateID,p_RateIDList) != 0
         AND FIND_IN_SET(tblCustomerRate.CustomerID,p_AccountIdList) != 0;
                           
                           
        INSERT  INTO tblCustomerRate
                ( RateID ,
                  CustomerID ,
                  TrunkID ,
                  Rate ,
					   ConnectionFee,
                  EffectiveDate ,
                  Interval1, 
				  		IntervalN ,
				  		RoutinePlan,
                  CreatedDate ,
                  LastModifiedBy ,
                  LastModifiedDate
                )
                SELECT  r.RateID,
                        r.AccountId ,
                        p_TrunkId ,
                        p_Rate ,
								p_ConnectionFee,
                        p_EffectiveDate ,
                        p_Interval1, 
					    		p_IntervalN,
								RoutinePlan,
                        NOW() ,
                        p_ModifiedBy ,
                        NOW()
                FROM    ( SELECT    tblRate.RateID ,
                                    a.AccountId,
                                    tblRate.CompanyID,
									RoutinePlan
                          FROM      tblRate ,
                                    ( SELECT  a.AccountId,
													CASE WHEN ctr.TrunkID IS NOT NULL 
														THEN p_RoutinePlan 
														ELSE 0
													END AS RoutinePlan
                                      FROM tblAccount a 
												  INNER JOIN tblCustomerTrunk ON TrunkID = p_TrunkId
                                      		AND a.AccountId = tblCustomerTrunk.AccountID
                                          AND tblCustomerTrunk.Status = 1
												LEFT JOIN tblCustomerTrunk ctr
													ON ctr.TrunkID = p_TrunkId
													AND ctr.AccountID = a.AccountID
													AND ctr.RoutinePlanStatus = 1
												WHERE  FIND_IN_SET(a.AccountID,p_AccountIdList) != 0 
                                    ) a
                          WHERE FIND_IN_SET(tblRate.RateID,p_RateIDList) != 0     
                        ) r
                        LEFT JOIN ( SELECT DISTINCT
                                                    c.RateID,
                                                    c.CustomerID as AccountId ,
                                                    c.TrunkID,
                                                    c.EffectiveDate
                                          FROM      tblCustomerRate c
                                                    INNER JOIN tblCustomerTrunk ON tblCustomerTrunk.TrunkID = c.TrunkID
                                                              AND tblCustomerTrunk.AccountID = c.CustomerID
                                                              AND tblCustomerTrunk.Status = 1
                                          WHERE FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 AND c.TrunkID = p_TrunkId
                                        ) cr ON r.RateID = cr.RateID
                                                AND r.AccountId = cr.AccountId
                                                AND r.CompanyID = p_CompanyId   
                                                and cr.EffectiveDate = p_EffectiveDate 
                WHERE  
                 r.CompanyID = p_CompanyId       
                 and cr.RateID is NULL;
  
       CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId);
       SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;                            
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetAccounts
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetAccounts`(IN `p_CompanyID` int, IN `p_userID` int , IN `p_IsVendor` int , IN `p_isCustomer` int , IN `p_activeStatus` int, IN `p_VerificationStatus` int, IN `p_AccountNo` VARCHAR(100), IN `p_ContactName` VARCHAR(50), IN `p_AccountName` VARCHAR(50), IN `p_tags` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT )
BEGIN
   DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
		SELECT cs.Value INTO v_Round_ from RateManagement4.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
		
	IF p_isExport = 0
	THEN



		SELECT 
		 	tblAccount.AccountID,
			tblAccount.Number, 
			tblAccount.AccountName,
			CONCAT(tblUser.FirstName,' ',tblUser.LastName) as Ownername,
			tblAccount.Phone, 
			
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(((Select ifnull(sum(GrandTotal),0)  from RMBilling4.tblInvoice where AccountID = tblAccount.AccountID and CompanyID = p_CompanyID AND InvoiceStatus != 'cancel' ) -(Select ifnull(sum(Amount),0)  from RMBilling4.tblPayment where tblPayment.AccountID = tblAccount.AccountID and tblPayment.CompanyID = p_CompanyID and Status = 'Approved' AND tblPayment.Recall = 0 )),v_Round_)) as OutStandingAmount,
			tblAccount.Email, 
			tblAccount.IsCustomer, 
			tblAccount.IsVendor,
			tblAccount.VerificationStatus,
			tblAccount.Address1,
			tblAccount.Address2,
			tblAccount.Address3,
			tblAccount.City,
			tblAccount.Country,
			tblAccount.Picture
		FROM tblAccount
	 	LEFT JOIN tblUser ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact ON tblContact.Owner=tblAccount.AccountID
		WHERE tblAccount.CompanyID = p_CompanyID
		AND tblAccount.AccountType = 1
		AND tblAccount.Status = p_activeStatus
		AND tblAccount.VerificationStatus = p_VerificationStatus
		AND(p_userID = 0 OR tblAccount.Owner = p_userID)
	   AND((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
	   AND((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
		AND((p_AccountNo = '' OR tblAccount.Number like p_AccountNo))
		AND((p_AccountName = '' OR tblAccount.AccountName like Concat('%',p_AccountName,'%')))
		AND((p_tags = '' OR tblAccount.tags like Concat(p_tags,'%')))
		AND((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) like Concat('%',p_ContactName,'%')))
			ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tblAccount.AccountName
             END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tblAccount.AccountName
                END DESC,                
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberDESC') THEN tblAccount.Number
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberASC') THEN tblAccount.Number
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameDESC') THEN tblUser.FirstName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameASC') THEN tblUser.FirstName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneDESC') THEN tblAccount.Phone
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneASC') THEN tblAccount.Phone
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailDESC') THEN tblAccount.Email
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailASC') THEN tblAccount.Email
	                END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;

	SELECT
   	COUNT(tblAccount.AccountID) AS totalcount
	FROM tblAccount
 	LEFT JOIN tblUser ON tblAccount.Owner = tblUser.UserID
	LEFT JOIN tblContact ON tblContact.Owner=tblAccount.AccountID
	WHERE tblAccount.CompanyID = p_CompanyID
	AND tblAccount.AccountType = 1
	AND tblAccount.Status = p_activeStatus
	AND tblAccount.VerificationStatus = p_VerificationStatus
	AND(p_userID = 0 OR tblAccount.Owner = p_userID)
   AND((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
   AND((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
	AND((p_AccountNo = '' OR tblAccount.Number like p_AccountNo))
	AND((p_AccountName = '' OR tblAccount.AccountName like Concat('%',p_AccountName,'%')))
	AND((p_tags = '' OR tblAccount.tags like Concat('%',p_tags,'%')))
	AND((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) like Concat('%',p_ContactName,'%')));

	END IF;
	IF p_isExport = 1
    THEN
        SELECT
            tblAccount.Number, tblAccount.AccountName,CONCAT(tblUser.FirstName,' ',tblUser.LastName) as Ownername,tblAccount.Phone, 
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,(Select ifnull(sum(GrandTotal),0)  from RMBilling4.tblInvoice  where AccountID = tblAccount.AccountID and CompanyID = p_CompanyID AND InvoiceStatus != 'cancel' ) -(Select ifnull(sum(Amount),0)  from RMBilling4.tblPayment  where tblPayment.AccountID = tblAccount.AccountID and tblPayment.CompanyID = p_CompanyID and Status = 'Approved' AND tblPayment.Recall = 0 )) as OutStandingAmount ,
			tblAccount.Email, tblAccount.IsCustomer, tblAccount.IsVendor,
			Case
				when tblAccount.VerificationStatus = 0 Then 'Not Verified'
				when tblAccount.VerificationStatus = 1 Then 'Pending Verification'
				when tblAccount.VerificationStatus = 2 Then 'Verified'
			End as VerificationStatus,
			tblAccount.Address1,
			tblAccount.Address2,
			tblAccount.Address3,
			tblAccount.City,
			tblAccount.Country,
			tblAccount.Picture
			FROM tblAccount
			LEFT JOIN tblUser ON tblAccount.Owner = tblUser.UserID
			LEFT JOIN tblContact ON tblContact.Owner=tblAccount.AccountID
			WHERE tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND(p_userID = 0 OR tblAccount.Owner = p_userID)
			AND((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND((p_AccountNo = '' OR tblAccount.Number like p_AccountNo))
			AND((p_AccountName = '' OR tblAccount.AccountName like Concat('%',p_AccountName,'%')))
			AND((p_tags = '' OR tblAccount.tags like Concat('%',p_tags,'%')))
			AND((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) like Concat('%',p_ContactName,'%')));
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetActiveCronJob
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetActiveCronJob`(IN `p_companyid` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT )
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	
    IF p_isExport = 0
    THEN
         
		SELECT
			tblCronJob.JobTitle,                
			tblCronJob.PID,
			CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,NOW()),':',TIMESTAMPDIFF(minute,LastRunTime,now())%60) AS RunningHour,
			tblCronJob.LastRunTime,                				
			tblCronJob.CronJobID
		FROM tblCronJob
      WHERE tblCronJob.CompanyID = p_companyid AND tblCronJob.Active=1
      ORDER BY                
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleDESC') THEN tblCronJob.JobTitle
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleASC') THEN tblCronJob.JobTitle
			END ASC,				
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDDESC') THEN tblCronJob.PID
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDASC') THEN tblCronJob.PID
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeASC') THEN tblCronJob.LastRunTime
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeDESC') THEN tblCronJob.LastRunTime
			END DESC
		LIMIT p_RowspPage OFFSET v_OffSet_;


     SELECT
         COUNT(tblCronJob.CronJobID) as totalcount
     FROM tblCronJob
         WHERE tblCronJob.CompanyID = p_companyid AND tblCronJob.Active=1;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
			tblCronJob.JobTitle,                
			tblCronJob.PID,
			tblCronJob.CronJobID,
			tblCronJob.LastRunTime  
        FROM tblCronJob
        WHERE tblCronJob.CompanyID = p_companyid AND tblCronJob.Active=1;
    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetAjaxResourceList
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAjaxResourceList`(IN `p_CompanyID` INT, IN `p_userid` LONGTEXT, IN `p_roleids` LONGTEXT, IN `p_action` int)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	
	IF p_action = 1
    THEN

        select distinct tblResourceCategories.ResourceCategoryID,tblResourceCategories.ResourceCategoryName,usrper.AddRemove,rolres.Checked
			from tblResourceCategories
			LEFT OUTER JOIN(
			select distinct rs.ResourceID, rs.ResourceName,usrper.AddRemove
			from tblResource rs
			inner join tblUserPermission usrper on usrper.resourceID = rs.ResourceID and FIND_IN_SET(usrper.UserID ,p_userid) != 0 ) usrper
			on usrper.ResourceID = tblResourceCategories.ResourceCategoryID
			LEFT OUTER JOIN(
			select distinct res.ResourceID, res.ResourceName,'true' as Checked
			from `tblResource` res
			inner join `tblRolePermission` rolper on rolper.resourceID = res.ResourceID and FIND_IN_SET(rolper.roleID ,p_roleids) != 0) rolres
			on rolres.ResourceID = tblResourceCategories.ResourceCategoryID
			where tblResourceCategories.CompanyID= p_CompanyID order by rolres.Checked desc,usrper.AddRemove desc,tblResourceCategories.ResourceCategoryName;
    
	
	END IF;
	
	IF p_action = 2
	THEN

		select  distinct `tblResourceCategories`.`ResourceCategoryID`, `tblResourceCategories`.`ResourceCategoryName`, tblRolePermission.resourceID as Checked ,case when tblRolePermission.resourceID>0
	 then
		1
     else
	    0
     end as check2
		from tblResourceCategories left join tblRolePermission on `tblRolePermission`.`resourceID` = `tblResourceCategories`.`ResourceCategoryID` 
		and `tblRolePermission`.`CompanyID` = p_CompanyID and FIND_IN_SET(`tblRolePermission`.`roleID`,p_roleids) != 0
		order by
		case when tblRolePermission.resourceID>0
	 then
		1
     else
	    0
     end
	  desc,`tblResourceCategories`.`ResourceCategoryName` asc;
	
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetAjaxRoleList
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAjaxRoleList`(IN `p_CompanyID` INT, IN `p_UserID` LONGTEXT, IN `p_ResourceID` LONGTEXT, IN `p_action` int)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	
	IF p_action = 1
	THEN

	select distinct `tblRole`.`RoleID`, `tblRole`.`RoleName`, tblUserRole.RoleID as Checked,case when tblUserRole.RoleID>0
	 then
		1
     else
	    0
     end as check2
	 from tblRole 
	 left join tblUserRole on `tblUserRole`.`RoleID` = `tblRole`.`RoleID` and `tblRole`.`CompanyID` = p_CompanyID and  FIND_IN_SET(`tblUserRole`.`UserID`, p_UserID) != 0
	 order by
	 case when tblUserRole.RoleID>0
	 then
		1
     else
	    0
     end
	  desc,`tblRole`.`RoleName` asc;

	END IF;
	
	IF p_action = 2
	THEN

	select distinct `tblRole`.`RoleID`, `tblRole`.`RoleName`, tblRolePermission.RoleID as Checked,case when tblRolePermission.RoleID>0
	 then
		1
     else
	    0
     end as check2
	 from tblRole 
	 left join tblRolePermission on `tblRolePermission`.`roleID` = `tblRole`.`RoleID` and `tblRolePermission`.`CompanyID` = p_CompanyID and FIND_IN_SET(`tblRolePermission`.`resourceID`,p_ResourceID) != 0
	  order by
	  case when tblRolePermission.RoleID>0
	 then
		1
     else
	    0
     end
	  desc,`tblRole`.`RoleName` asc;
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetAjaxUserList
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAjaxUserList`(IN `p_CompanyID` INT, IN `p_ResourceCategoryID` LONGTEXT, IN `p_roleids` LONGTEXT, IN `p_action` int)
BEGIN

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	
	IF p_action = 1
	THEN

	select distinct `tblUser`.`UserID`, (CONCAT(tblUser.FirstName,' ',tblUser.LastName)) as UserName,case when tblUserRole.RoleID>0
	 then
		1
     else
	    null
     end As Checked 
		from tblUser left join tblUserRole on `tblUserRole`.`UserID` = `tblUser`.`UserID` 
		and `tblUser`.`CompanyID` = p_CompanyID and FIND_IN_SET(`tblUserRole`.`RoleID`,p_roleids)  != 0
		where `AdminUser` != '1' or `AdminUser` is null
		 order by
		 case when tblUserRole.RoleID>0
	 then
		1
     else
	    null
     end
	  desc, UserName asc;

	END IF;
	
	IF p_action = 2
	THEN

	SELECT distinct u.UserID,(CONCAT(u.FirstName,' ',u.LastName)) as UserName,Perm.Checked, UPerm.AddRemove
		FROM tblUser u
		LEFT OUTER JOIN
		(SELECT distinct u.userid,'true' as Checked,tblResourceCategories.ResourceCategoryName as permname
		FROM tblUser u
		inner join tblUserRole ur on u.UserID = ur.UserID
		inner join tblRole r on ur.RoleID = r.RoleID
		inner join tblRolePermission rp on r.RoleID = rp.roleID
		inner join tblResourceCategories on rp.resourceID = tblResourceCategories.ResourceCategoryID
		WHERE  FIND_IN_SET(tblResourceCategories.ResourceCategoryID,p_ResourceCategoryID) != 0 ) Perm
		ON u.UserID = Perm.UserID
		LEFT OUTER JOIN
		(SELECT distinct u.userid, up.AddRemove,tblResourceCategories.ResourceCategoryName as permname
		FROM tblUser u
		inner join tblUserPermission up on u.UserID = up.UserID
		inner join tblResourceCategories on up.resourceID = tblResourceCategories.ResourceCategoryID
		WHERE FIND_IN_SET(tblResourceCategories.ResourceCategoryID ,p_ResourceCategoryID) != 0 ) UPerm
		on u.userid = UPerm.userid
		where u.CompanyID = p_CompanyID and u.AdminUser != 1 or u.AdminUser is null
		order by Perm.Checked desc, UPerm.AddRemove desc,UserName asc;

	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetAllDashboardData
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAllDashboardData`(IN `p_companyId` INT, IN `p_userId` INT, IN `p_isadmin` INT)
BEGIN
  DECLARE v_isAccountManager_ int;
   
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 

SELECT count(*) as TotalDueCustomer,DAYSDIFF from (
			SELECT distinct  a.AccountName,a.AccountID,cr.TrunkID,cr.EffectiveDate,TIMESTAMPDIFF(DAY,cr.EffectiveDate, NOW()) as DAYSDIFF
			FROM tblCustomerRate cr
			INNER JOIN tblRate  r on r.RateID =cr.RateID
			INNER JOIN tblCountry c on c.CountryID = r.CountryID
			INNER JOIN tblAccount a on a.AccountID = cr.CustomerID
			WHERE a.CompanyId =@companyId
			AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, DATE_FORMAT (NOW(),120)) BETWEEN -1 AND 1

		) as tbl
		LEFT JOIN tblJob ON tblJob.AccountID = tbl.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='CD'))
					AND FIND_IN_SET (tbl.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0

					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

		where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
		group by DAYSDIFF
		order by DAYSDIFF desc;

 
        

        SELECT count(*) as TotalDueVendor ,DAYSDIFF from (
				SELECT  DISTINCT a.AccountName,a.AccountID,vr.TrunkID,vr.EffectiveDate,TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) as DAYSDIFF
			FROM tblVendorRate vr
			INNER JOIN tblRate  r on r.RateID =vr.RateID
			INNER JOIN tblCountry c on c.CountryID = r.CountryID
			INNER JOIN tblAccount a on a.AccountID = vr.AccountId
			WHERE a.CompanyId = @companyId
			AND TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) BETWEEN -1 AND 1
	   ) as tbl
		LEFT JOIN tblJob ON tblJob.AccountID = tbl.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='VD'))
					AND FIND_IN_SET (tbl.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

		where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
		group by DAYSDIFF
		order by DAYSDIFF desc;
  
   
     select `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`HasRead`, `tblJob`.`CreatedBy`, `tblJob`.`created_at` 
     from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` inner join tblJobType on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID` 
     where `tblJob`.`CompanyID` = p_companyId AND ( p_isadmin = 1 OR (p_isadmin = 0 and tblJob.JobLoggedUserID = p_userId) )
     order by `tblJob`.`ShowInCounter` desc, `tblJob`.`updated_at` desc,tblJob.JobID desc
	  limit 10;
     
    
 
    select count(*) as aggregate from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
    where `tblJobStatus`.`Title` = 'Pending' and `tblJob`.`CompanyID` = p_companyId AND 
     ( p_isadmin = 1 OR ( p_isadmin = 0 and tblJob.JobLoggedUserID = p_userId) );
   
  
 
      select `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`CreatedBy`, `tblJob`.`created_at` from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` inner join tblJobType on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID` 
        where ( tblJobStatus.Title = 'Success' OR tblJobStatus.Title = 'Completed' ) and `tblJob`.`CompanyID` = p_companyId and
        ( p_isadmin = 1 OR ( p_isadmin = 0 and `tblJob`.`JobLoggedUserID` = p_userId))  and `tblJob`.`updated_at` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
		  limit 10; 
        
	  
        select AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount where (`AccountType` = '0' and `CompanyID` = p_companyId and `Status` = '1') order by `tblAccount`.`AccountID` desc limit 10;
    
 
    select AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount 
          where 
          `AccountType` = '1' and CompanyID = p_companyId and `Status` = '1' 
          AND (  v_isAccountManager_ = 0 OR (v_isAccountManager_ = 1 AND tblAccount.Owner = p_userId ) )
    order by `tblAccount`.`AccountID` desc
	 limit 10;
  
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
 
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetAllJobs
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAllJobs`(IN `p_companyid` INT, IN `p_Status` INT, IN `p_Type` INT, IN `p_AccountID` INT, IN `p_UserID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
    

    IF p_isExport = 0
    THEN
          
            SELECT
                tblJob.Title,
                tblJobType.Title as Type,
                tblJobStatus.Title as Status,
                tblJob.created_at,
                tblJob.CreatedBy as CreatedBy,
                tblJob.JobID,
                tblJob.ShowInCounter,
                tblJob.updated_at
            FROM tblJob
           JOIN tblJobStatus 
                ON tblJob.JobStatusID = tblJobStatus.JobStatusID
            JOIN tblJobType 
                ON tblJob.JobTypeID = tblJobType.JobTypeID
            WHERE tblJob.CompanyID = p_companyid
            AND ( p_UserID = 0  OR ( p_UserID > 0 AND  tblJob.JobLoggedUserID = p_UserID )  )
            AND ( p_Status = ''  OR ( p_Status > 0 AND  tblJob.JobStatusID = p_Status )  )
            AND ( p_Type = ''  OR ( p_Type > 0 AND  tblJob.JobTypeID = p_Type )  )
            AND ( p_AccountID = ''  OR ( p_AccountID > 0 AND  tblJob.AccountID = p_AccountID )  )
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblJob.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblJob.Title
                END ASC,
                 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeDESC') THEN tblJobType.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeASC') THEN tblJobType.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tblJobStatus.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tblJobStatus.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblJob.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblJob.created_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN tblJob.CreatedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN tblJob.CreatedBy
                END ASC

           LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblJob.JobID) as totalcount
        FROM tblJob
           JOIN tblJobStatus 
                ON tblJob.JobStatusID = tblJobStatus.JobStatusID
            JOIN tblJobType 
                ON tblJob.JobTypeID = tblJobType.JobTypeID
            WHERE tblJob.CompanyID = p_companyid
            AND ( p_UserID = 0  OR ( p_UserID > 0 AND  tblJob.JobLoggedUserID = p_UserID )  )
            AND ( p_Status = ''  OR ( p_Status > 0 AND  tblJob.JobStatusID = p_Status )  )
            AND ( p_Type = ''  OR ( p_Type > 0 AND  tblJob.JobTypeID = p_Type )  )
            AND ( p_AccountID = ''  OR ( p_AccountID > 0 AND  tblJob.AccountID = p_AccountID )  );


    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            tblJob.Title,
                tblJobType.Title as Type,
                tblJobStatus.Title as Status,
                tblJob.created_at,
                tblJob.CreatedBy as CreatedBy
        FROM tblJob
           JOIN tblJobStatus 
                ON tblJob.JobStatusID = tblJobStatus.JobStatusID
            JOIN tblJobType 
                ON tblJob.JobTypeID = tblJobType.JobTypeID
            WHERE tblJob.CompanyID = p_companyid
            AND ( p_UserID = 0  OR ( p_UserID > 0 AND  tblJob.JobLoggedUserID = p_UserID )  )
            AND ( p_Status = ''  OR ( p_Status > 0 AND  tblJob.JobStatusID = p_Status )  )
            AND ( p_Type = ''  OR ( p_Type > 0 AND  tblJob.JobTypeID = p_Type )  )
            AND ( p_AccountID = ''  OR ( p_AccountID > 0 AND  tblJob.AccountID = p_AccountID )  );
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetAllResourceCategoryByUser
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAllResourceCategoryByUser`(IN `p_CompanyID` INT, IN `p_userid` LONGTEXT)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
     select distinct
		case
		when (rolres.Checked is not null and  usrper.AddRemove ='add') or (rolres.Checked is not null and usrper.AddRemove is null ) or	(rolres.Checked is null and  usrper.AddRemove ='add')
		then rescat.ResourceCategoryID
		end as ResourceCategoryID,
		case
		when (rolres.Checked is not null and  usrper.AddRemove ='add') or (rolres.Checked is not null and usrper.AddRemove is null ) or	(rolres.Checked is null and  usrper.AddRemove ='add')
		then rescat.ResourceCategoryName
		end as ResourceCategoryName
		from tblResourceCategories rescat
		LEFT OUTER JOIN(
			select distinct rescat.ResourceCategoryID, rescat.ResourceCategoryName,usrper.AddRemove
			from tblResourceCategories rescat
			inner join tblUserPermission usrper on usrper.resourceID = rescat.ResourceCategoryID and  FIND_IN_SET(usrper.UserID,p_userid) != 0 ) usrper
			on usrper.ResourceCategoryID = rescat.ResourceCategoryID
	      LEFT OUTER JOIN(
			select distinct rescat.ResourceCategoryID, rescat.ResourceCategoryName,'true' as Checked
			from `tblResourceCategories` rescat
			inner join `tblRolePermission` rolper on rolper.resourceID = rescat.ResourceCategoryID and rolper.roleID in(SELECT RoleID FROM `tblUserRole` where FIND_IN_SET(UserID,p_userid) != 0 )) rolres
			on rolres.ResourceCategoryID = rescat.ResourceCategoryID
		where rescat.CompanyID= p_CompanyID;
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetBlockUnblockVendor
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetBlockUnblockVendor`(IN `p_companyid` INT, IN `p_UserID` int , IN `p_TrunkID` INT, IN `p_CountryIDs` VARCHAR(100), IN `p_CountryCodes` VARCHAR(100), IN `p_isCountry` int , IN `p_action` VARCHAR(10), IN `p_isAllCountry` int )
BEGIN

    SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		 
		 
		 


	if p_isCountry = 0 AND p_action = 0  
	Then
			SELECT DISTINCT tblVendorRate.AccountId , tblAccount.AccountName
				from tblVendorRate
				inner join tblAccount on tblVendorRate.AccountId = tblAccount.AccountID
						and tblAccount.Status = 1
						and tblAccount.CompanyID = p_companyid 
						AND tblAccount.IsVendor = 1 
						and tblAccount.AccountType = 1						
				inner join tblRate on tblVendorRate.RateId  = tblRate.RateId  
					and  FIND_IN_SET(tblRate.Code,p_CountryCodes) != 0 
					and tblVendorRate.TrunkID = p_TrunkID
				inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid
					and tblVendorTrunk.AccountID =	tblVendorRate.AccountID 
					and tblVendorTrunk.Status = 1 
					and tblVendorTrunk.TrunkID = p_TrunkID 
				LEFT OUTER JOIN tblVendorBlocking
					ON tblVendorRate.AccountId = tblVendorBlocking.AccountId
						AND tblVendorTrunk.TrunkID = tblVendorBlocking.TrunkID
						AND tblRate.RateID = tblVendorBlocking.RateId											
			WHERE tblVendorBlocking.VendorBlockingId IS NULL
			ORDER BY tblAccount.AccountName;
			
	END IF;

	if p_isCountry = 0 AND p_action = 1	

	Then
		select DISTINCT tblVendorBlocking.AccountId, tblAccount.AccountName
			from tblVendorBlocking
			inner join tblAccount on tblVendorBlocking.AccountId = tblAccount.AccountID
								and tblAccount.Status = 1
								and tblAccount.CompanyID = p_companyid 
								AND tblAccount.IsVendor = 1 
								and tblAccount.AccountType = 1
			inner join tblRate on tblVendorBlocking.RateId  = tblRate.RateId  
				and  FIND_IN_SET(tblRate.Code,p_CountryCodes) != 0  
				and tblVendorBlocking.TrunkID = p_TrunkID
			inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid 
				and tblVendorTrunk.AccountID = tblVendorBlocking.AccountID 
				and tblVendorTrunk.Status = 1 
				and tblVendorTrunk.TrunkID = p_TrunkID 
			inner join tblVendorRate on tblVendorRate.RateId = tblRate.RateId 
				and tblVendorRate.AccountID = tblVendorBlocking.AccountID 								
				and tblVendorRate.TrunkID = p_TrunkID 
		ORDER BY tblAccount.AccountName;
	END IF;

	if p_isCountry = 1 AND p_action = 0 
	Then
	
		SELECT DISTINCT  tblVendorRate.AccountId , tblAccount.AccountName
			from tblVendorRate
			inner join tblAccount on tblVendorRate.AccountId = tblAccount.AccountID
					and tblAccount.Status = 1
					and tblAccount.CompanyID = p_companyid 
					AND tblAccount.IsVendor = 1 
					and tblAccount.AccountType = 1 										
			inner join tblRate on tblVendorRate.RateId  = tblRate.RateId  
				and  (
						(p_isAllCountry = 1 and p_isCountry = 1 and  tblRate.CountryID in  (SELECT CountryID FROM tblCountry) )
						OR
						(FIND_IN_SET(tblRate.CountryID,p_CountryIDs) != 0 )
						
					)
				
				and tblVendorRate.TrunkID = p_TrunkID
			inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid
				and tblVendorTrunk.AccountID =	tblVendorRate.AccountID 
				and tblVendorTrunk.Status = 1 
				and tblVendorTrunk.TrunkID = p_TrunkID 
			LEFT OUTER JOIN tblVendorBlocking
				ON tblVendorRate.AccountId = tblVendorBlocking.AccountId
					AND tblVendorTrunk.TrunkID = tblVendorBlocking.TrunkID
					AND tblRate.CountryId = tblVendorBlocking.CountryId												
		WHERE tblVendorBlocking.VendorBlockingId IS NULL
			ORDER BY tblAccount.AccountName;											
										
	END IF;

	if p_isCountry = 1 AND p_action = 1  

	Then
		select DISTINCT tblVendorBlocking.AccountId , tblAccount.AccountName
			from tblVendorBlocking
				inner join tblAccount on tblVendorBlocking.AccountId = tblAccount.AccountID
								and tblAccount.Status = 1
								and tblAccount.CompanyID = p_companyid 
								AND tblAccount.IsVendor = 1 
								and tblAccount.AccountType = 1
			inner join tblRate on tblVendorBlocking.CountryId  = tblRate.CountryId  
				and  (
						(p_isAllCountry = 1 and p_isCountry = 1 and tblRate.CountryID in(SELECT CountryID FROM tblCountry) )
						OR
						( FIND_IN_SET(tblRate.CountryID,p_CountryIDs) != 0 )
						
					) 
 				and tblVendorBlocking.TrunkID = p_TrunkID
			inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid 
				and tblVendorTrunk.AccountID = tblVendorBlocking.AccountID 
				and tblVendorTrunk.Status = 1 
				and tblVendorTrunk.TrunkID = p_TrunkID 
			inner join tblVendorRate on tblVendorRate.RateId = tblRate.RateId 
				and tblVendorRate.AccountID = tblVendorBlocking.AccountID 								
				and tblVendorRate.TrunkID = p_TrunkID 
			ORDER BY tblAccount.AccountName;
	END IF;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetCodeDeck
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCodeDeck`(IN `p_companyid` int, IN `p_codedeckid` int, IN `p_contryid` int, IN `p_code` varchar(50), IN `p_description` varchar(50), IN `p_PageNumber` int, IN `p_RowspPage` int, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` int )
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	           
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    

    IF p_isExport = 0
    THEN


        SELECT
            RateID,
            tblCountry.Country,
            Code,
            Description,
            Interval1,
            IntervalN
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid)
        ORDER BY
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
            END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(RateID) AS totalcount
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid);

    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            tblCountry.Country,
            Code,
            Description,
            Interval1,
            IntervalN
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid  = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid);

    END IF;
    IF p_isExport = 2
    THEN

        SELECT
	        RateID,
            tblCountry.Country,
            Code,
            Description,
            Interval1,
            IntervalN
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid  = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid);

    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetCronJob
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCronJob`(IN `p_companyid` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
          
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

    IF p_isExport = 0
    THEN
          
            SELECT
				tblCronJob.JobTitle,
                tblCronJobCommand.Title,
                tblCronJob.Status,
                tblCronJob.CronJobID,
                tblCronJob.CronJobCommandID,
                tblCronJob.Settings
            FROM tblCronJob
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJob.CompanyID = p_companyid
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblCronJobCommand.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblCronJobCommand.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tblCronJob.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tblCronJob.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleDESC') THEN tblCronJob.JobTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleASC') THEN tblCronJob.JobTitle
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblCronJob.CronJobID) as totalcount
        FROM tblCronJob
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJob.CompanyID = p_companyid;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
			tblCronJob.JobTitle,
            tblCronJobCommand.Title,
            tblCronJob.Status
        FROM tblCronJob
        INNER JOIN tblCronjobCommand
            ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
        WHERE tblCronJob.CompanyID = p_companyid;
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetCronJobHistory
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCronJobHistory`(IN `p_CronJobID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   
       
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
    IF p_isExport = 0
    THEN
          
            SELECT
                tblCronJobCommand.Title,
                tblCronJobLog.CronJobStatus,
                tblCronJobLog.Message,
                tblCronJobLog.created_at
            FROM tblCronJob
            INNER JOIN tblCronJobLog 
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJobLog.CronJobID = p_CronJobID
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblCronJobCommand.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblCronJobCommand.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusDESC') THEN tblCronJobLog.CronJobStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusASC') THEN tblCronJobLog.CronJobStatus
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageDESC') THEN tblCronJobLog.Message
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageASC') THEN tblCronJobLog.Message
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblCronJobLog.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblCronJobLog.created_at
                END ASC
           LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblCronJobLog.CronJobID) as totalcount
        FROM tblCronJob
            INNER JOIN tblCronJobLog 
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJobLog.CronJobID = p_CronJobID;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            tblCronJobCommand.Title,
            tblCronJobLog.CronJobStatus,
            tblCronJobLog.Message,
            tblCronJobLog.created_at
        FROM tblCronJob
        INNER JOIN tblCronJobLog 
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
        INNER JOIN tblCronjobCommand
            ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
        WHERE tblCronJobLog.CronJobID = p_CronJobID;
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetCronJobSetting
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCronJobSetting`(IN `p_CronJobID` INT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	select Settings from tblCronJob where CronJobID=p_CronJobID;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_getCustomerCliRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getCustomerCliRate`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT, IN `p_Duration` DECIMAL(18,6), IN `p_CustomerCLI` VARCHAR(50))
BEGIN
     
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	 CALL prc_GetCustomerRate (p_CompanyID,p_AccountID,p_TrunkID,NULL,p_CustomerCLI,NULL,'All',1,'','','','','',1);
   

    

    SELECT 
    CASE WHEN  p_Duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((p_Duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN
        ElSE 
        Rate
    END AS Rate
    FROM tmp_customerrate_   WHERE Code = p_CustomerCLI

    ORDER BY CHAR_LENGTH(RTRIM(Code)) DESC
	 LIMIT 1;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_getCustomerCliRateByAccount
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getCustomerCliRateByAccount`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT , IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    call prc_GetCustomerRate (p_CompanyID,p_AccountID,p_TrunkID,NULL,NULL,NULL,'All',1,0,0,0,'','',1);

    set @stm1 = CONCAT('UPDATE   RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    SET cost = CASE WHEN  billed_duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((billed_duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+ifnull(ConnectionFee,0)
        ElSE 
        Rate+ifnull(ConnectionFee,0)
    END
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'" 
    AND trunk = (select Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" limit 1)
    AND cr.rate is not null') ;
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    

    set @stm2 = CONCAT('UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    SET cost = 0.0
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND cr.rate is null ');
    
    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;

    set @stm3 = CONCAT('UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` SET cost = 0.0
    WHERE processid = "',p_processId,'" AND accountid is null');
    
    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
    set @stm4 = CONCAT('SELECT DISTINCT ud.cld as area_prefix,ud.trunk,ud.GatewayAccountID,a.AccountName
    FROM RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    LEFT JOIN tblAccount a ON a.AccountID = ud.AccountID
    WHERE processid ="',p_processId,'" 
    AND ud.accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND cr.rate is null');
    
    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetCustomerRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCustomerRate`(IN `p_companyid` INT, IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(50), IN `p_Effective` VARCHAR(50), IN `p_effectedRates` INT, IN `p_RoutinePlan` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT )
BEGIN
   DECLARE v_codedeckid_ INT;
   DECLARE v_ratetableid_ INT;
   DECLARE v_RateTableAssignDate_ DATETIME;
   DECLARE v_NewA2ZAssign_ INT;
   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

    SELECT
        CodeDeckId,
        RateTableID,
        RateTableAssignDate INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
    FROM tblCustomerTrunk
    WHERE CompanyID = p_companyid
    AND tblCustomerTrunk.TrunkID = p_trunkID
    AND tblCustomerTrunk.AccountID = p_AccountID
    AND tblCustomerTrunk.Status = 1;

    DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        RateTableRateID INT,
        TrunkID INT,
        RoutinePlan INT,
        INDEX tmp_CustomerRates__RateID (`RateID`),
        INDEX tmp_CustomerRates__TrunkID (`TrunkID`),
        INDEX tmp_CustomerRates__EffectiveDate (`EffectiveDate`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        RateID INT,
        Interval1 INT,
        IntervalN INT,
        Rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        RateTableRateID INT,
        TrunkID INT,
        INDEX tmp_RateTableRate__RateID (`RateID`),
        INDEX tmp_RateTableRate__TrunkID (`TrunkID`),
        INDEX tmp_RateTableRate__EffectiveDate (`EffectiveDate`)

    );

     

    INSERT INTO tmp_CustomerRates_

            SELECT
                tblCustomerRate.RateID,
                tblCustomerRate.Interval1,
                tblCustomerRate.IntervalN,
                
                tblCustomerRate.Rate,
                tblCustomerRate.ConnectionFee,
                tblCustomerRate.EffectiveDate,
                tblCustomerRate.LastModifiedDate,
                tblCustomerRate.LastModifiedBy,
                tblCustomerRate.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID,
                tblCustomerRate.RoutinePlan
                     
            FROM tblCustomerRate
            INNER JOIN tblRate
                ON tblCustomerRate.RateID = tblRate.RateID
            WHERE (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
            AND (tblRate.CompanyID = p_companyid)
            AND tblRate.CodeDeckId = v_codedeckid_
            AND tblCustomerRate.TrunkID = p_trunkID
            AND (p_RoutinePlan = 0 or tblCustomerRate.RoutinePlan = p_RoutinePlan)
            AND CustomerID = p_AccountID
            AND (
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() )
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
               )
            ORDER BY
                tblCustomerRate.TrunkId, tblCustomerRate.CustomerId,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;
         
        
         
        
                
            
        SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
        SELECT MAX(EffectiveDate) as EffectiveDate
        FROM 
        tblRateTableRate
        WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() 
        ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
        )tbl;

    INSERT INTO tmp_RateTableRate_
            SELECT
                tblRateTableRate.RateID,
                tblRateTableRate.Interval1,
                tblRateTableRate.IntervalN,
                tblRateTableRate.Rate,
                tblRateTableRate.ConnectionFee,
                 case when v_NewA2ZAssign_ = 1
                then
                    v_RateTableAssignDate_  
                else 
                    tblRateTableRate.EffectiveDate
                end  as EffectiveDate,
                NULL AS LastModifiedDate,
                NULL AS LastModifiedBy,
                NULL AS CustomerRateId,
                RateTableRateID,
                p_trunkID as TrunkID
            FROM tblRateTableRate
            INNER JOIN tblRate
                ON tblRateTableRate.RateID = tblRate.RateID
            WHERE (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
            AND (tblRate.CompanyID = p_companyid)
            AND tblRate.CodeDeckId = v_codedeckid_
            AND RateTableID = v_ratetableid_
            AND (
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() 
                     OR (v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW() )
                    )
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
               )
                ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC;
            
        IF p_Effective = 'Now'
        THEN
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);         
            DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
            AND n1.TrunkId = n2.TrunkId
            AND  n1.RateID = n2.RateID;
          
          CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);           
        DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
          AND n1.TrunkId = n2.TrunkId
          AND  n1.RateID = n2.RateID;
        END IF;

        CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates3_ as (select * from tmp_CustomerRates_);

   
    

    IF p_isExport = 0
    THEN





         SELECT
                r.RateID,
                r.Code,
                r.Description,
                case when allRates.Interval1 is null
                then 
                    r.Interval1
                else
                    allRates.Interval1
                end as Interval1,
                case when allRates.IntervalN is null
                then 
                    r.IntervalN
                else
                    allRates.IntervalN
                end as IntervalN,
                allRates.ConnectionFee,
                allRates.RoutinePlanName,
                allRates.Rate,
                allRates.EffectiveDate,
                allRates.LastModifiedDate,
                allRates.LastModifiedBy,
                allRates.CustomerRateId,
                p_trunkID as TrunkID,
                allRates.RateTableRateId
            FROM tblRate r
            LEFT JOIN (SELECT
                    CustomerRates.RateID,
                    CustomerRates.Interval1,
                    CustomerRates.IntervalN,
                    CustomerRates.ConnectionFee,
                    tblTrunk.Trunk as RoutinePlanName,
                    CustomerRates.Rate,
                    CustomerRates.EffectiveDate,
                    CustomerRates.LastModifiedDate,
                    CustomerRates.LastModifiedBy,
                    CustomerRates.CustomerRateId,
                    NULL AS RateTableRateID,
                    p_trunkID as TrunkID
                FROM tmp_CustomerRates_ CustomerRates
                left join tblTrunk on tblTrunk.TrunkID = CustomerRates.RoutinePlan

                UNION ALL

                SELECT
                DISTINCT
                    rtr.RateID,
                    rtr.Interval1,
                    rtr.IntervalN,
                    rtr.ConnectionFee,
                    NULL,
                    rtr.Rate,
                    rtr.EffectiveDate,
                    NULL,
                    NULL,
                    NULL AS CustomerRateId,
                    rtr.RateTableRateID,
                    p_trunkID as TrunkID
                FROM tmp_RateTableRate_ AS rtr
                LEFT JOIN tmp_CustomerRates2_ cr
                    ON cr.RateID = rtr.RateID
                WHERE (
                (
                    p_Effective = 'Now'
                    AND rtr.EffectiveDate <= NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL) -- Only CR Data
                        )

                )
                OR
                (
                    p_Effective = 'Future'
                    AND rtr.EffectiveDate > NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            ( cr.RateID IS NOT NULL AND rtr.EffectiveDate < (
                                                                            SELECT IFNULL(MIN(cr.EffectiveDate), rtr.EffectiveDate)
                                                                            FROM tmp_CustomerRates3_ cr
                                                                            WHERE cr.RateID = rtr.RateID
                                                                            )
                            )
                        )
                )
                OR p_Effective = 'All'

                )) allRates
                ON allRates.RateID = r.RateID

            WHERE (p_contryID IS NULL OR r.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'))
            AND (r.CompanyID = p_companyid)
            AND r.CodeDeckId = v_codedeckid_
            AND ((p_effectedRates = 1 AND Rate IS NOT NULL) OR  (p_effectedRates = 0))
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN r.Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN r.Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN allRates.Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN allRates.Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN allRates.Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN allRates.Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN allRates.IntervalN
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN allRates.IntervalN
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN allRates.ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN allRates.ConnectionFee
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN allRates.EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN allRates.EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedDateDESC') THEN allRates.LastModifiedDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedDateASC') THEN allRates.LastModifiedDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedByDESC') THEN allRates.LastModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedByASC') THEN allRates.LastModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIdDESC') THEN allRates.CustomerRateId
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIdASC') THEN allRates.CustomerRateId
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(r.RateID) AS totalcount
        FROM tblRate r
        LEFT JOIN (
            SELECT
                CustomerRates.RateID,
                CustomerRates.Rate,
                CustomerRates.EffectiveDate,
                CustomerRates.LastModifiedDate,
                CustomerRates.LastModifiedBy,
                CustomerRates.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_CustomerRates_ CustomerRates


            UNION ALL

            SELECT
            DISTINCT
                rtr.RateID,
                rtr.Rate,
                rtr.EffectiveDate,
                NULL,
                NULL,
                NULL AS CustomerRateId,
                rtr.RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_RateTableRate_ AS rtr
            LEFT JOIN tmp_CustomerRates2_ cr
                ON cr.RateID = rtr.RateID
            WHERE (
                (
                    p_Effective = 'Now' AND rtr.EffectiveDate <= NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL) -- Only CR Data
                        )

                )
                OR
                ( p_Effective = 'Future' AND rtr.EffectiveDate > NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (
                                cr.RateID IS NOT NULL AND rtr.EffectiveDate < (
                                                                                SELECT IFNULL(MIN(cr.EffectiveDate), rtr.EffectiveDate)
                                                                                FROM tmp_CustomerRates3_ cr
                                                                                WHERE cr.RateID = rtr.RateID
                                                                                )
                            )
                        )
                )
            OR p_Effective = 'All'

            )) allRates
            ON allRates.RateID = r.RateID
        WHERE (p_contryID IS NULL OR r.CountryID = p_contryID)
        AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'))
        AND (r.CompanyID = p_companyid)
        AND r.CodeDeckId = v_codedeckid_
        AND  ((p_effectedRates = 1 AND Rate IS NOT NULL) OR  (p_effectedRates = 0));

    END IF;

    IF p_isExport = 1
    THEN

        CREATE TEMPORARY TABLE IF NOT EXISTS  tmp_customerrate_ as (


        SELECT
            r.Code,
            r.Description,
            allRates.Interval1,
            allRates.IntervalN,
            allRates.RoutinePlanName,
            allRates.ConnectionFee,
            allRates.Rate,
            allRates.EffectiveDate,
            allRates.LastModifiedDate,
            allRates.LastModifiedBy
        FROM tblRate r
        LEFT JOIN (SELECT
                CustomerRates.RateID,
                CustomerRates.Interval1,
                CustomerRates.IntervalN,
                tblTrunk.Trunk as RoutinePlanName,
                CustomerRates.ConnectionFee,
                CustomerRates.Rate,
                CustomerRates.EffectiveDate,
                CustomerRates.LastModifiedDate,
                CustomerRates.LastModifiedBy,
                CustomerRates.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_CustomerRates_ CustomerRates
            left join tblTrunk on tblTrunk.TrunkID = CustomerRates.RoutinePlan


            UNION ALL

            SELECT
            DISTINCT
                rtr.RateID,
                rtr.Interval1,
                rtr.IntervalN,
                NULL,
                rtr.ConnectionFee,
                rtr.Rate,
                rtr.EffectiveDate,
                NULL,
                NULL,
                NULL AS CustomerRateId,
                rtr.RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_RateTableRate_ AS rtr
            LEFT JOIN tmp_CustomerRates2_ as cr
                ON cr.RateID = rtr.RateID
            WHERE (
                (
                    p_Effective = 'Now' AND rtr.EffectiveDate <= NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL) -- Only CR Data
                        )

                )
                OR
                ( p_Effective = 'Future' AND rtr.EffectiveDate > NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (
                                cr.RateID IS NOT NULL AND rtr.EffectiveDate < (
                                                                                SELECT IFNULL(MIN(crr.EffectiveDate), rtr.EffectiveDate)
                                                                                FROM tmp_CustomerRates3_ as crr
                                                                                WHERE crr.RateID = rtr.RateID
                                                                                )
                            )
                        )
                )
            OR p_Effective = 'All'

            )) allRates
            ON allRates.RateID = r.RateID
        WHERE (p_contryID IS NULL OR r.CountryID = p_contryID)
        AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'))
        AND (r.CompanyID = p_companyid)
        AND r.CodeDeckId = v_codedeckid_
        AND  ((p_effectedRates = 1 AND Rate IS NOT NULL) OR  (p_effectedRates = 0))
          );
          select * from tmp_customerrate_;

    END IF;
 
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetDashboardDataJobs
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardDataJobs`(IN `p_companyId` int , IN `p_userId` int , IN `p_isadmin` int)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` AS `Status`, `tblJob`.`HasRead`, `tblJob`.`CreatedBy`, `tblJob`.`created_at`
	FROM tblJob INNER JOIN tblJobStatus ON `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` INNER JOIN tblJobType ON `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID`
	WHERE `tblJob`.`CompanyID` = p_companyId AND ( p_isadmin = 1 OR (p_isadmin = 0 AND tblJob.JobLoggedUserID = p_userId) )
	ORDER BY `tblJob`.`ShowInCounter` DESC, `tblJob`.`updated_at` DESC,tblJob.JobID DESC
	LIMIT 10;


	
	SELECT count(*) AS totalpending FROM tblJob INNER JOIN tblJobStatus ON `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID`
	WHERE `tblJobStatus`.`Title` = 'Pending' AND `tblJob`.`CompanyID` = p_companyId AND
	( p_isadmin = 1 OR ( p_isadmin = 0 AND tblJob.JobLoggedUserID = p_userId) );
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetDashboardDataRecentDueRateSheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardDataRecentDueRateSheet`(IN `p_companyId` int)
BEGIN
 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
        SELECT count(*) as TotalDueCustomer,DAYSDIFF from (
			SELECT distinct  a.AccountName,a.AccountID,cr.TrunkID,cr.EffectiveDate,TIMESTAMPDIFF(DAY,cr.EffectiveDate, NOW()) as DAYSDIFF
			FROM tblCustomerRate cr
			INNER JOIN tblRate  r on r.RateID =cr.RateID
			INNER JOIN tblCountry c on c.CountryID = r.CountryID
			INNER JOIN tblAccount a on a.AccountID = cr.CustomerID
			WHERE a.CompanyId =@companyId
			AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, DATE_FORMAT (NOW(),120)) BETWEEN -1 AND 1

		) as tbl
		LEFT JOIN tblJob ON tblJob.AccountID = tbl.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='CD'))
					AND FIND_IN_SET (tbl.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0

					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

		where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
		group by DAYSDIFF
		order by DAYSDIFF desc;

 
        

        SELECT count(*) as TotalDueVendor ,DAYSDIFF from (
				SELECT  DISTINCT a.AccountName,a.AccountID,vr.TrunkID,vr.EffectiveDate,TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) as DAYSDIFF
			FROM tblVendorRate vr
			INNER JOIN tblRate  r on r.RateID =vr.RateID
			INNER JOIN tblCountry c on c.CountryID = r.CountryID
			INNER JOIN tblAccount a on a.AccountID = vr.AccountId
			WHERE a.CompanyId = @companyId
			AND TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) BETWEEN -1 AND 1
	   ) as tbl
		LEFT JOIN tblJob ON tblJob.AccountID = tbl.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='VD'))
					AND FIND_IN_SET (tbl.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

		where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
		group by DAYSDIFF
		order by DAYSDIFF desc;
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetDashboardProcessedFiles
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardProcessedFiles`(IN `p_companyId` int , IN `p_userId` int , IN `p_isadmin` int)
BEGIN
    select `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`CreatedBy`, `tblJob`.`created_at` from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` inner join tblJobType on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID`
        where ( tblJobStatus.Title = 'Success' OR tblJobStatus.Title = 'Completed' ) and `tblJob`.`CompanyID` = p_companyId and
        ( p_isadmin = 1 OR ( p_isadmin = 0 and `tblJob`.`JobLoggedUserID` = p_userId))  and `tblJob`.`updated_at` >= DATE_ADD(NOW(),INTERVAL -7 DAY)
		  LIMIT 10;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetDashboardRecentAccounts
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardRecentAccounts`(IN `p_companyId` int , IN `p_userId` int)
BEGIN
  DECLARE v_isAccountManage int;
    select  AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount
          where
          `AccountType` = '1' and CompanyID = p_companyId and `Status` = '1'
          AND (  v_isAccountManage = 0 OR (v_isAccountManage = 1 AND tblAccount.Owner = p_userId ) )
    order by `tblAccount`.`AccountID` desc
	 LIMIT 10;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetDashboardRecentLeads
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardRecentLeads`(IN `p_companyId` int)
BEGIN
    select AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount where (`AccountType` = '0' and `CompanyID` = p_companyId and `Status` = '1') order by `tblAccount`.`AccountID` desc LIMIT 10;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_getJobDropdown
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getJobDropdown`(IN `p_CompanyId` int , IN `p_userId` int , IN `p_isadmin` int , IN `p_reset` int )
BEGIN
   
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;  
  
  IF p_reset = 1 
  THEN
    update `tblJob` set `ShowInCounter` = '0' where `CompanyID` = p_CompanyId and `ShowInCounter` = '1';
  END IF;
        select  `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`HasRead`, `tblJob`.`CreatedBy`, `tblJob`.`created_at` 
        from `tblJob` inner join `tblJobStatus` on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
        inner join `tblJobType` on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID` 
        where `tblJob`.`CompanyID` = p_CompanyId AND  `tblJob`.JobLoggedUserID = p_userId
        order by `tblJob`.`ShowInCounter` desc, `tblJob`.`updated_at` desc,`tblJob`.JobID desc 
		  limit 10;

	  select count(*) as totalNonVisitedJobs from `tblJob` 
    inner join `tblJobStatus` on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
    where `tblJob`.`CompanyID` = p_CompanyId and `tblJob`.`ShowInCounter` = '1' and `tblJob`.JobLoggedUserID = p_userId;
    
    

    select count(*) as totalPendingJobs from `tblJob` inner join `tblJobStatus` on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
    where `tblJobStatus`.`Title` = 'Pending' and `tblJob`.`CompanyID` = p_CompanyId AND 
	`tblJob`.JobLoggedUserID = p_userId;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
    
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetLastRateTableRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetLastRateTableRate`(IN `p_companyid` INT, IN `p_RateTableId` INT, IN `p_EffectiveDate` datetime)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
        SELECT
            Code,
            Description,
            tblRateTableRate.Interval1,
            tblRateTableRate.IntervalN,
			ConnectionFee,
            Rate,
            EffectiveDate
        FROM tblRate
        INNER JOIN tblRateTableRate
            ON tblRateTableRate.RateID = tblRate.RateID
            AND tblRateTableRate.RateTableId = p_RateTableId
        INNER JOIN tblRateTable
            ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
        WHERE		(tblRate.CompanyID = p_companyid)
		AND tblRateTableRate.RateTableId = p_RateTableId
		AND EffectiveDate = p_EffectiveDate;
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetLCR
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetLCR`(IN `p_companyid` INT, IN `p_trunkID` INT, IN `p_codedeckID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_SortOrder` VARCHAR(50), IN `p_Preference` INT, IN `p_isExport` INT)
BEGIN
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
                
    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
  Call fnVendorCurrentRates(p_companyid,p_codedeckID,p_trunkID,p_contryID, p_code,'0');
  DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
  CREATE TEMPORARY TABLE tmp_VendorRate_ (      
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     rankname INT
   );
        
               
       
    INSERT  INTO tmp_VendorRate_
                SELECT  vr.AccountID ,
                        vr.AccountName ,
                        vr.Code ,
                        vr.Rate ,
                        vr.EffectiveDate ,
                        vr.Description,
                        vr.rankname
                FROM    ( 

                        SELECT 
                                    Description ,
                                    AccountID ,
                                    AccountName ,
                                    Code ,
                                    Rate ,
                                    EffectiveDate ,
                                    case when p_Preference = 1
                                    then preference_rank
                                    else
                                        rankname
                                    end as rankname
                                    
                                FROM (
                                                SELECT 
                                                Description ,
                                                AccountID ,
                                                AccountName ,
                                                Code ,
                                                Preference,
                                                Rate,
                                                EffectiveDate,
                                                preference_rank,
                                                @rank := CASE WHEN (@prev_Code2  = Code  AND @prev_Rate2 <  Rate) THEN @rank+1
                                                	   		  WHEN (@prev_Code2  = Code  AND @prev_Rate2 =  Rate) THEN @rank
                                                				  ELSE
																					1
																				  END
																				  AS rankname,
                                                @prev_Code2  := Code,
                                                @prev_Rate2  := Rate
                                                FROM    (SELECT 
                                                    Description ,
                                                    AccountID ,
                                                    AccountName ,
                                                    Code ,
                                                    Preference,
                                                    Rate,
                                                    EffectiveDate,
                                                    @preference_rank := CASE WHEN (@prev_Code = Code AND @prev_Preference = Preference AND @prev_Rate2 <  Rate) THEN @preference_rank+1
					                                                	   		  WHEN (@prev_Code = Code  AND @prev_Preference = Preference AND @prev_Rate2 =  Rate) THEN @preference_rank
               					                                 				  ELSE
																										1
																									  END as preference_rank,
                                                    @prev_Code  := Code,
                                                    @prev_Preference  := Preference,
                                                    @prev_Rate  := Rate
                                                    FROM 
                                                    (SELECT DISTINCT
                                                                r.Description ,
                                                                v.AccountId,v.TrunkID,v.RateId ,
                                                                a.AccountName ,
                                                                r.Code ,
                                                                vp.Preference,
                                                                v.Rate,
                                                                v.EffectiveDate ,
                                                                @row_num := IF(@prev_AccountId=v.AccountId AND @prev_TrunkID=v.TrunkID AND @prev_RateId=v.RateId and @prev_EffectiveDate >= v.effectivedate ,@row_num+1,1) AS RowID,
                                                                @prev_AccountId  := v.AccountId,
                                                                @prev_TrunkID  := v.TrunkID,
                                                                @prev_RateId  := v.RateId,
                                                                @prev_EffectiveDate  := v.effectivedate
                                                      FROM      tblVendorRate AS v
                                                                INNER JOIN tblAccount a ON v.AccountId = a.AccountID
                                                                INNER JOIN tblRate r ON v.RateId = r.RateID and r.CodeDeckId = p_codedeckID
                                                                INNER JOIN  tmp_VendorCurrentRates_ AS e ON 
                                                                    v.AccountId = e.AccountID 
                                                                    AND v.TrunkID = e.TrunkID
                                                                    AND     v.RateId = e.RateId
                                                                    AND v.EffectiveDate = e.EffectiveDate
                                                                    AND v.Rate > 0
                                                             
                                                                LEFT JOIN tblVendorPreference vp 
                                                                    ON vp.AccountId = v.AccountId
                                                                    AND vp.TrunkID = v.TrunkID
                                                                    AND vp.RateId = v.RateId
                                                            ,(SELECT @row_num := 1) x,(SELECT @prev_AccountId := '') a,(SELECT @prev_TrunkID := '') b,(SELECT @prev_RateId := '') c,(SELECT @prev_EffectiveDate := '') d

                                                    ORDER BY v.AccountId,v.TrunkID,v.RateId,v.EffectiveDate DESC
                                                    
                                            )as preference,(SELECT @preference_rank := 1) x,(SELECT @prev_Code := '') a,(SELECT @prev_Preference := '') b,(SELECT @prev_Rate := '') c
                                            where preference.RowID = 1
                                            ORDER BY preference.Code ASC, preference.Preference desc, preference.Rate ASC
                                            
                                        )as rank ,(SELECT @rank := 1) x,(SELECT @prev_Code2 := '') d,(SELECT @prev_Rate2 := '') f
                                        ORDER BY rank.Code ASC, rank.Rate ASC
    
                                    ) tbl
                           
                        ) vr
                WHERE   vr.rankname <= 5 
               ORDER BY rankname; 

         IF p_isExport = 0
        THEN
        
          SELECT
            
        	 CONCAT(t.Code , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 1, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 1`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 2, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 2`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 3, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 3`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 4, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 4`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 5, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 5`
        FROM      tmp_VendorRate_ t
          GROUP BY  t.Code
          ORDER BY  CASE WHEN (p_SortOrder = 'DESC') THEN `Destination` END DESC,
                       CASE WHEN (p_SortOrder = 'ASC') THEN `Destination` END ASC
                        
          LIMIT p_RowspPage OFFSET v_OffSet_;     

         select count(*)as totalcount FROM(SELECT code
         from tmp_VendorRate_ 
         GROUP by Code)tbl;
    
    END IF;
    
    
    IF p_isExport = 1
    THEN

        SELECT  
        	 CONCAT(t.Code , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 1, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 1`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 2, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 2`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 3, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 3`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 4, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 4`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 5, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 5`
        FROM tmp_VendorRate_  t
          GROUP BY  t.Code
          ORDER BY `Destination` ASC;
        
    END IF;
    
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetRateTableRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetRateTableRate`(IN `p_companyid` INT, IN `p_RateTableId` INT, IN `p_trunkID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(50), IN `p_effective` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        ID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
          ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RateTableRateID INT,
        RateID INT,
        INDEX tmp_RateTableRate_RateID (`RateID`)
    );
    
    
    
    INSERT INTO tmp_RateTableRate_
    SELECT
        RateTableRateID AS ID,
        Code,
        Description,
        ifnull(tblRateTableRate.Interval1,1) as Interval1,
        ifnull(tblRateTableRate.IntervalN,1) as IntervalN,
          tblRateTableRate.ConnectionFee,
        IFNULL(tblRateTableRate.Rate, 0) as Rate,
        IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
        tblRateTableRate.updated_at,
        tblRateTableRate.ModifiedBy,
        RateTableRateID,
        tblRate.RateID
    FROM tblRate
    LEFT JOIN tblRateTableRate
        ON tblRateTableRate.RateID = tblRate.RateID
        AND tblRateTableRate.RateTableId = p_RateTableId
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
    WHERE       (tblRate.CompanyID = p_companyid)
        AND (p_contryID = '' OR CountryID = p_contryID)
        AND (p_code = '' OR Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description = '' OR Description LIKE REPLACE(p_description, '*', '%'))
        AND TrunkID = p_trunkID
        AND (           
            p_effective = 'All' 
        OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
        OR (p_effective = 'Future' AND EffectiveDate > NOW())
            );
     
      IF p_effective = 'Now'
        THEN
           CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);          
         DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
           AND  n1.RateID = n2.RateID;
        END IF;
     
    IF p_isExport = 0
    THEN

       SELECT * FROM tmp_RateTableRate_
                    ORDER BY CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
                END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;



        SELECT
            COUNT(RateID) AS totalcount
        FROM tmp_RateTableRate_;

        


    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            Code,
            Description,
            Interval1,
            IntervalN,
            Rate,
            EffectiveDate,
            updated_at,
            ModifiedBy

        FROM   tmp_RateTableRate_;
         

    END IF;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetRecentDueSheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetRecentDueSheet`(IN `p_companyId` INT , IN `p_isAdmin` INT , IN `p_AccountType` INT, IN `p_DueDate` VARCHAR(10), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT 
)
BEGIN
	
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


    DROP TEMPORARY TABLE IF EXISTS tmp_DueSheets_;
    CREATE TEMPORARY TABLE tmp_DueSheets_ (
        AccountID INT,
        AccountName VARCHAR(100),
        Trunk VARCHAR(50),
        EffectiveDate DATE
    );


    IF p_AccountType = 1
    THEN
	  INSERT INTO tmp_DueSheets_
            SELECT DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate
            FROM (SELECT DISTINCT
                    a.AccountID,
                    a.AccountName,
                    t.Trunk,
					t.TrunkID,
                    vr.EffectiveDate,
					TIMESTAMPDIFF(DAY, vr.EffectiveDate, DATE_FORMAT(NOW(),'%Y-%m-%d')) AS DAYSDIFF
                FROM tblVendorRate vr
				inner join tblVendorTrunk vt on vt.AccountID = vr.AccountId and vt.TrunkID = vr.TrunkID and vt.Status =1
                INNER JOIN tblRate r
                    ON r.RateID = vr.RateID
                INNER JOIN tblCountry c
                    ON c.CountryID = r.CountryID
                INNER JOIN tblAccount a
                    ON a.AccountID = vr.AccountId
                INNER JOIN tblTrunk t
                    ON t.TrunkID = vr.TrunkID
                WHERE a.CompanyId = p_companyId

				AND (
						(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, NOW()) = 0)
							OR
						(p_DueDate = 'Tomorrow' AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, NOW()) = -1)
							OR
						(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, NOW()) = 1)
					)
				AND  ((p_isAdmin <> 0 AND a.Owner = p_isAdmin) OR  (p_isAdmin = 0))

				) AS TBL

				Left JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='VD'))
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND (
					(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = 0)
					OR
					(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)
					)
					where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S');
    END IF;

    IF p_AccountType = 2
    THEN
	  INSERT INTO tmp_DueSheets_
            SELECT  DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate
            FROM (SELECT DISTINCT
                a.AccountID,
                a.AccountName,
                t.Trunk,
				t.TrunkID,
                cr.EffectiveDate,
				TIMESTAMPDIFF(DAY, cr.EffectiveDate, DATE_FORMAT(NOW(),'%Y-%m-%d')) AS DAYSDIFF
            FROM tblCustomerRate cr
			inner join tblCustomerTrunk ct on ct.AccountID = cr.CustomerID and ct.TrunkID = cr.TrunkID and ct.Status =1
            INNER JOIN tblRate r
                ON r.RateID = cr.RateID
            INNER JOIN tblCountry c
                ON c.CountryID = r.CountryID
            INNER JOIN tblAccount a
                ON a.AccountID = cr.CustomerID
            INNER JOIN tblTrunk t
                ON t.TrunkID = cr.TrunkID
            WHERE a.CompanyId = p_companyId
				AND (
						(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, NOW()) = 0)
							OR
						(p_DueDate = 'Tomorrow' AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, NOW()) = -1)
							OR
						(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, NOW()) = 1)
					)
					AND  ((p_isAdmin <> 0 AND a.Owner = p_isAdmin) OR  (p_isAdmin = 0))
			) AS TBL

			LEFT JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='CD'))
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND (
					(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = 0)
					OR
					(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)
					)
						where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S');

    END IF;

    IF p_isExport = 0
    THEN
        SELECT
            AccountName,
            Trunk,
            EffectiveDate,
            AccountID
        FROM tmp_DueSheets_ AS DueSheets
        ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkDESC') THEN Trunk
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkASC') THEN Trunk
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;


		SELECT count(AccountName) as totalcount
      FROM tmp_DueSheets_ AS DueSheets;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            AccountName,
            Trunk,
            EffectiveDate
        FROM tmp_DueSheets_ TBL;
    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetRecentDueSheetCronJob
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetRecentDueSheetCronJob`(IN `p_companyId` INT)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

            SELECT  DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate,
				TBL.Owner,
				DAYSDIFF,
				'vender' as type
            FROM (SELECT DISTINCT
                    a.AccountID,
                    a.AccountName,
                    t.Trunk,
					t.TrunkID,
                    vr.EffectiveDate,
					a.Owner,
					TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) as DAYSDIFF
                FROM tblVendorRate vr
				inner join tblVendorTrunk vt on vt.AccountID = vr.AccountId and vt.TrunkID = vr.TrunkID and vt.Status =1
                INNER JOIN tblRate r
                    ON r.RateID = vr.RateID
                INNER JOIN tblCountry c
                    ON c.CountryID = r.CountryID
                INNER JOIN tblAccount a
                    ON a.AccountID = vr.AccountId
                INNER JOIN tblTrunk t
                    ON t.TrunkID = vr.TrunkID
                WHERE a.CompanyId = p_companyId

				AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, DATE_FORMAT (NOW(),'%Y-%m-%d')) BETWEEN -1 AND 1

				) AS TBL

				Left JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobTypeID = (select JobTypeID from tblJobType where Code='VD')
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)
					where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')

						UNION ALL

            SELECT  DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate,
				tbl.Owner,
				DAYSDIFF,
				'customer' as type
            FROM (SELECT DISTINCT
                a.AccountID,
                a.AccountName,
				a.Owner,
                t.Trunk,
				t.TrunkID,
                cr.EffectiveDate,
				TIMESTAMPDIFF(DAY,cr.EffectiveDate, NOW()) as DAYSDIFF
            FROM tblCustomerRate cr
			inner join tblCustomerTrunk ct on ct.AccountID = cr.CustomerID and ct.TrunkID = cr.TrunkID and ct.Status =1
            INNER JOIN tblRate r
                ON r.RateID = cr.RateID
            INNER JOIN tblCountry c
                ON c.CountryID = r.CountryID
            INNER JOIN tblAccount a
                ON a.AccountID = cr.CustomerID
            INNER JOIN tblTrunk t
                ON t.TrunkID = cr.TrunkID
            WHERE a.CompanyId = p_companyId
				AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, DATE_FORMAT (NOW(),'%Y-%m-%d')) BETWEEN -1 AND 1
			) AS TBL

			LEFT JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobTypeID = (select JobTypeID from tblJobType where Code='CD')
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

						where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
						order by Owner,DAYSDIFF,type ASC;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetVendorBlockByCode
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorBlockByCode`(IN `p_companyid` INT , IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT , IN `p_status` VARCHAR(50) , IN `p_code` VARCHAR(50), IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` VARCHAR(50) , IN `p_SortOrder` VARCHAR(5) , IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
   IF p_isExport = 0
   THEN
   
		SELECT  
            `tblRate`.RateID
           ,`tblRate`.Code
           ,CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status 
           ,`tblRate`.Description
	    FROM      `tblRate`
       INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
	    INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
       LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
		 WHERE ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
             AND ( tblRate.CompanyID = p_companyid )
             AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
             AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)  
		 ORDER BY
		 		 	CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
					END DESC ,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
					END ASC ,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN tblRate.Description
					END DESC ,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN tblRate.Description
					END ASC,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN VendorBlockingId
					END DESC,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN VendorBlockingId
					END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
            
      
		SELECT DISTINCT COUNT(tblRate.RateID) AS totalcount
      FROM    tblRate
      INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
		INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
      WHERE   ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
      		AND ( tblRate.CompanyID = p_companyid )
            AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
            AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);  
	END IF;
  
   IF p_isExport = 1
   THEN 
		SELECT DISTINCT  Code
      		,CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
            ,tblRate.Description

		FROM    tblRate
      INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
		INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId		
		WHERE   ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
      		AND ( tblRate.CompanyID = p_companyid )
            AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
            AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)    
		ORDER BY Code,Status  ;                    
   END IF;
   IF p_isExport = 2
   THEN 
		SELECT  DISTINCT 
			    tblRate.RateID as RateID
				,Code
      		,CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
            ,tblRate.Description

		FROM    tblRate
      INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
		INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId		
		WHERE   ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
      		AND ( tblRate.CompanyID = p_companyid )
            AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
            AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)    
		ORDER BY Code,Status  ;                    
   END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	  
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetVendorBlockByCountry
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorBlockByCountry`(IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT , IN `p_status` VARCHAR(50) , IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` VARCHAR(50) , IN `p_SortOrder` VARCHAR(5) , IN `p_isExport` INT)
BEGIN
       
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;      
		  
	IF p_isExport = 0
   THEN
  
		SELECT 
      	tblCountry.CountryID,
         Country,
         CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
		FROM      tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE         ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)  
      ORDER BY 
	   	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
	      END DESC ,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC')	THEN Country
	      END ASC ,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN VendorBlockingId
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN VendorBlockingId
	      END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT  COUNT(tblCountry.CountryID) AS totalcount
      FROM    tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE   ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);  
	END IF;
  
   IF p_isExport = 1
   THEN 
  
   	SELECT   Country,
      			CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
		FROM    tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE   ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);    
	END IF;
	IF p_isExport = 2
   THEN 
  
   	SELECT   tblCountry.CountryID,
					Country,
      			CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
		FROM    tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE   ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);    
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetVendorCodes
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorCodes`(IN `p_companyid` INT , IN `p_trunkID` INT, IN `p_contryID` VARCHAR(50) , IN `p_code` VARCHAR(50), IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` NVARCHAR(50) , IN `p_SortOrder` NVARCHAR(5) , IN `p_isExport` INT)
BEGIN

	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
        
        IF p_isExport = 0
		  THEN
  
				SELECT  RateID,Code FROM(
                                SELECT distinct tblRate.Code as RateID
                                    ,tblRate.Code
                                FROM      tblRate
                                INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND tblVendorRate.TrunkID = p_trunkID   
                                INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId  AND tblVendorTrunk.TrunkID = p_trunkID   
                                WHERE ( p_contryID  = '' OR  FIND_IN_SET(tblRate.CountryID,p_contryID) != 0  )
                                AND ( tblRate.CompanyID = p_companyid )
                                AND ( p_code = '' OR Code LIKE REPLACE(p_code,'*', '%') )
                            ) AS TBL2
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC
				LIMIT p_RowspPage OFFSET v_OffSet_;
            
                    
                    
                    
      
            SELECT  COUNT(distinct tblRate.Code) AS totalcount
            FROM    tblRate
          INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId  AND tblVendorRate.TrunkID = p_trunkID   
          INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId  AND tblVendorTrunk.TrunkID = p_trunkID   
                    
            WHERE   ( p_contryID  = '' OR  FIND_IN_SET(tblRate.CountryID,p_contryID) != 0  )
                    AND ( tblRate.CompanyID = p_companyid )
                    AND ( p_code  = '' OR Code LIKE REPLACE(p_code,'*', '%') );
                    
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
       
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetVendorPreference
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorPreference`(IN `p_companyid` INT , IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT , IN `p_code` VARCHAR(50) , IN `p_description` VARCHAR(50) , IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` VARCHAR(50) , IN `p_SortOrder` VARCHAR(5) , IN `p_isExport` INT )
BEGIN	

		 
		DECLARE v_CodeDeckId_ int;
		DECLARE v_OffSet_ int;
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
		select CodeDeckId into v_CodeDeckId_  from tblVendorTrunk where AccountID = p_AccountID and TrunkID = p_trunkID;
        
         IF p_isExport = 0
			THEN

				SELECT
					tblRate.RateID,
					Code,
					Preference,
					Description,
					VendorPreferenceID
				FROM  tblVendorRate
				JOIN tblRate
					ON tblVendorRate.RateId = tblRate.RateId
				LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
				WHERE (p_contryID IS NULL OR CountryID = p_contryID)
				AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
				AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
				AND (tblRate.CompanyID = p_companyid)
				AND tblVendorRate.TrunkID = p_trunkID
				AND tblVendorRate.AccountID = p_AccountID
				AND CodeDeckId = v_CodeDeckId_
				ORDER BY CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
					END ASC,					 
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Preference
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Preference
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorPreferenceIDDESC') THEN VendorPreferenceID
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorPreferenceIDASC') THEN VendorPreferenceID
					END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;



				SELECT
					COUNT(RateID) AS totalcount
				FROM (SELECT
					tblRate.RateId,
					EffectiveDate
				FROM tblVendorRate
				JOIN tblRate
					ON tblVendorRate.RateId = tblRate.RateId
				LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
				WHERE (p_contryID IS NULL
				OR CountryID = p_contryID)
				AND (p_code IS NULL
				OR Code LIKE REPLACE(p_code, '*', '%'))
				AND (p_description IS NULL
				OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
				AND (tblRate.CompanyID = p_companyid)
				AND tblVendorRate.TrunkID = p_trunkID
				AND tblVendorRate.AccountID = p_AccountID
				AND CodeDeckId = v_CodeDeckId_
				
			) AS tbl2;

		END IF;
	
		IF p_isExport = 1
		THEN

			SELECT
				Code,
				tblVendorPreference.Preference,
				Description
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
			WHERE (p_contryID IS NULL OR CountryID = p_contryID)
			AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
			AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
			AND (tblRate.CompanyID = p_companyid)
			AND tblVendorRate.TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_;
			
		END IF;
		IF p_isExport = 2
		THEN

			SELECT
				tblRate.RateID as RateID,
            Code,
            tblVendorPreference.Preference,
            Description
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
			WHERE (p_contryID IS NULL OR CountryID = p_contryID)
			AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
			AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
			AND (tblRate.CompanyID = p_companyid)
			AND tblVendorRate.TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_;
			
		END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_GetVendorRates
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorRates`(IN `p_companyid` INT , IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT , IN `p_code` VARCHAR(50) , IN `p_description` VARCHAR(50) , IN `p_effective` varchar(100), IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` VARCHAR(50) , IN `p_SortOrder` VARCHAR(5) , IN `p_isExport` INT )
BEGIN   
        
 
    DECLARE v_CodeDeckId_ int;
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
         

    SELECT CodeDeckId into v_CodeDeckId_  FROM tblVendorTrunk WHERE AccountID = p_AccountID AND TrunkID = p_trunkID;
        
        
        DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
        CREATE TEMPORARY TABLE tmp_VendorRate_ (
            VendorRateID INT,
            Code VARCHAR(50),
            Description VARCHAR(200),
            ConnectionFee DECIMAL(18, 6),
            Interval1 INT,
            IntervalN INT,
            Rate DECIMAL(18, 6),
            EffectiveDate DATE,
            updated_at DATETIME,
            updated_by VARCHAR(50),
            INDEX tmp_VendorRate_RateID (`Code`)

        );
        
        INSERT INTO tmp_VendorRate_
        SELECT
                    VendorRateID,
                    Code,
                    tblRate.Description,
                    tblVendorRate.ConnectionFee,
                    CASE WHEN tblVendorRate.Interval1 IS NOT NULL
                    THEN tblVendorRate.Interval1
                    ELSE tblRate.Interval1
                    END AS Interval1,
                    CASE WHEN tblVendorRate.IntervalN IS NOT NULL
                    THEN tblVendorRate.IntervalN
                    ELSE tblRate.IntervalN
                    END AS IntervalN ,
                    Rate,
                    EffectiveDate,
                    tblVendorRate.updated_at,
                    tblVendorRate.updated_by
                FROM tblVendorRate
                JOIN tblRate
                    ON tblVendorRate.RateId = tblRate.RateId
                WHERE (p_contryID IS NULL
                OR CountryID = p_contryID)
                AND (p_code IS NULL
                OR Code LIKE REPLACE(p_code, '*', '%'))
                AND (p_description IS NULL
                OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
                AND (tblRate.CompanyID = p_companyid)
                AND TrunkID = p_trunkID
                AND tblVendorRate.AccountID = p_AccountID
                AND CodeDeckId = v_CodeDeckId_
                AND
                    (
                    (p_effective = 'Now' AND EffectiveDate <= NOW() )
                    OR 
                    (p_effective = 'Future' AND EffectiveDate > NOW())
                    );
        IF p_effective = 'Now'
        THEN
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);           
            DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
            AND  n1.Code = n2.Code;
        END IF;        
        
          
           IF p_isExport = 0
            THEN
                SELECT
                    VendorRateID,
                    Code,
                    Description,
                    ConnectionFee,
                    Interval1,
                    IntervalN,
                    Rate,
                    EffectiveDate,
                    updated_at,
                    updated_by
                    
                FROM  tmp_VendorRate_
                ORDER BY CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN IntervalN
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN IntervalN
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN updated_by
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN updated_by
                    END ASC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDDESC') THEN VendorRateID
                    END DESC,
                    CASE
                        WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDASC') THEN VendorRateID
                    END ASC
                LIMIT p_RowspPage OFFSET v_OffSet_;



                SELECT
                    COUNT(code) AS totalcount
                FROM tmp_VendorRate_;


            END IF;
    
       IF p_isExport = 1
        THEN

            SELECT
                Code,
                Description,
                Rate,
                EffectiveDate,
                updated_at AS `Modified Date`,
                updated_by AS `Modified By`

            FROM tmp_VendorRate_;
        END IF;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_RateTableRateInsertUpdate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_RateTableRateInsertUpdate`(IN `p_RateTableRateID` LONGTEXT
, IN `p_RateTableId` int 
, IN `p_Rate` DECIMAL(18, 6) 
, IN `p_EffectiveDate` DATETIME
, IN `p_ModifiedBy` VARCHAR(50)
, IN `p_Interval1` int
, IN `p_IntervalN` int
, IN `p_ConnectionFee` DECIMAL(18, 6)
, IN `p_companyid` INT, IN `p_trunkID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(50), IN `p_effective` VARCHAR(50), IN `p_action` INT)
BEGIN
              
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_updaterateid_;
    CREATE TEMPORARY TABLE tmp_updaterateid_ (
      RateID INT
    ); 
    
    DROP TEMPORARY TABLE IF EXISTS tmp_insertrateid_;
    CREATE TEMPORARY TABLE tmp_insertrateid_ (
      RateID INT
    );
     
     DROP TEMPORARY TABLE IF EXISTS tmp_criteria_;
    CREATE TEMPORARY TABLE tmp_criteria_ (
      RateTableRateID INT,
      INDEX tmp_criteria_RateTableRateID (`RateTableRateID`)
    );
        if(p_action=1 or p_action=2)
    THEN
    
        DROP TEMPORARY TABLE IF EXISTS tmp_RateTable_;
      CREATE TEMPORARY TABLE tmp_RateTable_ (
         RateId INT,
         EffectiveDate DATE,
         RateTableRateID INT,
            INDEX tmp_RateTable_RateId (`RateId`)  
     );
        INSERT INTO tmp_RateTable_
                SELECT
                     tblRateTableRate.RateID,
                IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
                tblRateTableRate.RateTableRateID
            FROM tblRate
            LEFT JOIN tblRateTableRate
                ON tblRateTableRate.RateID = tblRate.RateID
                AND tblRateTableRate.RateTableId = p_RateTableId
            INNER JOIN tblRateTable
                ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
            WHERE       (tblRate.CompanyID = p_companyid)
                    AND (p_contryID = '' OR CountryID = p_contryID)
                    AND (p_code = '' OR Code LIKE REPLACE(p_code, '*', '%'))
                    AND (p_description = '' OR Description LIKE REPLACE(p_description, '*', '%'))
                    AND TrunkID = p_trunkID
                    AND (           
                            p_effective = 'All' 
                        OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
                        OR (p_effective = 'Future' AND EffectiveDate > NOW())
                        );
         
          IF p_effective = 'Now'
          THEN 
             CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTable4_ as (select * from tmp_RateTable_);            
          DELETE n1 FROM tmp_RateTable_ n1, tmp_RateTable4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
           AND  n1.RateId = n2.RateId;
          
          END IF;
          
          INSERT INTO tmp_criteria_
          SELECT RateTableRateID FROM tmp_RateTable_;
          
          
           
    END IF;                  
     
                    
    INSERT INTO tmp_updaterateid_
    SELECT r.RateID
    from
    (SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId   
        AND (p_RateTableRateID='' or  FIND_IN_SET(RateTableRateID,p_RateTableRateID) != 0 )
        AND (p_action=0 or RateTableRateID IN ( SELECT RateTableRateID FROM tmp_criteria_)) 
            
     ) r
    LEFT OUTER JOIN (
        SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId  AND EffectiveDate = p_EffectiveDate 
    ) cr on r.RateID = cr.RateID WHERE cr.RateID IS NOT NULL;

    
    
    UPDATE tblRateTableRate tr
    LEFT JOIN tmp_updaterateid_ r ON  r.RateID = tr.RateID
    SET Rate = p_Rate ,updated_at = NOW(),ModifiedBy =   p_ModifiedBy,Interval1 = p_Interval1,IntervalN = p_IntervalN,ConnectionFee= p_ConnectionFee
    WHERE  RateTableId = p_RateTableId  AND EffectiveDate = p_EffectiveDate
    and r.RateID is NOT NULL;

    INSERT INTO tmp_insertrateid_
    SELECT r.RateID
    
    from
    (SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId AND FIND_IN_SET(RateTableRateID,p_RateTableRateID) != 0 ) r 
    
    LEFT OUTER JOIN (
     SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId AND ( Rate = p_Rate OR Rate != p_Rate) AND EffectiveDate = p_EffectiveDate 
    ) cr on r.RateID = cr.RateID WHERE cr.RateID IS NULL;
    
    
    
    
    INSERT INTO tblRateTableRate (RateID,RateTableId,Rate,EffectiveDate,created_at,CreatedBy,Interval1,IntervalN,ConnectionFee)
    SELECT DISTINCT  tr.RateID,RateTableId,p_Rate,p_EffectiveDate,NOW(),p_ModifiedBy,p_Interval1,p_IntervalN,p_ConnectionFee FROM tblRateTableRate tr
    LEFT JOIN tmp_insertrateid_ r ON  r.RateID = tr.RateID
    WHERE  RateTableId = p_RateTableId
    and r.RateID is NOT NULL;
      
   CALL prc_ArchiveOldRateTableRate(p_RateTableId);
   
   if(p_action=2)
    THEN
        DELETE tblRateTableRate FROM tblRateTableRate
        INNER JOIN tmp_criteria_ ON tblRateTableRate.RateTableRateID = tmp_criteria_.RateTableRateID;
        
        
    END IF;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
     
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_tmpUseTempTable
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_tmpUseTempTable`()
BEGIN

    
 
    
     Call prc_createtemptable();

	select * from tmpSplit;
	
	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorBlockByCodeBlock
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockByCodeBlock`(IN `p_AccountId` int , IN `p_Trunk` varchar(50) , IN `p_RateId` int , IN `p_Username` varchar(100)
)
BEGIN
   
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF  (SELECT COUNT(*) FROM tblVendorBlocking WHERE AccountId = p_AccountId AND TrunkID = p_Trunk AND RateId = p_RateId) = 0
	THEN 
	
	  INSERT INTO tblVendorBlocking
	      (
	         `AccountId`        
	         ,`RateId`
	         ,`TrunkID`
	         ,`BlockedBy`
	      )          
	      VALUES (
	        p_AccountId  
	        ,p_RateId
	        ,p_Trunk
	        ,p_Username 
	        );
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorBlockByCodeUnblock
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockByCodeUnblock`(IN `p_AccountId` int , IN `p_Trunk` varchar(50) , IN `p_RateId` int)
BEGIN
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


  
  DELETE FROM `tblVendorBlocking`
   WHERE AccountId = p_AccountId
    AND TrunkID = p_Trunk AND RateId = p_RateId;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	    
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorBlockByCountryBlock
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockByCountryBlock`(IN `p_AccountId` int , IN `p_Trunk` varchar(50) , IN `p_CountryId` int , IN `p_Username` varchar(100)
)
BEGIN
  
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  
	IF  (SELECT COUNT(*) FROM tblVendorBlocking WHERE AccountId = p_AccountId AND TrunkID = p_Trunk AND CountryId = p_CountryId) = 0
	THEN 

		  INSERT INTO tblVendorBlocking
	      (
	         `AccountId`        
	         ,CountryId
	         ,`TrunkID`
	         ,`BlockedBy`
	      )          
	      VALUES (
	        p_AccountId  
	        ,p_CountryId
	        ,p_Trunk
	        ,p_Username 
	        );
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorBlockByCountryUnblock
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockByCountryUnblock`(IN `p_AccountId` int , IN `p_Trunk` varchar(50) , IN `p_CountryId` int)
BEGIN 
  
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  DELETE FROM `tblVendorBlocking`
   WHERE AccountId = p_AccountId
    AND TrunkID = p_Trunk AND CountryId = p_CountryId;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
  
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorBlockUnblockByAccount
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockUnblockByAccount`(IN `p_CompanyId` int, IN `p_AccountId` int , IN `p_TrunkID` varchar(50) , IN `p_CountryId` longtext, IN `p_RateId` longtext, IN `p_Username` varchar(100), IN `p_status` varchar(100), IN `p_code` VARCHAR(50), IN `p_blockunblockby` varchar(100), IN `p_action` int)
BEGIN
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  if(p_blockunblockby ='country')

  THEN

  IF p_action = 1
  THEN
  INSERT INTO tblVendorBlocking
      (
         `AccountId`
         ,CountryId
         ,`TrunkID`
         ,`BlockedBy`
      )
  SELECT  p_AccountId as AccountId
         ,tblCountry.CountryID as CountryId
         ,p_TrunkID as TrunkID
         ,p_Username as BlockedBy
                FROM    tblCountry
                        LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_TrunkID AND AccountId = p_AccountId
                WHERE   ( p_CountryId ='' OR  FIND_IN_SET(tblCountry.CountryID, p_CountryId) )
                  AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)
						AND tblVendorBlocking.VendorBlockingId IS NULL;
 END IF;

 IF p_action = 2
  THEN

	delete tblVendorBlocking from tblVendorBlocking INNER JOIN(
	select vb.VendorBlockingId FROM `tblVendorBlocking` vb
					INNER JOIN (
			SELECT  p_AccountId as AccountId
			 ,tblCountry.CountryID as CountryId
			 ,p_TrunkID as TrunkID
			 ,p_Username as BlockedBy
					FROM    tblCountry
							LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_TrunkID AND AccountId = p_AccountId
					WHERE   ( p_CountryId ='' OR  FIND_IN_SET(tblCountry.CountryID, p_CountryId) )
					AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)
					) v on vb.CountryId = v.CountryId) vb2 on vb2.VendorBlockingId=tblVendorBlocking.VendorBlockingId;			
  END IF;

  END IF;

   if(p_blockunblockby ='code')

  THEN
  IF p_action = 1
  THEN

  
  INSERT INTO tblVendorBlocking
      (
         `AccountId`
         ,`RateId`
         ,`TrunkID`
         ,`BlockedBy`
      )
  SELECT   p_AccountId as AccountId, tblRate.RateID as RateId,p_TrunkID as TrunkID ,p_Username as BlockedBy

                FROM    tblRate
            INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountId AND tblVendorRate.TrunkID = p_TrunkID   
			INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountId AND tblVendorTrunk.TrunkID = p_TrunkID   
                        LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId		
                        
                WHERE   (p_RateId IS NULL OR FIND_IN_SET(tblRate.RateID, p_RateId) )
						AND ( p_CountryId ='' OR tblRate.CountryID = p_CountryId)
                        AND ( tblRate.CompanyID = p_CompanyId )
                        AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
                        AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)    
						AND tblVendorBlocking.VendorBlockingId IS NULL;
 END IF;

 IF p_action = 2
  THEN

	delete tblVendorBlocking from tblVendorBlocking INNER JOIN(
		select VendorBlockingId FROM `tblVendorBlocking` vb  INNER JOIN (
			SELECT   p_AccountId as AccountId, tblRate.RateID as RateId,p_TrunkID as TrunkID ,p_Username as BlockedBy
                FROM    `tblRate`
            INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountId AND tblVendorRate.TrunkID = p_TrunkID
			INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountId AND tblVendorTrunk.TrunkID = p_TrunkID
            LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
            WHERE   (p_RateId IS NULL OR FIND_IN_SET(tblRate.RateID, p_RateId) )
            
            AND ( p_CountryId ='' OR tblRate.CountryID = p_CountryId)
                        AND ( tblRate.CompanyID = p_CompanyId )
                        AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
                        AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)
		)v on vb.RateID = v.RateID )vb2 on vb2.VendorBlockingId=tblVendorBlocking.VendorBlockingId;	

  END IF;

  END IF;
  
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorBulkRateUpdate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBulkRateUpdate`(IN `p_AccountId` INT
, IN `p_TrunkId` INT 
, IN `p_code` varchar(50)
, IN `p_description` varchar(200)
, IN `p_CountryId` INT
, IN `p_CompanyId` INT
, IN `p_Rate` decimal(18,6)
, IN `p_EffectiveDate` DATETIME
, IN `p_ConnectionFee` decimal(18,6)
, IN `p_Interval1` INT
, IN `p_IntervalN` INT
, IN `p_ModifiedBy` varchar(50)
, IN `p_effective` VARCHAR(50)
, IN `p_action` INT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_action = 1
	
	THEN
	
	UPDATE tblVendorRate

	INNER JOIN
	( 
	SELECT VendorRateID
	  FROM tblVendorRate v
	  INNER JOIN tblRate r ON r.RateID = v.RateId
	  INNER JOIN tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
	 WHERE 
	 ((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId))
	 AND ((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%')))
	 AND ((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%')))
	 AND  ((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) )
	 AND v.AccountId = p_AccountId AND v.TrunkID = p_TrunkId
	 ) vr 
	 ON vr.VendorRateID = tblVendorRate.VendorRateID
	 	SET 
		Rate = p_Rate, 
		EffectiveDate = p_EffectiveDate,
		Interval1 = p_Interval1, 
		IntervalN = p_IntervalN,
		updated_by = p_ModifiedBy,
		ConnectionFee = p_ConnectionFee,
		updated_at = NOW(); 
 	END IF;
	 
	 IF p_action = 2
	 THEN
	 
	 
	 DELETE tblVendorRate
	FROM tblVendorRate INNER JOIN
	( 
	SELECT VendorRateID
	  FROM tblVendorRate v
	  INNER JOIN tblRate r ON r.RateID = v.RateId
	  INNER JOIN tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
	 WHERE 
	 ((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId))
	 AND ((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%')))
	 AND ((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%')))
	 AND  ((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) )
	 AND v.AccountId = p_AccountId AND v.TrunkID = p_TrunkId
	 ) vr 
	 ON vr.VendorRateID = tblVendorRate.VendorRateID;
	 
	 END IF;
	
	 CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorCDRReRateByAccount
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_VendorCDRReRateByAccount`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT, IN `p_processId` VARCHAR(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
    
    DECLARE v_CodeDeckId_ INT;
    
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	select CodeDeckId into v_CodeDeckId_  from tblVendorTrunk where AccountID = p_AccountID and TrunkID = p_trunkID;
    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
	   CREATE TEMPORARY TABLE tmp_VendorRate_ (
	        VendorRateID INT,
	        Code VARCHAR(50),
	        Description VARCHAR(200),
			  ConnectionFee DECIMAL(18, 6),
	        Interval1 INT,
	        IntervalN INT,
	        Rate DECIMAL(18, 6),
	        EffectiveDate DATE,
	        updated_at DATETIME,
	        updated_by VARCHAR(50),
	        INDEX tmp_VendorRate_RateID (`RateID`) 
	    );
	    
    INSERT INTO tmp_VendorRate_
	 SELECT
				VendorRateID,
				Code,
				tblRate.Description,
				tblVendorRate.ConnectionFee,
				CASE WHEN tblVendorRate.Interval1 IS NOT NULL
				THEN tblVendorRate.Interval1
				ELSE tblRate.Interval1
				END AS Interval1,
				CASE WHEN tblVendorRate.IntervalN IS NOT NULL
				THEN tblVendorRate.IntervalN
				ELSE tblRate.IntervalN
				END AS IntervalN ,
				Rate,
				EffectiveDate,
				tblVendorRate.updated_at,
				tblVendorRate.updated_by
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			WHERE (tblRate.CompanyID = p_companyid)
			AND TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_
			AND EffectiveDate <= NOW();
			
	   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);	        
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	   AND  n1.Code = n2.Code;

    set @stm1 = CONCAT('UPDATE   RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    SET selling_cost = CASE WHEN  billed_duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((billed_duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+ifnull(ConnectionFee,0)
        ElSE 
        Rate+ifnull(ConnectionFee,0)
    END
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'" 
    AND trunk = (select Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" limit 1)
    AND vr.rate is not null') ;
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    

    set @stm2 = CONCAT('UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    SET selling_cost = 0.0
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND vr.rate is null ');
    
    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;

    set @stm3 = CONCAT('UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` SET selling_cost = 0.0
    WHERE processid = "',p_processId,'" AND accountid is null');
    
    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
    set @stm4 = CONCAT('SELECT DISTINCT ud.cld as area_prefix,ud.trunk,ud.GatewayAccountID,a.AccountName
    FROM RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    LEFT JOIN tblAccount a ON a.AccountID = ud.AccountID
    WHERE processid ="',p_processId,'" 
    AND ud.accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND vr.rate is null');
    
    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_VendorPreferenceUpdateBySelectedRateId
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_VendorPreferenceUpdateBySelectedRateId`(IN `p_CompanyId` INT, IN `p_AccountId` LONGTEXT , IN `p_RateIDList` LONGTEXT , IN `p_TrunkId` VARCHAR(100) , IN `p_Preference` INT, IN `p_ModifiedBy` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	IF p_Preference != 0
  THEN     

    UPDATE  tblVendorPreference v
   INNER JOIN (
    select  VendorPreferenceID,RateId from tblVendorPreference
      where AccountId = p_AccountId and TrunkID = p_TrunkId and   FIND_IN_SET (RateId,p_RateIDList) != 0 
   )vp on vp.VendorPreferenceID = v.VendorPreferenceID
    SET Preference = p_Preference;

    INSERT  INTO tblVendorPreference
   (  RateID ,
    AccountId ,
      TrunkID ,
      Preference,
      CreatedBy,
      created_at
   )
    SELECT  
            r.RateID,
         p_AccountId ,
         p_TrunkId,
         p_Preference ,
         p_ModifiedBy,
         NOW()
      FROM    ( 
            SELECT    
                distinct tblRate.RateID ,
                     a.AccountId,
                     tblRate.CompanyID
         FROM tblRate ,
         ( select AccountId from tblAccount
            where AccountId = p_AccountId 
         ) a
         WHERE   FIND_IN_SET (tblRate.RateID,p_RateIDList) != 0    
         ) r
      LEFT JOIN (  
            select  VendorPreferenceID,RateId,AccountId from tblVendorPreference
         where AccountId = p_AccountId 
                    and TrunkID = p_TrunkId 
        ) cr ON r.RateID = cr.RateID
        AND r.AccountId = cr.AccountId
         AND r.CompanyID = p_CompanyId   
      WHERE r.CompanyID = p_CompanyId       
            and cr.RateID is NULL;
  	END IF;

	IF p_Preference = 0
  THEN     
  	delete  from  tblVendorPreference where AccountId = p_AccountId and TrunkID = p_TrunkId and   FIND_IN_SET (RateId,p_RateIDList) != 0 ; 
  END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSCronJobDeleteOldCustomerRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldCustomerRate`()
BEGIN
     
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

   DELETE cr
   FROM tblCustomerRate cr
   INNER JOIN tblCustomerRate cr2
   ON cr2.CustomerID = cr.CustomerID
   AND cr2.TrunkID = cr.TrunkID
   AND cr2.RateID = cr.RateID
   WHERE   cr.EffectiveDate <= NOW() AND cr2.EffectiveDate <= NOW() AND cr.EffectiveDate < cr2.EffectiveDate;

    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
   CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
        INDEX _tmp_ArchiveOldEffectiveRates_CustomerRateID (`CustomerRateID`,`RateID`,`TrunkId`)
	);
	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates2 (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
       INDEX _tmp_ArchiveOldEffectiveRates_CustomerRateID (`CustomerRateID`,`RateID`,`TrunkId`)
	);
    
   INSERT INTO _tmp_ArchiveOldEffectiveRates (CustomerRateID,RateID,CustomerID,TrunkID,Rate,EffectiveDate)	
	SELECT
	   cr.CustomerRateID,
	   cr.RateID,
	   cr.CustomerID,
	   cr.TrunkID,
	   cr.Rate,
	   cr.EffectiveDate
	FROM tblCustomerRate cr
	ORDER BY cr.CustomerID ASC,cr.TrunkID ASC,cr.RateID ASC,EffectiveDate ASC;
	
	INSERT INTO _tmp_ArchiveOldEffectiveRates2
	SELECT * FROM _tmp_ArchiveOldEffectiveRates;

    DELETE tblCustomerRate
        FROM tblCustomerRate
        INNER JOIN(
        SELECT
            tt.CustomerRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
        ON tt.RowID = t.RowID + 1
            AND  t.CustomerId = tt.CustomerId
			   AND  t.TrunkId = tt.TrunkId
            AND t.RateID = tt.RateID
            AND t.Rate = tt.Rate) aold on aold.CustomerRateID = tblCustomerRate.CustomerRateID;
            
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;         
	            
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSCronJobDeleteOldRateSheetDetails
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldRateSheetDetails`()
BEGIN
     
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DELETE tblRateSheetDetails
    FROM tblRateSheetDetails
    INNER JOIN
    (
       SELECT
				DISTINCT
            rs.RateSheetID
         FROM tblRateSheet rs,tblRateSheet rs2
         WHERE rs.CustomerID = rs2.CustomerID
         AND rs.`Level` = rs2.`Level`
         AND rs.RateSheetID < rs2.RateSheetID 
    ) tbl ON tblRateSheetDetails.RateSheetID = tbl.RateSheetID;
        
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
 
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSCronJobDeleteOldRateTableRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldRateTableRate`()
BEGIN
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 

   DELETE rtr
      FROM tblRateTableRate rtr
      INNER JOIN tblRateTableRate rtr2
      ON rtr.RateTableId = rtr2.RateTableId
      AND rtr.RateID = rtr2.RateID
      WHERE rtr.EffectiveDate <= NOW()
			AND rtr2.EffectiveDate <= NOW()
         AND rtr.EffectiveDate < rtr2.EffectiveDate;
            
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;            

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSCronJobDeleteOldVendorRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldVendorRate`()
BEGIN
 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  
   DELETE vr
      FROM tblVendorRate vr
      INNER JOIN tblVendorRate vr2
      ON vr2.AccountId = vr.AccountId
      AND vr2.TrunkID = vr.TrunkID
      AND vr2.RateID = vr.RateID
      WHERE   vr.EffectiveDate <= NOW()
			AND  vr2.EffectiveDate <= NOW()
         AND vr.EffectiveDate < vr2.EffectiveDate;
	
   DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
   CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  VendorRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
        INDEX _tmp_ArchiveOldEffectiveRates_VendorRateID (`VendorRateID`,`RateID`,`TrunkId`,`CustomerID`)
	);
	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates2 (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  VendorRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
       INDEX _tmp_ArchiveOldEffectiveRates_VendorRateID (`VendorRateID`,`RateID`,`TrunkId`,`CustomerID`)
	);

    
   INSERT INTO _tmp_ArchiveOldEffectiveRates (VendorRateID,RateID,CustomerID,TrunkID,Rate,EffectiveDate)	
	SELECT
	   VendorRateID,
	   RateID,
	   AccountId,
	   TrunkID,
	   Rate,
	   EffectiveDate
	FROM tblVendorRate 
 	ORDER BY AccountId ASC,TrunkID ,RateID ASC,EffectiveDate ASC;
	
	INSERT INTO _tmp_ArchiveOldEffectiveRates2
	SELECT * FROM _tmp_ArchiveOldEffectiveRates;

    DELETE tblVendorRate
        FROM tblVendorRate
        INNER JOIN(
        SELECT
            tt.VendorRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
            ON tt.RowID = t.RowID + 1
            AND  t.CustomerID = tt.CustomerID
			   AND  t.TrunkId = tt.TrunkId
            AND t.RateID = tt.RateID
            AND t.Rate = tt.Rate) aold on aold.VendorRateID = tblVendorRate.VendorRateID;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSDeleteOldRateSheetDetails
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSDeleteOldRateSheetDetails`(IN `p_LatestRateSheetID` INT , IN `p_customerID` INT , IN `p_rateSheetCategory` VARCHAR(50)
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
        
        DELETE  tblRateSheetDetails
        FROM    tblRateSheetDetails
                JOIN tblRateSheet ON tblRateSheet.RateSheetID = tblRateSheetDetails.RateSheetID
        WHERE   CustomerID = p_customerID
                AND Level = p_rateSheetCategory
                AND tblRateSheetDetails.RateSheetID <> p_LatestRateSheetID; 
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
                
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGenerateRateSheet
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_WSGenerateRateSheet`(IN `p_CustomerID` INT, IN `p_Trunk` VARCHAR(100)
)
BEGIN
    DECLARE v_trunkDescription_ VARCHAR(50);
    DECLARE v_lastRateSheetID_ INT ;
    DECLARE v_IncludePrefix_ TINYINT;
    DECLARE v_Prefix_ VARCHAR(50);
    DECLARE v_codedeckid_  INT;
    DECLARE v_ratetableid_ INT;
    DECLARE v_RateTableAssignDate_  DATETIME;
    DECLARE v_NewA2ZAssign_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


       SELECT trunk INTO v_trunkDescription_
         FROM   tblTrunk
         WHERE  TrunkID = p_Trunk;

    DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate_;
    CREATE TEMPORARY TABLE tmp_RateSheetRate_(
        RateID        INT,
        Destination   VARCHAR(200),
        Codes         VARCHAR(50),
        Interval1     INT,
        IntervalN     INT,
        Rate          DECIMAL(18, 6),
        `level`         VARCHAR(10),
        `change`        VARCHAR(50),
        EffectiveDate  DATE,
			  INDEX tmp_RateSheetRate_RateID (`RateID`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        INDEX tmp_CustomerRates_RateId (`RateID`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        updated_at DATETIME,
        INDEX tmp_RateTableRate_RateId (`RateID`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail_;
    CREATE TEMPORARY TABLE tmp_RateSheetDetail_ (
        ratesheetdetailsid int,
        RateID int ,
        RateSheetID int,
        Destination varchar(50),
        Code varchar(50),
        Rate DECIMAL(18, 6),
        `change` varchar(50),
        EffectiveDate Date,
			  INDEX tmp_RateSheetDetail_RateId (`RateID`,`RateSheetID`)
    );
    SELECT RateSheetID INTO v_lastRateSheetID_
    FROM   tblRateSheet
    WHERE  CustomerID = p_CustomerID
        AND level = v_trunkDescription_
    ORDER  BY RateSheetID DESC LIMIT 1 ;

    SELECT includeprefix INTO v_IncludePrefix_
    FROM   tblCustomerTrunk
    WHERE  AccountID = p_CustomerID
      AND TrunkID = p_Trunk;

    SELECT prefix INTO v_Prefix_
    FROM   tblCustomerTrunk
    WHERE  AccountID = p_CustomerID
      AND TrunkID = p_Trunk;


    SELECT
        CodeDeckId,
        RateTableID,
        RateTableAssignDate
        INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
    FROM tblCustomerTrunk
    WHERE tblCustomerTrunk.TrunkID = p_Trunk
    AND tblCustomerTrunk.AccountID = p_CustomerID
    AND tblCustomerTrunk.Status = 1;


  SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
	 	SELECT MAX(EffectiveDate) as EffectiveDate
		FROM
		tblRateTableRate
		WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW()
		ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
	)tbl;

  INSERT INTO tmp_CustomerRates_
      SELECT  RateID,
              Interval1,
              IntervalN,
              tblCustomerRate.Rate,
              effectivedate,
              lastmodifieddate
      FROM   tblAccount
         JOIN tblCustomerRate
           ON tblAccount.AccountID = tblCustomerRate.CustomerID
      WHERE  tblAccount.AccountID = p_CustomerID
          AND tblCustomerRate.Rate > 0
         AND tblCustomerRate.TrunkID = p_Trunk
         ORDER BY tblCustomerRate.CustomerID,tblCustomerRate.TrunkID,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);
   DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);

	INSERT INTO tmp_RateTableRate_
		SELECT
			tblRateTableRate.RateID,
         tblRateTableRate.Interval1,
         tblRateTableRate.IntervalN,
         tblRateTableRate.Rate,
         CASE WHEN v_NewA2ZAssign_ = 1   THEN
         	v_RateTableAssignDate_
         ELSE
         	tblRateTableRate.EffectiveDate
         END as EffectiveDate ,
         tblRateTableRate.updated_at
      FROM tblAccount
      JOIN tblCustomerTrunk
           ON tblCustomerTrunk.AccountID = tblAccount.AccountID
      JOIN tblRateTable
           ON tblCustomerTrunk.ratetableid = tblRateTable.ratetableid
      JOIN tblRateTableRate
           ON tblRateTableRate.ratetableid = tblRateTable.ratetableid
      LEFT JOIN tmp_CustomerRates_ trc1
            ON trc1.RateID = tblRateTableRate.RateID
      WHERE  tblAccount.AccountID = p_CustomerID
         AND tblRateTableRate.Rate > 0
         AND tblCustomerTrunk.TrunkID = p_Trunk
         AND (( tblRateTableRate.EffectiveDate <= Now()
             AND ( ( trc1.RateID IS NULL )
                OR ( trc1.RateID IS NOT NULL
                   AND tblRateTableRate.ratetablerateid IS NULL )
              ) )
          OR ( tblRateTableRate.EffectiveDate > Now()
             AND ( ( trc1.RateID IS NULL )
                OR ( trc1.RateID IS NOT NULL
                   AND tblRateTableRate.EffectiveDate < (SELECT
									   IFNULL(MIN(crr.EffectiveDate),
									   tblRateTableRate.EffectiveDate)
										  FROM   tmp_CustomerRates2_ crr
										  WHERE  crr.RateID =
					   tblRateTableRate.RateID
                      ) ) ) ) )
      ORDER BY tblRateTableRate.RateID,tblRateTableRate.EffectiveDate desc;

   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
   DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

    INSERt INTO tmp_RateSheetDetail_
      SELECT ratesheetdetailsid,
                     RateID,
                     RateSheetID,
                     Destination,
                     Code,
                     Rate,
                     `change`,
                     effectivedate
              FROM   tblRateSheetDetails
              WHERE  RateSheetID = v_lastRateSheetID_
          ORDER BY RateID,effectivedate desc;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetDetail4_ as (select * from tmp_RateSheetDetail_);
   DELETE n1 FROM tmp_RateSheetDetail_ n1, tmp_RateSheetDetail4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

      /* Create duplicate tmp_RateSheetDetail_*/
      DROP TABLE IF EXISTS tmp_CloneRateSheetDetail_ ;
      CREATE TEMPORARY TABLE tmp_CloneRateSheetDetail_ LIKE tmp_RateSheetDetail_;
      INSERT tmp_CloneRateSheetDetail_ SELECT * FROM tmp_RateSheetDetail_;


      INSERT INTO tmp_RateSheetRate_
      SELECT tbl.RateID,
             description,
             Code,
             tbl.Interval1,
             tbl.IntervalN,
             tbl.Rate,
             v_trunkDescription_,
             'NEW' as `change`,
             tbl.EffectiveDate
      FROM   (
			SELECT
				rt.RateID,				
				rt.Interval1,
				rt.IntervalN,
				rt.Rate,
				rt.EffectiveDate
         FROM   tmp_RateTableRate_ rt
         LEFT JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
            	v_lastRateSheetID_
         LEFT JOIN tmp_RateSheetDetail_ as  rsd
         	ON rsd.RateID = rt.RateID AND rsd.RateSheetID = v_lastRateSheetID_
			WHERE  rsd.ratesheetdetailsid IS NULL

			UNION

			SELECT
				trc2.RateID,            
            trc2.Interval1,
            trc2.IntervalN,
            trc2.Rate,
            trc2.EffectiveDate
         FROM   tmp_CustomerRates_ trc2
         LEFT JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
         		v_lastRateSheetID_
         LEFT JOIN tmp_CloneRateSheetDetail_ as  rsd2
         	ON rsd2.RateID = trc2.RateID AND rsd2.RateSheetID = v_lastRateSheetID_
			WHERE  rsd2.ratesheetdetailsid IS NULL

			) AS tbl
      INNER JOIN tblRate
      	ON tbl.RateID = tblRate.RateID;

      INSERT INTO tmp_RateSheetRate_
      SELECT tbl.RateID,
             description,
             Code,
             tbl.Interval1,
             tbl.IntervalN,
             tbl.Rate,
             v_trunkDescription_,
             'NO CHANGE',
             tbl.EffectiveDate
		FROM   (
			SELECT
				rt.RateID,
            rt.Interval1,
            rt.IntervalN,
            rt.Rate,
            rt.EffectiveDate
         FROM tmp_RateTableRate_ rt
			INNER JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
         		v_lastRateSheetID_
         INNER JOIN tmp_RateSheetDetail_ as  rsd3
            ON rsd3.RateID = rt.RateID AND rsd3.RateSheetID = v_lastRateSheetID_

         WHERE ( rt.updated_at < tblRateSheet.dategenerated  OR rt.updated_at IS NULL )

         UNION

			SELECT
				trc3.RateID,
            trc3.Interval1,
            trc3.IntervalN,
            trc3.Rate,
            trc3.EffectiveDate
			FROM   tmp_CustomerRates_ trc3
         INNER JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
            	v_lastRateSheetID_
			INNER JOIN tmp_CloneRateSheetDetail_ as  rsd4
         	ON rsd4.RateID = trc3.RateID
            	AND rsd4.RateSheetID = v_lastRateSheetID_
			WHERE ( trc3.LastModifiedDate < dategenerated OR trc3.LastModifiedDate IS NULL )
      ) AS tbl
             INNER JOIN tblRate
                     ON tbl.RateID = tblRate.RateID;

      INSERT INTO tmp_RateSheetRate_
      SELECT tbl.RateID,
             description,
             Code,
             tbl.Interval1,
             tbl.IntervalN,
             tbl.Rate,
             v_trunkDescription_,
             tbl.`change`,
             tbl.EffectiveDate
      FROM   (SELECT rt.RateID,
                     description,
                     tblRate.Code,
                     rt.Interval1,
                     rt.IntervalN,
                     rt.Rate,
                     rsd5.Rate AS rate2,
                     rt.EffectiveDate,
                     CASE
                                WHEN rsd5.Rate > rt.Rate
                                     AND rsd5.Destination !=
                                         description THEN
                                'NAME CHANGE & DECREASE'
                                ELSE
                                  CASE
                                    WHEN rsd5.Rate > rt.Rate
                                         AND rt.EffectiveDate <= Now() THEN
                                    'DECREASE'
                                    ELSE
                                      CASE
                                        WHEN ( rsd5.Rate >
                                               rt.Rate
                                               AND rt.EffectiveDate > Now()
                                             )
                                      THEN
                                        'PENDING DECREASE'
                                        ELSE
                                          CASE
                                            WHEN ( rsd5.Rate <
                                                   rt.Rate
                                                   AND rt.EffectiveDate <=
                                                       Now() )
                                          THEN
                                            'INCREASE'
                                            ELSE
                                              CASE
                                                WHEN ( rsd5.Rate
                                                       <
                                                       rt.Rate
                                                       AND rt.EffectiveDate >
                                                           Now() )
                                              THEN
                                                'PENDING INCREASE'
                                                ELSE
                                                  CASE
                                                    WHEN
                                              rsd5.Destination !=
                                              description THEN
                                                    'NAME CHANGE'
                                                    ELSE 'NO CHANGE'
                                                  END
                                              END
                                          END
                                      END
                                  END
                              END as `Change`
              FROM   tblRate
                     INNER JOIN tmp_RateTableRate_ rt
                             ON rt.RateID = tblRate.RateID
                     INNER JOIN tblRateSheet
                             ON tblRateSheet.RateSheetID = v_lastRateSheetID_
                     INNER JOIN tmp_RateSheetDetail_ as  rsd5
                             ON rsd5.RateID = rt.RateID
                                AND rsd5.RateSheetID =
                                    v_lastRateSheetID_
              WHERE  rt.updated_at > tblRateSheet.dategenerated
              UNION
              SELECT trc4.RateID,
                     description,
                     tblRate.Code,
                     trc4.Interval1,
                     trc4.IntervalN,
                     trc4.Rate,
                     rsd6.Rate AS rate2,
                     trc4.EffectiveDate,
                     CASE
                                WHEN rsd6.Rate > trc4.Rate
                                     AND rsd6.Destination !=
                                         description THEN
                                'NAME CHANGE & DECREASE'
                                ELSE
                                  CASE
                                    WHEN rsd6.Rate > trc4.Rate
                                         AND trc4.EffectiveDate <= Now() THEN
                                    'DECREASE'
                                    ELSE
                                      CASE
                                        WHEN ( rsd6.Rate >
                                               trc4.Rate
                                               AND trc4.EffectiveDate > Now()
                                             )
                                      THEN
                                        'PENDING DECREASE'
                                        ELSE
                                          CASE
                                            WHEN ( rsd6.Rate <
                                                   trc4.Rate
                                                   AND trc4.EffectiveDate <=
                                                       Now() )
                                          THEN
                                            'INCREASE'
                                            ELSE
                                              CASE
                                                WHEN ( rsd6.Rate
                                                       <
                                                       trc4.Rate
                                                       AND trc4.EffectiveDate >
                                                           Now() )
                                              THEN
                                                'PENDING INCREASE'
                                                ELSE
                                                  CASE
                                                    WHEN
                                              rsd6.Destination !=
                                              description THEN
                                                    'NAME CHANGE'
                                                    ELSE 'NO CHANGE'
                                                  END
                                              END
                                          END
                                      END
                                  END
                              END as  `Change`
              FROM   tblRate
                     INNER JOIN tmp_CustomerRates_ trc4
                             ON trc4.RateID = tblRate.RateID
                     INNER JOIN tblRateSheet
                             ON tblRateSheet.RateSheetID = v_lastRateSheetID_
                     INNER JOIN tmp_CloneRateSheetDetail_ as rsd6
                             ON rsd6.RateID = trc4.RateID
                                AND rsd6.RateSheetID =
                                    v_lastRateSheetID_
              WHERE  trc4.LastModifiedDate > tblRateSheet.dategenerated) AS tbl ;

      INSERT INTO tmp_RateSheetRate_
      SELECT tblRateSheetDetails.RateID,
             tblRateSheetDetails.Destination,
             tblRateSheetDetails.Code,
             tblRateSheetDetails.Interval1,
             tblRateSheetDetails.IntervalN,
             tblRateSheetDetails.Rate,
             v_trunkDescription_,
             'DELETE',
             tblRateSheetDetails.EffectiveDate
      FROM   tblRate
             INNER JOIN tblRateSheetDetails
                     ON tblRate.RateID = tblRateSheetDetails.RateID
                        AND tblRateSheetDetails.RateSheetID = v_lastRateSheetID_
             LEFT JOIN (SELECT DISTINCT RateID,
                                        effectivedate
                        FROM   tmp_RateTableRate_
                        UNION
                        SELECT DISTINCT RateID,
                                        effectivedate
                        FROM tmp_CustomerRates_) AS TBL
                    ON TBL.RateID = tblRateSheetDetails.RateID
      WHERE  `change` != 'DELETE'
             AND TBL.RateID IS NULL ;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetRate4_ as (select * from tmp_RateSheetRate_);
	DELETE n1 FROM tmp_RateSheetRate_ n1, tmp_RateSheetRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
	AND  n1.RateId = n2.RateId;

      IF v_IncludePrefix_ = 1
        THEN
            SELECT rsr.RateID AS rateid,
                 rsr.Interval1 AS interval1,
                 rsr.IntervalN AS intervaln,
                 rsr.Destination AS destination,
                 rsr.Codes AS codes,
                 v_Prefix_ AS `tech prefix`,
                 CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS `interval`,
                 FORMAT(rsr.Rate,6) AS `rate per minute (usd)`,
                 rsr.`level`,
                 rsr.`change`,
                 rsr.EffectiveDate AS `effective date`
            FROM   tmp_RateSheetRate_ rsr
            WHERE rsr.Rate > 0
            ORDER BY rsr.Destination,rsr.Codes desc;
      ELSE
            SELECT rsr.RateID AS rateid ,
                           rsr.Interval1 AS interval1,
                           rsr.IntervalN AS intervaln,
                           rsr.Destination AS destination,
                           rsr.Codes AS codes,
                           CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS  `interval`,
                           FORMAT(rsr.Rate, 6) AS `rate per minute (usd)`,
                           rsr.`level`,
                           rsr.`change`,
                           rsr.EffectiveDate AS `effective date`
            FROM   tmp_RateSheetRate_ rsr
            WHERE rsr.Rate > 0
          	ORDER BY rsr.Destination,rsr.Codes DESC;
        END IF; 
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGenerateRateTable
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateRateTable`(IN `p_jobId` INT
, IN `p_RateGeneratorId` INT
, IN `p_RateTableId` INT
, IN `p_rateTableName` VARCHAR(200) 
, IN `p_EffectiveDate` VARCHAR(10))
GenerateRateTable:BEGIN
		
		DECLARE v_RTRowCount_ INT;       
		DECLARE v_RatePosition_ INT;
		DECLARE v_Use_Preference_ INT;
		DECLARE v_CurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;
    	DECLARE v_Average_ TINYINT;  
    	DECLARE v_CompanyId_ INT;
    	DECLARE v_codedeckid_ INT;
    	DECLARE v_trunk_ INT; 
    	DECLARE v_rateRuleId_ INT;
    	DECLARE v_RateGeneratorName_ VARCHAR(200);
    	DECLARE v_pointer_ INT ;
    	DECLARE v_rowCount_ INT ;
    	
    	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    	 


    	SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);
       

    	IF p_rateTableName IS NOT NULL
	 	THEN


        SET v_RTRowCount_ = (SELECT
                COUNT(*)
            FROM tblRateTable
            WHERE RateTableName = p_rateTableName
            AND CompanyId = (SELECT
                    CompanyId
                FROM tblRateGenerator
                WHERE RateGeneratorID = p_RateGeneratorId));

        IF v_RTRowCount_ > 0
        THEN
            CALL prc_WSJobStatusUpdate  (p_jobId, 'F', 'RateTable Name is already exist, Please try using another RateTable Name', '');
            LEAVE GenerateRateTable;
        END IF;
    	END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
		  code VARCHAR(50) ,
		  rate DECIMAL(18, 6),
		  ConnectionFee DECIMAL(18, 6),
		  INDEX tmp_Rates_code (`code`) 
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
		  code VARCHAR(50) ,
		  rate DECIMAL(18, 6),
		  ConnectionFee DECIMAL(18, 6),
		  INDEX tmp_Rates2_code (`code`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
        CodeDeckId INT
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

     	CREATE TEMPORARY TABLE tmp_Raterules_  (
        rateruleid INT,
        code VARCHAR(50),
        RowNo INT,
        INDEX tmp_Raterules_code (`code`),
        INDEX tmp_Raterules_rateruleid (`rateruleid`),
        INDEX tmp_Raterules_RowNo (`RowNo`)
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
     	CREATE TEMPORARY TABLE tmp_Vendorrates_  (
        code VARCHAR(50),
        rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        AccountId INT,
        RowNo INT,
        PreferenceRank INT,
        INDEX tmp_Vendorrates_code (`code`),
        INDEX tmp_Vendorrates_rate (`rate`)
    	);
    	
    	
    	DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage2_;
     	CREATE TEMPORARY TABLE tmp_Vendorrates_stage2_  (
        code VARCHAR(50),
        rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        INDEX tmp_Vendorrates_stage2__code (`code`)
    	);
    	
    	
     	DROP TEMPORARY TABLE IF EXISTS tmp_code_;
     	CREATE TEMPORARY TABLE tmp_code_  (
        code VARCHAR(50),
        rateid INT,
        countryid INT,
        INDEX tmp_code_code (`code`),
        INDEX tmp_code_rateid (`rateid`)
    	);
    	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorRate_;
     	CREATE TEMPORARY TABLE tmp_tblVendorRate_  (
			AccountId INT,
	      RateID INT,
	      Rate FLOAT,
	      ConnectionFee FLOAT,
	      TrunkId INT,
	      EffectiveDate DATE,
			RowID INT,
			INDEX tmp_tblVendorRate_AccountId (`AccountId`,`TrunkId`,`RateID`),
			INDEX tmp_tblVendorRate_EffectiveDate (`EffectiveDate`),
			INDEX tmp_tblVendorRate_Rate (`Rate`)
		);


    	SET  v_Use_Preference_ = (SELECT ifnull( JSON_EXTRACT(Options, '$.Use_Preference'),0)  FROM  tblJob WHERE JobID = p_jobId);
    	SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;

    	SELECT
        rateposition,
        companyid ,
        CodeDeckId,
        tblRateGenerator.TrunkID,
        tblRateGenerator.UseAverage  ,
        tblRateGenerator.RateGeneratorName INTO v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
    	FROM tblRateGenerator
    	WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;

    	INSERT INTO tmp_Raterules_
			SELECT
            rateruleid,
            tblRateRule.code,
            @row_num := @row_num+1 AS RowID
        FROM tblRateRule,(SELECT @row_num := 0) x
        WHERE rategeneratorid = p_RateGeneratorId
        ORDER BY rateruleid;

	     INSERT INTO tmp_Codedecks_
	        SELECT DISTINCT
	            tblVendorTrunk.CodeDeckId
	        FROM tblRateRule
	        INNER JOIN tblRateRuleSource
	            ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
	        INNER JOIN tblAccount
	            ON tblAccount.AccountID = tblRateRuleSource.AccountId
			JOIN tblVendorTrunk
	                ON tblAccount.AccountId = tblVendorTrunk.AccountID
	                AND  tblVendorTrunk.TrunkID = v_trunk_
	                AND tblVendorTrunk.Status = 1
	        WHERE RateGeneratorId = p_RateGeneratorId;

	    SET v_pointer_ = 1;
	    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Raterules_);

	    WHILE v_pointer_ <= v_rowCount_
	    DO
	        SET v_rateRuleId_ = (SELECT
	                rateruleid
	            FROM tmp_Raterules_ rr
	            WHERE rr.rowno = v_pointer_);

	        INSERT INTO tmp_code_
	            SELECT
	                tblRate.code,
	                tblRate.rateid,
	                tblRate.CountryID

	            FROM tblRate
	            JOIN tmp_Codedecks_ cd
	                ON tblRate.CodeDeckId = cd.CodeDeckId
	            JOIN tmp_Raterules_ rr
	                ON tblRate.code LIKE (REPLACE(rr.code,
	                '*', '%%'))
	            WHERE rr.rowno = v_pointer_;



				 INSERT INTO tmp_tblVendorRate_
	          SELECT AccountId,
	          RateID,
	          Rate,
	          ConnectionFee,
	          TrunkId,
	          EffectiveDate,
				 RowID
				 FROM(SELECT AccountId,
	          RateID,
	          Rate,
	          ConnectionFee,
	          TrunkId,
	          EffectiveDate,
				 @row_num := IF(@prev_RateId=tblVendorRate.RateId AND @prev_TrunkID=tblVendorRate.TrunkID AND @prev_AccountId=tblVendorRate.AccountId and @prev_EffectiveDate >= tblVendorRate.EffectiveDate ,@row_num+1,1) AS RowID,
				 @prev_RateId  := tblVendorRate.RateId,
				 @prev_TrunkID  := tblVendorRate.TrunkID,
				 @prev_AccountId  := tblVendorRate.AccountId,
				 @prev_EffectiveDate  := tblVendorRate.EffectiveDate
	          FROM tblVendorRate ,(SELECT @row_num := 1) x,(SELECT @prev_RateId := '') y,(SELECT @prev_TrunkID := '') z ,(SELECT @prev_AccountId := '') v ,(SELECT @prev_EffectiveDate := '') u
	          WHERE TrunkId = v_trunk_
				 ORDER BY tblVendorRate.AccountId , tblVendorRate.TrunkID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC) TBLVENDOR;

	       INSERT INTO tmp_Vendorrates_   (code ,rate ,  ConnectionFee , AccountId ,RowNo ,PreferenceRank )
			  SELECT code,rate,ConnectionFee,AccountId,RowNo,PreferenceRank FROM(SELECT *,
				 @row_num := IF(@prev_Code2 = code AND  @prev_Preference <= Preference ,(@row_num+1),1) AS PreferenceRank,
				 @prev_Code2  := Code,
				 @prev_Rate2  := rate,
				 @prev_Preference  := Preference
				 FROM (SELECT code,
				rate,
				ConnectionFee,
				AccountId,
				Preference,
				@row_num := IF(@prev_Code = code AND @prev_Rate <= rate ,(@row_num+1),1) AS RowNo,
				@prev_Code  := Code,
				@prev_Rate  := rate
				from  (
				SELECT DISTINCT
	                c.code,
					 CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
						THEN
							tmp_tblVendorRate_.rate
	               WHEN  v_CompanyCurrencyID_ = v_CurrencyID_  
					 THEN
						  (
						   ( tmp_tblVendorRate_.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
						 )
					  ELSE 
					  (
					  			-- Convert to base currrncy and x by RateGenerator Exhange
      					  (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
					  		* (tmp_tblVendorRate_.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
					  )
					 END
						as  Rate,
	                tmp_tblVendorRate_.ConnectionFee,
	                tblAccount.AccountId,
					Preference

	            FROM tmp_Raterules_ rateRule
	            JOIN tblRateRuleSource
	                ON tblRateRuleSource.rateruleid = rateRule.rateruleid
	            JOIN tblAccount
	                ON tblAccount.AccountId = tblRateRuleSource.AccountId
	            JOIN tmp_tblVendorRate_
	                ON tblAccount.AccountId = tmp_tblVendorRate_.AccountId
	                AND tmp_tblVendorRate_.TrunkId = v_trunk_ and RowID = 1

	            JOIN tblVendorTrunk
	                ON tmp_tblVendorRate_.AccountId = tblVendorTrunk.AccountID
	                AND tmp_tblVendorRate_.TrunkId = tblVendorTrunk.TrunkID
	                AND tblVendorTrunk.Status = 1
	            JOIN tmp_code_ c
	                ON c.RateID = tmp_tblVendorRate_.RateId
	            LEFT JOIN tblVendorBlocking blockCountry
	                ON tblAccount.AccountId = blockCountry.AccountId
	                AND c.CountryID = blockCountry.CountryId
	                AND blockCountry.TrunkID = v_trunk_
	            LEFT JOIN tblVendorBlocking blockCode
	                ON tblAccount.AccountId = blockCode.AccountId
	                AND c.RateID = blockCode.RateId
	                AND blockCode.TrunkID = v_trunk_
	            LEFT JOIN tblVendorPreference
	                ON tblAccount.AccountId = tblVendorPreference.AccountId
	                AND c.RateID = tblVendorPreference.RateId
	                AND tblVendorPreference.TrunkID = v_trunk_
	            WHERE rateRule.rowno = v_pointer_
	            AND blockCode.VendorBlockingId IS NULL
	            AND blockCountry.VendorBlockingId IS NULL
	            AND CAST(tmp_tblVendorRate_.EffectiveDate AS DATE) <= CAST(NOW() AS DATE)
				) VNDRTBL ,(SELECT @row_num := 1) x,(SELECT @prev_Code := '') y,(SELECT @prev_Rate := '') z
				ORDER BY code , rate
				) VNDRTBL ,(SELECT @row_num := 1) x,(SELECT @prev_Code2 := '') y,(SELECT @prev_Rate2 := '') z,(SELECT @prev_Preference := '') v
			 ORDER BY code,Preference desc,rate) LASTTBL;

	       DELETE FROM tmp_tblVendorRate_;

			 INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
			 select  code,rate,ConnectionFee from tmp_Rates_;
 
			 -- Inner query
            truncate   tmp_Vendorrates_stage2_;
            INSERT INTO tmp_Vendorrates_stage2_         
				SELECT
                    vr.code,
                    vr.rate,
                    vr.ConnectionFee
                FROM tmp_Vendorrates_ vr
                left join tmp_Rates2_ rate on rate.code = vr.code 
                WHERE 
                rate.code is null
	             and   (
	                    (v_Use_Preference_ =0 and vr.RowNo <= v_RatePosition_ ) or 
	                    (v_Use_Preference_ =1 and vr.PreferenceRank <= v_RatePosition_)
	                )
	                --  AND IFNULL(vr.Rate, 0) > 0
					order by
						CASE WHEN v_Use_Preference_ =1 THEN 
							PreferenceRank
						ELSE 
							RowNo
						END desc;
							
	        IF v_Average_ = 0 
	        THEN
	           
	           	
	                 
	              INSERT INTO tmp_Rates_
	                SELECT
	                    code,
	                    (SELECT 
	                            CASE WHEN vRate.rate >= minrate AND
	                            vRate.rate <= maxrate THEN vRate.rate
	                                + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin
	                                END) ELSE vRate.rate
	                            END
	                        FROM tblRateRuleMargin 
	                        WHERE rateruleid = v_rateRuleId_ LIMIT 1
							) as Rate,
	                	ConnectionFee
	                FROM ( 
             					select  max(code) as code , max(rate) as rate , max(ConnectionFee) as  ConnectionFee,
											@row_num := IF(@prev_Code = code   ,(@row_num+1),1) AS RowNoNew,
											@prev_Code  := code
								 	from tmp_Vendorrates_stage2_
										 , (SELECT @row_num := 1 , @prev_Code := '' ) t
							 		group by code 
						 ) vRate;
						 
						 
	         ELSE -- AVERAGE
	            
	           INSERT INTO tmp_Rates_
	                SELECT
	                    code,
	                     (SELECT 
	                            CASE WHEN vRate.rate >= minrate AND
	                            vRate.rate <= maxrate THEN vRate.rate
	                                + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100)
	                                    * vRate.rate) ELSE addmargin
	                                END) ELSE vRate.rate
	                            END
	                        FROM tblRateRuleMargin 
	                        WHERE rateruleid = v_rateRuleId_ LIMIT 1 
								) as Rate,
	                    ConnectionFee
	                FROM ( 
			                select code,
				                    AVG(Rate) as Rate,
				                    AVG(ConnectionFee) as ConnectionFee
								 	from tmp_Vendorrates_stage2_
							 		group by code 
						 ) vRate;
	
	        END IF;
	        DELETE FROM tmp_Vendorrates_;
	        DELETE FROM tmp_code_;
	
	        SET v_pointer_ = v_pointer_ + 1;
	    END WHILE;

		 
		 
	   -- DELETE FROM tmp_Rates_
	   -- WHERE IFNULL(Rate, 0) = 0;
		/*select * from tmp_Rates_;*/

	    IF p_RateTableId = -1
	    THEN
	
	        INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
	            VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);
	
	        SET p_RateTableId = LAST_INSERT_ID();
	
	        INSERT INTO tblRateTableRate (RateID,
	        RateTableId,
	        Rate,
	        EffectiveDate,
	        PreviousRate,
	        Interval1,
	        IntervalN,
	        ConnectionFee
	        )
	            SELECT DISTINCT
	                RateId,
	                p_RateTableId,
	                Rate,
	                p_EffectiveDate,
	                Rate,
	                Interval1,
	                IntervalN,
	                ConnectionFee
	            FROM tmp_Rates_ rate
	            INNER JOIN tblRate
	                ON rate.code  = tblRate.Code 
	            WHERE tblRate.CodeDeckId = v_codedeckid_;
	
	    ELSE
	
	        INSERT INTO tblRateTableRate (RateID,
	        RateTableId,
	        Rate,
	        EffectiveDate,
	        PreviousRate,
	        Interval1,
	        IntervalN,
	        ConnectionFee
	        )
	            SELECT DISTINCT
	                tblRate.RateId,
	                p_RateTableId,
	                rate.Rate,
	                p_EffectiveDate,
	                rate.Rate,
	                tblRate.Interval1,
	                tblRate.IntervalN,
	                rate.ConnectionFee
	            FROM tmp_Rates_ rate
	            INNER JOIN tblRate 
	                ON rate.code  = tblRate.Code 
	            LEFT JOIN tblRateTableRate tbl1 
	                ON tblRate.RateId = tbl1.RateId
	                AND tbl1.RateTableId = p_RateTableId
	            LEFT JOIN tblRateTableRate tbl2 
	            ON tblRate.RateId = tbl2.RateId
	            and tbl2.EffectiveDate = p_EffectiveDate
	            AND tbl2.RateTableId = p_RateTableId
	            WHERE  (    tbl1.RateTableRateID IS NULL 
	                    OR
	                    (
	                        tbl2.RateTableRateID IS NULL 
	                        AND  tbl1.EffectiveDate != p_EffectiveDate                       
	                        
	                    )
	                 )
	            AND tblRate.CodeDeckId = v_codedeckid_;
	
	        UPDATE tblRateTableRate
	        INNER JOIN tblRate 
	            ON tblRate.RateId = tblRateTableRate.RateId
	            AND tblRateTableRate.RateTableId = p_RateTableId
	            AND tblRateTableRate.EffectiveDate = p_EffectiveDate
	         INNER JOIN tmp_Rates_ as rate
	          ON  rate.code  = tblRate.Code 
	          SET tblRateTableRate.PreviousRate = tblRateTableRate.Rate,
	            tblRateTableRate.EffectiveDate = p_EffectiveDate,
	            tblRateTableRate.Rate = rate.Rate,
	            tblRateTableRate.ConnectionFee = rate.ConnectionFee,
	            tblRateTableRate.updated_at = NOW(),
	            tblRateTableRate.ModifiedBy = 'RateManagementService',
	            tblRateTableRate.Interval1 = tblRate.Interval1,
	            tblRateTableRate.IntervalN = tblRate.IntervalN
	        WHERE tblRate.CodeDeckId = v_codedeckid_
	        AND rate.rate != tblRateTableRate.Rate;
	
	        DELETE tblRateTableRate
	            FROM tblRateTableRate 
	        WHERE tblRateTableRate.RateTableId = p_RateTableId
	            AND RateId NOT IN (SELECT DISTINCT
	                    RateId
	                FROM tmp_Rates_ rate
	                INNER JOIN tblRate 
	                    ON rate.code  = tblRate.Code 
	                WHERE tblRate.CodeDeckId = v_codedeckid_)
	            AND tblRateTableRate.EffectiveDate = p_EffectiveDate;
	
	
	
	    END IF;


		UPDATE tblRateTable
	   SET RateGeneratorID = p_RateGeneratorId,
		  TrunkID = v_trunk_,
		  CodeDeckId = v_codedeckid_,
		  updated_at = now()
		WHERE RateTableID = p_RateTableId;

    	SELECT p_RateTableId as RateTableID;

    	CALL prc_WSJobStatusUpdate(p_jobId, 'S', 'RateTable Created Successfully', '');
    	
    	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    	

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGenerateRateTableTEST
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_WSGenerateRateTableTEST`(IN `p_jobId` INT
, IN `p_RateGeneratorId` INT
, IN `p_RateTableId` INT
, IN `p_rateTableName` VARCHAR(200) 
, IN `p_EffectiveDate` VARCHAR(10))
GenerateRateTable:BEGIN
		
		DECLARE v_RTRowCount_ INT;       
		DECLARE v_RatePosition_ INT;
		DECLARE v_Use_Preference_ INT;
		DECLARE v_CurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;
    	DECLARE v_Average_ TINYINT;  
    	DECLARE v_CompanyId_ INT;
    	DECLARE v_codedeckid_ INT;
    	DECLARE v_trunk_ INT; 
    	DECLARE v_rateRuleId_ INT;
    	DECLARE v_RateGeneratorName_ VARCHAR(200);
    	DECLARE v_pointer_ INT ;
    	DECLARE v_rowCount_ INT ;
    	
    	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    	 
    	SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);
       
    	IF p_rateTableName IS NOT NULL
	 	THEN
        SET v_RTRowCount_ = (SELECT
                COUNT(*)
            FROM tblRateTable
            WHERE RateTableName = p_rateTableName
            AND CompanyId = (SELECT
                    CompanyId
                FROM tblRateGenerator
                WHERE RateGeneratorID = p_RateGeneratorId));
        IF v_RTRowCount_ > 0
        THEN
            CALL prc_WSJobStatusUpdate  (p_jobId, 'F', 'RateTable Name is already exist, Please try using another RateTable Name', '');
            LEAVE GenerateRateTable;
        END IF;
    	END IF;
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
		  code VARCHAR(50) ,
		  rate DECIMAL(18, 6),
		  ConnectionFee DECIMAL(18, 6),
		  INDEX tmp_Rates_code (`code`) 
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
		  code VARCHAR(50) ,
		  rate DECIMAL(18, 6),
		  ConnectionFee DECIMAL(18, 6),
		  INDEX tmp_Rates2_code (`code`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
        CodeDeckId INT
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;
     	CREATE TEMPORARY TABLE tmp_Raterules_  (
        rateruleid INT,
        code VARCHAR(50),
        RowNo INT,
        INDEX tmp_Raterules_code (`code`),
        INDEX tmp_Raterules_rateruleid (`rateruleid`),
        INDEX tmp_Raterules_RowNo (`RowNo`)
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
     	CREATE TEMPORARY TABLE tmp_Vendorrates_  (
        code VARCHAR(50),
        rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        AccountId INT,
        RowNo INT,
        PreferenceRank INT,
        INDEX tmp_Vendorrates_code (`code`),
        INDEX tmp_Vendorrates_rate (`rate`)
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_code_;
     	CREATE TEMPORARY TABLE tmp_code_  (
        code VARCHAR(50),
        rateid INT,
        countryid INT,
        INDEX tmp_code_code (`code`),
        INDEX tmp_code_rateid (`rateid`)
    	);
    	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorRate_;
     	CREATE TEMPORARY TABLE tmp_tblVendorRate_  (
			AccountId INT,
	      RateID INT,
	      Rate FLOAT,
	      ConnectionFee FLOAT,
	      TrunkId INT,
	      EffectiveDate DATE,
			RowID INT,
			INDEX tmp_tblVendorRate_AccountId (`AccountId`,`TrunkId`,`RateID`),
			INDEX tmp_tblVendorRate_EffectiveDate (`EffectiveDate`),
			INDEX tmp_tblVendorRate_Rate (`Rate`)
		);
    	SET  v_Use_Preference_ = (SELECT ifnull( JSON_EXTRACT(Options, '$.Use_Preference'),0)  FROM  tblJob WHERE JobID = p_jobId);
    	SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;
    	SELECT
        rateposition,
        companyid ,
        CodeDeckId,
        tblRateGenerator.TrunkID,
        tblRateGenerator.UseAverage  ,
        tblRateGenerator.RateGeneratorName INTO v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
    	FROM tblRateGenerator
    	WHERE RateGeneratorId = p_RateGeneratorId;
		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
    	INSERT INTO tmp_Raterules_
			SELECT
            rateruleid,
            tblRateRule.code,
            @row_num := @row_num+1 AS RowID
        FROM tblRateRule,(SELECT @row_num := 0) x
        WHERE rategeneratorid = p_RateGeneratorId
        ORDER BY rateruleid;
	     INSERT INTO tmp_Codedecks_
	        SELECT DISTINCT
	            tblVendorTrunk.CodeDeckId
	        FROM tblRateRule
	        INNER JOIN tblRateRuleSource
	            ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
	        INNER JOIN tblAccount
	            ON tblAccount.AccountID = tblRateRuleSource.AccountId
			JOIN tblVendorTrunk
	                ON tblAccount.AccountId = tblVendorTrunk.AccountID
	                AND  tblVendorTrunk.TrunkID = v_trunk_
	                AND tblVendorTrunk.Status = 1
	        WHERE RateGeneratorId = p_RateGeneratorId;
	    SET v_pointer_ = 1;
	    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Raterules_);
	    WHILE v_pointer_ <= v_rowCount_
	    DO
	        SET v_rateRuleId_ = (SELECT
	                rateruleid
	            FROM tmp_Raterules_ rr
	            WHERE rr.rowno = v_pointer_);
	        INSERT INTO tmp_code_
	            SELECT
	                tblRate.code,
	                tblRate.rateid,
	                tblRate.CountryID
	            FROM tblRate
	            JOIN tmp_Codedecks_ cd
	                ON tblRate.CodeDeckId = cd.CodeDeckId
	            JOIN tmp_Raterules_ rr
	                ON tblRate.code LIKE (REPLACE(rr.code,
	                '*', '%%'))
	            WHERE rr.rowno = v_pointer_;
				 INSERT INTO tmp_tblVendorRate_
	          SELECT AccountId,
	          RateID,
	          Rate,
	          ConnectionFee,
	          TrunkId,
	          EffectiveDate,
				 RowID
				 FROM(SELECT AccountId,
	          RateID,
	          Rate,
	          ConnectionFee,
	          TrunkId,
	          EffectiveDate,
				 @row_num := IF(@prev_RateId=tblVendorRate.RateId AND @prev_TrunkID=tblVendorRate.TrunkID AND @prev_AccountId=tblVendorRate.AccountId and @prev_EffectiveDate >= tblVendorRate.EffectiveDate ,@row_num+1,1) AS RowID,
				 @prev_RateId  := tblVendorRate.RateId,
				 @prev_TrunkID  := tblVendorRate.TrunkID,
				 @prev_AccountId  := tblVendorRate.AccountId,
				 @prev_EffectiveDate  := tblVendorRate.EffectiveDate
	          FROM tblVendorRate ,(SELECT @row_num := 1) x,(SELECT @prev_RateId := '') y,(SELECT @prev_TrunkID := '') z ,(SELECT @prev_AccountId := '') v ,(SELECT @prev_EffectiveDate := '') u
	          WHERE TrunkId = v_trunk_
				 ORDER BY tblVendorRate.AccountId , tblVendorRate.TrunkID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC) TBLVENDOR;
	       INSERT INTO tmp_Vendorrates_   (code ,rate ,  ConnectionFee , AccountId ,RowNo ,PreferenceRank )
			  SELECT code,rate,ConnectionFee,AccountId,RowNo,PreferenceRank FROM(SELECT *,
				 @row_num := IF(@prev_Code2 = code AND  @prev_Preference >= Preference AND  @prev_Rate2 >= rate ,@row_num+1,1) AS PreferenceRank,
				 @prev_Code2  := Code,
				 @prev_Rate2  := rate,
				 @prev_Preference  := Preference
				 FROM (SELECT code,
				rate,
				ConnectionFee,
				AccountId,
				Preference,
				@row_num := IF(@prev_Code   = code AND @prev_Rate >= rate ,(@row_num+1),1) AS RowNo,
				@prev_Code  := Code,
				@prev_Rate  := rate
				from  (
				SELECT DISTINCT
	                c.code,
					 ( tmp_tblVendorRate_.rate / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CompanyCurrencyID_ and  CompanyID = v_CompanyId_ ))
						* (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ ) as  Rate,
	                tmp_tblVendorRate_.ConnectionFee,
	                tblAccount.AccountId,
					Preference
	            FROM tmp_Raterules_ rateRule
	            JOIN tblRateRuleSource
	                ON tblRateRuleSource.rateruleid = rateRule.rateruleid
	            JOIN tblAccount
	                ON tblAccount.AccountId = tblRateRuleSource.AccountId
	            JOIN tmp_tblVendorRate_
	                ON tblAccount.AccountId = tmp_tblVendorRate_.AccountId
	                AND tmp_tblVendorRate_.TrunkId = v_trunk_ and RowID = 1
	            JOIN tblVendorTrunk
	                ON tmp_tblVendorRate_.AccountId = tblVendorTrunk.AccountID
	                AND tmp_tblVendorRate_.TrunkId = tblVendorTrunk.TrunkID
	                AND tblVendorTrunk.Status = 1
	            JOIN tmp_code_ c
	                ON c.RateID = tmp_tblVendorRate_.RateId
	            LEFT JOIN tblVendorBlocking blockCountry
	                ON tblAccount.AccountId = blockCountry.AccountId
	                AND c.CountryID = blockCountry.CountryId
	                AND blockCountry.TrunkID = v_trunk_
	            LEFT JOIN tblVendorBlocking blockCode
	                ON tblAccount.AccountId = blockCode.AccountId
	                AND c.RateID = blockCode.RateId
	                AND blockCode.TrunkID = v_trunk_
	            LEFT JOIN tblVendorPreference
	                ON tblAccount.AccountId = tblVendorPreference.AccountId
	                AND c.RateID = tblVendorPreference.RateId
	                AND tblVendorPreference.TrunkID = v_trunk_
	            WHERE rateRule.rowno = v_pointer_
	            AND blockCode.VendorBlockingId IS NULL
	            AND blockCountry.VendorBlockingId IS NULL
	            AND CAST(tmp_tblVendorRate_.EffectiveDate AS DATE) <= CAST(NOW() AS DATE)
	            
				) VNDRTBL ,(SELECT @row_num := 1) x,(SELECT @prev_Code := '') y,(SELECT @prev_Rate := '') z
				ORDER BY code , rate
				) VNDRTBL ,(SELECT @row_num := 1) x,(SELECT @prev_Code2 := '') y,(SELECT @prev_Rate2 := '') z,(SELECT @prev_Preference := '') v
			 ORDER BY code,Preference desc,rate) LASTTBL;
	       
		  select * from tmp_Vendorrates_;
		  			 
			 DELETE FROM tmp_tblVendorRate_;
			 
			 INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
			 select  code,rate,ConnectionFee from tmp_Rates_;
 

	        IF v_Average_ = 0 
	        THEN
	           
	           
	                 
	            INSERT INTO tmp_Rates_
	                SELECT
	                    code,
	                    (SELECT 
	                            CASE WHEN vRate.rate >= minrate AND
	                            vRate.rate <= maxrate THEN vRate.rate
	                                + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin
	                                END) ELSE vRate.rate
	                            END
	                        FROM tblRateRuleMargin 
	                        WHERE rateruleid = v_rateRuleId_ LIMIT 1) as Rate,
	                ConnectionFee
	                FROM ( 
									 SELECT
			                    vr.code,
			                    vr.rate,
			                    vr.ConnectionFee,
			                    AccountId
			                FROM tmp_Vendorrates_ vr
			                left join tmp_Rates2_ rate on rate.code = vr.code 
			                WHERE 
			                (
			                    (v_Use_Preference_ =0 and RowNo <= v_RatePosition_ ) or 
			                    (v_Use_Preference_ =1 and PreferenceRank <= v_RatePosition_)
			                )
			                AND rate.code is null
			             --   AND IFNULL(vr.Rate, 0) > 0
						 ) vRate;
	         ELSE -- AVERAGE
	            
	           INSERT INTO tmp_Rates_
	                SELECT
	                    code,
	                     (SELECT 
	                            CASE WHEN vRate.rate >= minrate AND
	                            vRate.rate <= maxrate THEN vRate.rate
	                                + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100)
	                                    * vRate.rate) ELSE addmargin
	                                END) ELSE vRate.rate
	                            END
	                        FROM tblRateRuleMargin 
	                        WHERE rateruleid = v_rateRuleId_ LIMIT 1 ) as Rate,
	                    ConnectionFee
	                FROM ( 
						 	SELECT
	                    vr.code,
	                    AVG(vr.Rate) as Rate,
	                    AVG(vr.ConnectionFee) as ConnectionFee
	                FROM tmp_Vendorrates_ vr
	                left join tmp_Rates2_ rate on rate.code = vr.code 
	                WHERE 
	                (
	                    (v_Use_Preference_ =0 and RowNo <= v_RatePosition_ ) or 
	                    (v_Use_Preference_ =1 and PreferenceRank <= v_RatePosition_)
	                )
	                AND rate.code is null
	               -- AND IFNULL(vr.Rate, 0) > 0
	                GROUP BY vr.code 
						 ) vRate;
	
	        END IF;
	        DELETE FROM tmp_Vendorrates_;
	        DELETE FROM tmp_code_;
	
	        SET v_pointer_ = v_pointer_ + 1;
	    END WHILE;
		 
		 
	  --  DELETE FROM tmp_Rates_
	 --   WHERE IFNULL(Rate, 0) = 0;
		
		select * from tmp_Rates_;
		
	    IF p_RateTableId = -1
	    THEN
	
	        INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
	            VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);
	
	        SET p_RateTableId = LAST_INSERT_ID();
	
	        /*INSERT INTO tblRateTableRate (RateID,
	        RateTableId,
	        Rate,
	        EffectiveDate,
	        PreviousRate,
	        Interval1,
	        IntervalN,
	        ConnectionFee
	        )*/
	            SELECT DISTINCT
	                RateId,
	                p_RateTableId,
	                Rate,
	                p_EffectiveDate,
	                Rate,
	                Interval1,
	                IntervalN,
	                ConnectionFee
	            FROM tmp_Rates_ rate
	            INNER JOIN tblRate
	                ON rate.code  = tblRate.Code 
	            WHERE tblRate.CodeDeckId = v_codedeckid_;
	
	    ELSE
	
	        /*INSERT INTO tblRateTableRate (RateID,
	        RateTableId,
	        Rate,
	        EffectiveDate,
	        PreviousRate,
	        Interval1,
	        IntervalN,
	        ConnectionFee
	        )*/
	            SELECT DISTINCT
	                tblRate.RateId,
	                p_RateTableId,
	                rate.Rate,
	                p_EffectiveDate,
	                rate.Rate,
	                tblRate.Interval1,
	                tblRate.IntervalN,
	                rate.ConnectionFee
	            FROM tmp_Rates_ rate
	            INNER JOIN tblRate 
	                ON rate.code  = tblRate.Code 
	            LEFT JOIN tblRateTableRate tbl1 
	                ON tblRate.RateId = tbl1.RateId
	                AND tbl1.RateTableId = p_RateTableId
	            LEFT JOIN tblRateTableRate tbl2 
	            ON tblRate.RateId = tbl2.RateId
	            and tbl2.EffectiveDate = p_EffectiveDate
	            AND tbl2.RateTableId = p_RateTableId
	            WHERE  (    tbl1.RateTableRateID IS NULL 
	                    OR
	                    (
	                        tbl2.RateTableRateID IS NULL 
	                        AND  tbl1.EffectiveDate != p_EffectiveDate                       
	                        
	                    )
	                 )
	            AND tblRate.CodeDeckId = v_codedeckid_;
	
	        /*UPDATE tblRateTableRate
	        INNER JOIN tblRate 
	            ON tblRate.RateId = tblRateTableRate.RateId
	            AND tblRateTableRate.RateTableId = p_RateTableId
	            AND tblRateTableRate.EffectiveDate = p_EffectiveDate
	         INNER JOIN tmp_Rates_ as rate
	          ON  rate.code  = tblRate.Code 
	          SET tblRateTableRate.PreviousRate = tblRateTableRate.Rate,
	            tblRateTableRate.EffectiveDate = p_EffectiveDate,
	            tblRateTableRate.Rate = rate.Rate,
	            tblRateTableRate.ConnectionFee = rate.ConnectionFee,
	            tblRateTableRate.updated_at = NOW(),
	            tblRateTableRate.ModifiedBy = 'RateManagementService',
	            tblRateTableRate.Interval1 = tblRate.Interval1,
	            tblRateTableRate.IntervalN = tblRate.IntervalN
	        WHERE tblRate.CodeDeckId = v_codedeckid_
	        AND rate.rate != tblRateTableRate.Rate;
	
	        DELETE tblRateTableRate
	            FROM tblRateTableRate 
	        WHERE tblRateTableRate.RateTableId = p_RateTableId
	            AND RateId NOT IN (SELECT DISTINCT
	                    RateId
	                FROM tmp_Rates_ rate
	                INNER JOIN tblRate 
	                    ON rate.code  = tblRate.Code 
	                WHERE tblRate.CodeDeckId = v_codedeckid_)
	            AND tblRateTableRate.EffectiveDate = p_EffectiveDate;
			*/
	
	
	    END IF;
		
		/*UPDATE tblRateTable
	   SET RateGeneratorID = p_RateGeneratorId,
		  TrunkID = v_trunk_,
		  CodeDeckId = v_codedeckid_,
		  updated_at = now()
		WHERE RateTableID = p_RateTableId;
    	SELECT p_RateTableId as RateTableID;
    	CALL prc_WSJobStatusUpdate(p_jobId, 'S', 'RateTable Created Successfully', '');*/
    	
    	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    	
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGenerateSippySheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateSippySheet`(IN `p_CustomerID` INT , IN `p_Trunks` varchar(200) )
BEGIN
    
    DECLARE v_codedeckid_ INT;
   DECLARE v_ratetableid_ INT;
   DECLARE v_RateTableAssignDate_ DATETIME;
   DECLARE v_NewA2ZAssign_ INT;
   
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
     
        DROP TEMPORARY TABLE IF EXISTS tmp_RateTable_;
        CREATE TEMPORARY TABLE tmp_RateTable_ (
            RateId INT,
            Rate DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            Prefix VARCHAR(50),
                INDEX tmp_RateTable_RateId (`RateId`)  
        );
        
        DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRate_;
        CREATE TEMPORARY TABLE tmp_CustomerRate_ (
            RateId INT,
            Rate  DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            Prefix VARCHAR(50),
                INDEX tmp_CustomerRate_RateId (`RateId`)  
        );
        DROP TEMPORARY TABLE IF EXISTS tmp_tbltblRate_;
        CREATE TEMPORARY TABLE tmp_tbltblRate_ (
            RateId INT 
        );
        
        SELECT
            CodeDeckId,
            RateTableID,
            RateTableAssignDate INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
        FROM tblCustomerTrunk
        WHERE 
        FIND_IN_SET(tblCustomerTrunk.TrunkID,p_Trunks)!= 0  
        AND tblCustomerTrunk.AccountID = p_CustomerID
        AND tblCustomerTrunk.Status = 1
          LIMIT 1;

       SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
            SELECT MAX(EffectiveDate) as EffectiveDate
            FROM 
            tblRateTableRate
            WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() 
            ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
        )tbl;
        
        INSERT INTO tmp_RateTable_
        
                SELECT  RateId ,
                        tblRateTableRate.Rate ,
                        CASE WHEN v_NewA2ZAssign_ = 1 THEN
                            v_RateTableAssignDate_  
                        ELSE
                            tblRateTableRate.EffectiveDate
                        END  AS EffectiveDate,
                        tblRateTableRate.Interval1,
                        tblRateTableRate.IntervalN,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        tblTrunk.Prefix 
                FROM    tblAccount
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                        JOIN tblRateTable ON tblCustomerTrunk.RateTableId = tblRateTable.RateTableId
                        JOIN tblRateTableRate ON tblRateTableRate.RateTableId = tblRateTable.RateTableId
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerTrunk.TrunkId
                         
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblRateTableRate.Rate > 0
                        And FIND_IN_SET(tblTrunk.TrunkID,p_Trunks)!= 0  
                        AND ( EffectiveDate <= now() or ( v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW()) )
               ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC;
    
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTable4_ as (select * from tmp_RateTable_);         
         DELETE n1 FROM tmp_RateTable_ n1, tmp_RateTable4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
           AND n1.Trunk = n2.Trunk
           AND  n1.RateId = n2.RateId;
    
        INSERT INTO tmp_CustomerRate_
         SELECT  RateId ,
                        tblCustomerRate.Rate ,
                        EffectiveDate ,
                        tblCustomerRate.Interval1,
                        tblCustomerRate.IntervalN,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        CASE  WHEN t.Prefix is not null
                        THEN
                            t.Prefix
                        ELSE
                            tblTrunk.Prefix
                        END AS Prefix 
                FROM    tblAccount
                        JOIN tblCustomerRate ON tblAccount.AccountID = tblCustomerRate.CustomerID
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerRate.TrunkId
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.Accountid = tblAccount.AccountID
                            AND tblCustomerTrunk.TrunkId = tblTrunk.TrunkId
                        LEFT JOIN tblCustomerTrunk ct ON ct.AccountId = tblCustomerRate.CustomerID and ct.TrunkID =tblCustomerRate.RoutinePlan AND ct.RoutinePlanStatus = 1
                        LEFT JOIN tblTrunk t ON t.TrunkID = tblCustomerRate.RoutinePlan
                         
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblCustomerRate.Rate > 0
                       And FIND_IN_SET(tblTrunk.TrunkID,p_Trunks)!= 0  
                       AND EffectiveDate <= now()
               ORDER BY tblCustomerRate.CustomerId,tblCustomerRate.TrunkId,tblCustomerRate.RateID,tblCustomerRate.effectivedate DESC;
    
    
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRate_);          
         DELETE n1 FROM tmp_CustomerRate_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
           AND n1.Trunk = n2.Trunk
           AND  n1.RateId = n2.RateId;
           
           
            INSERT INTO tmp_tbltblRate_
            SELECT    RateID
            FROM      tmp_RateTable_
         UNION
         SELECT    RateID
         FROM      tmp_CustomerRate_;
    
            
        SELECT 
                'A' as `Action [A|D|U|S|SA` ,
                '' as id,
                        Concat(CASE WHEN cr.RateID IS NULL
                       THEN IFNULL(rt.Prefix, '')
                       ELSE  IFNULL(cr.Prefix, '')
                   END , Code) as Prefix
                ,
                Description as COUNTRY ,
                 CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.Interval1 
                                ELSE
                                    rt.Interval1 
                                END as `Interval 1`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.IntervalN 
                                ELSE
                                    rt.IntervalN
                                END as `Interval N`,
                
                CASE WHEN cr.RateID IS NULL
                                 THEN CASE WHEN rt.rate < rt.Rate
                                                AND rt.EffectiveDate  > NOW()
                                           THEN rt.rate
                                           ELSE rt.Rate
                                      END
                                 ELSE CASE WHEN cr.rate < cr.Rate
                                                AND cr.EffectiveDate  > NOW() 
                                           THEN cr.rate
                                           ELSE cr.Rate
                                      END
                            END as `Price 1`,
                CASE WHEN cr.RateID IS NULL
                                 THEN CASE WHEN rt.rate < rt.Rate
                                                AND rt.EffectiveDate  > NOW()
                                           THEN rt.rate
                                           ELSE rt.Rate
                                      END
                                 ELSE CASE WHEN cr.rate < cr.Rate
                                                AND cr.EffectiveDate  > NOW() 
                                           THEN cr.rate
                                           ELSE cr.Rate
                                      END
                            END as `Price N` ,
                0  as Forbidden,
                0 as `Grace Period`,
                'NOW' as `Activation Date`,
                '' as `Expiration Date` 
        FROM    tblRate
                LEFT JOIN tmp_RateTable_ rt ON rt.RateID = tblRate.RateID
                LEFT JOIN tmp_CustomerRate_ cr ON cr.RateID = tblRate.RateID  
        WHERE tblRate.RateID in (SELECT RateID FROM tmp_tbltblRate_)    
        ORDER BY Prefix;
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGenerateVendorSippySheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateVendorSippySheet`(IN `p_VendorID` INT  , IN `p_Trunks` varchar(200))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
         
        call vwVendorSippySheet(p_VendorID,p_Trunks); 
        SELECT 
				 `Action [A|D|U|S|SA`,
                id ,
                vendorRate.Prefix,
                COUNTRY,
                Preference ,
                `Interval 1` ,
                `Interval N` ,
                `Price 1` ,
                `Price N` ,
                `1xx Timeout` ,
                `2xx Timeout` ,
                `Huntstop` ,
                Forbidden ,
                `Activation Date` ,
                `Expiration Date`
        FROM    tmp_VendorSippySheet_ vendorRate     
        WHERE   vendorRate.AccountId = p_VendorID
        And  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0;
        
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGenerateVendorVersion3VosSheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateVendorVersion3VosSheet`(IN `p_VendorID` INT , IN `p_Trunks` varchar(200) )
BEGIN
         SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
         
        call vwVendorVersion3VosSheet(p_VendorID,p_Trunks);
        SELECT  `Rate Prefix` ,
                `Area Prefix` ,
                `Rate Type` ,
                `Area Name` ,
                `Billing Rate` ,
                `Billing Cycle`,
                `Minute Cost` ,
                `Lock Type` ,
                `Section Rate` ,
                `Billing Rate for Calling Card Prompt` ,
                `Billing Cycle for Calling Card Prompt`
        FROM    tmp_VendorVersion3VosSheet_
        WHERE   AccountID = p_VendorID
        AND  FIND_IN_SET(TrunkId,p_Trunks)!= 0
        ORDER BY `Rate Prefix`;
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGenerateVersion3VosSheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateVersion3VosSheet`(IN `p_CustomerID` INT , IN `p_trunks` varchar(200) )
BEGIN
        DECLARE v_codedeckid_  INT;
        DECLARE v_ratetableid_ INT;
        DECLARE v_RateTableAssignDate_  DATETIME;
        DECLARE v_NewA2ZAssign_ INT;
        
        SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

         
    
        DROP TEMPORARY TABLE IF EXISTS tmp_RateTable_;
        CREATE TEMPORARY TABLE tmp_RateTable_ (
            RateId INT ,
            Rate DECIMAL(18,6),
            ConnectionFee DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            PreviousRate DECIMAL(18,6),
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            RatePrefix VARCHAR(50),
            AreaPrefix VARCHAR(50),
            INDEX tmp_RateTable_RateID (`RateID`)
        );
        DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRate_;
        CREATE TEMPORARY TABLE tmp_CustomerRate_ (
            RateId INT ,
            Rate DECIMAL(18,6),
            ConnectionFee DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            PreviousRate DECIMAL(18,6),
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            RatePrefix VARCHAR(50),
            AreaPrefix VARCHAR(50),
            INDEX tmp_CustomerRate_RateID (`RateID`)
        );
        DROP TEMPORARY TABLE IF EXISTS tmp_tbltblRate_;
        CREATE TEMPORARY TABLE tmp_tbltblRate_ (
            RateId INT 
        );
        
        
        SELECT 
        CodeDeckId,
        RateTableID,
         RateTableAssignDate INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
        FROM tblCustomerTrunk
        WHERE 
        FIND_IN_SET(tblCustomerTrunk.TrunkID,p_trunks)!= 0
        AND tblCustomerTrunk.AccountID = p_CustomerID
        AND tblCustomerTrunk.Status = 1
         LIMIT 1;

       SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
            SELECT MAX(EffectiveDate) as EffectiveDate
            FROM 
            tblRateTableRate
            WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() 
            ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
        )tbl;
        


        INSERT INTO tmp_RateTable_
          
                SELECT  RateId ,
                        tblRateTableRate.Rate ,
                        tblRateTableRate.ConnectionFee,
                        EffectiveDate ,
                        tblRateTableRate.Interval1,
                        tblRateTableRate.IntervalN,
                        PreviousRate ,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        tblTrunk.RatePrefix ,
                        tblTrunk.AreaPrefix 
                FROM    tblAccount
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                        JOIN tblRateTable ON tblCustomerTrunk.RateTableId = tblRateTable.RateTableId
                        JOIN tblRateTableRate ON tblRateTableRate.RateTableId = tblRateTable.RateTableId
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerTrunk.TrunkId
                         
                    
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblRateTableRate.Rate > 0
                        And FIND_IN_SET(tblTrunk.TrunkId,p_trunks)!= 0
                        AND ( EffectiveDate <= now() or ( v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW()) )
               ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC;
    
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTable4_ as (select * from tmp_RateTable_);         
         DELETE n1 FROM tmp_RateTable_ n1, tmp_RateTable4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
           AND n1.Trunk = n2.Trunk
           AND  n1.RateId = n2.RateId;
           
           
        INSERT INTO tmp_CustomerRate_
        
                SELECT  RateId ,
                        tblCustomerRate.Rate ,
                        tblCustomerRate.ConnectionFee,
                        EffectiveDate ,
                        tblCustomerRate.Interval1,
                        tblCustomerRate.IntervalN,
                        PreviousRate ,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        CASE  WHEN t.RatePrefix is not null 
                        THEN
                            t.RatePrefix
                        ELSE
                            tblTrunk.RatePrefix
                        END AS RatePrefix,
                        CASE  WHEN t.AreaPrefix is not null
                        THEN
                            t.AreaPrefix
                        ELSE
                            tblTrunk.AreaPrefix
                        END AS AreaPrefix
                          

                FROM    tblAccount
                        JOIN tblCustomerRate ON tblAccount.AccountID = tblCustomerRate.CustomerID
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerRate.TrunkId
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                            AND tblCustomerTrunk.TrunkId = tblTrunk.TrunkId
                        LEFT JOIN tblCustomerTrunk ct ON ct.AccountId = tblCustomerRate.CustomerID and ct.TrunkID =tblCustomerRate.RoutinePlan AND ct.RoutinePlanStatus = 1
                        LEFT JOIN tblTrunk t ON t.TrunkID = tblCustomerRate.RoutinePlan 
                         
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblCustomerRate.Rate > 0 
                        And FIND_IN_SET(tblTrunk.TrunkId,p_trunks)!= 0
                        AND EffectiveDate <= now()
               ORDER BY tblCustomerRate.CustomerId,tblCustomerRate.TrunkId,tblCustomerRate.RateID,tblCustomerRate.effectivedate DESC;
        
        
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRate_);          
         DELETE n1 FROM tmp_CustomerRate_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
           AND n1.Trunk = n2.Trunk
           AND  n1.RateId = n2.RateId;
    
         INSERT INTO tmp_tbltblRate_
            SELECT    RateID
            FROM      tmp_RateTable_
         UNION
         SELECT    RateID
         FROM      tmp_CustomerRate_;
            
        SELECT distinct 
                CASE WHEN cr.RateID IS NULL
                                     THEN  IFNULL(rt.RatePrefix, '')
                                     ELSE 
                                                IFNULL(cr.RatePrefix, '')  
                                END as `Rate Prefix` ,
                Concat(CASE WHEN cr.RateID IS NULL
                       THEN IFNULL(rt.AreaPrefix, '')
                       ELSE IFNULL(cr.AreaPrefix, '')
                  END , Code) as `Area Prefix` ,
                'International' as `Rate Type` ,
                Description  as `Area Name`,
                CASE WHEN cr.RateID IS NULL
                                      THEN CASE WHEN rt.PreviousRate < rt.Rate
                                                     AND rt.EffectiveDate > NOW() 
                                                THEN rt.PreviousRate
                                                     / 60
                                                ELSE rt.Rate / 60
                                           END
                                      ELSE CASE WHEN cr.PreviousRate < cr.Rate
                                                     AND cr.EffectiveDate > NOW() 
                                                THEN cr.PreviousRate
                                                     / 60
                                                ELSE cr.Rate / 60
                                           END
                                 END  as `Billing Rate`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.IntervalN 
                                ELSE
                                    rt.IntervalN
                                END as `Billing Cycle`,
                CASE WHEN cr.RateID IS NULL
                                          THEN 
                                                     rt.Rate
                                          ELSE  
                                                     cr.Rate
                                     END as `Minute Cost` ,
                'No Lock'  as `Lock Type`,
                CASE WHEN tblRate.Interval1 != tblRate.IntervalN or cr.Interval1 != cr.IntervalN or rt.Interval1!= rt.IntervalN 
                                      THEN Concat('0,'
                                           , CASE WHEN cr.RateID IS NULL
                                                            THEN 
                                                                rt.Rate
                                                            ELSE 
                                                                cr.Rate
                                                        END
                                           , ','
                                        ,   CASE WHEN cr.RateID IS NULL
                                                THEN 
                                                    rt.Interval1
                                                ELSE 
                                                    cr.Interval1
                                            END)
                                      ELSE ''
                                 END as `Section Rate`,

                0 AS `Billing Rate for Calling Card Prompt`,
                0  as `Billing Cycle for Calling Card Prompt`
        FROM    tblRate
                LEFT JOIN tmp_RateTable_ rt ON rt.RateID = tblRate.RateID
                LEFT JOIN tmp_CustomerRate_ cr ON cr.RateID = tblRate.RateID
        WHERE tblRate.RateID in (SELECT RateID FROM tmp_tbltblRate_)
        ORDER BY `Rate Prefix`; 
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSGetJobDetails
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGetJobDetails`(IN `p_jobId` int)
BEGIN
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
  SELECT tblJob.CompanyID 
  , IFNULL(tblUser.EmailAddress,'')  as EmailAddress
  , tblJob.Title as JobTitle
  , IFNULL(tblJob.JobStatusMessage,'') as JobStatusMessage
  , tblJobStatus.Title as StatusTitle
  ,OutputFilePath
  ,AccountID 
  FROM tblJob   
    JOIN tblJobStatus 
      ON tblJob.JobStatusID = tblJobStatus.JobStatusID    
    JOIN tblUser
      ON tblUser.UserID = tblJob.JobLoggedUserID
  WHERE tblJob.JobID = p_jobId;   
  
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSInsertJobLog
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSInsertJobLog`(IN `p_companyId` INT , IN `p_accountId` INT , IN `p_processId` VARCHAR(50) , IN `p_process` VARCHAR(50) , IN `p_message` LONGTEXT)
BEGIN
	 
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  
	INSERT  INTO tblLog
    ( CompanyID ,
      AccountID ,
      ProcessID ,
      Process ,
      Message
    )
    SELECT  p_companyId ,
            ( CASE WHEN ( p_accountId IS NOT NULL
                          AND p_accountId > 0
                        ) THEN p_accountId
                   ELSE ( SELECT    AccountID
                          FROM      tblJob
                          WHERE     ProcessID = p_processId
                        )
              END ) ,
            p_processId ,
            p_process ,
            p_message;
     SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;   
  
    END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSJobStatusUpdate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSJobStatusUpdate`(IN `p_JobId` int , IN `p_Status` varchar(5) , IN `p_JobStatusMessage` longtext , IN `p_OutputFilePath` longtext
)
BEGIN
  DECLARE v_JobStatusId_ int;
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED; 

  SELECT  JobStatusId INTO v_JobStatusId_ FROM tblJobStatus WHERE Code = p_Status;

  UPDATE tblJob
  SET JobStatusID = v_JobStatusId_
  , JobStatusMessage = p_JobStatusMessage
  , OutputFilePath = p_OutputFilePath
  , updated_at = NOW()
  , ModifiedBy = 'WindowsService'
  WHERE JobID = p_JobId;
  
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSProcessCodeDeck
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessCodeDeck`(IN `p_processId` VARCHAR(200) , IN `p_companyId` INT)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;     
    DECLARE   v_CodeDeckId_ INT;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    
	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  ( 
        Message VARCHAR(200)     
    );

    SELECT CodeDeckId INTO v_CodeDeckId_ FROM tblTempCodeDeck WHERE ProcessId = p_processId AND CompanyId = p_companyId LIMIT 1;
    
    DELETE n1 FROM tblTempCodeDeck n1, tblTempCodeDeck n2 WHERE n1.TempCodeDeckRateID < n2.TempCodeDeckRateID 
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.CompanyId = n2.CompanyId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;
 
    UPDATE tblTempCodeDeck
    SET  
        tblTempCodeDeck.Interval1 = CASE WHEN tblTempCodeDeck.Interval1 is not null  and tblTempCodeDeck.Interval1 > 0
                                    THEN    
                                        tblTempCodeDeck.Interval1
                                    ELSE
                                    CASE WHEN tblTempCodeDeck.Interval1 is null and (tblTempCodeDeck.Description LIKE '%gambia%' OR tblTempCodeDeck.Description LIKE '%mexico%')
                                            THEN 
                                            60
                                    ELSE CASE WHEN tblTempCodeDeck.Description LIKE '%USA%' 
                                            THEN 
                                            6
                                            ELSE 
                                            1
                                        END
                                    END
                                    END,
        tblTempCodeDeck.IntervalN = CASE WHEN tblTempCodeDeck.IntervalN is not null  and tblTempCodeDeck.IntervalN > 0
                                    THEN    
                                        tblTempCodeDeck.IntervalN
                                    ELSE
                                        CASE WHEN tblTempCodeDeck.Description LIKE '%mexico%' THEN 
                                        60
                                    ELSE CASE
                                        WHEN tblTempCodeDeck.Description LIKE '%USA%' THEN 
                                        6
                                    ELSE 
                                        1
                                    END
                                    END
                                    END
    WHERE tblTempCodeDeck.ProcessId = p_processId;
  
    UPDATE tblTempCodeDeck t
    INNER JOIN (
        SELECT DISTINCT
            tblTempCodeDeck.Code,
            tblCountry.CountryID
        FROM tblCountry
        LEFT OUTER JOIN (
            SELECT
                Prefix
            FROM tblCountry
            GROUP BY Prefix
            HAVING COUNT(*) > 1) d
            ON tblCountry.Prefix = d.Prefix
        INNER JOIN tblTempCodeDeck
            ON 
                tblTempCodeDeck.Country IS NOT NULL
            AND (tblTempCodeDeck.Code LIKE CONCAT(tblCountry.Prefix , '%') AND d.Prefix IS NULL)
                OR (tblTempCodeDeck.Code LIKE CONCAT(tblCountry.Prefix , '%') AND d.Prefix IS NOT NULL AND 
                (tblTempCodeDeck.Country LIKE Concat('%' , tblCountry.Country , '%') OR tblTempCodeDeck.Description LIKE Concat('%' , tblCountry.Country , '%')) )
        WHERE tblTempCodeDeck.ProcessId = p_processId) c
        ON t.Code = c.Code
        AND t.ProcessId = p_processId
    SET t.CountryId = c.CountryID;
  
          IF ( SELECT COUNT(*)
                 FROM   tblTempCodeDeck
                 WHERE  tblTempCodeDeck.ProcessId = p_processId
                        AND CountryId IS NULL
               ) > 0
            THEN
                
        INSERT INTO tmp_JobLog_  (Message)
        SELECT DISTINCT CONCAT(CODE , ' INVALID CODE - COUNTRY NOT FOUND ')
        FROM tblTempCodeDeck 
        WHERE tblTempCodeDeck.ProcessId = p_processId 
        AND CountryId IS NULL AND Country IS NOT NULL;
        
        
            
            END IF;   


 IF ( SELECT COUNT(*)
                 FROM   tblTempCodeDeck
                 WHERE  tblTempCodeDeck.ProcessId = p_processId
                        AND tblTempCodeDeck.Action = 'D'
               ) > 0
            THEN
      DELETE  tblRate
            FROM    tblRate
                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
                                                  AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
                    LEFT OUTER JOIN tblCustomerRate ON tblRate.RateID = tblCustomerRate.RateID
                    LEFT OUTER JOIN tblRateTableRate ON tblRate.RateID = tblRateTableRate.RateID
                    LEFT OUTER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId
            WHERE   tblTempCodeDeck.Action = 'D'
          AND tblTempCodeDeck.CompanyID = p_companyId
          AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
          AND tblTempCodeDeck.ProcessId = p_processId                    
                    AND tblCustomerRate.CustomerRateID IS NULL
                    AND tblRateTableRate.RateTableRateID IS NULL
                    AND tblVendorRate.VendorRateID IS NULL ;   
		END IF; 
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
  
  
      INSERT INTO tmp_JobLog_ (Message)
      SELECT CONCAT(tblRate.Code , 'FAILED TO DELETE - CODE IS IN USE')
      FROM    tblRate
                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
          AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
            WHERE   tblTempCodeDeck.Action = 'D'
          AND tblTempCodeDeck.ProcessId = p_processId
          AND tblTempCodeDeck.CompanyID = p_companyId AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_;
      
      UPDATE  tblRate
      JOIN tblTempCodeDeck ON tblRate.Code = tblTempCodeDeck.Code
      		AND tblRate.CountryID = tblTempCodeDeck.CountryId
            AND tblTempCodeDeck.ProcessId = p_processId
            AND tblRate.CompanyID = p_companyId
            AND tblRate.CodeDeckId = v_CodeDeckId_
            AND tblTempCodeDeck.Action != 'D'
		SET   tblRate.Description = tblTempCodeDeck.Description,
            tblRate.Interval1 = tblTempCodeDeck.Interval1,
            tblRate.IntervalN = tblTempCodeDeck.IntervalN;
  
      
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
      
            INSERT  INTO tblRate
                    ( CountryID ,
                      CompanyID ,
                      CodeDeckId,
                      Code ,
                      Description,
                      Interval1,
                      IntervalN
                    )
                    SELECT  DISTINCT
              tblTempCodeDeck.CountryId ,
                            tblTempCodeDeck.CompanyId ,
                            tblTempCodeDeck.CodeDeckId,
                            tblTempCodeDeck.Code ,
                            tblTempCodeDeck.Description,
                            tblTempCodeDeck.Interval1,
                            tblTempCodeDeck.IntervalN
                    FROM    tblTempCodeDeck left join tblRate on(tblRate.CompanyID = p_companyId AND  tblRate.CodeDeckId = v_CodeDeckId_ AND tblTempCodeDeck.Code=tblRate.Code)
                    WHERE  tblRate.RateID is null
                            AND tblTempCodeDeck.ProcessId = p_processId
              AND tblTempCodeDeck.CompanyID = p_companyId
              AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
                            AND tblTempCodeDeck.Action != 'D';
  
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
    
	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );
	 
    DELETE  FROM tblTempCodeDeck WHERE   tblTempCodeDeck.ProcessId = p_processId;
 	 SELECT * from tmp_JobLog_;
	      
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSProcessRateTableRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessRateTableRate`(IN `p_ratetableid` INT, IN `p_replaceAllRates` INT, IN `p_effectiveImmediately` INT, IN `p_processId` VARCHAR(200), IN `p_addNewCodesToCodeDeck` INT, IN `p_companyId` INT)
BEGIN
	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	 DECLARE     v_CodeDeckId_ INT ;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;    
	 
    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message VARCHAR(200)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
			`CodeDeckId` int ,
			`Code` varchar(50) ,
			`Description` varchar(200) ,
			`Rate` decimal(18, 6) ,
			`EffectiveDate` Datetime ,
			`Change` varchar(100) ,
			`ProcessId` varchar(200) ,
			`Preference` int ,
			`ConnectionFee` decimal(18, 6),
			`Interval1` int,
			`IntervalN` int,
			INDEX tmp_EffectiveDate (`EffectiveDate`),
			INDEX tmp_Code (`Code`),
			INDEX tmp_Change (`Change`)
    );
    
     DELETE n1 FROM tblTempRateTableRate n1, tblTempRateTableRate n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;

		  INSERT INTO tmp_TempRateTableRate_
        SELECT distinct `CodeDeckId`,`Code`,`Description`,`Rate`,`EffectiveDate`,`Change`,`ProcessId`,`Preference`,`ConnectionFee`,`Interval1`,`IntervalN` FROM tblTempRateTableRate WHERE tblTempRateTableRate.ProcessId = p_processId;
		 
      
		
	 	     SELECT CodeDeckId INTO v_CodeDeckId_ FROM tmp_TempRateTableRate_ WHERE ProcessId = p_processId  LIMIT 1;

            UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
            LEFT JOIN tblRate
                ON tblRate.Code = tblTempRateTableRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                AND tblRate.CodeDeckId =  v_CodeDeckId_
            SET  
                tblTempRateTableRate.Interval1 = CASE WHEN tblTempRateTableRate.Interval1 is not null  and tblTempRateTableRate.Interval1 > 0
                                            THEN    
                                                tblTempRateTableRate.Interval1
                                            ELSE
                                            CASE WHEN tblRate.Interval1 is not null  
                                            THEN    
                                                tblRate.Interval1
                                            ELSE CASE WHEN tblTempRateTableRate.Interval1 is null and (tblTempRateTableRate.Description LIKE '%gambia%' OR tblTempRateTableRate.Description LIKE '%mexico%')
                                                 THEN 
                                                    60
                                            ELSE CASE WHEN tblTempRateTableRate.Description LIKE '%USA%' 
                                                 THEN 
                                                    6
                                                 ELSE 
                                                    1
                                                END
                                            END

                                            END
                                            END,
                tblTempRateTableRate.IntervalN = CASE WHEN tblTempRateTableRate.IntervalN is not null  and tblTempRateTableRate.IntervalN > 0
                                            THEN    
                                                tblTempRateTableRate.IntervalN
                                            ELSE
                                                CASE WHEN tblRate.IntervalN is not null 
                                          THEN
                                            tblRate.IntervalN
                                          ElSE
                                            CASE
                                                WHEN tblTempRateTableRate.Description LIKE '%mexico%' THEN 60
                                            ELSE CASE
                                                WHEN tblTempRateTableRate.Description LIKE '%USA%' THEN 6
                                                
                                            ELSE 
                                            1
                                            END
                                            END
                                          END
                                          END;
                                           
          DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate2_;
			 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate2_ as (select * from tmp_TempRateTableRate_);   
 


            IF  p_addNewCodesToCodeDeck = 1
            THEN

                INSERT INTO tblRate (CompanyID,
                Code,
                Description,
                CreatedBy,
                CountryID,
                CodeDeckId,
                Interval1,
                IntervalN)
                    SELECT DISTINCT
                        p_companyId,
                        vc.Code,
                        vc.Description,
                        'WindowsService',
                        c.CountryID,
                        CodeDeckId,
                        Interval1,
                        IntervalN
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempRateTableRate.Code,
                            tblTempRateTableRate.Description,
                            tblTempRateTableRate.CodeDeckId,
                            tblTempRateTableRate.Interval1,
                            tblTempRateTableRate.IntervalN

                        FROM tmp_TempRateTableRate_  as tblTempRateTableRate
                        LEFT JOIN tblRate
					             ON tblRate.Code = tblTempRateTableRate.Code
					             AND tblRate.CompanyID = p_companyId
					             AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                        WHERE tblRate.RateID IS NULL
                        AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) vc
                    LEFT JOIN
                    (
                        SELECT DISTINCT
                            tblTempRateTableRate2.Code,
                            tblCountry.CountryID
                        FROM tblCountry
                        LEFT OUTER JOIN
                        (
                            SELECT
                                Prefix
                            FROM tblCountry
                            GROUP BY Prefix
                            HAVING COUNT(*) > 1) d
                            ON tblCountry.Prefix = d.Prefix
                            INNER JOIN tmp_TempRateTableRate2_ as tblTempRateTableRate2
                                ON (tblTempRateTableRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NULL
                                )
                                OR (tblTempRateTableRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NOT NULL
                                AND (tblTempRateTableRate2.Description LIKE Concat('%'
                                , tblCountry.Country
                                , '%')
                                )
                                )
                        WHERE tblTempRateTableRate2.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c
                        ON vc.Code = c.Code;
                        /* AND c.CountryID IS NOT NULL*/


                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(tblTempRateTableRate.Code , ' INVALID CODE - COUNTRY NOT FOUND ')
                    FROM tmp_TempRateTableRate_  as tblTempRateTableRate 
                    INNER JOIN tblRate
				             ON tblRate.Code = tblTempRateTableRate.Code
				             AND tblRate.CompanyID = p_companyId
				             AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						  WHERE tblRate.CountryID IS NULL
                    AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
            ELSE
                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(c.Code , ' CODE DOES NOT EXIST IN CODE DECK')
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempRateTableRate.Code,
                            tblTempRateTableRate.Description
                        FROM tmp_TempRateTableRate_  as tblTempRateTableRate 
                        LEFT JOIN tblRate
			                ON tblRate.Code = tblTempRateTableRate.Code
			                AND tblRate.CompanyID = p_companyId
			                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
								WHERE tblRate.RateID IS NULL
                        AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c;


            END IF;

            IF  p_replaceAllRates = 1
            THEN


                DELETE FROM tblRateTableRate
                WHERE RateTableId = p_ratetableid;

            END IF;

            DELETE tblRateTableRate
                FROM tblRateTableRate
                JOIN tblRate
                    ON tblRate.RateID = tblRateTableRate.RateId
                    AND tblRate.CompanyID = p_companyId
                    JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                        ON tblRate.Code = tblTempRateTableRate.Code
            WHERE tblRateTableRate.RateTableId = p_ratetableid
                AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            IF  p_effectiveImmediately = 1
            THEN
                UPDATE tmp_TempRateTableRate_
                SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
                WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
            END IF;

            UPDATE tblRateTableRate 
            LEFT JOIN tblRate 
					ON tblRateTableRate.RateId = tblRate.RateId
	            AND tblRateTableRate.RateTableId = p_ratetableid 
            LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                ON tblRate.Code = tblTempRateTableRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                AND tblRateTableRate.RateId = tblRate.RateId
				SET tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
                tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
                tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN;

            DELETE tblTempRateTableRate
                FROM tmp_TempRateTableRate_ as tblTempRateTableRate
                JOIN tblRate
                    ON tblRate.Code = tblTempRateTableRate.Code
                    JOIN tblRateTableRate
                        ON tblRateTableRate.RateId = tblRate.RateId
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                        AND tblRateTableRate.RateTableId = p_ratetableid
                        AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
                        AND (
                        tblRateTableRate.EffectiveDate = tblTempRateTableRate.EffectiveDate 
                        OR  
                        (
                        DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempRateTableRate.EffectiveDate > NOW() THEN 1 
                            ELSE 0
                        END)
                        )
            WHERE  tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
            JOIN tblRate
                ON tblRate.Code = tblTempRateTableRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                JOIN tblRateTableRate
                    ON tblRateTableRate.RateId = tblRate.RateId
                    AND tblRateTableRate.RateTableId = p_ratetableid
				SET tblRateTableRate.Rate = tblTempRateTableRate.Rate
            WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
            AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
            AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            INSERT INTO tblRateTableRate (RateTableId,
            RateId,
            Rate,
            EffectiveDate,
            ConnectionFee,
            Interval1,
            IntervalN
            )
                SELECT DISTINCT
                    p_ratetableid,
                    tblRate.RateID,
                    tblTempRateTableRate.Rate,
                    tblTempRateTableRate.EffectiveDate,
                    tblTempRateTableRate.ConnectionFee,
                    tblTempRateTableRate.Interval1,
                    tblTempRateTableRate.IntervalN
                FROM tmp_TempRateTableRate_ as tblTempRateTableRate
                JOIN tblRate
                    ON tblRate.Code = tblTempRateTableRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                    LEFT JOIN tblRateTableRate
                        ON tblRate.RateID = tblRateTableRate.RateId
                        AND tblRateTableRate.RateTableId = p_ratetableid
                WHERE (tblRateTableRate.RateTableRateID IS NULL
                OR (
                tblRateTableRate.RateTableRateID IS NOT NULL
                AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d')
                AND tblTempRateTableRate.Rate <> tblRateTableRate.Rate
                AND tblTempRateTableRate.EffectiveDate <> tblRateTableRate.EffectiveDate
                AND tblRateTableRate.EffectiveDate <> tblTempRateTableRate.EffectiveDate
                )
                )
                AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


      
	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );
	 
 	 SELECT * from tmp_JobLog_;
	 DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSProcessVendorRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessVendorRate`(IN `p_accountId` INT
, IN `p_trunkId` INT
, IN `p_replaceAllRates` INT
, IN `p_effectiveImmediately` INT
, IN `p_processId` VARCHAR(200)
, IN `p_addNewCodesToCodeDeck` INT
, IN `p_companyId` INT)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	 DECLARE     v_CodeDeckId_ INT ;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;    
	 
    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message VARCHAR(200)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
    CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
			`CodeDeckId` int ,
			`Code` varchar(50) ,
			`Description` varchar(200) ,
			`Rate` decimal(18, 6) ,
			`EffectiveDate` Datetime ,
			`Change` varchar(100) ,
			`ProcessId` varchar(200) ,
			`Preference` int ,
			`ConnectionFee` decimal(18, 6),
			`Interval1` int,
			`IntervalN` int,
			INDEX tmp_EffectiveDate (`EffectiveDate`),
			INDEX tmp_Code (`Code`),
			INDEX tmp_Change (`Change`)
    );
    
     DELETE n1 FROM tblTempVendorRate n1, tblTempVendorRate n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;
 	

		  INSERT INTO tmp_TempVendorRate_
        SELECT distinct `CodeDeckId`,`Code`,`Description`,`Rate`,`EffectiveDate`,`Change`,`ProcessId`,`Preference`,`ConnectionFee`,`Interval1`,`IntervalN` FROM tblTempVendorRate WHERE tblTempVendorRate.ProcessId = p_processId;
			
		 
      
		
	 	     SELECT CodeDeckId INTO v_CodeDeckId_ FROM tmp_TempVendorRate_ WHERE ProcessId = p_processId  LIMIT 1;

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            LEFT JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                AND tblRate.CodeDeckId =  v_CodeDeckId_
            SET  
                tblTempVendorRate.Interval1 = CASE WHEN tblTempVendorRate.Interval1 is not null  and tblTempVendorRate.Interval1 > 0
                                            THEN    
                                                tblTempVendorRate.Interval1
                                            ELSE
                                            CASE WHEN tblRate.Interval1 is not null  
                                            THEN    
                                                tblRate.Interval1
                                            ELSE CASE WHEN tblTempVendorRate.Interval1 is null and (tblTempVendorRate.Description LIKE '%gambia%' OR tblTempVendorRate.Description LIKE '%mexico%')
                                                 THEN 
                                                    60
                                            ELSE CASE WHEN tblTempVendorRate.Description LIKE '%USA%' 
                                                 THEN 
                                                    6
                                                 ELSE 
                                                    1
                                                END
                                            END

                                            END
                                            END,
                tblTempVendorRate.IntervalN = CASE WHEN tblTempVendorRate.IntervalN is not null  and tblTempVendorRate.IntervalN > 0
                                            THEN    
                                                tblTempVendorRate.IntervalN
                                            ELSE
                                                CASE WHEN tblRate.IntervalN is not null 
                                          THEN
                                            tblRate.IntervalN
                                          ElSE
                                            CASE
                                                WHEN tblTempVendorRate.Description LIKE '%mexico%' THEN 60
                                            ELSE CASE
                                                WHEN tblTempVendorRate.Description LIKE '%USA%' THEN 6
                                                
                                            ELSE 
                                            1
                                            END
                                            END
                                          END
                                          END;
                                           
          DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate2_;
			 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorRate2_ as (select * from tmp_TempVendorRate_);   
 


            IF  p_addNewCodesToCodeDeck = 1
            THEN

                INSERT INTO tblRate (CompanyID,
                Code,
                Description,
                CreatedBy,
                CountryID,
                CodeDeckId,
                Interval1,
                IntervalN)
                    SELECT DISTINCT
                        p_companyId,
                        vc.Code,
                        vc.Description,
                        'WindowsService',
                        c.CountryID,
                        CodeDeckId,
                        Interval1,
                        IntervalN
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description,
                            tblTempVendorRate.CodeDeckId,
                            tblTempVendorRate.Interval1,
                            tblTempVendorRate.IntervalN

                        FROM tmp_TempVendorRate_  as tblTempVendorRate
                        LEFT JOIN tblRate
					             ON tblRate.Code = tblTempVendorRate.Code
					             AND tblRate.CompanyID = p_companyId
					             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        WHERE tblRate.RateID IS NULL
                        AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) vc
                    LEFT JOIN
                    (
                        SELECT DISTINCT
                            tblTempVendorRate2.Code,
                            tblCountry.CountryID
                        FROM tblCountry
                        LEFT OUTER JOIN
                        (
                            SELECT
                                Prefix
                            FROM tblCountry
                            GROUP BY Prefix
                            HAVING COUNT(*) > 1) d
                            ON tblCountry.Prefix = d.Prefix
                            INNER JOIN tmp_TempVendorRate2_ as tblTempVendorRate2
                                ON (tblTempVendorRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NULL
                                )
                                OR (tblTempVendorRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NOT NULL
                                AND (tblTempVendorRate2.Description LIKE Concat('%'
                                , tblCountry.Country
                                , '%')
                                )
                                )
                        WHERE tblTempVendorRate2.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c
                        ON vc.Code = c.Code;
                        /* AND c.CountryID IS NOT NULL*/
								


                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(tblTempVendorRate.Code , ' INVALID CODE - COUNTRY NOT FOUND ')
                    FROM tmp_TempVendorRate_  as tblTempVendorRate 
                    INNER JOIN tblRate
				             ON tblRate.Code = tblTempVendorRate.Code
				             AND tblRate.CompanyID = p_companyId
				             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						  WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
            ELSE
                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(c.Code , ' CODE DOES NOT EXIST IN CODE DECK')
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description
                        FROM tmp_TempVendorRate_  as tblTempVendorRate 
                        LEFT JOIN tblRate
			                ON tblRate.Code = tblTempVendorRate.Code
			                AND tblRate.CompanyID = p_companyId
			                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
								WHERE tblRate.RateID IS NULL
                        AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c;


            END IF;

            IF  p_replaceAllRates = 1
            THEN


                DELETE FROM tblVendorRate
                WHERE AccountId = p_accountId
                    AND TrunkID = p_trunkId;

            END IF;

            DELETE tblVendorRate
                FROM tblVendorRate
                JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId
                    AND tblRate.CompanyID = p_companyId
                    JOIN tmp_TempVendorRate_ as tblTempVendorRate
                        ON tblRate.Code = tblTempVendorRate.Code
            WHERE tblVendorRate.AccountId = p_accountId
                AND tblVendorRate.TrunkId = p_trunkId
                AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            IF  p_effectiveImmediately = 1
            THEN


            
            	
            
            	/*DELETE n1 FROM tmp_TempVendorRate_ n1, tmp_TempVendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate AND n1.Code = n2.Code;*/
            

                
           

                UPDATE tmp_TempVendorRate_
                SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
                WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');



            END IF;

            UPDATE tblVendorRate 
            LEFT JOIN tblRate 
					ON tblVendorRate.RateId = tblRate.RateId
	            AND tblVendorRate.AccountId = p_accountId 
					AND tblVendorRate.TrunkId = p_trunkId
            LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                AND tblVendorRate.RateId = tblRate.RateId
				SET tblVendorRate.ConnectionFee = tblTempVendorRate.ConnectionFee,
                tblVendorRate.Interval1 = tblTempVendorRate.Interval1,
                tblVendorRate.IntervalN = tblTempVendorRate.IntervalN;

            DELETE tblTempVendorRate
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    JOIN tblVendorRate
                        ON tblVendorRate.RateId = tblRate.RateId
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        AND tblVendorRate.AccountId = p_accountId
                        AND tblVendorRate.TrunkId = p_trunkId
                        AND tblTempVendorRate.Rate = tblVendorRate.Rate
                        AND (
                        tblVendorRate.EffectiveDate = tblTempVendorRate.EffectiveDate 
                        OR  
                        (
                        DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempVendorRate.EffectiveDate > NOW() THEN 1 
                            ELSE 0
                        END)
                        )
            WHERE  tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                JOIN tblVendorRate
                    ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				SET tblVendorRate.Rate = tblTempVendorRate.Rate
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
            AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
            AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');


            


            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            INSERT INTO tblVendorRate (AccountId,
            TrunkID,
            RateId,
            Rate,
            EffectiveDate,
            ConnectionFee,
            Interval1,
            IntervalN
            )
                SELECT DISTINCT
                    p_accountId,
                    p_trunkId,
                    tblRate.RateID,
                    tblTempVendorRate.Rate,
                    tblTempVendorRate.EffectiveDate,
                    tblTempVendorRate.ConnectionFee,
                    tblTempVendorRate.Interval1,
                    tblTempVendorRate.IntervalN
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                    LEFT JOIN tblVendorRate
                        ON tblRate.RateID = tblVendorRate.RateId
                        AND tblVendorRate.AccountId = p_accountId
                        AND tblVendorRate.trunkid = p_trunkId
                WHERE (tblVendorRate.VendorRateID IS NULL
                OR (
                tblVendorRate.VendorRateID IS NOT NULL
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d')
                AND tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.EffectiveDate <> tblVendorRate.EffectiveDate
                AND tblVendorRate.EffectiveDate <> tblTempVendorRate.EffectiveDate
                )
                )
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


      
	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );
	 
 	 SELECT * from tmp_JobLog_;
	 DELETE  FROM tmp_TempVendorRate_ WHERE  ProcessId = p_processId;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.prc_WSUpdateAllInProgressJobStatusToPending
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSUpdateAllInProgressJobStatusToPending`(IN `p_companyid` INT, IN `p_ModifiedBy` LONGTEXT )
BEGIN
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
  UPDATE tblJob 
  SET JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'P' )
  , updated_at = NOW()
  , ModifiedBy = p_ModifiedBy
  where 
  CompanyID =  p_companyid
 AND JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'I' )    ;
 
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END//
DELIMITER ;


-- Dumping structure for function RateManagement4.split_token
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `split_token`(txt TEXT CHARSET utf8, delimiter_text VARCHAR(255) CHARSET utf8, token_index INT UNSIGNED) RETURNS text CHARSET utf8
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Return substring by index in delimited text'
begin
  if CHAR_LENGTH(delimiter_text) = '' then
    return SUBSTRING(txt, token_index, 1);
  else
    return SUBSTRING_INDEX(SUBSTRING_INDEX(txt, delimiter_text, token_index), delimiter_text, -1);
  end if;
end//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.sp_split
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_split`(IN `toSplit` text, IN `target` char(255))
BEGIN
	
	SET @tableName = 'tmpSplit';
	SET @fieldName = 'variable';

	
	SET @sql := CONCAT('DROP TABLE IF EXISTS ', @tableName);
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	
	SET @sql := CONCAT('CREATE TEMPORARY TABLE ', @tableName, ' (', @fieldName, ' VARCHAR(1000))');
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	
	SET @vars := toSplit;
	SET @vars := CONCAT("('", REPLACE(@vars, ",", "'),('"), "')");

	
	SET @sql := CONCAT('INSERT INTO ', @tableName, ' VALUES ', @vars);
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	
	IF target IS NULL THEN
		SET @sql := CONCAT('SELECT TRIM(`', @fieldName, '`) AS `', @fieldName, '` FROM ', @tableName);
	ELSE
		SET @sql := CONCAT('INSERT INTO ', target, ' SELECT TRIM(`', @fieldName, '`) FROM ', @tableName);
	END IF;

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
	
		
END//
DELIMITER ;


-- Dumping structure for function RateManagement4.trim_wspace
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `trim_wspace`(txt TEXT CHARSET utf8) RETURNS text CHARSET utf8
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Trim whitespace characters on both sides'
begin
  declare len INT UNSIGNED DEFAULT 0;
  declare done TINYINT UNSIGNED DEFAULT 0;
  if txt IS NULL then
    return txt;
  end if;
  while not done do
    set len := CHAR_LENGTH(txt);
    set txt = trim(' ' FROM txt);
    set txt = trim('\r' FROM txt);
    set txt = trim('\n' FROM txt);
    set txt = trim('\t' FROM txt);
    set txt = trim('\b' FROM txt);
    if CHAR_LENGTH(txt) = len then
      set done := 1;
    end if;
  end while;
  return txt;
end//
DELIMITER ;


-- Dumping structure for function RateManagement4.unquote
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `unquote`(txt TEXT CHARSET utf8) RETURNS text CHARSET utf8
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Unquotes a given text'
begin
  declare quoting_char VARCHAR(1) CHARSET utf8;
  declare terminating_quote_escape_char VARCHAR(1) CHARSET utf8;
  declare current_pos INT UNSIGNED;
  declare end_quote_pos INT UNSIGNED;

  if CHAR_LENGTH(txt) < 2 then
    return txt;
  end if;
  
  set quoting_char := LEFT(txt, 1);
  if not quoting_char in ('''', '"', '`', '/') then
    return txt;
  end if;
  if txt in ('''''', '""', '``', '//') then
    return '';
  end if;
  
  set current_pos := 1;
  terminating_quote_loop: while current_pos > 0 do
    set current_pos := LOCATE(quoting_char, txt, current_pos + 1);
    if current_pos = 0 then
      -- No terminating quote
      return txt;
    end if;
    if SUBSTRING(txt, current_pos, 2) = REPEAT(quoting_char, 2) then
      set current_pos := current_pos + 1;
      iterate terminating_quote_loop;
    end if;
    set terminating_quote_escape_char := SUBSTRING(txt, current_pos - 1, 1);
    if (terminating_quote_escape_char = quoting_char) or (terminating_quote_escape_char = '\\') then
      -- This isn't really a quote end: the quote is escaped. 
      -- We do nothing; just a trivial assignment.
      iterate terminating_quote_loop;
    end if;
    -- Found terminating quote.
    leave terminating_quote_loop;
  end while;
  if current_pos = CHAR_LENGTH(txt) then
      return SUBSTRING(txt, 2, CHAR_LENGTH(txt) - 2);
    end if;
  return txt;
end//
DELIMITER ;


-- Dumping structure for function RateManagement4.unwrap
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `unwrap`(txt TEXT CHARSET utf8) RETURNS text CHARSET utf8
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Unwraps a given text from braces'
begin
  if CHAR_LENGTH(txt) < 2 then
    return txt;
  end if;
  if LEFT(txt, 1) = '{' AND RIGHT(txt, 1) = '}' then
    return SUBSTRING(txt, 2, CHAR_LENGTH(txt) - 2);
  end if;
  if LEFT(txt, 1) = '[' AND RIGHT(txt, 1) = ']' then
    return SUBSTRING(txt, 2, CHAR_LENGTH(txt) - 2);
  end if;
  if LEFT(txt, 1) = '(' AND RIGHT(txt, 1) = ')' then
    return SUBSTRING(txt, 2, CHAR_LENGTH(txt) - 2);
  end if;
  return txt;
end//
DELIMITER ;


-- Dumping structure for table RateManagement4.vendor_merge
CREATE TABLE IF NOT EXISTS `vendor_merge` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `customer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendor` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `is_vendor_customer` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.vendor_merge_to_customer
CREATE TABLE IF NOT EXISTS `vendor_merge_to_customer` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Vendor` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for procedure RateManagement4.vwVendorCurrentRates
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `vwVendorCurrentRates`(IN `p_AccountID` INT, IN `p_Trunks` LONGTEXT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			Code varchar(50),
			Description varchar(200),
			Rate float,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Interval1 INT,
			IntervalN varchar(100),
			ConnectionFee float
	);
	INSERT INTO tmp_VendorCurrentRates_ 
	SELECT DISTINCT
    v_1.AccountId,
    r.Code,
    r.Description,
    v_1.Rate,
    DATE_FORMAT (v_1.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
    v_1.TrunkID,
    r.CountryID,
    r.RateID,
   case when v_1.Interval1 is not null
          then v_1.Interval1
          else r.Interval1
          end as  Interval1,
    case when v_1.IntervalN is not null
          then v_1.IntervalN
          else r.IntervalN
          end IntervalN,
    v_1.ConnectionFee
FROM
	(
    SELECT DISTINCT
        v.AccountId,
        v.TrunkID,
        v.RateId,
        e.EffectiveDate,
        v.Rate,
        v.Interval1,
        v.IntervalN,
        v.ConnectionFee
    
    FROM tblVendorRate AS v
    INNER JOIN
    (
        SELECT
            AccountId,
            TrunkID,
            RateId,
            MAX(EffectiveDate) AS EffectiveDate
        FROM tblVendorRate
        WHERE (EffectiveDate <= NOW())
        AND tblVendorRate.AccountId = p_AccountID
        AND FIND_IN_SET(tblVendorRate.TrunkID,p_Trunks)
        GROUP BY AccountId,
                 TrunkID,
                 RateId
    ) AS e
        ON v.AccountId = e.AccountId
        AND v.TrunkID = e.TrunkID
        AND v.RateId = e.RateId
        AND v.EffectiveDate = e.EffectiveDate
	) AS v_1
INNER JOIN tblRate AS r
    ON r.RateID = v_1.RateId;
    
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;    

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.vwVendorSippySheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `vwVendorSippySheet`(IN `p_AccountID` INT, IN `p_Trunks` LONGTEXT)
BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

DROP TEMPORARY TABLE IF EXISTS tmp_VendorSippySheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(10),
			`Expiration Date` varchar(10),
			AccountID int,
			TrunkID int
	);
	

call vwVendorCurrentRates(p_AccountID,p_Trunks); 

INSERT INTO tmp_VendorSippySheet_ 
SELECT
    NULL AS RateID,
    'A' AS `Action [A|D|U|S|SA`,
    '' AS id,
    Concat('' , tblTrunk.Prefix ,vendorRate.Code) AS Prefix,
    vendorRate.Description AS COUNTRY,
    IFNULL(tblVendorPreference.Preference,5) as Preference,
    vendorRate.Interval1 as `Interval 1`,
    vendorRate.IntervalN as `Interval N`,
    vendorRate.Rate AS `Price 1`,
    vendorRate.Rate AS `Price N`,
    10 AS `1xx Timeout`,
    60 AS `2xx Timeout`,
    0 AS Huntstop,
    CASE
        WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
        	FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
			OR
            (blockCountry.VendorBlockingId IS NOT NULL AND
            FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
            )
            ) THEN 1
        ELSE 0
    END  AS Forbidden,
    'NOW' AS `Activation Date`,
    '' AS `Expiration Date`,
    tblAccount.AccountID,
    tblTrunk.TrunkID
FROM tmp_VendorCurrentRates_ AS vendorRate
INNER JOIN tblAccount
    ON vendorRate.AccountId = tblAccount.AccountID
LEFT OUTER JOIN tblVendorBlocking
    ON vendorRate.RateID = tblVendorBlocking.RateId
    AND tblAccount.AccountID = tblVendorBlocking.AccountId
    AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
LEFT OUTER JOIN tblVendorBlocking AS blockCountry
    ON vendorRate.CountryID = blockCountry.CountryId
    AND tblAccount.AccountID = blockCountry.AccountId
    AND vendorRate.TrunkID = blockCountry.TrunkID
LEFT JOIN tblVendorPreference 
  ON tblVendorPreference.AccountId = vendorRate.AccountId
  AND tblVendorPreference.TrunkID = vendorRate.TrunkID
  AND tblVendorPreference.RateId = vendorRate.RateID
INNER JOIN tblTrunk
    ON tblTrunk.TrunkID = vendorRate.TrunkID
WHERE (vendorRate.Rate > 0);

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RateManagement4.vwVendorVersion3VosSheet
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `vwVendorVersion3VosSheet`(IN `p_AccountID` INT, IN `p_Trunks` LONGTEXT)
BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorVersion3VosSheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorVersion3VosSheet_(
			RateID int,
			`Rate Prefix` varchar(50),
			`Area Prefix` varchar(50),
			`Rate Type` varchar(50),
			`Area Name` varchar(200),
			`Billing Rate` float,
			`Billing Cycle` int,
			`Minute Cost` float,
			`Lock Type` varchar(50),
			`Section Rate` varchar(50),
			`Billing Rate for Calling Card Prompt` float,
			`Billing Cycle for Calling Card Prompt` INT,
			AccountID int,
			TrunkID int
	);
	 Call vwVendorCurrentRates(p_AccountID,p_Trunks);	
	 
	 
INSERT INTO tmp_VendorVersion3VosSheet_	 
SELECT


    NULL AS RateID,
    IFNULL(tblTrunk.RatePrefix, '') AS `Rate Prefix`,
    Concat('' , IFNULL(tblTrunk.AreaPrefix, '') , vendorRate.Code) AS `Area Prefix`,
    'International' AS `Rate Type`,
    vendorRate.Description AS `Area Name`,
    vendorRate.Rate / 60 AS `Billing Rate`,
    CASE
        WHEN vendorRate.Description LIKE '%mexico%' THEN 
		  	60
        ELSE 
		  1
    END AS `Billing Cycle`,
    CAST(vendorRate.Rate AS DECIMAL(18, 5)) AS `Minute Cost`,
    CASE
        WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
        FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
             OR
            (blockCountry.VendorBlockingId IS NOT NULL AND
             FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
            )) THEN 'No Lock'
        ELSE 'No Lock'
    END
    AS `Lock Type`,
        CASE WHEN vendorRate.Interval1 != vendorRate.IntervalN 
                                      THEN 
                    Concat('0,', vendorRate.Rate, ',',vendorRate.Interval1)
                                      ELSE ''
                                 END as `Section Rate`,
    0 AS `Billing Rate for Calling Card Prompt`,
    0 AS `Billing Cycle for Calling Card Prompt`,
    tblAccount.AccountID,
    vendorRate.TrunkId
FROM tmp_VendorCurrentRates_ AS vendorRate
INNER JOIN tblAccount 
    ON vendorRate.AccountId = tblAccount.AccountID
LEFT OUTER JOIN tblVendorBlocking
    ON vendorRate.TrunkId = tblVendorBlocking.TrunkID
    AND vendorRate.RateID = tblVendorBlocking.RateId
    AND tblAccount.AccountID = tblVendorBlocking.AccountId
LEFT OUTER JOIN tblVendorBlocking AS blockCountry
    ON vendorRate.TrunkId = blockCountry.TrunkID
    AND vendorRate.CountryID = blockCountry.CountryId
    AND tblAccount.AccountID = blockCountry.AccountId
INNER JOIN tblTrunk
    ON tblTrunk.TrunkID = vendorRate.TrunkId
WHERE (vendorRate.Rate > 0);

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
