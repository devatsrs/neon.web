DROP TABLE IF EXISTS `tblCLIRateTable`;
CREATE TABLE IF NOT EXISTS `tblCLIRateTable` (
  `CLIRateTableID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `CLI` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RateTableID` int(11) NOT NULL,
  PRIMARY KEY (`CLIRateTableID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP PROCEDURE IF EXISTS `migrateCLI`;
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `migrateCLI`()
BEGIN

DECLARE i INT; 
DROP TEMPORARY TABLE IF EXISTS `CLIRateTable`; /*Temp table for matching ipcli account*/
CREATE TEMPORARY TABLE `CLIRateTable` (
	`CompanyID` INT NOT NULL,
  `AccountID` INT NOT NULL,
  `CLI` LONGTEXT NOT NULL
);

 

SET i = 1;
REPEAT
	INSERT INTO CLIRateTable
	SELECT CompanyID,AccountID,NeonRMDev.FnStringSplit(CustomerAuthValue, ',', i) FROM tblAccountAuthenticate WHERE CustomerAuthRule = 'CLI' AND NeonRMDev.FnStringSplit(CustomerAuthValue, ',', i) IS NOT NULL LIMIT 1;
	SET i = i + 1;
	UNTIL ROW_COUNT() = 0
END REPEAT;

INSERT INTO tblCLIRateTable (CompanyID,AccountID,CLI,RateTableID)
SELECT CompanyID,AccountID,CLI,0 FROM CLIRateTable;


END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetCrmDashboardSalesManager`;
DELIMITER //
CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardSalesManager`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_CurrencyID` INT,
	IN `p_ListType` VARCHAR(50),
	IN `p_Start` DATETIME,
	IN `p_End` DATETIME
)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Round_ INT;

	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_invoices_(
		AssignedUserText VARCHAR(100),
		AssignedUserID INT,
		Revenue decimal(18,8),
		`Year` INT,
		`Month` INT,
		`Week` INT
	);

	INSERT INTO tmp_Dashboard_invoices_
	SELECT
		CONCAT(tu.FirstName,' ',tu.LastName) AS `AssignedUserText`,
		 tu.UserID AS  AssignedUserID,
		sum(`inv`.`GrandTotal`) AS `Revenue`,
		YEAR(inv.IssueDate) AS Year,
		MONTH(inv.IssueDate) AS Month,
		WEEK(inv.IssueDate)	 AS `Week`
	FROM NeonBillingDev.tblInvoice inv
	Inner JOIN tblAccount ac 
		ON ac.AccountID = inv.AccountID 
	INNER JOIN tblUser tu 
		ON tu.UserID = ac.Owner
	WHERE
		ac.`Status` = 1
	AND tu.`Status` = 1 		
	AND (p_CurrencyID = '' OR ( p_CurrencyID !=''  AND ac.CurrencyId = p_CurrencyID))
	AND ac.CompanyID = p_CompanyID
	AND (p_OwnerID = '' OR   FIND_IN_SET(tu.UserID,p_OwnerID))
	AND `inv`.`InvoiceType` = 1
	AND `inv`.GrandTotal != 0
	AND (inv.IssueDate BETWEEN p_Start AND p_End)
	GROUP BY
		YEAR(inv.IssueDate) 
		,MONTH(inv.IssueDate)
		,WEEK(inv.IssueDate)
		,CONCAT(tu.FirstName,' ',tu.LastName)
		,tu.UserID;
			
	IF p_ListType = 'Monthly'
	THEN

		SELECT	
			td.AssignedUserText AS AssignedUserText,
			td.AssignedUserID AS AssignedUserID,
			ROUND(sum(td.Revenue),v_Round_) AS Revenue,
			v_CurrencyCode_,
			CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName,
			v_Round_ AS round_number
		FROM tmp_Dashboard_invoices_  td
		GROUP BY 
			td.Month,
			td.Year,
			td.AssignedUserID,
			td.AssignedUserText;

	END IF;

	IF p_ListType = 'Weekly'
	THEN
		SELECT	
			td.AssignedUserText AS AssignedUserText,
			td.AssignedUserID,
			ROUND(sum(td.Revenue),v_Round_) AS Revenue,
			v_CurrencyCode_,
			CONCAT(td.`Week`,'-',MAX( td.Year)) AS `Week`,
			MAX( td.Year) AS `Year`,
			td.`Week` AS `Weekday`,
			v_Round_ AS round_number
		FROM  tmp_Dashboard_invoices_  td 
		GROUP BY
			td.`Week`
			,td.AssignedUserText
			,td.AssignedUserID
		ORDER BY
			`Year`,
			`Weekday` ASC;
	END IF;

END//
DELIMITER ;

-- Dumping structure for procedure NeonRMDev.prc_GetCrmDashboardSalesUser
DROP PROCEDURE IF EXISTS `prc_GetCrmDashboardSalesUser`;
DELIMITER //
CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardSalesUser`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_CurrencyID` INT,
	IN `p_ListType` VARCHAR(50),
	IN `p_Start` DATETIME,
	IN `p_End` VARCHAR(50),
	IN `p_WeekOrMonth` INT,
	IN `p_Year` INT
)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Round_ int;

	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_user_invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_user_invoices_(
		AssignedUserText varchar(100),
		Revenue decimal(18,8)
	);

	INSERT INTO tmp_Dashboard_user_invoices_
	SELECT
		ac.AccountName  AS `AssignedUserText`,
		SUM(`inv`.`GrandTotal`) AS `Revenue`
	FROM NeonBillingDev.tblInvoice inv
	INNER JOIN  tblAccount ac 
		ON ac.AccountID = inv.AccountID
	INNER JOIN tblUser tu 
		ON tu.UserID = ac.Owner
	WHERE
		ac.`Status` = 1
	AND tu.`Status` = 1
	AND (p_CurrencyID = '' OR ( p_CurrencyID !=''  AND ac.CurrencyId = p_CurrencyID))
	AND ac.CompanyID = p_CompanyID
	AND (p_OwnerID = '' OR   FIND_IN_SET(tu.UserID,p_OwnerID))
	AND `inv`.`InvoiceType` = 1
	AND (inv.IssueDate BETWEEN p_Start AND p_End)
	AND (( p_ListType = 'Weekly' AND WEEK(inv.IssueDate) = p_WeekOrMonth AND YEAR(inv.IssueDate) = p_Year) OR ( p_ListType = 'Monthly' AND MONTH(inv.IssueDate) = p_WeekOrMonth AND YEAR(inv.IssueDate) = p_Year))
	GROUP BY
		ac.AccountName;

	SELECT	
		td.AssignedUserText,
		ROUND(td.Revenue,v_Round_) AS Revenue,
		v_CurrencyCode_ AS CurrencyCode,
		v_Round_ AS round_number
	FROM  tmp_Dashboard_user_invoices_  td;

END//
DELIMITER ;

-- Dumping structure for procedure NeonRMDev.prc_getCustomerInboundRate
DROP PROCEDURE IF EXISTS `prc_getCustomerInboundRate`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getCustomerInboundRate`(
	IN `p_AccountID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_CLD` VARCHAR(500)
)
BEGIN

	DECLARE v_inboundratetableid_ INT;

	IF p_CLD != ''
	THEN

		SELECT
			RateTableID INTO v_inboundratetableid_
		FROM tblCLIRateTable
		WHERE AccountID = p_AccountID AND CLI = p_CLD;

	ELSE

		SELECT
			InboudRateTableID INTO v_inboundratetableid_
		FROM tblAccount
		WHERE AccountID = p_AccountID;

	END IF;
	
	IF p_RateCDR = 1
	THEN 

		DROP TEMPORARY TABLE IF EXISTS tmp_inboundcodes_;
		CREATE TEMPORARY TABLE tmp_inboundcodes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			INDEX tmp_inboundcodes_RateID (`RateID`),
			INDEX tmp_inboundcodes_Code (`Code`)
		);
		INSERT INTO tmp_inboundcodes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblRateTableRate.Rate,
			tblRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1,
			tblRateTableRate.IntervalN
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		WHERE RateTableID = v_inboundratetableid_
		AND tblRateTableRate.EffectiveDate <= NOW();

		/* if Specify Rate is set when cdr rerate */
		IF p_RateMethod = 'SpecifyRate'
		THEN

			UPDATE tmp_inboundcodes_ SET Rate=p_SpecifyRate;

		END IF;

	END IF;
END//
DELIMITER ;