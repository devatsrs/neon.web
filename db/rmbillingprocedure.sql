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

-- Dumping database structure for RMBilling4
CREATE DATABASE IF NOT EXISTS `RMBilling4` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `RMBilling4`;


-- Dumping structure for procedure RMBilling4.fnUsageDetail
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `fnUsageDetail`(IN `p_CompanyID` int , IN `p_AccountID` int , IN `p_GatewayID` int , IN `p_StartDate` datetime , IN `p_EndDate` datetime , IN `p_UserID` INT , IN `p_isAdmin` INT, IN `p_billing_time` INT   

, IN `p_cdr_type` CHAR(1))
BEGIN
		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetails_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetails_(
			AccountID int,
			AccountName varchar(50),
			trunk varchar(50),
			area_prefix varchar(50),
			pincode VARCHAR(50),
			extension VARCHAR(50),
			UsageDetailID int,
			duration int,
			billed_duration int,
			cli varchar(100),
			cld varchar(100),
			cost decimal(18,6),
			connect_time datetime,
			disconnect_time datetime,
			is_inbound tinyint(1) default 0
	);
	INSERT INTO tmp_tblUsageDetails_
	SELECT
    *
	FROM (SELECT
		uh.AccountID,
		a.AccountName,
		trunk,
		area_prefix,
		pincode,
		extension,
		UsageDetailID,
		duration,
		billed_duration,
		cli,
		cld,
		cost,
		connect_time,
		disconnect_time,
		ud.is_inbound
	FROM RMCDR4.tblUsageDetails  ud
	INNER JOIN RMCDR4.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	INNER JOIN RateManagement4.tblAccount a
		ON uh.AccountID = a.AccountID
	WHERE
	(p_cdr_type = '' OR  ud.is_inbound = p_cdr_type)
	AND  StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
	AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
	AND uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID)) 
	) tbl
	WHERE 
	(p_billing_time =1 and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.fnUsageDetailbyProcessID
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `fnUsageDetailbyProcessID`(IN `p_ProcessID` VARCHAR(200)

)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailsProcess_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetailsProcess_(
			CompanyID int,
			CompanyGatewayID int,
			AccountID int,
			trunk varchar(50),			
			area_prefix varchar(50),			
			pincode varchar(50),
			extension VARCHAR(50),
			UsageDetailID int,
			duration int,
			billed_duration int,
			cli varchar(100),
			cld varchar(100),
			cost float,
			connect_time datetime,
			disconnect_time datetime
	);
	INSERT INTO tmp_tblUsageDetailsProcess_
	 SELECT
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.AccountID,
		trunk,
		area_prefix,
		pincode,
		extension,
		UsageDetailID,
		duration,
		billed_duration,
		cli,
		cld,
		cost,
		connect_time,
		disconnect_time
	FROM RMCDR4.tblUsageDetails ud
	INNER JOIN RMCDR4.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.AccountID is not null
	AND  ud.ProcessID = p_ProcessID;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.fnUsageDetailbyUsageHeaderID
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `fnUsageDetailbyUsageHeaderID`( 
	p_UsageHeaderID INT

)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailswithHeader_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetailswithHeader_(
			CompanyID int,
			CompanyGatewayID int,
			AccountID int,
			trunk varchar(50),			
			area_prefix varchar(50),			
			UsageDetailID int,
			duration int,
			billed_duration int,
			cli varchar(100),
			cld varchar(100),
			cost float,
			connect_time datetime,
			disconnect_time datetime
	);
	INSERT INTO tmp_tblUsageDetailswithHeader_
	 SELECT
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.AccountID,
		trunk,
		area_prefix,
		UsageDetailID,
		duration,
		billed_duration,
		cli,
		cld,
		cost,
		connect_time,
		disconnect_time
	FROM RMCDR4.tblUsageDetails ud
	INNER JOIN RMCDR4.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.AccountID is not null
	AND  uh.UsageHeaderID = p_UsageHeaderID;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.fnVendorUsageDetail
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `fnVendorUsageDetail`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_billing_time` INT)
BEGIN
	
	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorUsageDetails_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblVendorUsageDetails_(
			AccountID INT,
			AccountName VARCHAR(50),
			trunk VARCHAR(50),
			area_prefix VARCHAR(50),
			VendorCDRID INT,
			billed_duration INT,
			cli VARCHAR(100),
			cld VARCHAR(100),
			selling_cost DOUBLE,
			buying_cost DOUBLE,
			connect_time DATETIME,
			disconnect_time DATETIME
	);
	INSERT INTO tmp_tblVendorUsageDetails_
	SELECT
    *
	FROM (SELECT
		uh.AccountID,
		a.AccountName,
		trunk,
		area_prefix,
		VendorCDRID,
		billed_duration,
		cli,
		cld,
		selling_cost,
		buying_cost,
		connect_time,
		disconnect_time
	FROM RMCDR4.tblVendorCDR  ud
	INNER JOIN RMCDR4.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
	LEFT JOIN RateManagement4.tblAccount a
		ON uh.AccountID = a.AccountID
	WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
	AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
	AND uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID)) 
	) tbl
	WHERE 
	
	(p_billing_time =1 and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_checkCDRIsLoadedOrNot
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkCDRIsLoadedOrNot`(IN `p_AccountID` INT, IN `p_CompanyID` INT, IN `p_UsageEndDate` DATETIME )
BEGIN

    DECLARE v_end_time_ DATE;
    DECLARE v_notInGateeway_ INT;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SELECT COUNT(*) INTO v_notInGateeway_ FROM tblGatewayAccount WHERE AccountID = p_AccountID and CompanyID  = p_CompanyID;    


    IF v_notInGateeway_ > 0 
    THEN

        SELECT DATE_FORMAT(MIN(end_time), '%y-%m-%d') INTO v_end_time_  
        FROM  (
            SELECT  MAX(tmpusglog.end_time) AS end_time ,ga.CompanyGatewayID  
            FROM  tblGatewayAccount ga  
            INNER  JOIN  tblTempUsageDownloadLog tmpusglog on tmpusglog.CompanyGatewayID = ga.CompanyGatewayID
            INNER  JOIN  RateManagement4.tblCompanyGateway cg ON cg.CompanyGatewayID  = ga.CompanyGatewayID AND cg.Status = 1
            WHERE  ga.AccountID = p_AccountID and ga.CompanyID  = p_CompanyID
            GROUP BY ga.CompanyGatewayID
            )TBL;        

        IF p_UsageEndDate < v_end_time_ 
        THEN
            SELECT '1' AS isLoaded;
        ELSE
            SELECT '0' AS isLoaded;
        END IF;
    
    ELSE
        SELECT '1' AS isLoaded;
    END IF;

 	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_checkIfFileAlreadyDownloaded
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkIfFileAlreadyDownloaded`(IN `p_CompanyGatewayID` INT, IN `p_filenames` LONGTEXT)
BEGIN

 select  filename from tblUsageDownloadFiles where CompanyGatewayID = p_CompanyGatewayID and FIND_IN_SET( filename ,p_filenames) > 0 ; 

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_DeleteCDR
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_DeleteCDR`(IN `p_CompanyID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_AccountID` INT)
BEGIN

    DECLARE v_BillingTime_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

		SELECT BillingTime INTO v_BillingTime_
		FROM RateManagement4.tblCompanyGateway cg
		INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
		WHERE AccountID = p_AccountID AND (p_GatewayID = 0 OR ga.CompanyGatewayID = p_GatewayID)
		LIMIT 1;
			
		SET v_BillingTime_ = IFNULL(v_BillingTime_,1);
        
        
        CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetail_ AS 
        (

	        SELECT
	        UsageDetailID
	        
	        FROM (SELECT
	            uh.AccountID,
	            a.AccountName,
	            trunk,
	            area_prefix,
	            UsageDetailID,
	            duration,
	            billed_duration,
	            cli,
	            cld,
	            cost,
	            connect_time,
	            disconnect_time
	
			FROM `RMCDR4`.tblUsageDetails  ud 
			INNER JOIN `RMCDR4`.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
	        LEFT JOIN RateManagement4.tblAccount a
	            ON uh.AccountID = a.AccountID
	        WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
			  AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
	        AND uh.CompanyID = p_CompanyID
	        AND (p_AccountID = '' OR uh.AccountID = p_AccountID)
	        AND (p_GatewayID = '' OR CompanyGatewayID = p_GatewayID)
	        ) tbl
	        WHERE 
	    
	        (v_BillingTime_ =1 and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	        OR 
	        (v_BillingTime_ =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	        AND billed_duration > 0
        );


		
		 delete ud.*
        From `RMCDR4`.tblUsageDetails ud
        inner join tmp_tblUsageDetail_ uds on ud.UsageDetailID = uds.UsageDetailID;
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_DeleteVCDR
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_DeleteVCDR`(IN `p_CompanyID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_AccountID` INT)
    COMMENT 'Delete Vendor CDR'
BEGIN

    DECLARE v_BillingTime_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

		SELECT BillingTime INTO v_BillingTime_
		FROM RateManagement4.tblCompanyGateway cg
		INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
		WHERE AccountID = p_AccountID AND (p_GatewayID = 0 OR ga.CompanyGatewayID = p_GatewayID)
		LIMIT 1;
			
		SET v_BillingTime_ = IFNULL(v_BillingTime_,1);
        
        
        CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetail_ AS 
        (

	        SELECT
	        VendorCDRID
	        
	        FROM (SELECT
	            ud.VendorCDRID,
	            billed_duration,
	            connect_time,
	            disconnect_time
	
			FROM `RMCDR4`.tblVendorCDR  ud 
			INNER JOIN `RMCDR4`.tblVendorCDRHeader uh
				ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
	        LEFT JOIN RateManagement4.tblAccount a
	            ON uh.AccountID = a.AccountID
	        WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
			  AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
	        AND uh.CompanyID = p_CompanyID
	        AND (p_AccountID = '' OR uh.AccountID = p_AccountID)
	        AND (p_GatewayID = '' OR CompanyGatewayID = p_GatewayID)
	        ) tbl
	        WHERE 
	    
	        (v_BillingTime_ =1 and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	        OR 
	        (v_BillingTime_ =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	        AND billed_duration > 0
        );


		
		 delete ud.*
        From `RMCDR4`.tblVendorCDR ud
        inner join tmp_tblUsageDetail_ uds on ud.VendorCDRID = uds.VendorCDRID;
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getAccountInvoiceTotal
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getAccountInvoiceTotal`(IN `p_AccountID` INT, IN `p_CompanyID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_checkDuplicate` INT, IN `p_InvoiceDetailID` INT)
BEGIN 
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	
	SELECT BillingTime INTO v_BillingTime_
	FROM RateManagement4.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_,''); 
	
	IF p_checkDuplicate = 1 THEN
	
		SELECT COUNT(inv.InvoiceID) INTO v_InvoiceCount_
		FROM tblInvoice inv
		LEFT JOIN tblInvoiceDetail invd ON invd.InvoiceID = inv.InvoiceID
		WHERE inv.CompanyID = p_CompanyID AND (p_InvoiceDetailID = 0 OR (p_InvoiceDetailID > 0 AND invd.InvoiceDetailID != p_InvoiceDetailID)) AND AccountID = p_AccountID AND 
		(
		 (p_StartDate BETWEEN invd.StartDate AND invd.EndDate) OR
		 (p_EndDate BETWEEN invd.StartDate AND invd.EndDate) OR
		 (invd.StartDate BETWEEN p_StartDate AND p_EndDate) 
		) AND inv.InvoiceStatus != 'cancel'; 
		
		IF v_InvoiceCount_ = 0 THEN
		
			SELECT IFNULL(CAST(SUM(cost) AS DECIMAL(18,8)), 0) AS TotalCharges
			FROM tmp_tblUsageDetails_ ud; 
		ELSE
			SELECT 0 AS TotalCharges; 
		END IF; 
	
	ELSE
		SELECT IFNULL(CAST(SUM(cost) AS DECIMAL(18,8)), 0) AS TotalCharges
		FROM tmp_tblUsageDetails_ ud; 
	END IF; 
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getAccountNameByGatway
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getAccountNameByGatway`(IN `p_company_id` INT, IN `p_gatewayid` INT
)
BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    
     SELECT DISTINCT
                    a.AccountID,
                    a.AccountName                    
                FROM RateManagement4.tblAccount a
                INNER JOIN tblGatewayAccount ga ON a.AccountiD = ga.AccountiD AND a.Status = 1
                WHERE GatewayAccountID IS NOT NULL                
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid 
					 order by a.AccountName ASC;
					 
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	             
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getAccountOutstandingAmount
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getAccountOutstandingAmount`(IN `p_company_id` INT, IN `p_AccountID` int 
)
BEGIN

	  Declare v_TotalDue_ decimal(18,6);
    Declare v_TotalPaid_ decimal(18,6);
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    
	  
      

   
    

    -- Sum of Invoice Created - Sum of Payment = Outstanidng
    Select   ifnull(sum(GrandTotal),0) INTO v_TotalDue_
    from tblInvoice
    where AccountID = p_AccountID
    and CompanyID = p_company_id
    AND InvoiceStatus != 'cancel';   -- cancel invoice

    -- print concat('Total Due ', v_TotalDue_)

    Select  ifnull(sum(Amount),0) INTO v_TotalPaid_ 
    from tblPayment
    where AccountID = p_AccountID
    and CompanyID = p_company_id
    and Status = 'Approved'
    and Recall = 0;

    -- print concat('Total Paid ',v_TotalPaid_)

    Select ifnull((v_TotalDue_ - v_TotalPaid_ ),0) as Outstanding;
                
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getAccountPreviousBalance
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getAccountPreviousBalance`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceID` INT)
BEGIN

	

	Declare v_PreviousBalance_ decimal(18,6);
	Declare v_totalpaymentin_ decimal(18,6);
	Declare v_totalInvoiceOut_ decimal(18,6);
	Declare v_Cancel_ VARCHAR(50) Default 'cancel';
	Declare v_InvoiceOut_ int Default 1;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	 
	 
		SELECT  ifnull(SUM(Amount),0) INTO v_totalpaymentin_ 
		FROM tblPayment
        INNER JOIN RateManagement4.tblAccount
            ON tblPayment.AccountID = tblAccount.AccountID
        WHERE tblPayment.AccountID = p_AccountID 
		AND tblPayment.Status = 'Approved' 
		AND tblPayment.Recall = 0
		AND tblPayment.PaymentType = 'Payment In';

		
		-- Commited Invoice in total
		
		SELECT  ifnull(SUM(GrandTotal),0) INTO v_totalInvoiceOut_ 
		FROM tblInvoice inv
		where InvoiceType = v_InvoiceOut_
			and AccountID = p_AccountID 
			and CompanyID = p_CompanyID 
			and InvoiceStatus != v_Cancel_ 
			and InvoiceID !=  p_InvoiceID;
		

		set v_PreviousBalance_ = v_totalInvoiceOut_ - v_totalpaymentin_  ;

	
		select ifnull(v_PreviousBalance_,0) as PreviousBalance;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getActiveGatewayAccount
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getActiveGatewayAccount`(IN `p_company_id` INT, IN `p_gatewayid` INT, IN `p_UserID` INT, IN `p_isAdmin` INT)
BEGIN

    DECLARE v_NameFormat_ VARCHAR(10);
    DECLARE v_RTR_ INT;
    DECLARE v_pointer_ INT ;
    DECLARE v_rowCount_ INT ;
    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    
    
    DROP TEMPORARY TABLE IF EXISTS tmp_ActiveAccount;
    CREATE TEMPORARY TABLE tmp_ActiveAccount (
        GatewayAccountID varchar(100),
        AccountID INT,
        AccountName varchar(100),
        CDRType INT
    );
  
	DROP TEMPORARY TABLE IF EXISTS tmp_AuthenticateRules_;
	CREATE TEMPORARY TABLE tmp_AuthenticateRules_ (
		RowNo INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AuthRule VARCHAR(50)
	);
    	 INSERT INTO tmp_AuthenticateRules_  (AuthRule)
	  	 SELECT  case when Settings like '%"NameFormat":"NAMENUB"%'
			  then 'NAMENUB'
			  else
			  case when Settings like '%"NameFormat":"NUBNAME"%'
			  then 'NUBNAME'
			  else 
			  case when Settings like '%"NameFormat":"NUB"%'
			  then 'NUB'
			  else 
			  case when Settings like '%"NameFormat":"IP"%'
			  then 'IP'
			  else 
			  case when Settings like '%"NameFormat":"CLI"%'
			  then 'CLI'
			  else 
			  case when Settings like '%"NameFormat":"NAME"%'
			  then 'NAME'
			   
			else 'NAME' end end end end end end   as  NameFormat 
        FROM RateManagement4.tblCompanyGateway
        WHERE Settings LIKE '%NameFormat%' AND
        CompanyGatewayID = p_gatewayid
        limit 1;
       
		 INSERT INTO tmp_AuthenticateRules_  (AuthRule)  
       SELECT DISTINCT CustomerAuthRule FROM RateManagement4.tblAccountAuthenticate aa WHERE CustomerAuthRule IS NOT NULL
		 UNION 
		 SELECT DISTINCT VendorAuthRule FROM RateManagement4.tblAccountAuthenticate aa WHERE VendorAuthRule IS NOT NULL;


		 SET v_pointer_ = 1;
	    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AuthenticateRules_);

         
 
		WHILE v_pointer_ <= v_rowCount_ 
		DO
		
		SET v_NameFormat_ = ( SELECT AuthRule FROM tmp_AuthenticateRules_  WHERE RowNo = v_pointer_ );
		
        IF  v_NameFormat_ = 'NAMENUB'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM RateManagement4.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON concat(a.AccountName , '-' , a.Number) = ga.AccountName
                    AND a.Status = 1 
                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;

        IF v_NameFormat_ = 'NUBNAME'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM RateManagement4.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON concat(a.Number, '-' , a.AccountName) = ga.AccountName
                    AND a.Status = 1 

                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;

        IF v_NameFormat_ = 'NUB'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM RateManagement4.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON a.Number = ga.AccountName
                    AND a.Status = 1 

                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;

         IF v_NameFormat_ = 'IP'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM RateManagement4.tblAccount  a
                INNER JOIN RateManagement4.tblAccountAuthenticate aa ON 
                	a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'IP' OR aa.VendorAuthRule ='IP')
                INNER JOIN tblGatewayAccount ga
                    ON   a.Status = 1 	
                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid
					 AND ( FIND_IN_SET(ga.AccountName,aa.CustomerAuthValue) != 0 OR FIND_IN_SET(ga.AccountName,aa.VendorAuthValue) != 0 );
        END IF;
 
 


      	IF v_NameFormat_ = 'CLI'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM RateManagement4.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON FIND_IN_SET(a.CustomerCLI,ga.AccountName) != 0
                AND a.Status = 1  
                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;


    IF v_NameFormat_ = ''
            OR v_NameFormat_ IS NULL OR v_NameFormat_ = 'NAME'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM RateManagement4.tblAccount  a
                LEFT JOIN RateManagement4.tblAccountAuthenticate aa ON 
              			a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'Other' OR aa.VendorAuthRule ='Other')
                INNER JOIN tblGatewayAccount ga
                    ON    a.Status = 1
                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid
					 AND ((aa.AccountAuthenticateID IS NOT NULL AND (aa.VendorAuthValue = ga.AccountName OR aa.CustomerAuthValue = ga.AccountName  )) OR (aa.AccountAuthenticateID IS NULL AND a.AccountName = ga.AccountName));
        END IF;
        
     SET v_pointer_ = v_pointer_ + 1;
     END WHILE;
   
    SELECT DISTINCT
        GatewayAccountID,AccountID,AccountName,CDRType
    FROM tmp_ActiveAccount;


    UPDATE tblGatewayAccount
    INNER JOIN tmp_ActiveAccount a
        ON a.GatewayAccountID = tblGatewayAccount.GatewayAccountID
        AND tblGatewayAccount.CompanyGatewayID = p_gatewayid
    SET tblGatewayAccount.AccountID = a.AccountID
    where tblGatewayAccount.AccountID is null;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getBillingSubscription
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getBillingSubscription`(IN `p_CompanyID` INT, IN `p_Advance` INT, IN `p_Name` VARCHAR(50), IN `p_CurrencyID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_Export` INT)
BEGIN
	
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	   
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	   
   	IF p_Export = 0
	THEN
		SELECT   
			tblBillingSubscription.Name,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.MonthlyFee) AS MonthlyFeeWithSymbol,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.WeeklyFee) AS WeeklyFeeWithSymbol,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.DailyFee) AS DailyFeeWithSymbol,
			tblBillingSubscription.SubscriptionID,
			tblBillingSubscription.ActivationFee,
			tblBillingSubscription.CurrencyID,
			tblBillingSubscription.InvoiceLineDescription,
			tblBillingSubscription.Description,
			tblBillingSubscription.Advance,
			tblBillingSubscription.MonthlyFee,
			tblBillingSubscription.WeeklyFee,
			tblBillingSubscription.DailyFee
    	FROM tblBillingSubscription
    	LEFT JOIN RateManagement4.tblCurrency on tblBillingSubscription.CurrencyID =tblCurrency.CurrencyId 
    	WHERE tblBillingSubscription.CompanyID = p_CompanyID
	        AND(p_CurrencyID =0  OR tblBillingSubscription.CurrencyID   = p_CurrencyID)
			AND(p_Advance is null OR tblBillingSubscription.Advance = p_Advance)
			AND(p_Name ='' OR tblBillingSubscription.Name like Concat('%',p_Name,'%'))
		ORDER BY
			CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
            END DESC,
            CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
           	END ASC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeDESC') THEN MonthlyFee
           	END DESC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeASC') THEN MonthlyFee
           	END ASC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeDESC') THEN WeeklyFee
           	END DESC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeASC') THEN WeeklyFee
           	END ASC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeDESC') THEN DailyFee
           	END DESC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeASC') THEN DailyFee
           	END ASC
         LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
        	COUNT(tblBillingSubscription.SubscriptionID) AS totalcount
     	FROM tblBillingSubscription
     	WHERE tblBillingSubscription.CompanyID = p_CompanyID
			AND(p_CurrencyID =0  OR tblBillingSubscription.CurrencyID   = p_CurrencyID)
			AND(p_Advance is null OR tblBillingSubscription.Advance = p_Advance)
			AND(p_Name ='' OR tblBillingSubscription.Name like Concat('%',p_Name,'%'));
 	END IF;
      
      
    IF p_Export = 1
	THEN
		
		SELECT   
			tblBillingSubscription.Name,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.MonthlyFee) AS MonthlyFee,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.WeeklyFee) AS WeeklyFee,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.DailyFee) AS DailyFee,
			tblBillingSubscription.ActivationFee,
			tblBillingSubscription.InvoiceLineDescription,
			tblBillingSubscription.Description,
			tblBillingSubscription.Advance
        FROM tblBillingSubscription
        LEFT JOIN RateManagement4.tblCurrency on tblBillingSubscription.CurrencyID =tblCurrency.CurrencyId 
        WHERE tblBillingSubscription.CompanyID = p_CompanyID
	    	AND(p_CurrencyID =0  OR tblBillingSubscription.CurrencyID   = p_CurrencyID)
			AND(p_Advance is null OR tblBillingSubscription.Advance = p_Advance)
			AND(p_Name ='' OR tblBillingSubscription.Name like Concat('%',p_Name,'%'));
				
	END IF;       
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_GetCDR
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetCDR`(IN `p_company_id` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT , IN `p_CDRType` CHAR(1), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

	   DECLARE v_OffSet_ int;
	   DECLARE v_BillingTime_ INT;
	   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
	 
		SELECT BillingTime INTO v_BillingTime_
		FROM RateManagement4.tblCompanyGateway cg
		INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
		WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
		LIMIT 1;
		
		SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    
    
    	Call fnUsageDetail(p_company_id,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType);


	IF p_isExport = 0
	THEN 
		   SELECT
		        AccountName,        
		        connect_time,
		        disconnect_time,        
		        duration,
		        cost,
		        cli,
		        cld,
		        AccountID,
		        p_CompanyGatewayID as CompanyGatewayID,
		        p_start_date as StartDate,
		        p_end_date as EndDate,
				  is_inbound as CDRType  from(
		        SELECT
		        		Distinct
		            uh.AccountName as AccountName,           
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.duration,
		            uh.cli,
		            uh.cld,
						format(uh.cost,6) as cost,
						AccountID,
						is_inbound
		        FROM tmp_tblUsageDetails_ uh
		         
		        
		
		    ) AS TBL  
		    ORDER BY
		    			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeDESC') THEN connect_time
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeASC') THEN connect_time
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeDESC') THEN disconnect_time
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeASC') THEN disconnect_time
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'durationDESC') THEN duration
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'durationASC') THEN duration
		            END ASC,
						 
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'costDESC') THEN cost
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'costASC') THEN cost
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliDESC') THEN cli
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliASC') THEN cli
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldDESC') THEN cld
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldASC') THEN cld
		            END ASC
		
			 LIMIT p_RowspPage OFFSET v_OffSet_;
		
		
		    SELECT
		        COUNT(*) AS totalcount
		    FROM (
		    select Distinct
		            uh.AccountName as AccountName,           
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.duration,
		            uh.cli,
		            uh.cld,
						format(uh.cost,6) as cost,
						AccountID
		    FROM tmp_tblUsageDetails_ uh
		    ) AS TBL2;
		
		END IF;
		
		IF p_isExport = 1
		THEN
		
			SELECT
		        AccountName,        
		        connect_time,
		        disconnect_time,        
		        duration,
		        cost,
		        cli,
		        cld,
		        is_inbound
			from(
		        SELECT
		        		Distinct
		            uh.AccountName as AccountName,           
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.duration,
		            uh.cli,
		            uh.cld,
						format(uh.cost,6) as cost,
						AccountID,
						is_inbound
		        FROM tmp_tblUsageDetails_ uh
		    ) AS TBL;
		
		END IF;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getDashboardinvoiceExpense
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getDashboardinvoiceExpense`(IN `p_CompanyID` INT, IN `p_CurrencyID` INT, IN `p_AccountID` INT)
BEGIN
    DECLARE v_Round_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    
     CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalDue_(
			`Year` int,
			`Month` int,
			MonthName varchar(50),
			TotalAmount float,
			CurrencyID int
			
		);
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalReceived_(
			`Year` int,
			`Month` int,
			MonthName varchar(50),
			TotalAmount float,
			CurrencyID int
			
		);

		SELECT cs.Value INTO v_Round_ from RateManagement4.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	 INSERT INTO tmp_MonthlyTotalDue_
    SELECT YEAR(created_at) as Year, MONTH(created_at) as Month,MONTHNAME(MAX(created_at)) as  MonthName, ROUND(SUM(IFNULL(GrandTotal,0)),v_Round_) as TotalAmount,CurrencyID
    from tblInvoice
    where 
        CompanyID = p_CompanyID
        and CurrencyID = p_CurrencyID
        and created_at >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        and InvoiceType = 1 -- Invoice Out
        and InvoiceStatus != 'cancel'
        and (p_AccountID = 0 or AccountID = p_AccountID)

    GROUP BY YEAR(created_at), MONTH(created_at),CurrencyID
    ORDER BY Year, Month;

	 INSERT INTO tmp_MonthlyTotalReceived_
	 
	 SELECT YEAR(p.PaymentDate) as Year, MONTH(p.PaymentDate) as Month,MONTHNAME(MAX(p.PaymentDate)) as  MonthName, ROUND(SUM(IFNULL(p.Amount,0)),v_Round_) as TotalAmount,ac.CurrencyID
	 FROM tblPayment p 
	 INNER JOIN RateManagement4.tblAccount ac 
	 	ON ac.AccountID = p.AccountID
	 WHERE 
        		p.CompanyID = p_CompanyID
        and ac.CurrencyID = p_CurrencyID
        and p.PaymentDate >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        AND p.Status = 'Approved'
        AND p.Recall=0
        AND p.PaymentType = 'Payment In'
        and (p_AccountID = 0 or ac.AccountID = p_AccountID)
    GROUP BY YEAR(p.PaymentDate), MONTH(p.PaymentDate),ac.CurrencyID
    ORDER BY Year, Month;
	 
    /*SELECT YEAR(inv.created_at) as Year, MONTH(inv.created_at) as Month,MONTHNAME(MAX(inv.created_at)) as  MonthName, ROUND(SUM(IFNULL(p.Amount,0)),v_Round_) as TotalAmount,inv.CurrencyID
    from tblInvoice inv
    inner join RateManagement4.tblAccount ac on ac.AccountID = inv.AccountID
    left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
    left join tblPayment p on REPLACE(p.InvoiceNo,'-','') = CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall=0
    where 
        inv.CompanyID = p_CompanyID
        and inv.CurrencyID = p_CurrencyID
        and inv.created_at >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        and inv.InvoiceType = 1 
        and (p_AccountID = 0 or inv.AccountID = p_AccountID)
        and (
            inv.InvoiceStatus = 'paid' -- Paid Invoice
            OR inv.InvoiceStatus = 'partially_paid' 
            )
     
    GROUP BY YEAR(inv.created_at), MONTH(inv.created_at),inv.CurrencyID
    ORDER BY Year, Month;*/
 

    
    SELECT CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName ,
	 td.Year,ROUND(IFNULL(td.TotalAmount,0),v_Round_) TotalInvoice ,  ROUND(IFNULL(tr.TotalAmount,0),v_Round_) PaymentReceived, ROUND(IFNULL((IFNULL(td.TotalAmount,0) - IFNULL(tr.TotalAmount,0)),0),v_Round_) TotalOutstanding , td.CurrencyID CurrencyID from 
        tmp_MonthlyTotalDue_ td
        left join tmp_MonthlyTotalReceived_ tr on td.Month = tr.Month and td.Year = tr.Year and tr.CurrencyID = td.CurrencyID
   ORDER BY td.Year,td.Month;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getDashBoardPinCodes
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getDashBoardPinCodes`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE, IN `p_AccountID` INT, IN `p_Type` INT, IN `p_Limit` INT, IN `p_PinExt` VARCHAR(50), IN `p_CurrencyID` INT)
    COMMENT 'Top 10 pincodes p_Type = 1 = cost,p_Type =2 =Duration'
BEGIN
	DECLARE v_Round_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SELECT cs.Value INTO v_Round_ from RateManagement4.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,p_StartDate,p_EndDate,0,1,1,'');
	
	IF p_Type =1 AND  p_PinExt = 'pincode' /* Top Pincodes by cost*/
	THEN 
	
		SELECT ROUND(SUM(ud.cost),v_Round_) as PincodeValue,IFNULL(ud.pincode,'n/a') as Pincode  FROM tmp_tblUsageDetails_ ud 
		INNER JOIN RateManagement4.tblAccount a on ud.AccountID = a.AccountID 
		WHERE  ud.cost>0 AND ud.pincode IS NOT NULL AND ud.pincode != ''
			AND a.CurrencyId = p_CurrencyID
		GROUP BY ud.pincode
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	IF p_Type =2 AND  p_PinExt = 'pincode' /* Top Pincodes by Duration*/
	THEN 
	
		SELECT SUM(ud.billed_duration) as PincodeValue,IFNULL(ud.pincode,'n/a') as Pincode FROM tmp_tblUsageDetails_ ud
		INNER JOIN RateManagement4.tblAccount a on ud.AccountID = a.AccountID  
		WHERE  ud.cost>0 AND ud.pincode IS NOT NULL AND ud.pincode != ''
			AND a.CurrencyId = p_CurrencyID
		GROUP BY ud.pincode
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	IF p_Type =1 AND  p_PinExt = 'extension' /* Top Extension by cost*/
	THEN 
	
		SELECT ROUND(SUM(ud.cost),v_Round_) as PincodeValue,IFNULL(ud.extension,'n/a') as Pincode  FROM tmp_tblUsageDetails_ ud 
		INNER JOIN RateManagement4.tblAccount a on ud.AccountID = a.AccountID 
		WHERE  ud.cost>0 AND ud.extension IS NOT NULL AND ud.extension != ''
			AND a.CurrencyId = p_CurrencyID 
		GROUP BY ud.extension
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	IF p_Type =2 AND  p_PinExt = 'extension' /* Top Extension by Duration*/
	THEN 
	
		SELECT SUM(ud.billed_duration) as PincodeValue,IFNULL(ud.extension,'n/a') as Pincode FROM tmp_tblUsageDetails_ ud
		INNER JOIN RateManagement4.tblAccount a on ud.AccountID = a.AccountID 
		WHERE  ud.cost>0 AND ud.extension IS NOT NULL AND ud.extension != ''
			AND a.CurrencyId = p_CurrencyID
		GROUP BY ud.extension
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getInvoice
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getInvoice`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceNumber` VARCHAR(50), IN `p_IssueDateStart` DATETIME, IN `p_IssueDateEnd` DATETIME, IN `p_InvoiceType` INT, IN `p_InvoiceStatus` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT, IN `p_sageExport` INT, IN `p_zerovalueinvoice` INT, IN `p_InvoiceID` LONGTEXT)
BEGIN
    DECLARE v_OffSet_ int;
    DECLARE v_Round_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	        
 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	 SELECT cs.Value INTO v_Round_ from RateManagement4.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;

    IF p_isExport = 0 and p_sageExport = 0
    THEN

        SELECT inv.InvoiceType ,
        ac.AccountName,
        CONCAT(ltrim(rtrim(IFNULL(it.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber))) as InvoiceNumber,
        inv.IssueDate,
        CONCAT(IFNULL(cr.Symbol,''),ROUND(inv.GrandTotal,v_Round_)) as GrandTotal2,
		  CONCAT(IFNULL(cr.Symbol,''),format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))) , ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0),v_Round_),'/',IFNULL(cr.Symbol,''),format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ),v_Round_)) as `PendingAmount`,
        inv.InvoiceStatus,
        inv.InvoiceID,
        inv.Description,
        inv.Attachment,
        inv.AccountID,
        ROUND(inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0 ),v_Round_) as OutstandingAmount, 
        inv.ItemInvoice,
		  IFNULL(ac.BillingEmail,'') as BillingEmail,
		  ROUND(inv.GrandTotal,v_Round_) as GrandTotal
        FROM tblInvoice inv
        inner join RateManagement4.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        left join RateManagement4.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0))
        ORDER BY
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
            END DESC,
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeDESC') THEN inv.InvoiceType
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeASC') THEN inv.InvoiceType
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusDESC') THEN inv.InvoiceStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusASC') THEN inv.InvoiceStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN inv.InvoiceNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN inv.InvoiceNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN inv.IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN inv.IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN inv.GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN inv.GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDDESC') THEN inv.InvoiceID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDASC') THEN inv.InvoiceID
            END ASC
        
        LIMIT p_RowspPage OFFSET v_OffSet_;
        
        
        SELECT
            COUNT(*) AS totalcount
        FROM
        tblInvoice inv
        inner join RateManagement4.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));
    END IF;
    IF p_isExport = 1
    THEN

        SELECT ac.AccountName ,
        ( CONCAT(ltrim(rtrim(IFNULL(it.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber)))) as InvoiceNumber,
        inv.IssueDate,
        ROUND(inv.GrandTotal,v_Round_) as GrandTotal,
        CONCAT(format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND (p.Recall =0)),v_Round_),'/',format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND (p.Recall =0)) ),v_Round_)) as `Paid/OS`,
        inv.InvoiceStatus,
        inv.InvoiceType,
        inv.ItemInvoice
        FROM tblInvoice inv
        inner join RateManagement4.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));

    END IF;
     IF p_isExport = 2
    THEN

        SELECT ac.AccountID ,
        ac.AccountName,
        ( CONCAT(ltrim(rtrim(IFNULL(it.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber)))) as InvoiceNumber,
        inv.IssueDate,
		  ROUND(inv.GrandTotal,v_Round_) as GrandTotal,
		  CONCAT(format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND (p.Recall =0)),v_Round_),'/',format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND (p.Recall =0)) ),v_Round_)) as `Paid/OS`,
        inv.InvoiceStatus,
        inv.InvoiceType,
        inv.ItemInvoice,
        inv.InvoiceID
        FROM tblInvoice inv
        inner join RateManagement4.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));
    END IF;

    IF p_sageExport =1 OR p_sageExport =2
    THEN
    		 -- mark as paid invoice that are sage export
        IF p_sageExport = 2
        THEN 
        UPDATE tblInvoice  inv
        INNER JOIN RateManagement4.tblAccount ac
          ON ac.AccountID = inv.AccountID
        INNER JOIN RateManagement4.tblCurrency c
          ON c.CurrencyId = ac.CurrencyId
        SET InvoiceStatus = 'paid' 
        WHERE ac.CompanyID = p_CompanyID
                AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
                AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
                AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
			       AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
                AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
                AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
                AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0))
                AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ));
        END IF; 
        SELECT
          Number AS AccountNumber,
          DATE_FORMAT(DATE_ADD(inv.IssueDate,INTERVAL ac.PaymentDueInDays DAY), '%Y-%m-%d') AS DueDate,
          GrandTotal AS GoodsValueInAccountCurrency,
          GrandTotal AS SalControlValueInBaseCurrency,
          1 AS DocumentToBaseCurrencyRate,
          1 AS DocumentToAccountCurrencyRate,
          DATE_FORMAT(IssueDate, '%Y-%m-%d') AS PostedDate,
          inv.InvoiceNumber AS TransactionReference,
          '' AS SecondReference,
          '' AS Source,
          4 AS SYSTraderTranType, -- 4 - Sales invoice (SI)
          DATE_FORMAT((select PaymentDate from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID order by PaymentID desc limit 1),'%Y-%m-%d') AS TransactionDate,
          TotalTax AS TaxValue,
          SubTotal AS `NominalAnalysisTransactionValue/1`,
          ac.NominalAnalysisNominalAccountNumber AS `NominalAnalysisNominalAccountNumber/1`,
          'NEON' AS `NominalAnalysisNominalAnalysisNarrative/1`,
          '' AS `NominalAnalysisTransactionAnalysisCode/1`,
          1 AS `TaxAnalysisTaxRate/1`,
          SubTotal AS `TaxAnalysisGoodsValueBeforeDiscount/1`,
          TotalTax as   `TaxAnalysisTaxOnGoodsValue/1`
        FROM tblInvoice inv
        INNER JOIN RateManagement4.tblAccount ac
          ON ac.AccountID = inv.AccountID
        INNER JOIN RateManagement4.tblCurrency c
          ON c.CurrencyId = ac.CurrencyId
        LEFT JOIN tblInvoiceTemplate it 
          ON ac.InvoiceTemplateID = it.InvoiceTemplateID        

        WHERE ac.CompanyID = p_CompanyID
                AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
                AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
                AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
			       AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
                AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
                AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
                AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0))
                AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ));
    END IF;

 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_GetInvoiceLog
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetInvoiceLog`(IN `p_CompanyID` INT, IN `p_InvoiceID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_isExport` INT)
BEGIN


	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	            
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


    IF p_isExport = 0
    THEN

       
            SELECT
                inv.InvoiceNumber,
                tl.Note,
                tl.InvoiceLogStatus,
                tl.created_at,                
                inv.InvoiceID
                
            FROM tblInvoice inv
            INNER JOIN RateManagement4.tblAccount ac
                ON ac.AccountID = inv.AccountID
            INNER JOIN tblInvoiceLog tl
                ON tl.InvoiceID = inv.InvoiceID
            WHERE ac.CompanyID = p_CompanyID
            AND (p_InvoiceID = '' 
            OR (p_InvoiceID != ''
            AND inv.InvoiceID = p_InvoiceID))
             ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceLogStatusDESC') THEN tl.InvoiceLogStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceLogStatusASC') THEN tl.InvoiceLogStatus
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tl.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tl.created_at
                END ASC
					LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(*) AS totalcount
        FROM tblInvoice inv
        INNER JOIN RateManagement4.tblAccount ac
            ON ac.AccountID = inv.AccountID
        INNER JOIN tblInvoiceLog tl
            ON tl.InvoiceID = inv.InvoiceID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));

    END IF;
    IF p_isExport = 1
    THEN

        SELECT
            tl.Note,
            tl.created_at,
            tl.InvoiceLogStatus,
            inv.InvoiceNumber
        FROM tblInvoice inv
        INNER JOIN RateManagement4.tblAccount ac
            ON ac.AccountID = inv.AccountID
        INNER JOIN tblInvoiceLog tl
            ON tl.InvoiceID = inv.InvoiceID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));


    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_GetInvoiceTransactionLog
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetInvoiceTransactionLog`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` int)
BEGIN
    
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;



    IF p_isExport = 0
    THEN

         
            SELECT
                `Transaction`,
                tl.Notes,
                tl.created_at,
                tl.Amount,
                tl.Status,
                inv.InvoiceID,
                inv.InvoiceNumber
            FROM tblInvoice inv
            INNER JOIN RateManagement4.tblAccount ac
                ON ac.AccountID = inv.AccountID
            INNER JOIN tblTransactionLog tl
                ON tl.InvoiceID = inv.InvoiceID
                AND tl.CompanyID = inv.CompanyID
            WHERE ac.CompanyID = p_CompanyID
            AND (p_AccountID = 0
            OR (p_AccountID != 0
            AND inv.AccountID = p_AccountID))
            AND (p_InvoiceID = ''
            OR (p_InvoiceID != ''
            AND inv.InvoiceID = p_InvoiceID)) 
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountDESC') THEN tl.Amount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountASC') THEN tl.Amount
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tl.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tl.Status
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tl.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tl.created_at
                END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(*) AS totalcount
        FROM tblInvoice inv
        INNER JOIN RateManagement4.tblAccount ac
            ON ac.AccountID = inv.AccountID
        INNER JOIN tblTransactionLog tl
            ON tl.InvoiceID = inv.InvoiceID
            AND tl.CompanyID = inv.CompanyID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0
        OR (p_AccountID != 0
        AND inv.AccountID = p_AccountID))
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));

    END IF;
    
    IF p_isExport = 1
    THEN

        SELECT
				`Transaction`,
            tl.Notes,
            tl.created_at,
            tl.Amount,
            tl.Status,
            inv.InvoiceID
        FROM tblInvoice inv
        INNER JOIN RateManagement4.tblAccount ac
            ON ac.AccountID = inv.AccountID
        INNER JOIN tblTransactionLog tl
            ON tl.InvoiceID = inv.InvoiceID
            AND tl.CompanyID = inv.CompanyID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0
        OR (p_AccountID != 0
        AND inv.AccountID = p_AccountID))
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));
		  
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getInvoiceUsage
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getInvoiceUsage`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_ShowZeroCall` INT)
BEGIN
    
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	DECLARE v_CDRType_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	

	
	SELECT BillingTime INTO v_BillingTime_
	FROM RateManagement4.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_,''); 

	Select CDRType  INTO v_CDRType_ from  RateManagement4.tblAccount where AccountID = p_AccountID;


            
        
            
    IF( v_CDRType_ = 2) -- Summery
    Then

			SELECT
            area_prefix AS AreaPrefix,
            max(Trunk) as Trunk,
            (SELECT 
                Country
            FROM RateManagement4.tblRate r
            INNER JOIN RateManagement4.tblCountry c
                ON c.CountryID = r.CountryID
            WHERE  r.Code = ud.area_prefix limit 1)
            AS Country,
            (SELECT Description
            FROM RateManagement4.tblRate r
            WHERE  r.Code = ud.area_prefix limit 1 )
            AS Description,
            COUNT(UsageDetailID) AS NoOfCalls,
            Concat( ROUND(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( ROUND(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
            SUM(cost) AS TotalCharges,
            SUM(duration ) as DurationInSec,
            SUM(billed_duration ) as BillDurationInSec

        FROM tmp_tblUsageDetails_ ud
        GROUP BY ud.area_prefix,
                 ud.AccountID;

         
    ELSE
        
        
            select
            trunk,
            area_prefix,
            cli,
            cld,
            connect_time,
            disconnect_time,
            billed_duration,
            cost
            FROM tmp_tblUsageDetails_ ud
            WHERE
             ((p_ShowZeroCall =0 and ud.cost >0 )or (p_ShowZeroCall =1 and ud.cost >= 0));
            
    END IF;    
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getInvoiceUsagecopy
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getInvoiceUsagecopy`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_ShowZeroCall` INT)
BEGIN
    
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	DECLARE v_CDRType_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	

	
	SELECT BillingTime INTO v_BillingTime_
	FROM RateManagement4.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_,''); 

	Select CDRType  INTO v_CDRType_ from  RateManagement4.tblAccount where AccountID = p_AccountID;


            
        
            
    IF( v_CDRType_ = 2) -- Summery
    Then

        SELECT
            area_prefix AS AreaPrefix,
            max(Trunk) as Trunk,
            (SELECT 
                Country
            FROM RateManagement4.tblRate r
            INNER JOIN RateManagement4.tblCustomerRate cr
                ON cr.RateID = r.RateID
            INNER JOIN RateManagement4.tblCustomerTrunk ct
                ON cr.CustomerID = ct.AccountID
                AND ct.TrunkID = cr.TrunkID
            INNER JOIN RateManagement4.tblTrunk t
                ON ct.TrunkID = t.TrunkID
            INNER JOIN RateManagement4.tblCountry c
                ON c.CountryID = r.CountryID
            WHERE t.Trunk = MAX(ud.trunk)
            AND ct.AccountID = ud.AccountID
            AND r.Code = ud.area_prefix limit 1)
            AS Country,
            (SELECT Description
            FROM RateManagement4.tblRate r
            WHERE  r.Code = ud.area_prefix limit 1 )
            AS Description,
            COUNT(UsageDetailID) AS NoOfCalls,
            SUM(duration ) AS Duration,
		    SUM(billed_duration ) AS BillDuration,
            SUM(cost) AS TotalCharges

        FROM tmp_tblUsageDetails_ ud
        GROUP BY ud.area_prefix,
                 ud.AccountID;

         
    ELSE
        
        
            select
            trunk,
            area_prefix,
            cli,
            cld,
            connect_time,
            disconnect_time,
            billed_duration,
            cost
            FROM tmp_tblUsageDetails_ ud
            WHERE
             ((p_ShowZeroCall =0 and ud.cost >0 )or (p_ShowZeroCall =1 and ud.cost >= 0));
            
    END IF;    
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getMissingAccounts
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getMissingAccounts`(IN `p_CompanyID` int)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
    -- Insert state... *** SQLINES FOR EVALUATION USE ONLY *** 
	SELECT cg.Title,ga.AccountName FROM tblGatewayAccount ga
	INNER JOIN RateManagement4.tblCompanyGateway cg ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE ga.GatewayAccountID IS NOT NULL AND ga.CompanyID =p_CompanyID AND ga.AccountID IS NULL AND cg.`Status` =1 ;

	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getPaymentPendingInvoice
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getPaymentPendingInvoice`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_PaymentDueInDays` INT )
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		MAX(i.InvoiceID) AS InvoiceID,
		(IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) AS RemaingAmount
	FROM tblInvoice i
	LEFT JOIN RateManagement4.tblAccount a
		ON i.AccountID = a.AccountID
	LEFT JOIN tblInvoiceTemplate it 
		ON a.InvoiceTemplateID = it.InvoiceTemplateID
	LEFT JOIN tblPayment p
		ON p.AccountID = i.AccountID
		AND REPLACE(p.InvoiceNo,'-','') = (CONCAT( ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(i.InvoiceNumber)) )) AND p.Status = 'Approved' AND p.AccountID = i.AccountID
		AND p.Status = 'Approved'
		AND p.Recall = 0
	WHERE i.CompanyID = p_CompanyID
	AND i.InvoiceStatus != 'cancel'
	AND i.AccountID = p_AccountID
	AND (p_PaymentDueInDays =0  OR (p_PaymentDueInDays =1 AND TIMESTAMPDIFF(DAY, i.IssueDate, NOW()) >= PaymentDueInDays) )

	GROUP BY i.InvoiceID,
			 p.AccountID
	HAVING (IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) > 0;	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getPayments
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getPayments`(IN `p_CompanyID` INT, IN `p_accountID` INT, IN `p_InvoiceNo` varchar(30), IN `p_Status` varchar(20), IN `p_PaymentType` varchar(20), IN `p_PaymentMethod` varchar(20), IN `p_RecallOnOff` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isCustomer` INT , IN `p_isExport` INT )
BEGIN
		
    	DECLARE v_OffSet_ int;
    	DECLARE v_Round_ int;
    	
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
     
		SELECT cs.Value INTO v_Round_ from RateManagement4.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;

	    
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	IF p_isExport = 0
    THEN
    select
            tblPayment.PaymentID,
            tblAccount.AccountName,
            tblPayment.AccountID,
            ROUND(tblPayment.Amount,v_Round_) AS Amount,
            CASE WHEN p_isCustomer = 1 THEN
              CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
              END
            ELSE  PaymentType
            END as PaymentType,
            tblPayment.CurrencyID,
            tblPayment.PaymentDate,
            tblPayment.Status,
            tblPayment.CreatedBy,
            tblPayment.PaymentProof,
            tblPayment.InvoiceNo,
            tblPayment.PaymentMethod,
            tblPayment.Notes,
            tblPayment.Recall,
            tblPayment.RecallReasoan,
            tblPayment.RecallBy,
            CONCAT(IFNULL(tblCurrency.Symbol,''),ROUND(tblPayment.Amount,v_Round_)) as AmountWithSymbol
            from tblPayment
            left join RateManagement4.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
            LEFT JOIN RateManagement4.tblCurrency  ON tblPayment.CurrencyID =   tblCurrency.CurrencyId
            where tblPayment.CompanyID = p_CompanyID
            AND(tblPayment.Recall = p_RecallOnOff)
            AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
            AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
            AND((p_Status IS NULL OR tblPayment.Status = p_Status))
            AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
            AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
            ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tblAccount.AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tblAccount.AccountName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoDESC') THEN InvoiceNo
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoASC') THEN InvoiceNo
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountDESC') THEN Amount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountASC') THEN Amount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentTypeDESC') THEN PaymentType
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentTypeASC') THEN PaymentType
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateDESC') THEN PaymentDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateASC') THEN PaymentDate
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tblPayment.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tblPayment.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN tblPayment.CreatedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN tblPayment.CreatedBy
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

            SELECT
            COUNT(tblPayment.PaymentID) AS totalcount
            from tblPayment
            left join RateManagement4.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
            where tblPayment.CompanyID = p_CompanyID
            AND(tblPayment.Recall = p_RecallOnOff)
            AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
            AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
            AND((p_Status IS NULL OR tblPayment.Status = p_Status))
            AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
            AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod));

	END IF;
	IF p_isExport = 1
    THEN

		SELECT 
            AccountName,
            CONCAT(IFNULL(tblCurrency.Symbol,''),ROUND(tblPayment.Amount,v_Round_)) as Amount,
            CASE WHEN p_isCustomer = 1 THEN
              CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
              END
            ELSE  PaymentType
            END as PaymentType,
            PaymentDate,
            tblPayment.Status,
            tblPayment.CreatedBy,
            InvoiceNo,
            tblPayment.PaymentMethod,
            Notes 
			from tblPayment
            left join RateManagement4.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
            LEFT JOIN RateManagement4.tblCurrency  ON tblPayment.CurrencyID =   tblCurrency.CurrencyId
            where tblPayment.CompanyID = p_CompanyID
            AND(tblPayment.Recall = p_RecallOnOff)
            AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
            AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
            AND((p_Status IS NULL OR tblPayment.Status = p_Status))
            AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
            AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod));
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getPincodesGrid
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getPincodesGrid`(IN `p_CompanyID` INT, IN `p_Pincode` VARCHAR(50), IN `p_PinExt` VARCHAR(50), IN `p_StartDate` DATE, IN `p_EndDate` DATE, IN `p_AccountID` INT, IN `p_CurrencyID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_isExport` INT)
    COMMENT 'Pincodes Grid For DashBorad'
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT cs.Value INTO v_Round_ from RateManagement4.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,p_StartDate,p_EndDate,0,1,1,'');
	
	
	IF p_isExport = 0
	THEN
		SELECT
      	cld as DestinationNumber,
         CONCAT(IFNULL(c.Symbol,''),ROUND(SUM(cost),v_Round_)) AS TotalCharges,
 			COUNT(UsageDetailID) AS NoOfCalls
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN RateManagement4.tblAccount a ON a.AccountID = uh.AccountID
		LEFT  JOIN RateManagement4.tblCurrency c ON c.CurrencyId = a.CurrencyId
      WHERE ((p_PinExt = 'pincode' AND uh.pincode = p_Pincode ) OR (p_PinExt = 'extension' AND uh.extension = p_Pincode )) AND uh.cost>0 AND a.CurrencyId = p_CurrencyID
		GROUP BY uh.cld
		ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationNumberDESC') THEN DestinationNumber
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationNumberASC') THEN DestinationNumber
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsDESC') THEN COUNT(UsageDetailID) 
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsASC') THEN COUNT(UsageDetailID) 
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesDESC') THEN SUM(cost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesASC') THEN SUM(cost)
                END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT COUNT(*) as totalcount FROM(SELECT
      	DISTINCT cld 
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN RateManagement4.tblAccount a on a.AccountID = uh.AccountID
      WHERE ((p_PinExt = 'pincode' AND uh.pincode = p_Pincode ) OR (p_PinExt = 'extension' AND uh.extension = p_Pincode )) AND uh.cost>0 AND a.CurrencyId = p_CurrencyID
		GROUP BY uh.cld) tbl;
		
	END IF;
	
	IF p_isExport = 1
	THEN
		SELECT
      	cld as `Destination Number`,
         CONCAT(IFNULL(c.Symbol,''),ROUND(SUM(cost),v_Round_)) AS `Total Cost`,
 			COUNT(UsageDetailID) AS `Number of Times Dialed`
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN RateManagement4.tblAccount a on a.AccountID = uh.AccountID
      WHERE ((p_PinExt = 'pincode' AND uh.pincode = p_Pincode ) OR (p_PinExt = 'extension' AND uh.extension = p_Pincode )) AND uh.cost>0 AND a.CurrencyId = p_CurrencyID
		GROUP BY uh.cld;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getProducts
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getProducts`(IN `p_CompanyID` INT, IN `p_Name` VARCHAR(50), IN `p_Code` VARCHAR(50), IN `p_Active` VARCHAR(1), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_Export` INT)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Amount,
			tblProduct.updated_at,
			tblProduct.Active,
			tblProduct.ProductID,
			tblProduct.Description,
			tblProduct.Note
            from tblProduct
            where tblProduct.CompanyID = p_CompanyID
			AND(p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountDESC') THEN Amount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountASC') THEN Amount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN tblProduct.updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN tblProduct.updated_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveDESC') THEN Active
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveASC') THEN Active
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(tblProduct.ProductID) AS totalcount
            from tblProduct
            where tblProduct.CompanyID = p_CompanyID
			AND(p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active));

	ELSE

			SELECT
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Amount,
			tblProduct.updated_at,
			tblProduct.Active
            from tblProduct
			where tblProduct.CompanyID = p_CompanyID
			AND(p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getSOA
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getSOA`(IN `p_CompanyID` INT, IN `p_accountID` INT, IN `p_StartDate` datetime, IN `p_EndDate` datetime, IN `p_isExport` INT )
BEGIN
	
		
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET SESSION sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

    DROP TEMPORARY TABLE IF EXISTS tmp_AOS_;
    CREATE TEMPORARY TABLE tmp_AOS_ (
        AccountName VARCHAR(50),
        InvoiceNo VARCHAR(50),
        PaymentType VARCHAR(15),
        PaymentDate LONGTEXT,
        PaymentMethod VARCHAR(20),
        Amount NUMERIC(18, 8),
        Currency VARCHAR(15),
        PeriodCover VARCHAR(30),
		  StartDate datetime,
  		  EndDate datetime,
        Type VARCHAR(10),
        InvoiceAmount NUMERIC(18, 8),
        CreatedDate VARCHAR(15),
        PaymentsID INT
    );
 /* Invoice & Payment with Invoice number */
    INSERT INTO tmp_AOS_
        SELECT
						DISTINCT 
            tblAccount.AccountName,
            tblInvoice.InvoiceNumber,
            tblPayment.PaymentType,
            CASE
            WHEN p_isExport = 1
            THEN
                DATE_FORMAT(tblPayment.PaymentDate,'%d/%m/%Y')
            ELSE
                DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            END AS dates,
            tblPayment.PaymentMethod,
            tblPayment.Amount,
            tblPayment.CurrencyID,
            CASE
            	WHEN (tblInvoice.ItemInvoice = 1 AND p_isExport = 1) THEN
	                DATE_FORMAT(tblInvoice.IssueDate,'%d/%m/%Y')
	            WHEN (tblInvoice.ItemInvoice = 1 AND p_isExport = 0) THEN
	            	 DATE_FORMAT(tblInvoice.IssueDate,'%d-%m-%Y')
					WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 1) THEN
	            	Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,' - ' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y'))
	            WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 0) THEN
	            	 Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d-%m-%Y') , ' => ' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d-%m-%Y'))
            END AS dates,
			tblInvoiceDetail.StartDate,
			tblInvoiceDetail.EndDate,
            tblInvoice.InvoiceType,
            tblInvoice.GrandTotal,
            tblInvoice.created_at,
            tblPayment.PaymentID
        FROM tblInvoice
        LEFT JOIN tblInvoiceDetail
            ON tblInvoice.InvoiceID = tblInvoiceDetail.InvoiceID AND ( (tblInvoice.InvoiceType = 1 AND tblInvoiceDetail.ProductType = 2 ) OR  tblInvoice.InvoiceType =2 )/* ProductType =2 = INVOICE USAGE AND InvoiceType = 1 Invoice sent and InvoiceType =2 invoice recevied */
        INNER JOIN RateManagement4.tblAccount
            ON tblInvoice.AccountID = tblAccount.AccountID
        LEFT JOIN tblInvoiceTemplate  on tblAccount.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
        LEFT JOIN tblPayment
            ON (tblInvoice.InvoiceNumber = tblPayment.InvoiceNo OR REPLACE(tblPayment.InvoiceNo,'-','') = concat( ltrim(rtrim(REPLACE(tblInvoiceTemplate.InvoiceNumberPrefix,'-',''))) , ltrim(rtrim(tblInvoice.InvoiceNumber)) ) )
            AND tblPayment.Status = 'Approved' 
            AND tblPayment.Recall = 0
        WHERE tblInvoice.CompanyID = p_CompanyID
        AND ( (IFNULL(tblInvoice.InvoiceStatus,'') = '') OR  tblInvoice.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting'))
        AND (p_accountID = 0
        OR tblInvoice.AccountID = p_accountID)
		AND 
		(
			(p_StartDate = '0000-00-00 00:00:00' OR  p_EndDate = '0000-00-00 00:00:00') OR ((p_StartDate != '0000-00-00 00:00:00' AND p_EndDate != '0000-00-00 00:00:00') AND str_to_date(tblInvoice.IssueDate,'%Y-%m-%d') between  str_to_date(p_StartDate,'%Y-%m-%d') and str_to_date(p_EndDate,'%Y-%m-%d') )
		)
        /* Payment without Invoice number  */
			
        UNION ALL
        SELECT
						DISTINCT
            tblAccount.AccountName,
            tblPayment.InvoiceNo,
            tblPayment.PaymentType,
            CASE
            WHEN p_isExport = 1
            THEN
               DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            ELSE
               DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            END AS dates,
            tblPayment.PaymentMethod,
            tblPayment.Amount,
            tblPayment.CurrencyID,
            '' AS dates,
			tblPayment.PaymentDate,
			tblPayment.PaymentDate,
            CASE
			WHEN tblPayment.PaymentType = 'Payment In'
            THEN
                1
            ELSE
                2
            END AS InvoiceType,
            0 AS GrandTotal,
            tblPayment.created_at,
            tblPayment.PaymentID
        FROM tblPayment
        INNER JOIN RateManagement4.tblAccount
            ON tblPayment.AccountID = tblAccount.AccountID
        WHERE tblPayment.Status = 'Approved'
        AND tblPayment.Recall = 0
        AND tblPayment.AccountID = p_accountID
        AND tblPayment.CompanyID = p_CompanyID
        AND tblPayment.InvoiceNo = ''
		AND 
		(
			(p_StartDate = '0000-00-00 00:00:00' OR  p_EndDate = '0000-00-00 00:00:00') OR ((p_StartDate != '0000-00-00 00:00:00' AND p_EndDate != '0000-00-00 00:00:00') AND str_to_date(tblPayment.PaymentDate,'%Y-%m-%d') between  str_to_date(p_StartDate,'%Y-%m-%d') and str_to_date(p_EndDate,'%Y-%m-%d') )
		);


    SELECT
				 
        IFNULL(InvoiceNo,'') as InvoiceNo,
        PeriodCover,
        InvoiceAmount,
        ' ' AS spacer,
         CASE
        WHEN p_isExport = 0
        THEN
        GROUP_CONCAT(PaymentDate SEPARATOR '<br/>')
		  ELSE
		   GROUP_CONCAT(PaymentDate SEPARATOR "\r\n") 
		  END	as PaymentDate,
        Amount AS payment,
        NULL AS ballence,
        CASE
        WHEN p_isExport = 0
        THEN
            MAX(PaymentsID)
        END PaymentID 
    FROM tmp_AOS_
    WHERE Type = 1
    GROUP BY InvoiceNo,PeriodCover,InvoiceAmount,StartDate,Amount
    ORDER BY StartDate desc;

    SELECT
				 
        IFNULL(InvoiceNo,'') as InvoiceNo,
        PeriodCover,
        IFNULL(InvoiceAmount,0) as InvoiceAmount,
        ' ' AS spacer,
        PaymentDate,
        IFNULL(Amount,0) AS payment,
        NULL AS ballence,
        CASE
        WHEN p_isExport = 0
        THEN
            PaymentsID
        END PaymentID
    FROM tmp_AOS_
    WHERE Type = 2
    ORDER BY StartDate desc;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getSummaryReportByCountry
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getSummaryReportByCountry`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_Country` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
    
    DECLARE v_BillingTime_ INT; 
    DECLARE v_OffSet_ int;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT BillingTime INTO v_BillingTime_
	FROM RateManagement4.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	AND (p_AccountID = '' OR ga.AccountID = p_AccountID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1);
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_,''); 
  
    IF p_isExport = 0
    THEN
            SELECT
                MAX(uh.AccountName) AS AccountName,
                MAX(c.Country) AS Country,
                COUNT(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
               CONCAT(IFNULL(cc.Symbol,''),SUM(cost)) AS TotalCharges
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN RateManagement4.tblRate r
                ON r.Code = uh.area_prefix 
			LEFT JOIN RateManagement4.tblCountry c
                ON r.CountryID = c.CountryID
         INNER JOIN RateManagement4.tblAccount a
         	ON a.AccountID = uh.AccountID
         LEFT JOIN RateManagement4.tblCurrency cc
         	ON cc.CurrencyId = a.CurrencyId          	
            WHERE (p_Country = '' OR c.CountryID = p_Country)
            group by 
            c.Country,uh.AccountID
				ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN MAX(uh.AccountName)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN MAX(uh.AccountName)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN MAX(c.Country)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN MAX(c.Country)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsDESC') THEN COUNT(UsageDetailID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsASC') THEN COUNT(UsageDetailID)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationDESC') THEN SUM(billed_duration)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationASC') THEN SUM(billed_duration)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesDESC') THEN SUM(cost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesASC') THEN SUM(cost)
                END ASC

        LIMIT p_RowspPage OFFSET v_OffSet_;


        SELECT count(*) as totalcount from (SELECT DISTINCT uh.AccountID ,c.Country 
        FROM tmp_tblUsageDetails_ uh
            LEFT JOIN RateManagement4.tblRate r
                ON r.Code = uh.area_prefix 
			LEFT JOIN RateManagement4.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE (p_Country = '' OR c.CountryID = p_Country)
        ) As TBL;

    END IF;
    IF p_isExport = 1
    THEN
        
            SELECT
                MAX(AccountName) AS AccountName,
                MAX(c.Country) AS Country,
                COUNT(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                SUM(cost) AS TotalCharges
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN RateManagement4.tblRate r
                ON r.Code = uh.area_prefix 
			LEFT JOIN RateManagement4.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE (p_Country = '' OR c.CountryID = p_Country)
            group by 
            c.Country,uh.AccountID;
            

    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getSummaryReportByCustomer
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getSummaryReportByCustomer`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
     
    DECLARE v_BillingTime_ INT; 
    DECLARE v_OffSet_ int;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT BillingTime INTO v_BillingTime_
	FROM RateManagement4.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	AND (p_AccountID = '' OR ga.AccountID = p_AccountID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1);
	
  CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_,''); 

    IF p_isExport = 0
    THEN
            SELECT
                MAX(uh.AccountName) as AccountName,
                count(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                CONCAT(IFNULL(cc.Symbol,''),SUM(cost)) AS TotalCharges
            FROM tmp_tblUsageDetails_ uh
            INNER JOIN RateManagement4.tblAccount a
         	ON a.AccountID = uh.AccountID
         LEFT JOIN RateManagement4.tblCurrency cc
         	ON cc.CurrencyId = a.CurrencyId
            group by 
            uh.AccountID
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN MAX(uh.AccountName)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN MAX(uh.AccountName)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsDESC') THEN count(UsageDetailID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsASC') THEN count(UsageDetailID)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationDESC') THEN SUM(billed_duration)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationASC') THEN SUM(billed_duration)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesDESC') THEN SUM(cost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesASC') THEN SUM(cost)
                END ASC
            
        LIMIT p_RowspPage OFFSET v_OffSet_;


        SELECT count(*) as totalcount from (SELECT DISTINCT AccountID  
        FROM tmp_tblUsageDetails_
        ) As TBL;
             

    END IF;
    IF p_isExport = 1
    THEN
        
            SELECT
                MAX(AccountName) as AccountName,
                count(UsageDetailID) AS NoOfCalls,
                Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,	
                SUM(cost) AS TotalCharges

            FROM tmp_tblUsageDetails_
            group by 
            AccountID;

    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_getSummaryReportByPrefix
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getSummaryReportByPrefix`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_Prefix` VARCHAR(50), IN `p_Country` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
     
    DECLARE v_BillingTime_ INT; 
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
     

	SELECT BillingTime INTO v_BillingTime_
	FROM RateManagement4.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	AND (p_AccountID = '' OR ga.AccountID = p_AccountID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1);
	
  CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_,''); 

    IF p_isExport = 0
    THEN
         
            SELECT
                MAX(uh.AccountName) AS AccountName,
                area_prefix as AreaPrefix,
                MAX(c.Country) AS Country,
                MAX(r.Description) AS Description,
                count(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                CONCAT(IFNULL(cc.Symbol,''),SUM(cost)) AS TotalCharges
                
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN RateManagement4.tblRate r
                ON r.Code = uh.area_prefix 
            LEFT JOIN RateManagement4.tblCountry c
                ON r.CountryID = c.CountryID
            INNER JOIN RateManagement4.tblAccount a
         		ON a.AccountID = uh.AccountID
         	LEFT JOIN RateManagement4.tblCurrency cc
	         	ON cc.CurrencyId = a.CurrencyId
            WHERE 
			(p_Prefix='' OR uh.area_prefix like REPLACE(p_Prefix, '*', '%'))
			AND (p_Country=0 OR r.CountryID=p_Country)
			GROUP BY uh.area_prefix,
                     uh.AccountID
                     ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN MAX(uh.AccountName)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN MAX(uh.AccountName)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixDESC') THEN area_prefix
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixASC') THEN area_prefix
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN MAX(c.Country)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN MAX(c.Country)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN MAX(r.Description)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN MAX(r.Description)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsDESC') THEN COUNT(UsageDetailID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsASC') THEN COUNT(UsageDetailID)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationDESC') THEN SUM(billed_duration)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationASC') THEN SUM(billed_duration)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesDESC') THEN SUM(cost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesASC') THEN SUM(cost)
                END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;


        SELECT
            COUNT(*) AS totalcount
        FROM (
            SELECT DISTINCT
                area_prefix as AreaPrefix,
                uh.AccountID
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN RateManagement4.tblRate r
                ON r.Code = uh.area_prefix 
            LEFT JOIN RateManagement4.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE 
			(p_Prefix='' OR uh.area_prefix like REPLACE(p_Prefix, '*', '%'))
			AND (p_Country=0 OR r.CountryID=p_Country)
        ) AS TBL;


    END IF;
    IF p_isExport = 1
    THEN

        SELECT
            MAX(AccountName) AS AccountName,
            area_prefix as AreaPrefix,
            MAX(c.Country) AS Country,
            MAX(r.Description) AS Description,
            COUNT(UsageDetailID) AS NoOfCalls,
  			Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
            SUM(cost) AS TotalCharges
        FROM tmp_tblUsageDetails_ uh
		
		LEFT JOIN RateManagement4.tblRate r
                ON r.Code = uh.area_prefix 
        LEFT JOIN RateManagement4.tblCountry c
                ON r.CountryID = c.CountryID
        WHERE 
			(p_Prefix='' OR uh.area_prefix like REPLACE(p_Prefix, '*', '%'))
			AND (p_Country=0 OR r.CountryID=p_Country)
        GROUP BY uh.area_prefix,
                 uh.AccountID;

    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_GetTransactionsLogbyInterval
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetTransactionsLogbyInterval`(IN `p_CompanyID` int, IN `p_Interval` varchar(50) )
BEGIN
   
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
        SELECT
			ac.AccountName,
			inv.InvoiceNumber,
            tl.Transaction,
            tl.Notes,
            tl.created_at,
            tl.Amount,
            Case WHEN tl.Status = 1 then 'Success' ELSE 'Failed'  
			END as Status
		FROM   tblTransactionLog tl
		INNER JOIN tblInvoice inv
            ON tl.CompanyID = inv.CompanyID 
            AND tl.InvoiceID = inv.InvoiceID
        INNER JOIN RateManagement4.tblAccount ac
            ON ac.AccountID = inv.AccountID
        
        WHERE ac.CompanyID = p_CompanyID 
        AND (
			( p_Interval = 'Daily' AND tl.created_at >= DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 DAY),'%Y-%m-%d') )
			OR
			( p_Interval = 'Weekly' AND tl.created_at >= DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 WEEK),'%Y-%m-%d') )
			OR
			( p_Interval = 'Monthly' AND tl.created_at >= DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 MONTH),'%Y-%m-%d') )
		);
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_GetVendorCDR
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetVendorCDR`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

   	DECLARE v_OffSet_ int;
   	DECLARE v_BillingTime_ int;
   	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT BillingTime INTO v_BillingTime_
	FROM RateManagement4.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    
    Call fnVendorUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_);

	IF p_isExport = 0
	THEN 
	
   SELECT
        AccountName,        
        connect_time,
        disconnect_time,        
        billed_duration,
        selling_cost,
        buying_cost,
        cli,
        cld,
        AccountID,
        p_CompanyGatewayID as CompanyGatewayID,
        p_start_date as StartDate,
        p_end_date as EndDate  from(
        SELECT
        		Distinct
            uh.AccountName as AccountName,           
            uh.connect_time,
            uh.disconnect_time,
            uh.billed_duration,
            uh.cli,
            uh.cld,
				format(uh.selling_cost,6) as selling_cost,
				format(uh.buying_cost ,6) as buying_cost,
				AccountID
            
        
        FROM tmp_tblVendorUsageDetails_ uh
         
        

    ) AS TBL  
    ORDER BY
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeDESC') THEN connect_time
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeASC') THEN connect_time
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeDESC') THEN disconnect_time
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeASC') THEN disconnect_time
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'billed_durationDESC') THEN billed_duration
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'billed_durationASC') THEN billed_duration
            END ASC,
				 
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'selling_costDESC') THEN selling_cost
            END DESC,
         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'selling_costASC') THEN selling_cost
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliDESC') THEN cli
            END DESC,
         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliASC') THEN cli
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldDESC') THEN cld
            END DESC,
         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldASC') THEN cld
            END ASC


	 LIMIT p_RowspPage OFFSET v_OffSet_;
	 
	  SELECT
        COUNT(*) AS totalcount
    FROM (
    select Distinct uh.AccountID,connect_time,disconnect_time
    FROM tmp_tblVendorUsageDetails_ uh
    ) AS TBL2;
    
    END IF;
	
	IF p_isExport = 1
		THEN
		
			SELECT
		        AccountName,        
		        connect_time,
		        disconnect_time,        
		        billed_duration,
		        selling_cost,
		        cli,
		        cld
			from(
		        SELECT
		        		Distinct
		            uh.AccountName as AccountName,           
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.billed_duration,
		            uh.cli,
		            uh.cld,
						format(uh.selling_cost,6) as selling_cost,
						AccountID
		        FROM tmp_tblVendorUsageDetails_ uh
		    ) AS TBL;
		
		END IF;

   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_insertDailyData
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertDailyData`(IN `p_ProcessID` VarCHAR(200), IN `p_Offset` INT)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	DROP TEMPORARY TABLE IF EXISTS tmp_DetailSummery_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_DetailSummery_(
			CompanyID int,
			CompanyGatewayID int,
			AccountID int,
			trunk varchar(50),			
			area_prefix varchar(50),			
			pincode varchar(50),
			extension VARCHAR(50),
			TotalCharges float,
			TotalDuration int,
			TotalBilledDuration int,
			NoOfCalls int,
			DailyDate datetime
	);
   
    CALL fnUsageDetailbyProcessID(p_ProcessID); 
    
   INSERT INTO tmp_DetailSummery_
	SELECT
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		ud.pincode,
		ud.extension,
		SUM(ud.cost) AS TotalCharges,
		SUM(duration) AS TotalDuration,
		SUM(billed_duration) AS TotalBilledDuration,
		COUNT(cld) AS NoOfCalls,
		DATE_FORMAT(connect_time, '%Y-%m-%d') AS DailyDate
	from (select CompanyID,
		CompanyGatewayID,
		AccountID,
		trunk,
		area_prefix,
		pincode,
		extension,
		cost,
		duration,
		billed_duration,
		cld,
		DATE_ADD(connect_time,INTERVAL p_Offset SECOND) as connect_time
		FROM tmp_tblUsageDetailsProcess_)
		ud
	GROUP BY ud.trunk,
				area_prefix,
				pincode,
				extension,
				DATE_FORMAT(connect_time, '%Y-%m-%d'),
				ud.AccountID,
				ud.CompanyID,
				ud.CompanyGatewayID;
	INSERT INTO tblUsageDaily (CompanyID,CompanyGatewayID,AccountID,Trunk,AreaPrefix,Pincode,Extension,TotalCharges,TotalDuration,TotalBilledDuration,NoOfCalls,DailyDate) 
		SELECT ds.*
		FROM tmp_DetailSummery_ ds LEFT JOIN 
		tblUsageDaily dd  ON 
		dd.CompanyID = ds.CompanyID 
		AND dd.AccountID = ds.AccountID
		AND dd.CompanyGatewayID = ds.CompanyGatewayID
		AND dd.Trunk = ds.trunk
		AND dd.AreaPrefix = ds.area_prefix
		AND dd.DailyDate = ds.DailyDate
		WHERE dd.UsageDailyID IS NULL;

	UPDATE tblUsageDaily  dd
 INNER JOIN 
	tmp_DetailSummery_ ds 
	ON 
	dd.CompanyID = ds.CompanyID 
	AND dd.AccountID = ds.AccountID
	AND dd.CompanyGatewayID = ds.CompanyGatewayID
	AND dd.Trunk = ds.trunk
	AND dd.AreaPrefix = ds.area_prefix
	AND (dd.Pincode = ds.pincode OR (dd.Pincode IS NULL AND ds.pincode IS NULL))
   AND (dd.Extension = ds.extension OR (dd.Extension IS NULL AND ds.extension IS NULL))
	AND dd.DailyDate = ds.DailyDate
SET dd.TotalCharges =  dd.TotalCharges + ds.TotalCharges,
	dd.TotalDuration = dd.TotalDuration + ds.TotalDuration,
	dd.TotalBilledDuration = dd.TotalBilledDuration +  ds.TotalBilledDuration,
	dd.NoOfCalls = dd.NoOfCalls + ds.NoOfCalls
	WHERE (
		ds.TotalDuration != dd.TotalDuration
	OR  ds.TotalBilledDuration != dd.TotalBilledDuration
	OR  ds.NoOfCalls != dd.NoOfCalls);


  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
	
 
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_insertGatewayAccount
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_insertGatewayAccount`(IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    set @stm = CONCAT('	 	  INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName)
        SELECT
            distinct
            ud.CompanyID,
            ud.CompanyGatewayID,
            ud.GatewayAccountID,
            ud.GatewayAccountID
        FROM RMCDR4.' , p_tbltempusagedetail_name , ' ud
        LEFT JOIN tblGatewayAccount ga
            ON ga.GatewayAccountID = ud.GatewayAccountID
            AND ga.CompanyGatewayID = ud.CompanyGatewayID
            AND ga.CompanyID = ud.CompanyID
        WHERE processid =  "' , p_processId , '"
        AND ga.GatewayAccountID IS NULL
        AND ud.GatewayAccountID IS NOT NULL;
   ');
   
   PREPARE stmt FROM @stm;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_insertGatewayVendorAccount
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_insertGatewayVendorAccount`(IN `p_processId` VARCHAR(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN
    
	SET @stm = CONCAT('INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName,IsVendor)
		SELECT
			distinct
			ud.CompanyID,
			ud.CompanyGatewayID,
			ud.GatewayAccountID,
			ud.GatewayAccountID,
			1 as IsVendor

		FROM RMCDR4.',p_tbltempusagedetail_name ,' ud
		LEFT JOIN tblGatewayAccount ga
			ON ga.GatewayAccountID = ud.GatewayAccountID
			AND ga.CompanyGatewayID = ud.CompanyGatewayID
			AND ga.CompanyID = ud.CompanyID
		WHERE processid =  "' , p_processId , '"
		AND ga.GatewayAccountID IS NULL
		AND ud.GatewayAccountID IS NOT NULL');
		
	PREPARE stmt FROM @stm;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_insertPayments
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_insertPayments`(IN `p_CompanyID` INT, IN `p_ProcessID` VARCHAR(100), IN `p_UserID` INT)
BEGIN

	
	DECLARE v_UserName varchar(30);
 	
 	SELECT CONCAT(u.FirstName,CONCAT(' ',u.LastName)) as name into v_UserName from RateManagement4.tblUser u where u.UserID=p_UserID;
 	
 	INSERT INTO tblPayment (
	 		CompanyID,
	 		 AccountID,
			 InvoiceNo,
			 PaymentDate,
			 PaymentMethod,
			 PaymentType,
			 Notes,
			 Amount,
			 CurrencyID,
			 Recall,
			 `Status`,
			 created_at,
			 updated_at,
			 CreatedBy,
			 ModifyBy,
			 RecallReasoan,
			 RecallBy,BulkUpload)
 	select tp.CompanyID,
	 		 tp.AccountID,
			 COALESCE(tp.InvoiceNo,''),
			 tp.PaymentDate,
			 tp.PaymentMethod,
			 tp.PaymentType,
			 tp.Notes,
			 tp.Amount,
			 ac.CurrencyId,
			 0 as Recall,
			 tp.Status,
			 Now() as created_at,
			 Now() as updated_at,
			 v_UserName as CreatedBy,
			 '' as ModifyBy,
			 '' as RecallReasoan,
			 '' as RecallBy,
			 1 as BulkUpload
	from tblTempPayment tp
	INNER JOIN RateManagement4.tblAccount ac 
		ON  ac.AccountID = tp.AccountID and ac.AccountType = 1 
	where tp.ProcessID = p_ProcessID
			AND tp.PaymentDate <= NOW()
			AND tp.CompanyID = p_CompanyID;
			
	/* Delete tmp table */		
	delete from tblTempPayment where CompanyID = p_CompanyID and ProcessID = p_ProcessID;
				
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_InsertTempReRateCDR
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_InsertTempReRateCDR`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT, IN `p_ProcessID` VARCHAR(50), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	 DECLARE v_BillingTime_ INT; 
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SELECT BillingTime INTO v_BillingTime_
	 FROM RateManagement4.tblCompanyGateway cg
	 INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	 WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
	 LIMIT 1;

    SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    
    CALL fnUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,''); 
    
    set @stm1 = CONCAT('

    INSERT INTO RMCDR4.`' , p_tbltempusagedetail_name , '` (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,connect_time,disconnect_time,billed_duration,trunk,area_prefix,cli,cld,cost,ProcessID,duration,is_inbound)

    SELECT "',p_CompanyID,'","',p_CompanyGatewayID,'",AccountName ,AccountID,connect_time,disconnect_time,billed_duration,trunk,area_prefix,cli,cld,cost,"',p_ProcessID,'",duration,is_inbound
    FROM tmp_tblUsageDetails_');
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_salesDashboard
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_salesDashboard`(IN `p_CompanyID` INT, IN `p_gatewayid` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_PrevStartDate` DATETIME, IN `p_PrevEndDate` DATETIME, IN `p_Executive` INT 
)
BEGIN
   
   DECLARE v_Round_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SELECT cs.Value INTO v_Round_ from RateManagement4.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	SELECT
        ROUND(ifNull(CAST(SUM(TotalCharges) as DECIMAL(16,5)),0),v_Round_) AS TotalCharges
    FROM tblUsageDaily ud
    LEFT JOIN RateManagement4.tblAccount a
        ON ud.AccountID = a.AccountID 
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID;
    
    



    SELECT
        COUNT( DISTINCT ud.AccountID ) AS totalactive
    FROM tblUsageDaily ud
    LEFT JOIN RateManagement4.tblAccount a
        ON ud.AccountID = a.AccountID
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID;
    


    SELECT
        COUNT( DISTINCT ga.GatewayAccountID) AS totalinactive
    FROM tblGatewayAccount ga
    left join tblUsageDaily ud on ud.AccountID = ga.AccountID AND  ud.DailyDate >= p_StartDate
        AND ud.DailyDate <= p_EndDate 
        and ud.CompanyID = ga.CompanyID
         LEFT JOIN RateManagement4.tblAccount a
        ON ga.AccountID = a.AccountID
    where ud.AccountID is null 
    and ga.AccountID is not null
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ga.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null;
    

    SELECT
        ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5)),v_Round_) AS TotalCharges,
        ud.DailyDate AS sales_date
    FROM tblUsageDaily ud
    LEFT JOIN RateManagement4.tblAccount a
        ON ud.AccountID = a.AccountID 
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID
    GROUP BY ud.DailyDate
    ORDER BY ud.DailyDate;

    SELECT
        ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS TotalCharges,
        max(a.AccountName) as AccountName
    FROM tblUsageDaily ud
    LEFT JOIN RateManagement4.tblAccount a
        ON ud.AccountID = a.AccountID  
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID

    GROUP BY ud.AccountID
    order by SUM(TotalCharges) desc;



    SELECT  
        Max(ifnull(c.Country,'OTHER')) as Country,
        ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5)),v_Round_) AS TotalCharges
    FROM tblUsageDaily ud
    LEFT JOIN RateManagement4.tblAccount a
        ON ud.AccountID = a.AccountID  
    LEFT JOIN RateManagement4.tblRate r
        ON r.Code = ud.AreaPrefix AND r.CodeDeckId = 1
    LEFT JOIN RateManagement4.tblCountry c
        ON r.CountryID = c.CountryID
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID
    GROUP BY r.CountryID
    ORDER BY SUM(TotalCharges) DESC limit 5;

    SELECT
       DISTINCT ga.GatewayAccountID,
            ga.AccountName,
            ga.AccountID
    FROM tblGatewayAccount ga
    left join tblUsageDaily ud on ud.AccountID = ga.AccountID AND  ud.DailyDate >= p_StartDate
        AND ud.DailyDate <= p_EndDate 
        and ud.CompanyID = ga.CompanyID
         LEFT JOIN RateManagement4.tblAccount a
        ON ga.AccountID = a.AccountID
    where ud.AccountID is null 
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    and ga.AccountID is not null
    AND ga.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null
    order by AccountName;

    IF p_Executive = 1
    THEN
        SELECT
            ROUND(ifNull(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),0),v_Round_) AS TotalCharges,a.Owner,(concat(max(u.FirstName),' ',max(u.LastName))) as FullName
        FROM tblUsageDaily ud
        LEFT JOIN RateManagement4.tblAccount a
            ON ud.AccountID = a.AccountID 
        LEFT JOIN RateManagement4.tblUser u
            ON u.UserID = a.Owner
        WHERE ud.DailyDate >= p_StartDate
        AND ud.DailyDate <= p_EndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND a.AccountID is not null
        AND ud.CompanyID = p_CompanyID
        group by a.Owner;

        IF p_PrevStartDate != ''
        THEN
        SELECT
            ROUND(IFNull(CAST(SUM(TotalCharges) as DECIMAL(16,5)),0),v_Round_) AS TotalCharges,a.Owner,(concat(max(u.FirstName),' ',max(u.LastName))) as FullName
        FROM tblUsageDaily ud
        LEFT JOIN RateManagement4.tblAccount a
            ON ud.AccountID = a.AccountID 
        LEFT JOIN RateManagement4.tblUser u
            ON u.UserID = a.Owner
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        group by a.Owner;

        else 
            select 1 as FullName,0 as TotalCharges;
        end if;

    else
        select 1 as FullName,0 as TotalCharges;
        select 1 as FullName,0 as TotalCharges;
    end if;

    IF p_PrevStartDate != ''
    THEN
        SELECT
            ROUND(IFNull(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),0),v_Round_)   AS PrevTotalCharges
        FROM tblUsageDaily ud
        LEFT JOIN RateManagement4.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID;
        


        SELECT
            COUNT( DISTINCT ud.AccountID ) AS prevtotalactive
        FROM tblUsageDaily ud
        LEFT JOIN RateManagement4.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID;
        



        SELECT
            COUNT( DISTINCT ga.GatewayAccountID ) AS prevtotalinactive
        FROM tblGatewayAccount ga
        left join tblUsageDaily ud on ud.AccountID = ga.AccountID AND
    			ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate 
            and ud.CompanyID = ga.CompanyID
        LEFT JOIN RateManagement4.tblAccount a
            ON ga.AccountID = a.AccountID  
    where ud.AccountID is null 
    and ga.AccountID is not null
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null;

        SELECT
            ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS prevTotalCharges,
            ud.DailyDate
        FROM tblUsageDaily ud
        LEFT JOIN RateManagement4.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        GROUP BY ud.DailyDate
        ORDER BY ud.DailyDate;

        SELECT
            ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS prevTotalCharges,
            max(a.AccountName) as AccountName
        FROM tblUsageDaily ud
        LEFT JOIN RateManagement4.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        GROUP BY ud.AccountID
        order by SUM(TotalCharges) desc;



        SELECT
            Max(ifnull(c.Country,'OTHER')) as Country,
            ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS prevTotalCharges
        FROM tblUsageDaily ud
        LEFT JOIN RateManagement4.tblAccount a
            ON ud.AccountID = a.AccountID  
        LEFT JOIN RateManagement4.tblRate r
            ON r.Code = ud.AreaPrefix AND r.CodeDeckId = 1
        LEFT JOIN RateManagement4.tblCountry c
            ON r.CountryID = c.CountryID
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        GROUP BY r.CountryID
        ORDER BY SUM(TotalCharges) DESC limit 5;
        

         SELECT
            DISTINCT ga.GatewayAccountID,
            ga.AccountName,
            ga.AccountID
        FROM tblGatewayAccount ga
        left join tblUsageDaily ud on ud.AccountID = ga.AccountID  AND
    ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate 
            and ud.CompanyID = ga.CompanyID
        LEFT JOIN RateManagement4.tblAccount a
            ON ga.AccountID = a.AccountID  
    where ud.AccountID is null 
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    and ga.AccountID is not null
    AND ga.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null
    order by AccountName;

    END IF;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_setAccountID
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_setAccountID`(IN `p_companyid` int)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	UPDATE RMCDR4.tblUsageHeader uh
	INNER JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_companyid;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_setAccountIDCDR
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_setAccountIDCDR`(IN `p_companyid` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	set @stm1 = CONCAT(' UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` uh
	INNER JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ' ,  p_companyid , '
	AND uh.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_setVendorAccountID
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_setVendorAccountID`(IN `p_companyid` int)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	UPDATE RMCDR4.tblVendorCDRHeader uh
	INNER JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_companyid;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_start_end_time
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_start_end_time`(IN `p_ProcessID` VARCHAR(2000), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    set @stm1 = CONCAT('select 
   MAX(disconnect_time) as max_date,MIN(connect_time)  as min_date
   from RMCDR4.`' , p_tbltempusagedetail_name , '` where ProcessID = "' , p_ProcessID , '"');
   
   PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_updatePrefixTrunk
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_updatePrefixTrunk`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

      -- update trunk with first trunk if not set UseInBilling
   set @stm1 = CONCAT(' UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblCustomerTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 
        AND UseInBilling = 0 and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
    ud.processId = "' , p_processId , '"
    AND ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.cost > 0);
    ');

    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	


 -- update trunk if set UseInBilling
     set @stm2 = CONCAT(' UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblCustomerTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 and ud.processId = "' , p_processId , '"
        AND UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
    LEFT JOIN RateManagement4.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
    ud.processId = "' , p_processId , '"
	 AND  ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.cost > 0)
    AND t.Trunk IS NOT NULL;
    ');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    
   DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
			TempUsageDetailID int,
			prefix varchar(50)
	);
	
	
	set @stm3 = CONCAT('
	 INSERT INTO tmp_TempUsageDetail_
    SELECT
		  TempUsageDetailID,
        MAX(r.Code) AS prefix
    FROM RMCDR4.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblCustomerTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1  
        AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 ) and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblCustomerRate cr 
        ON cr.CustomerID = ga.AccountID
        AND  cr.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblRate r 
        ON cr.RateID = r.RateID and ud.processId = "' , p_processId , '"
    WHERE  
 	 ud.processId = "' , p_processId , '"
	 AND ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.cost > 0)
    AND (
            (ct.UseInBilling = 1 AND ( (ct.AccountID is not null and  ct.Prefix is null and  cld LIKE CONCAT(r.Code , "%")) or (ct.Prefix is not null and  cld LIKE CONCAT(ct.Prefix,r.Code , "%"))))
            or 
            (ct.UseInBilling = 0 AND ( (ct.AccountID is not null and cld LIKE CONCAT(r.Code , "%")) or (cld LIKE CONCAT(r.Code , "%")) ) ) 
        )
    GROUP BY  TempUsageDetailID;
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
   
	
	set @stm4 = CONCAT('UPDATE RMCDR4.' , p_tbltempusagedetail_name , ' tbl2
    INNER JOIN tmp_TempUsageDetail_ tbl
        ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
    SET area_prefix = prefix
    WHERE tbl2.processId = "' , p_processId , '"
	 ');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

        
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_updateVendorPrefixTrunk
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_updateVendorPrefixTrunk`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

      -- update trunk with first trunk if not set UseInBilling
   set @stm1 = CONCAT(' UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 
        AND UseInBilling = 0 and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
    ud.processId = "' , p_processId , '"
    AND ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.selling_cost > 0);
    ');

    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	


 -- update trunk if set UseInBilling
     set @stm2 = CONCAT(' UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 and ud.processId = "' , p_processId , '"
        AND UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
    LEFT JOIN RateManagement4.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
    ud.processId = "' , p_processId , '"
	 AND  ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.selling_cost > 0)
    AND t.Trunk IS NOT NULL;
    ');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    
   DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorCDR_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorCDR_(
			TempVendorCDRID int,
			prefix varchar(50)
	);
	
	
	set @stm3 = CONCAT('
	 INSERT INTO tmp_TempVendorCDR_
    SELECT
		  TempVendorCDRID,
        MAX(r.Code) AS prefix
    FROM RMCDR4.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1  
        AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 ) and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblVendorRate cr 
        ON cr.AccountId = ga.AccountID
        AND  cr.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
    LEFT JOIN RateManagement4.tblRate r 
        ON cr.RateID = r.RateID and ud.processId = "' , p_processId , '"
    WHERE  
 	 ud.processId = "' , p_processId , '"
	 AND ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.selling_cost > 0)
    AND (
            (ct.UseInBilling = 1 AND ( (ct.AccountID is not null and  ct.Prefix is null and  cld LIKE CONCAT(r.Code , "%")) or (ct.Prefix is not null and  cld LIKE CONCAT(ct.Prefix,r.Code , "%"))))
            or 
            (ct.UseInBilling = 0 AND ( (ct.AccountID is not null and cld LIKE CONCAT(r.Code , "%")) or (cld LIKE CONCAT(r.Code , "%")) ) ) 
        )
    GROUP BY  TempVendorCDRID;
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
   
	
	set @stm4 = CONCAT('UPDATE RMCDR4.' , p_tbltempusagedetail_name , ' tbl2
    INNER JOIN tmp_TempVendorCDR_ tbl
        ON tbl2.TempVendorCDRID = tbl.TempVendorCDRID
    SET area_prefix = prefix
    WHERE tbl2.processId = "' , p_processId , '"
	 ');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

        
END//
DELIMITER ;


-- Dumping structure for procedure RMBilling4.prc_validatePayments
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_validatePayments`(IN `p_CompanyID` INT, IN `p_ProcessID` VARCHAR(50))
BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
SET SESSION group_concat_max_len=5000;


	DROP TEMPORARY TABLE IF EXISTS tmp_error_;
	CREATE TEMPORARY TABLE tmp_error_(
		`ErrorMessage` VARCHAR(500)
	);
	
		INSERT INTO tmp_error_	
	SELECT DISTINCT CONCAT('Future payments will not be uploaded - Account: ',IFNULL(ac.AccountName,''),' Action: ' ,IFNULL(pt.PaymentType,''),' Payment Date: ',IFNULL(pt.PaymentDate,''),' Amount: ',pt.Amount) as ErrorMessage
	FROM tblTempPayment pt
	INNER JOIN RateManagement4.tblAccount ac on ac.AccountID = pt.AccountID
	WHERE pt.CompanyID = p_CompanyID
		AND pt.PaymentDate > NOW()
		AND pt.ProcessID = p_ProcessID;
	

	INSERT INTO tmp_error_
	SELECT distinct CONCAT('Duplicate payment in file - Account: ',IFNULL(ac.AccountName,''),' Action: ' ,IFNULL(pt.PaymentType,''),' Payment Date: ',IFNULL(pt.PaymentDate,''),' Amount: ',pt.Amount) as ErrorMessage  
	FROM tblTempPayment pt
	INNER JOIN RateManagement4.tblAccount ac on ac.AccountID = pt.AccountID
	INNER JOIN tblTempPayment tp on tp.ProcessID = pt.ProcessID
		AND tp.AccountID = pt.AccountID
		AND tp.PaymentDate = pt.PaymentDate
		AND tp.Amount = pt.Amount
		AND tp.PaymentType = pt.PaymentType
		AND tp.PaymentMethod = pt.PaymentMethod
	WHERE pt.CompanyID = p_CompanyID
		AND pt.ProcessID = p_ProcessID
 	GROUP BY pt.InvoiceNo,pt.AccountID,pt.PaymentDate,pt.PaymentType,pt.PaymentMethod,pt.Amount having count(*)>=2;
 	
	INSERT INTO tmp_error_	
	SELECT DISTINCT
		CONCAT('Duplicate payment in system - Account: ',IFNULL(ac.AccountName,''),' Action: ' ,IFNULL(pt.PaymentType,''),' Payment Date: ',IFNULL(pt.PaymentDate,''),' Amount: ',pt.Amount) as ErrorMessage
	FROM tblTempPayment pt
	INNER JOIN RateManagement4.tblAccount ac on ac.AccountID = pt.AccountID
	INNER JOIN tblPayment p on p.CompanyID = pt.CompanyID 
		AND p.AccountID = pt.AccountID
		AND p.PaymentDate = pt.PaymentDate
		AND p.Amount = pt.Amount
		AND p.PaymentType = pt.PaymentType
		AND p.Recall = 0
	WHERE pt.CompanyID = p_CompanyID
		AND pt.ProcessID = p_ProcessID;
	

	
	IF (SELECT COUNT(*) FROM tmp_error_) > 0
	THEN
	SELECT GROUP_CONCAT(ErrorMessage SEPARATOR "\r\n" ) as ErrorMessage FROM	tmp_error_;
		
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for view RMBilling4.vwAccountInvoicePayments
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwAccountInvoicePayments` (
	`AccountName` VARCHAR(100) NULL COLLATE 'utf8_unicode_ci',
	`InvoicesIn` DECIMAL(40,6) NOT NULL,
	`InvoicesOut` DECIMAL(40,6) NOT NULL,
	`PaymentsIn` DECIMAL(40,8) NOT NULL,
	`PaymentsOut` DECIMAL(40,8) NOT NULL,
	`Balance` DECIMAL(45,8) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view RMBilling4.vwOldRMBillingRevenueByAccountManager
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vwOldRMBillingRevenueByAccountManager` (
	`AccountManager` VARCHAR(101) NULL COLLATE 'utf8_unicode_ci',
	`AccountName` VARCHAR(100) NULL COLLATE 'utf8_unicode_ci',
	`TotalRevenue` DECIMAL(40,6) NULL,
	`Week` INT(2) NULL,
	`Year` INT(4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view RMBilling4.vwAccountInvoicePayments
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwAccountInvoicePayments`;
CREATE ALGORITHM=UNDEFINED DEFINER=`neon-user`@`104.47.140.143` SQL SECURITY DEFINER VIEW `vwAccountInvoicePayments` AS select `a`.`AccountName` AS `AccountName`,ifnull((select sum(coalesce(`i`.`GrandTotal`,0)) from `RMBilling4`.`tblInvoice` `i` where ((`i`.`InvoiceType` = 2) and (`a`.`AccountID` = `i`.`AccountID`) and ((coalesce(`i`.`InvoiceStatus`,'') = '') or (`i`.`InvoiceStatus` not in ('cancel','draft','awaiting'))))),0) AS `InvoicesIn`,ifnull((select sum(coalesce(`i`.`GrandTotal`,0)) from `RMBilling4`.`tblInvoice` `i` where ((`i`.`InvoiceType` = 1) and (`a`.`AccountID` = `i`.`AccountID`) and ((coalesce(`i`.`InvoiceStatus`,'') = '') or (`i`.`InvoiceStatus` not in ('cancel','draft','awaiting'))))),0) AS `InvoicesOut`,ifnull((select sum(coalesce(`p`.`Amount`,0)) from `RMBilling4`.`tblPayment` `p` where ((`p`.`PaymentType` = 'Payment In') and (`a`.`AccountID` = `p`.`AccountID`) and (`p`.`Recall` = 0) and (`p`.`Status` = 'Approved'))),0) AS `PaymentsIn`,ifnull((select sum(coalesce(`p`.`Amount`,0)) from `RMBilling4`.`tblPayment` `p` where ((`p`.`PaymentType` = 'Payment Out') and (`a`.`AccountID` = `p`.`AccountID`) and (`p`.`Recall` = 0) and (`p`.`Status` = 'Approved'))),0) AS `PaymentsOut`,(((ifnull((select sum(coalesce(`i`.`GrandTotal`,0)) from `RMBilling4`.`tblInvoice` `i` where ((`i`.`InvoiceType` = 1) and (`a`.`AccountID` = `i`.`AccountID`) and ((coalesce(`i`.`InvoiceStatus`,'') = '') or (`i`.`InvoiceStatus` not in ('cancel','draft','awaiting'))))),0) - ifnull((select sum(coalesce(`p`.`Amount`,0)) from `RMBilling4`.`tblPayment` `p` where ((`p`.`PaymentType` = 'Payment In') and (`a`.`AccountID` = `p`.`AccountID`) and (`p`.`Recall` = 0) and (`p`.`Status` = 'Approved'))),0)) - ifnull((select sum(coalesce(`i`.`GrandTotal`,0)) from `RMBilling4`.`tblInvoice` `i` where ((`i`.`InvoiceType` = 2) and (`a`.`AccountID` = `i`.`AccountID`) and ((coalesce(`i`.`InvoiceStatus`,'') = '') or (`i`.`InvoiceStatus` not in ('cancel','draft','awaiting'))))),0)) + ifnull((select sum(coalesce(`p`.`Amount`,0)) from `RMBilling4`.`tblPayment` `p` where ((`p`.`PaymentType` = 'Payment Out') and (`a`.`AccountID` = `p`.`AccountID`) and (`p`.`Recall` = 0) and (`p`.`Status` = 'Approved'))),0)) AS `Balance` from `RateManagement4`.`tblAccount` `a` where ((`a`.`Status` = 1) and (`a`.`AccountType` = 1));


-- Dumping structure for view RMBilling4.vwOldRMBillingRevenueByAccountManager
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vwOldRMBillingRevenueByAccountManager`;
CREATE ALGORITHM=UNDEFINED DEFINER=`neon-user`@`104.47.140.143` SQL SECURITY DEFINER VIEW `vwOldRMBillingRevenueByAccountManager` AS select concat(max(`u`.`FirstName`),' ',max(`u`.`LastName`)) AS `AccountManager`,`a`.`AccountName` AS `AccountName`,sum(`inv`.`GrandTotal`) AS `TotalRevenue`,week(`inv`.`IssueDate`,0) AS `Week`,year(`inv`.`IssueDate`) AS `Year` from ((`RateManagement4`.`tblAccount` `a` left join `RMBilling4`.`tblInvoice` `inv` on(((`inv`.`AccountID` = `a`.`AccountID`) and (`inv`.`InvoiceType` = 1) and (`inv`.`IssueDate` >= date_format((now() + interval -(12) week),'%Y%m%d'))))) join `RateManagement4`.`tblUser` `u` on((`a`.`Owner` = `u`.`UserID`))) where ((`a`.`AccountType` = 1) and (`a`.`IsCustomer` = 1) and (`a`.`CompanyId` = 1) and (`a`.`Status` = 1)) group by `a`.`Owner`,`a`.`AccountName`,week(`inv`.`IssueDate`,0),year(`inv`.`IssueDate`);
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
