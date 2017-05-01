USE `RMBilling3`;

ALTER TABLE `tblAccountOneOffCharge`
  ADD COLUMN `ServiceID` int(11) NULL DEFAULT '0';

ALTER TABLE `tblAccountSubscription`
    ADD COLUMN `ServiceID` int(11) NULL DEFAULT '0';

ALTER TABLE `tblGatewayAccount`
  ADD COLUMN `ServiceID` int(11) NULL DEFAULT '0';

ALTER TABLE `tblInvoice`
    ADD COLUMN `ServiceID` int(11) NULL DEFAULT '0';

ALTER TABLE `tblInvoiceDetail`
  ADD COLUMN `ServiceID` int(11) NULL DEFAULT '0';

ALTER TABLE `tblInvoiceTemplate`
  ADD COLUMN `InvoiceTo` longtext NULL
  , ADD COLUMN `ServiceSplit` int(11) NULL DEFAULT '0'
  , ADD COLUMN `UsageColumn` longtext NULL
  , ADD COLUMN `GroupByService` INT NULL DEFAULT '0'
  , ADD COLUMN `CDRType` INT(11) NULL DEFAULT '0';    
  
DROP TABLE `tblUsageDaily`;

DROP TABLE `tblUsageHourly`;

DROP FUNCTION IF EXISTS `FnGetInvoiceNumber`;

DELIMITER |
CREATE FUNCTION `FnGetInvoiceNumber`(
	`p_account_id` INT,
	`p_BillingClassID` INT
) RETURNS int(11)
    NO SQL
    DETERMINISTIC
    COMMENT 'Return Next Invoice Number'
BEGIN
	DECLARE v_LastInv VARCHAR(50);
	DECLARE v_FoundVal INT(11);
	DECLARE v_InvoiceTemplateID INT(11);

	SET v_InvoiceTemplateID =
	CASE WHEN p_BillingClassID=0
	THEN (
		SELECT 
			b.InvoiceTemplateID 
		FROM Ratemanagement3.tblAccountBilling ab
		INNER JOIN Ratemanagement3.tblBillingClass b
			ON b.BillingClassID = ab.BillingClassID
		WHERE AccountID = p_AccountID AND ServiceID = 0
	)
	ELSE (
		SELECT b.InvoiceTemplateID
		FROM  Ratemanagement3.tblBillingClass b
		WHERE b.BillingClassID = p_BillingClassID
	) END;

	SELECT IF(LastInvoiceNumber=0,InvoiceStartNumber,LastInvoiceNumber) INTO v_LastInv FROM tblInvoiceTemplate WHERE InvoiceTemplateID =v_InvoiceTemplateID;

	-- select count(*) as total_res from tblInvoice where FnGetIntegerString(InvoiceNumber) = '64123';

	SET v_FoundVal = (SELECT COUNT(*) AS total_res FROM tblInvoice WHERE CompanyID = p_CompanyID AND InvoiceNumber=v_LastInv);
	IF v_FoundVal>=1 THEN
	WHILE v_FoundVal>0 DO
		SET v_LastInv = v_LastInv+1;
		SET v_FoundVal = (SELECT COUNT(*) AS total_res FROM tblInvoice WHERE CompanyID = p_CompanyID AND InvoiceNumber=v_LastInv);
	END WHILE;
	END IF;

RETURN v_LastInv;
END|
DELIMITER ;

DROP FUNCTION IF EXISTS `fnGetRoundingPoint`;

DELIMITER |
CREATE FUNCTION `fnGetRoundingPoint`(
	`p_CompanyID` INT
) RETURNS int(11)
BEGIN

DECLARE v_Round_ int;

SELECT cs.Value INTO v_Round_ FROM Ratemanagement3.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID AND cs.Value <> '';

SET v_Round_ = IFNULL(v_Round_,2);

RETURN v_Round_;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnServiceUsageDetail`;

DELIMITER |
CREATE PROCEDURE `fnServiceUsageDetail`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_ServiceID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_billing_time` INT
)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetails_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetails_(
			AccountID int,
			ServiceID int,
			AccountName varchar(100),
			GatewayAccountID varchar(100),
			trunk varchar(50),
			area_prefix varchar(50),
			pincode VARCHAR(50),
			extension VARCHAR(50),
			UsageDetailID int,
			duration int,
			billed_duration int,
			billed_second int,
			cli varchar(500),
			cld varchar(500),
			cost decimal(18,6),
			connect_time datetime,
			disconnect_time datetime,
			is_inbound tinyint(1) default 0,
			ID INT
	);
	INSERT INTO tmp_tblUsageDetails_
	SELECT
	*
	FROM (
		SELECT
			uh.AccountID,
			uh.ServiceID,
			a.AccountName,
			uh.GatewayAccountID,
			trunk,
			area_prefix,
			pincode,
			extension,
			UsageDetailID,
			duration,
			billed_duration,
			billed_second,
			cli,
			cld,
			cost,
			connect_time,
			disconnect_time,
			ud.is_inbound,
			ud.ID
		FROM RMCDR3.tblUsageDetails  ud
		INNER JOIN RMCDR3.tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		LEFT JOIN Ratemanagement3.tblAccountBilling ab
			ON ab.AccountID = a.AccountID AND ab.ServiceID = uh.ServiceID
		WHERE  StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
			AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
			AND uh.CompanyID = p_CompanyID
			AND uh.AccountID is not null
			AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
			AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
			AND ( (p_ServiceID = 0 AND ab.ServiceID IS NULL) OR  ab.ServiceID = p_ServiceID)
	) tbl
	WHERE 
	(p_billing_time =1 and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnVendorUsageDetail`;

DELIMITER |
CREATE PROCEDURE `fnVendorUsageDetail`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_billing_time` INT,
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_ZeroValueBuyingCost` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorUsageDetails_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblVendorUsageDetails_(
		AccountID INT,
		AccountName VARCHAR(50),
		trunk VARCHAR(50),
		area_prefix VARCHAR(50),
		VendorCDRID INT,
		billed_duration INT,
		cli VARCHAR(500),
		cld VARCHAR(500),
		selling_cost DECIMAL(18,6),
		buying_cost DECIMAL(18,6),
		connect_time DATETIME,
		disconnect_time DATETIME
	);
	INSERT INTO tmp_tblVendorUsageDetails_
	SELECT
	*
	FROM (
		SELECT
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
		FROM RMCDR3.tblVendorCDR  ud
		INNER JOIN RMCDR3.tblVendorCDRHeader uh
			ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
		AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
		AND uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
		AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
		AND (p_ZeroValueBuyingCost = 0 OR ( p_ZeroValueBuyingCost = 1 AND buying_cost = 0) OR ( p_ZeroValueBuyingCost = 2 AND buying_cost > 0))
	) tbl
	WHERE 
	(p_billing_time =1 AND connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 AND disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0
	ORDER BY disconnect_time DESC;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_ApplyAuthRule`;
DELIMITER |
CREATE PROCEDURE `prc_ApplyAuthRule`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ServiceID` INT
)
BEGIN
	DECLARE p_NameFormat VARCHAR(10);
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AuthenticateRules_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET p_NameFormat = ( SELECT AuthRule FROM tmp_AuthenticateRules_  WHERE RowNo = v_pointer_ );

		IF  p_NameFormat = 'NAMENUB'
		THEN
	
			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM Ratemanagement3.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND CONCAT(a.AccountName , '-' , a.Number) = ga.AccountName
			LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND a.CompanyId = p_CompanyID
			AND a.Status = 1
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAMENUB' OR aa.VendorAuthRule ='NAMENUB' )) OR
				aa.AccountID IS NULL
				);

		END IF;

		IF p_NameFormat = 'NUBNAME'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM Ratemanagement3.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND CONCAT(a.Number, '-' , a.AccountName) = ga.AccountName
			LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND a.CompanyId = p_CompanyID
			AND a.Status = 1
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NUBNAME' OR aa.VendorAuthRule ='NUBNAME' )) OR
					aa.AccountID IS NULL
				);

		END IF;

		IF p_NameFormat = 'NUB'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM Ratemanagement3.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND a.Number = ga.AccountName
			LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID	
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND a.CompanyId = p_CompanyID
			AND a.Status = 1
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NUB' OR aa.VendorAuthRule ='NUB' )) OR
					aa.AccountID IS NULL
				);

		END IF;

		IF p_NameFormat = 'IP'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				aa.ServiceID,
				a.AccountName
			FROM Ratemanagement3.tblAccount  a
			INNER JOIN Ratemanagement3.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'IP' OR aa.VendorAuthRule ='IP')
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId 
				AND ga.ServiceID = p_ServiceID AND aa.ServiceID = ga.ServiceID 
				AND ( FIND_IN_SET(ga.AccountName,aa.CustomerAuthValue) != 0 OR FIND_IN_SET(ga.AccountName,aa.VendorAuthValue) != 0 )
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID;

		END IF;

		IF p_NameFormat = 'CLI'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				aa.ServiceID,
				a.AccountName
			FROM Ratemanagement3.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId 
			INNER JOIN Ratemanagement3.tblCLIRateTable aa
				ON a.AccountID = aa.AccountID
				AND ga.ServiceID = p_ServiceID AND aa.ServiceID = ga.ServiceID 
				AND ga.AccountName = aa.CLI
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID;

		END IF;

		IF p_NameFormat = '' OR p_NameFormat IS NULL OR p_NameFormat = 'NAME'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM Ratemanagement3.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId 
				AND a.AccountName = ga.AccountName
			LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID 
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1			
			AND ga.ServiceID = p_ServiceID 
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAME' OR aa.VendorAuthRule ='NAME' )) OR
					aa.AccountID IS NULL
				);

		END IF;

		IF p_NameFormat = 'Other'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				aa.ServiceID,
				a.AccountName
			FROM Ratemanagement3.tblAccount  a
			INNER JOIN Ratemanagement3.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'Other' OR aa.VendorAuthRule ='Other')
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND ga.ServiceID = aa.ServiceID 
				AND ( aa.VendorAuthValue = ga.AccountName OR aa.CustomerAuthValue = ga.AccountName )
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID;

		END IF;

		SET v_pointer_ = v_pointer_ + 1;

	END WHILE;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_Convert_Invoices_to_Estimates`;

DELIMITER |
CREATE PROCEDURE `prc_Convert_Invoices_to_Estimates`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` VARCHAR(50),
	IN `p_EstimateNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_EstimateStatus` VARCHAR(50),
	IN `p_EstimateID` VARCHAR(50),
	IN `p_convert_all` INT
)
    COMMENT 'test'
BEGIN
	DECLARE estimate_ids int;
	DECLARE note_text varchar(50);
 	SET sql_mode = 'ALLOW_INVALID_DATES'; 	
	set note_text = 'Created From Estimate: ';
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
update tblInvoice  set EstimateID = '';
INSERT INTO tblInvoice (`CompanyID`, `AccountID`, `Address`, `InvoiceNumber`, `IssueDate`, `CurrencyID`, `PONumber`, `InvoiceType`, `SubTotal`, `TotalDiscount`, `TaxRateID`, `TotalTax`, `InvoiceTotal`, `GrandTotal`, `Description`, `Attachment`, `Note`, `Terms`, `InvoiceStatus`, `PDF`, `UsagePath`, `PreviousBalance`, `TotalDue`, `Payment`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ItemInvoice`, `FooterTerm`,EstimateID)
 	select te.CompanyID,
	 		 te.AccountID,
			 te.Address,
			 FNGetInvoiceNumber(p_CompanyID,te.AccountID,0) as InvoiceNumber,
			 DATE(NOW()) as IssueDate,
			 te.CurrencyID,
			 te.PONumber,
			 1 as InvoiceType,
			 te.SubTotal,
			 te.TotalDiscount,
			 te.TaxRateID,
			 te.TotalTax,
			 te.EstimateTotal,
			 te.GrandTotal,
			 te.Description,
			 te.Attachment,
			 te.Note,
			 te.Terms,
			 'awaiting' as InvoiceStatus,
			 te.PDF,
			 '' as UsagePath, 
			 0 as PreviousBalance,
			 0 as TotalDue,
			 0 as Payment,
			 te.CreatedBy,
			 '' as ModifiedBy,
			NOW() as created_at,
			NOW() as updated_at,
			1 as ItemInvoice,
			te.FooterTerm,
			te.EstimateID			
			from tblEstimate te		
			where
			(p_convert_all=0 and te.EstimateID = p_EstimateID)
			OR
			(p_EstimateID = '' and p_convert_all =1 and (te.CompanyID = p_CompanyID)		
			AND (p_AccountID = '' OR ( p_AccountID != '' AND te.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND te.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND te.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND te.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND te.EstimateStatus = p_EstimateStatus)) );
			

 select 	InvoiceID FROM tblInvoice inv
INNER JOIN tblEstimate ti ON  inv.EstimateID =  ti.EstimateID
where (p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR
		(p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))		
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus)) );
        
		INSERT INTO tblInvoiceDetail ( `InvoiceID`, `ProductID`, `Description`, `StartDate`, `EndDate`, `Price`, `Qty`, `Discount`, `TaxRateID`,`TaxRateID2`, `TaxAmount`, `LineTotal`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ProductType`)
			select 
				inv.InvoiceID,
				ted.ProductID,
				ted.Description,
				'' as StartDate,
				'' as EndDate,
				ted.Price,
				ted.Qty,
				ted.Discount,
				ted.TaxRateID,
				ted.TaxRateID2,
				ted.TaxAmount,
				ted.LineTotal,
				ted.CreatedBy,
				ted.ModifiedBy,	
				ted.created_at,	
				NOW() as updated_at,
				ted.ProductType	
from tblEstimateDetail ted
INNER JOIN tblInvoice inv ON  inv.EstimateID = ted.EstimateID
INNER JOIN tblEstimate ti ON  ti.EstimateID = ted.EstimateID
where	 
		 (p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR	(p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)	
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))			
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus)));
			
	INSERT INTO tblInvoiceTaxRate ( `InvoiceID`, `TaxRateID`, `TaxAmount`,`InvoiceTaxType`,`Title`, `CreatedBy`,`ModifiedBy`)
	SELECT 
		inv.InvoiceID,
		ted.TaxRateID,
		ted.TaxAmount,
		ted.EstimateTaxType,
		ted.Title,
		ted.CreatedBy,
		ted.ModifiedBy
	FROM tblEstimateTaxRate ted
	INNER JOIN tblInvoice inv ON  inv.EstimateID = ted.EstimateID
	INNER JOIN tblEstimate ti ON  ti.EstimateID = ted.EstimateID
	WHERE	(p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR	(
			p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)	
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))			
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus))
		);
		
insert into tblInvoiceLog (InvoiceID,Note,InvoiceLogStatus,created_at)
select inv.InvoiceID,concat(note_text, CONCAT(LTRIM(RTRIM(IFNULL(it.EstimateNumberPrefix,''))), LTRIM(RTRIM(ti.EstimateNumber)))) as Note,1 as InvoiceLogStatus,NOW() as created_at  FROM tblInvoice inv
INNER JOIN tblEstimate ti ON  inv.EstimateID =  ti.EstimateID
INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = ac.AccountID AND ab.ServiceID = 0
INNER JOIN Ratemanagement3.tblBillingClass b ON ab.BillingClassID = b.BillingClassID
LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
where
			(p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR	(p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)	
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))		
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus)));
		
		
update tblEstimate te set te.EstimateStatus='accepted'
where  
			(p_convert_all=0 and te.EstimateID = p_EstimateID)
			OR
			(p_EstimateID = '' and p_convert_all =1 and (te.CompanyID = p_CompanyID)	
			AND (p_AccountID = '' OR ( p_AccountID != '' AND te.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND te.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND te.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND te.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND te.EstimateStatus = p_EstimateStatus)));
	
	UPDATE tblInvoice 
	INNER JOIN tblEstimate ON  tblInvoice.EstimateID =  tblEstimate.EstimateID
	INNER JOIN Ratemanagement3.tblAccount ON tblAccount.AccountID = tblInvoice.AccountID
	INNER JOIN Ratemanagement3.tblAccountBilling ON tblAccount.AccountID = tblAccountBilling.AccountID AND tblAccountBilling.ServiceID = 0
	INNER JOIN Ratemanagement3.tblBillingClass ON tblAccountBilling.BillingClassID = tblBillingClass.BillingClassID
	INNER JOIN tblInvoiceTemplate ON tblBillingClass.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
	SET FullInvoiceNumber = IF(InvoiceType=1,CONCAT(ltrim(rtrim(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), ltrim(rtrim(tblInvoice.InvoiceNumber))),ltrim(rtrim(tblInvoice.InvoiceNumber)))
	WHERE FullInvoiceNumber IS NULL AND tblInvoice.CompanyID = p_CompanyID AND tblInvoice.InvoiceType = 1;
			
				SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_CreateInvoiceFromRecurringInvoice`;
DELIMITER |
CREATE PROCEDURE `prc_CreateInvoiceFromRecurringInvoice`(
	IN `p_CompanyID` INT,
	IN `p_InvoiceIDs` VARCHAR(200),
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_LogStatus` INT,
	IN `p_ProsessID` VARCHAR(50),
	IN `p_CurrentDate` DATETIME
)
    COMMENT 'test'
BEGIN
	DECLARE v_Note VARCHAR(100);
	DECLARE v_Check int;
	DECLARE v_SkippedWIthDate VARCHAR(200);
	DECLARE v_SkippedWIthOccurence VARCHAR(200);	
	DECLARE v_Message VARCHAR(200);
	DECLARE v_InvoiceID int;
    
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_Note = CONCAT('Recurring Invoice Generated by ',p_ModifiedBy,' ');
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		CompanyID int,
		Title varchar(50),
		AccountID int,
		Address varchar(500),
		InvoiceNumber varchar(30),
		IssueDate datetime,
		CurrencyID int,
		PONumber varchar(30),
		InvoiceType int,
		SubTotal decimal(18,6),
		TotalDiscount decimal(18,6),
		TaxRateID int,
		TotalTax decimal(18,6),
		RecurringInvoiceTotal decimal(18,6),
		GrandTotal decimal(18,6),
		Description varchar(100),
		Attachment varchar(200),
		Note  longtext,
		Terms longtext,
		InvoiceStatus varchar(50),
		PDF varchar(500),
		UsagePath varchar(500),
		PreviousBalance decimal(18,6),
		TotalDue decimal(18,6),
		Payment decimal(18,6),
		CreatedBy varchar(50),
		ModifiedBy varchar(50),
		created_at datetime,
		updated_at datetime,
		ItemInvoice tinyint(3),
		FooterTerm longtext,
		RecurringInvoiceID int,
		ProsessID varchar(50),
		NextInvoiceDate datetime,
		Occurrence int,
		BillingClassID int
	);
	
	INSERT INTO tmp_Invoices_ /*insert invoices in temp table on the bases of filter*/
 	SELECT rinv.CompanyID,
 				rinv.Title,
	 		 rinv.AccountID,
			 rinv.Address,
			 null as InvoiceNumber,
			 DATE(p_CurrentDate) as IssueDate,
			 rinv.CurrencyID,
			 '' as PONumber,
			 1 as InvoiceType,
			 rinv.SubTotal,
			 rinv.TotalDiscount,
			 rinv.TaxRateID,
			 rinv.TotalTax,
			 rinv.RecurringInvoiceTotal,
			 rinv.GrandTotal,
			 rinv.Description,
			 rinv.Attachment,
			 rinv.Note,
			 rinv.Terms,
			 'awaiting' as InvoiceStatus,
			 rinv.PDF,
			 '' as UsagePath, 
			 0 as PreviousBalance,
			 0 as TotalDue,
			 0 as Payment,
			 rinv.CreatedBy,
			 '' as ModifiedBy,
			p_CurrentDate as created_at,
			p_CurrentDate as updated_at,
			1 as ItemInvoice,
			rinv.FooterTerm,
			rinv.RecurringInvoiceID,
			p_ProsessID,
			rinv.NextInvoiceDate,
			rinv.Occurrence,
			rinv.BillingClassID
		FROM tblRecurringInvoice rinv		
		WHERE rinv.CompanyID = p_CompanyID
		AND rinv.RecurringInvoiceID=p_InvoiceIDs;
			 
		/*Fill error message for recurring invoices with date check*/
     SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with INVOICE DATE ',DATE(temp.NextInvoiceDate)) separator '\n\r') INTO v_SkippedWIthDate
	  FROM tmp_Invoices_ temp
	  WHERE (DATE(temp.NextInvoiceDate) > DATE(p_CurrentDate));
	  
	  /*Fill error message for recurring invoices with occurrence check*/
	  SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with exceding limit Occurrence ',(SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID)) separator '\n\r') INTO v_SkippedWIthOccurence
	  FROM tmp_Invoices_ temp
	  	WHERE (temp.Occurrence > 0 
		  	AND (SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID) >= temp.Occurrence);
     
     /*return message either fill or empty*/
     SELECT CASE 
	  				WHEN ((v_SkippedWIthDate IS NOT NULL) OR (v_SkippedWIthOccurence IS NOT NULL)) 
					THEN CONCAT(IFNULL(v_SkippedWIthDate,''),'\n\r',IFNULL(v_SkippedWIthOccurence,'')) ELSE '' 
				END as message INTO v_Message;

	IF(v_Message="") THEN
        /*insert new invoices and its related detail, texes and updating logs.*/

		INSERT INTO tblInvoice (`CompanyID`, `AccountID`, `Address`, `InvoiceNumber`, `IssueDate`, `CurrencyID`, `PONumber`, `InvoiceType`, `SubTotal`, `TotalDiscount`, `TaxRateID`, `TotalTax`, `InvoiceTotal`, `GrandTotal`, `Description`, `Attachment`, `Note`, `Terms`, `InvoiceStatus`, `PDF`, `UsagePath`, `PreviousBalance`, `TotalDue`, `Payment`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ItemInvoice`, `FooterTerm`,RecurringInvoiceID,ProcessID)
	 	SELECT 
		 rinv.CompanyID,
		 rinv.AccountID,
		 rinv.Address,
		 FNGetInvoiceNumber(p_CompanyID,rinv.AccountID,rinv.BillingClassID) as InvoiceNumber,
		 DATE(p_CurrentDate) as IssueDate,
		 rinv.CurrencyID,
		 '' as PONumber,
		 1 as InvoiceType,
		 rinv.SubTotal,
		 rinv.TotalDiscount,
		 rinv.TaxRateID,
		 rinv.TotalTax,
		 rinv.RecurringInvoiceTotal,
		 rinv.GrandTotal,
		 rinv.Description,
		 rinv.Attachment,
		 rinv.Note,
		 rinv.Terms,
		 'awaiting' as InvoiceStatus,
		 rinv.PDF,
		 '' as UsagePath, 
		 0 as PreviousBalance,
		 0 as TotalDue,
		 0 as Payment,
		 rinv.CreatedBy,
		 '' as ModifiedBy,
		p_CurrentDate as created_at,
		p_CurrentDate as updated_at,
		1 as ItemInvoice,
		rinv.FooterTerm,
		rinv.RecurringInvoiceID,
		p_ProsessID
		FROM tmp_Invoices_ rinv;
		
		SET v_InvoiceID = LAST_INSERT_ID();

		INSERT INTO tblInvoiceDetail ( `InvoiceID`, `ProductID`, `Description`, `StartDate`, `EndDate`, `Price`, `Qty`, `Discount`, `TaxRateID`,`TaxRateID2`, `TaxAmount`, `LineTotal`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ProductType`)
			select 
				inv.InvoiceID,
				rinvd.ProductID,
				rinvd.Description,
				null as StartDate,
				null as EndDate,
				rinvd.Price,
				rinvd.Qty,
				rinvd.Discount,
				rinvd.TaxRateID,
				rinvd.TaxRateID2,
				rinvd.TaxAmount,
				rinvd.LineTotal,
				rinvd.CreatedBy,
				rinvd.ModifiedBy,	
				rinvd.created_at,	
				p_CurrentDate as updated_at,
				rinvd.ProductType	
				FROM tblRecurringInvoiceDetail rinvd
				INNER JOIN tblInvoice inv ON  inv.RecurringInvoiceID = rinvd.RecurringInvoiceID
				INNER JOIN tblRecurringInvoice rinv ON  rinv.RecurringInvoiceID = rinvd.RecurringInvoiceID
				WHERE rinv.CompanyID = p_CompanyID
				AND inv.InvoiceID = v_InvoiceID;
				
		INSERT INTO tblInvoiceTaxRate ( `InvoiceID`, `TaxRateID`, `TaxAmount`,`InvoiceTaxType`,`Title`, `CreatedBy`,`ModifiedBy`)
		SELECT 
			inv.InvoiceID,
			rinvt.TaxRateID,
			rinvt.TaxAmount,
			rinvt.RecurringInvoiceTaxType,
			rinvt.Title,
			rinvt.CreatedBy,
			rinvt.ModifiedBy
		FROM tblRecurringInvoiceTaxRate rinvt
		INNER JOIN tblInvoice inv ON  inv.RecurringInvoiceID = rinvt.RecurringInvoiceID
		INNER JOIN tblRecurringInvoice rinv ON  rinv.RecurringInvoiceID = rinvt.RecurringInvoiceID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;
			
		INSERT INTO tblInvoiceLog (InvoiceID,Note,InvoiceLogStatus,created_at)
		SELECT inv.InvoiceID,CONCAT(v_Note, CONCAT(LTRIM(RTRIM(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.InvoiceNumber)))) as Note,1 as InvoiceLogStatus,p_CurrentDate as created_at  
		FROM tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID		
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;
			
			/*add log for recurring invoice*/
		INSERT INTO tblRecurringInvoiceLog (RecurringInvoiceID,Note,RecurringInvoiceLogStatus,created_at)
		SELECT inv.RecurringInvoiceID,CONCAT(v_Note, CONCAT(LTRIM(RTRIM(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.InvoiceNumber)))) as Note,p_LogStatus as InvoiceLogStatus,p_CurrentDate as created_at  
		FROM tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;
		
		
		/*update full invoice number related to curring processID*/
		UPDATE tblInvoice inv 
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		SET FullInvoiceNumber = IF(inv.InvoiceType=1,CONCAT(ltrim(rtrim(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber))),ltrim(rtrim(inv.InvoiceNumber)))
		WHERE inv.CompanyID = p_CompanyID 
		AND inv.InvoiceID = v_InvoiceID;
	
	END IF;
	
	SELECT v_Message as Message, IFNULL(v_InvoiceID,0) as InvoiceID;
			
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_CreateRerateLog`;

DELIMITER |
CREATE PROCEDURE `prc_CreateRerateLog`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL
	);

	SET @stm = CONCAT('
	INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
	SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,1,  CONCAT( "Account:  " , ga.AccountName ," - Gateway: ",cg.Title," - Doesnt exist in NEON") as Message ,DATE(NOW())
	FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN tblGatewayAccount ga 
		ON ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.CompanyID = ud.CompanyID
		AND ga.GatewayAccountID = ud.GatewayAccountID
	INNER JOIN Ratemanagement3.tblCompanyGateway cg ON cg.CompanyGatewayID = ud.CompanyGatewayID
	WHERE ud.ProcessID = "' , p_processid  , '" and ud.AccountID IS NULL');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	IF p_RateCDR = 1
	THEN

		IF ( SELECT COUNT(*) FROM tmp_Service_ ) > 0
		THEN

			SET @stm = CONCAT('
			INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
			SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Service: ",IFNULL(s.ServiceName,"")," - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
			FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
			LEFT JOIN Ratemanagement3.tblService s on  s.ServiceID = ud.ServiceID
			WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 0 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');

			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;

		ELSE

			SET @stm = CONCAT('
			INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
			SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Trunk: ",ud.trunk," - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
			FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
			WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 0 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');

			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;

		END IF;

		SET @stm = CONCAT('
		INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
		SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,3,  CONCAT( "Account:  " , a.AccountName ,  " - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
		FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
		WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		SET @stm = CONCAT('
		INSERT INTO Ratemanagement3.tblTempRateLog (CompanyID,CompanyGatewayID,MessageType,Message,RateDate,SentStatus,created_at)
		SELECT rt.CompanyID,rt.CompanyGatewayID,rt.MessageType,rt.Message,rt.RateDate,0 as SentStatus,NOW() as created_at FROM tmp_tblTempRateLog_ rt
		LEFT JOIN Ratemanagement3.tblTempRateLog rt2 
			ON rt.CompanyID = rt2.CompanyID
			AND rt.CompanyGatewayID = rt2.CompanyGatewayID
			AND rt.MessageType = rt2.MessageType
			AND rt.Message = rt2.Message
			AND rt.RateDate = rt2.RateDate
		WHERE rt2.TempRateLogID IS NULL;
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	SELECT DISTINCT Message FROM tmp_tblTempRateLog_;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_CustomerPanel_getInvoice`;

DELIMITER |
CREATE PROCEDURE `prc_CustomerPanel_getInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_InvoiceType` INT,
	IN `p_IsOverdue` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_zerovalueinvoice` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_TotalCount int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		InvoiceType tinyint(1),
		AccountName varchar(100),
		InvoiceNumber varchar(100),
		IssueDate datetime,
		InvoicePeriod varchar(100),
		CurrencySymbol varchar(5),
		GrandTotal decimal(18,6),
		TotalPayment decimal(18,6),
		PendingAmount decimal(18,6),
		InvoiceStatus varchar(50),
		InvoiceID int,
		Description varchar(500),
		Attachment varchar(255),
		AccountID int,
		ItemInvoice tinyint(1),
		BillingEmail varchar(255),
		AccountNumber varchar(100),
		PaymentDueInDays int,
		PaymentDate datetime,
		SubTotal decimal(18,6),
		TotalTax decimal(18,6),
		NominalAnalysisNominalAccountNumber varchar(100)
	);
	
		INSERT INTO tmp_Invoices_
		SELECT inv.InvoiceType ,
			ac.AccountName,
			FullInvoiceNumber as InvoiceNumber,
			inv.IssueDate,
			IF(invd.StartDate IS NULL ,'',CONCAT('From ',date(invd.StartDate) ,'<br> To ',date(invd.EndDate))) as InvoicePeriod,
			IFNULL(cr.Symbol,'') as CurrencySymbol,
			inv.GrandTotal as GrandTotal,
			(SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) as TotalPayment,
			(inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
			inv.InvoiceStatus,
			inv.InvoiceID,
			inv.Description,
			inv.Attachment,
			inv.AccountID,
			inv.ItemInvoice,
			IFNULL(ac.BillingEmail,'') as BillingEmail,
			ac.Number,
			(SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1) as PaymentDueInDays,
			(SELECT PaymentDate FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID ORDER BY PaymentID DESC LIMIT 1) AS PaymentDate,
			inv.SubTotal,
			inv.TotalTax,
			ac.NominalAnalysisNominalAccountNumber
			FROM tblInvoice inv
			INNER JOIN Ratemanagement3.tblAccount ac on ac.AccountID = inv.AccountID
			LEFT JOIN tblInvoiceDetail invd on invd.InvoiceID = inv.InvoiceID AND invd.ProductType = 2
			LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
			WHERE ac.CompanyID = p_CompanyID
					AND (inv.AccountID = p_AccountID)
					AND ( (IFNULL(inv.InvoiceStatus,'') = '') OR  inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting'))
					AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
					AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
					AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
					AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
					AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));
				
	IF p_isExport = 0
	THEN	
		SELECT 
			InvoiceType ,
			AccountName,
			InvoiceNumber,
			IssueDate,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `PendingAmount`,
			InvoiceStatus,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount, 
			ItemInvoice,
			BillingEmail,
			GrandTotal
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0 
							OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
									AND(PendingAmount>0)
								)
						)
		ORDER BY
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeDESC') THEN InvoiceType
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeASC') THEN InvoiceType
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusDESC') THEN InvoiceStatus
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusASC') THEN InvoiceStatus
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN InvoiceNumber
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN InvoiceNumber
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN IssueDate
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN IssueDate
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN GrandTotal
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN GrandTotal
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDDESC') THEN InvoiceID
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDASC') THEN InvoiceID
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;


		SELECT COUNT(*) into v_TotalCount FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0 
							OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
									AND(PendingAmount>0)
								)
						);

		SELECT
			v_TotalCount AS totalcount,
			ROUND(sum(GrandTotal),v_Round_) as total_grand,
			ROUND(sum(TotalPayment),v_Round_) as `TotalPayment`, 
			ROUND(sum(PendingAmount),v_Round_) as `TotalPendingAmount`,
			v_CurrencyCode_ as currency_symbol
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0 
							OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
									AND(PendingAmount>0)
								)
						);
	END IF;
	IF p_isExport = 1
	THEN
		SELECT
			AccountName,
			InvoiceNumber,
			IssueDate,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
			InvoiceStatus,
			InvoiceType,
			ItemInvoice
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0 
						OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
								AND(PendingAmount>0)
							)
					);
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getAccountInvoiceTotal`;

DELIMITER |
CREATE PROCEDURE `prc_getAccountInvoiceTotal`(
	IN `p_AccountID` INT,
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT,
	IN `p_ServiceID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_checkDuplicate` INT,
	IN `p_InvoiceDetailID` INT
)
BEGIN 
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;

	CALL fnServiceUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_ServiceID,p_StartDate,p_EndDate,v_BillingTime_);

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

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getAccountOneOffCharge`;
DELIMITER |
CREATE PROCEDURE `prc_getAccountOneOffCharge`(
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		tblAccountOneOffCharge.*
	FROM tblAccountOneOffCharge
	INNER JOIN Ratemanagement3.tblAccountService
		ON tblAccountService.AccountID = tblAccountOneOffCharge.AccountID AND tblAccountService.ServiceID = tblAccountOneOffCharge.ServiceID 
	LEFT JOIN Ratemanagement3.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccountOneOffCharge.AccountID AND tblAccountBilling.ServiceID = tblAccountOneOffCharge.ServiceID
	WHERE tblAccountOneOffCharge.AccountID = p_AccountID
	AND tblAccountOneOffCharge.Date BETWEEN p_StartDate AND p_EndDate
	AND tblAccountService.Status = 1
	AND ( (p_ServiceID = 0 AND tblAccountBilling.ServiceID IS NULL) OR  tblAccountBilling.ServiceID = p_ServiceID);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getAccountSubscription`;
DELIMITER |
CREATE PROCEDURE `prc_getAccountSubscription`(
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		tblAccountSubscription.*
	FROM tblAccountSubscription
	INNER JOIN tblBillingSubscription
		ON tblAccountSubscription.SubscriptionID = tblBillingSubscription.SubscriptionID
	INNER JOIN Ratemanagement3.tblAccountService
		ON tblAccountService.AccountID = tblAccountSubscription.AccountID AND tblAccountService.ServiceID = tblAccountSubscription.ServiceID 
	LEFT JOIN Ratemanagement3.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccountSubscription.AccountID AND tblAccountBilling.ServiceID =  tblAccountSubscription.ServiceID
	WHERE tblAccountSubscription.AccountID = p_AccountID
	AND Status = 1
	AND ( (p_ServiceID = 0 AND tblAccountBilling.ServiceID IS NULL) OR  tblAccountBilling.ServiceID = p_ServiceID)
	ORDER BY SequenceNo ASC;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getActiveGatewayAccount`;

DELIMITER |
CREATE PROCEDURE `prc_getActiveGatewayAccount`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_NameFormat` VARCHAR(50)
)
BEGIN

	DECLARE v_NameFormat_ VARCHAR(10);
	DECLARE v_RTR_ INT;
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ;
	DECLARE v_ServiceID_ INT ;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_ActiveAccount;
	CREATE TEMPORARY TABLE tmp_ActiveAccount (
		GatewayAccountID varchar(100),
		AccountID INT,
		ServiceID INT,
		AccountName varchar(100)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_AuthenticateRules_;
	CREATE TEMPORARY TABLE tmp_AuthenticateRules_ (
		RowNo INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AuthRule VARCHAR(50)
	);

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Service_);
	/* service level authentication rule */ 
	IF v_rowCount_ > 0
	THEN

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Service_ t WHERE t.RowID = v_pointer_);
			TRUNCATE TABLE tmp_AuthenticateRules_;
			IF p_NameFormat != ''
			THEN
				INSERT INTO tmp_AuthenticateRules_  (AuthRule)
				SELECT p_NameFormat;
			END IF;

			INSERT INTO tmp_AuthenticateRules_  (AuthRule)
			SELECT DISTINCT CustomerAuthRule FROM Ratemanagement3.tblAccountAuthenticate aa WHERE CustomerAuthRule IS NOT NULL AND ServiceID = v_ServiceID_
			UNION
			SELECT DISTINCT VendorAuthRule FROM Ratemanagement3.tblAccountAuthenticate aa WHERE VendorAuthRule IS NOT NULL AND ServiceID = v_ServiceID_;

			CALL prc_ApplyAuthRule(p_CompanyID,p_CompanyGatewayID,v_ServiceID_);

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	END IF;

	/* account level authentication rule */
	IF (SELECT COUNT(*) FROM Ratemanagement3.tblAccountAuthenticate WHERE CompanyID = p_CompanyID AND ServiceID = 0) > 0
	THEN

		TRUNCATE TABLE tmp_AuthenticateRules_;
		IF p_NameFormat != ''
		THEN
			INSERT INTO tmp_AuthenticateRules_  (AuthRule)
			SELECT p_NameFormat;
		END IF;
		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT DISTINCT CustomerAuthRule FROM Ratemanagement3.tblAccountAuthenticate aa WHERE CustomerAuthRule IS NOT NULL AND ServiceID = 0
		UNION
		SELECT DISTINCT VendorAuthRule FROM Ratemanagement3.tblAccountAuthenticate aa WHERE VendorAuthRule IS NOT NULL AND ServiceID = 0;

		CALL prc_ApplyAuthRule(p_CompanyID,p_CompanyGatewayID,0);

	END IF;

	/* gateway level authentication rule */
	IF p_NameFormat = ''
	THEN
		TRUNCATE TABLE tmp_AuthenticateRules_;
		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT
			CASE WHEN Settings LIKE '%"NameFormat":"NAMENUB"%'
			THEN 'NAMENUB'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"NUBNAME"%'
			THEN 'NUBNAME'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"NUB"%'
			THEN 'NUB'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"IP"%'
			THEN 'IP'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"CLI"%'
			THEN 'CLI'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"NAME"%'
			THEN 'NAME'
			ELSE 'NAME' END END END END END END   AS  NameFormat
		FROM Ratemanagement3.tblCompanyGateway
		WHERE Settings LIKE '%NameFormat%' 
		AND CompanyGatewayID = p_CompanyGatewayID
		LIMIT 1;

	END IF;

	IF p_NameFormat != ''
	THEN
		TRUNCATE TABLE tmp_AuthenticateRules_;
		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT p_NameFormat;

	END IF;

	CALL prc_ApplyAuthRule(p_CompanyID,p_CompanyGatewayID,0);

	UPDATE tblGatewayAccount
	INNER JOIN tmp_ActiveAccount a
		ON a.GatewayAccountID = tblGatewayAccount.GatewayAccountID
		AND tblGatewayAccount.CompanyGatewayID = p_CompanyGatewayID
		AND tblGatewayAccount.ServiceID = a.ServiceID
	SET tblGatewayAccount.AccountID = a.AccountID
	WHERE tblGatewayAccount.AccountID IS NULL;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getDashboardinvoiceExpenseDrilDown`;

DELIMITER |
CREATE PROCEDURE `prc_getDashboardinvoiceExpenseDrilDown`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` VARCHAR(50),
	IN `p_EndDate` VARCHAR(50),
	IN `p_Type` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_CustomerID` INT,
	IN `p_Export` INT
)
BEGIN
	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_TotalCount int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	SELECT cr.Symbol INTO v_CurrencyCode_ FROM Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	IF p_Type = 1  -- Payment Recived
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_Payment_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Payment_(
			AccountName varchar(100),
			Amount decimal(18,6),
			PaymentDate datetime,
			CreatedBy varchar(50),
			InvoiceNo varchar(50),
			Notes varchar(500),
			AmountWithSymbol varchar(30)
		);
		
		INSERT INTO tmp_Payment_
		SELECT 
			ac.AccountName,
			ROUND(p.Amount,v_Round_) AS Amount,
			CASE WHEN inv.InvoiceID IS NOT NULL
			THEN
				inv.IssueDate
			ELSE
				p.PaymentDate
			END as PaymentDate,
			p.CreatedBy,
			p.InvoiceNo,
			p.Notes,
			CONCAT(IFNULL(v_CurrencyCode_,''),ROUND(p.Amount,v_Round_)) as AmountWithSymbol
		FROM tblPayment p 
		INNER JOIN Ratemanagement3.tblAccount ac 
			ON ac.AccountID = p.AccountID
		LEFT JOIN tblInvoice inv on p.InvoiceID = inv.InvoiceID 
			AND p.Status = 'Approved' 
			AND p.AccountID = inv.AccountID 
			AND p.Recall=0
			AND InvoiceType = 1 
		WHERE 
			p.CompanyID = p_CompanyID
			AND ac.CurrencyId = p_CurrencyID
			AND (p_CustomerID=0 OR ac.AccountID = p_CustomerID)
			AND p.Status = 'Approved'
			AND p.Recall=0
			AND p.PaymentType = 'Payment In';

		IF  p_Export = 0
		THEN
			SELECT 
				AccountName,
				InvoiceNo,
				AmountWithSymbol,
				PaymentDate,
				CreatedBy,
				Notes
			FROM tmp_Payment_
			WHERE (PaymentDate BETWEEN p_StartDate AND p_EndDate)
			ORDER BY
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
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
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateDESC') THEN PaymentDate
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateASC') THEN PaymentDate
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN CreatedBy
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN CreatedBy
					END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;
			
			
			SELECT COUNT(AccountName) AS totalcount,ROUND(COALESCE(SUM(Amount),0),v_Round_) as totalsum
			FROM tmp_Payment_
			WHERE (PaymentDate BETWEEN p_StartDate AND p_EndDate);
		END IF;
		
		IF p_Export=1
		THEN
			SELECT 
				AccountName,
				Amount,
				PaymentDate,
				CreatedBy,
				InvoiceNo,
				Notes
			FROM tmp_Payment_
			WHERE (PaymentDate BETWEEN p_StartDate AND p_EndDate); 
		END IF;
	END IF;
	
	IF p_Type=2 OR p_Type=3 -- 2. Total Invoices 3. Total OutStanding
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
			InvoiceType tinyint(1),
			AccountName varchar(100),
			InvoiceNumber varchar(100),
			IssueDate datetime,
			InvoicePeriod varchar(100),
			CurrencySymbol varchar(5),
			GrandTotal decimal(18,6),
			TotalPayment decimal(18,6),
			PendingAmount decimal(18,6),
			InvoiceStatus varchar(50),
			InvoiceID int,
			AccountID int,
			ItemInvoice tinyint(1),
			BillingEmail varchar(255),
			AccountNumber varchar(100),
			PaymentDueInDays int,
			PaymentDate datetime,
			SubTotal decimal(18,6)
		);

		INSERT INTO tmp_Invoices_
		SELECT inv.InvoiceType ,
			ac.AccountName,
			inv.FullInvoiceNumber as InvoiceNumber,
			inv.IssueDate,
			IF(invd.StartDate IS NULL ,'',CONCAT('From ',date(invd.StartDate) ,'<br> To ',date(invd.EndDate))) as InvoicePeriod,
			IFNULL(cr.Symbol,'') as CurrencySymbol,
			inv.GrandTotal as GrandTotal,
			(SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) as TotalPayment,
			(inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
			inv.InvoiceStatus,
			inv.InvoiceID,
			inv.AccountID,
			inv.ItemInvoice,
			IFNULL(ac.BillingEmail,'') as BillingEmail,
			ac.Number,
			(SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1) as PaymentDueInDays,
			(SELECT PaymentDate FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID ORDER BY PaymentID DESC LIMIT 1) AS PaymentDate,
			inv.SubTotal
		FROM tblInvoice inv
		INNER JOIN Ratemanagement3.tblAccount ac ON inv.AccountID = ac.AccountID
		AND (p_CustomerID=0 OR ac.AccountID = p_CustomerID)
		LEFT JOIN tblInvoiceDetail invd on invd.InvoiceID = inv.InvoiceID AND invd.ProductType = 2
		LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
		WHERE 
		inv.CompanyID = p_CompanyID
		AND cr.CurrencyID = p_CurrencyID
		AND (IssueDate BETWEEN p_StartDate AND p_EndDate)
		AND InvoiceType = 1
		AND ((p_Type=2 AND inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' )) OR (p_Type=3 AND inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'paid')))
		AND (GrandTotal<>0);

		IF p_Export = 0
		THEN
			SELECT 
				AccountName,
				InvoiceNumber,
				IssueDate,
				InvoicePeriod,
				CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
				CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `PendingAmount`,
				InvoiceStatus,
				InvoiceID,
				AccountID,
				PendingAmount as OutstandingAmount, 
				ItemInvoice,
				BillingEmail,
				GrandTotal
			FROM tmp_Invoices_
			ORDER BY
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
				END ASC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeDESC') THEN InvoiceType
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeASC') THEN InvoiceType
				END ASC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusDESC') THEN InvoiceStatus
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusASC') THEN InvoiceStatus
				END ASC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN InvoiceNumber
				END ASC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN InvoiceNumber
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN IssueDate
				END ASC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN IssueDate
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodASC') THEN InvoicePeriod
				END ASC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodDESC') THEN InvoicePeriod
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN GrandTotal
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN GrandTotal
				END ASC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDDESC') THEN InvoiceID
				END DESC,
				CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDASC') THEN InvoiceID
				END ASC
				LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT COUNT(*) AS totalcount,
					ROUND(COALESCE(SUM(GrandTotal),0),v_Round_) as totalsum, 
					ROUND(COALESCE(SUM(TotalPayment),0),v_Round_) totalpaymentsum,
					ROUND(COALESCE(SUM(PendingAmount),0),v_Round_) totalpendingsum,
					v_CurrencyCode_ as currencySymbol
			FROM tmp_Invoices_;
				

		END IF;
		IF p_Export=1
		THEN
			SELECT 
				AccountName ,
				InvoiceNumber,
				IssueDate,
				REPLACE(InvoicePeriod, '<br>', '') as InvoicePeriod,
				CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal,
				CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
				InvoiceStatus,
				InvoiceType,
				ItemInvoice
		FROM tmp_Invoices_;
		END IF;
	END IF;
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getDashboardinvoiceExpenseTotalOutstanding`;

DELIMITER |
CREATE PROCEDURE `prc_getDashboardinvoiceExpenseTotalOutstanding`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` VARCHAR(50),
	IN `p_EndDate` VARCHAR(50)
)
BEGIN

	DECLARE v_Round_ INT;
	
	DECLARE v_TotalInvoiceIn_ DECIMAL(18,6);
	DECLARE v_TotalInvoiceOut_ DECIMAL(18,6);
	DECLARE v_TotalPaymentIn_ DECIMAL(18,6);
	DECLARE v_TotalPaymentOut_ DECIMAL(18,6);
	DECLARE v_TotalOutstanding_ DECIMAL(18,6);	
	DECLARE v_Outstanding_ DECIMAL(18,6);

	DECLARE v_InvoiceSentTotal_ DECIMAL(18,6);
	DECLARE v_InvoiceRecvTotal_ DECIMAL(18,6);
	DECLARE v_PaymentSentTotal_ DECIMAL(18,6);
	DECLARE v_PaymentRecvTotal_ DECIMAL(18,6);

	DECLARE v_TotalUnpaidInvoices_ DECIMAL(18,6);
	DECLARE v_TotalOverdueInvoices_ DECIMAL(18,6);
	DECLARE v_TotalPaidInvoices_ DECIMAL(18,6);
	DECLARE v_TotalDispute_ DECIMAL(18,6);
	DECLARE v_TotalEstimate_ DECIMAL(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		InvoiceType TINYINT(1),
		IssueDate DATETIME,
		GrandTotal DECIMAL(18,6),
		InvoiceStatus VARCHAR(50),
		PaymentDueInDays INT,
		PendingAmount DECIMAL(18,6),
		AccountID INT
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Payment_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Payment_(
		PaymentAmount DECIMAL(18,6),
		PaymentDate DATETIME,
		PaymentType VARCHAR(50)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Dispute_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dispute_(
		DisputeAmount DECIMAL(18,6)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Estimate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Estimate_(
		EstimateTotal DECIMAL(18,6)
	);

	/* all disputes with pending status*/
	INSERT INTO tmp_Dispute_
	SELECT 
		ds.DisputeAmount 
	FROM tblDispute ds
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON ac.AccountID = ds.AccountID
	WHERE ds.CompanyID = p_CompanyID
	AND ac.CurrencyId = p_CurrencyID
	AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
	AND ds.Status = 0
	AND ((p_EndDate = '0' AND fnGetMonthDifference(ds.created_at,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND ds.created_at between p_StartDate AND p_EndDate));

	/* all estimates with are pending to conevert invoice*/
	INSERT INTO tmp_Estimate_
	SELECT 
		es.GrandTotal 
	FROM tblEstimate es
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON ac.AccountID = es.AccountID
	WHERE es.CompanyID = p_CompanyID
	AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
	AND ac.CurrencyId = p_CurrencyID
	AND es.EstimateStatus NOT IN ('draft','accepted','rejected')
	AND ((p_EndDate = '0' AND fnGetMonthDifference(es.IssueDate,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND es.IssueDate between p_StartDate AND p_EndDate));
	
	/* all invoice sent and recived*/
	INSERT INTO tmp_Invoices_
	SELECT 
		inv.InvoiceType,
		inv.IssueDate,
		inv.GrandTotal,
		inv.InvoiceStatus,
		(SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1 ) as PaymentDueInDays,
		(inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
		ac.AccountID
	FROM tblInvoice inv
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON ac.AccountID = inv.AccountID 
		AND inv.CompanyID = p_CompanyID
		AND inv.CurrencyID = p_CurrencyID
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
		AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft') )  )
		AND ((p_EndDate = '0' AND fnGetMonthDifference(IssueDate,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND IssueDate BETWEEN p_StartDate AND p_EndDate));

	/* all payments recevied and sent*/
	INSERT INTO tmp_Payment_
	SELECT 
		p.Amount,
		p.PaymentDate,
		p.PaymentType
		FROM tblPayment p 
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON ac.AccountID = p.AccountID
	WHERE 
		p.CompanyID = p_CompanyID
		AND ac.CurrencyId = p_CurrencyID
		AND p.Status = 'Approved'
		AND p.Recall=0
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
		AND (
			(p_EndDate = '0' AND fnGetMonthDifference(PaymentDate,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND PaymentDate between p_StartDate AND p_EndDate)
			);

	
	/* total outstanding 
	SELECT 
		SUM(IF(InvoiceType=1,GrandTotal,0)),
		SUM(IF(InvoiceType=2,GrandTotal,0)) INTO v_TotalInvoiceOut_,v_TotalInvoiceIn_
	FROM tmp_Invoices_;
	
	SELECT 
		SUM(IF(PaymentType='Payment In',PaymentAmount,0)),
		SUM(IF(PaymentType='Payment Out',PaymentAmount,0)) INTO v_TotalPaymentIn_,v_TotalPaymentOut_
	FROM tmp_Payment_;
	
	SELECT (IFNULL(v_TotalInvoiceOut_,0) - IFNULL(v_TotalPaymentIn_,0)) - (IFNULL(v_TotalInvoiceIn_,0) - IFNULL(v_TotalPaymentOut_,0)) INTO v_TotalOutstanding_;*/
	
	/* outstanding */
	SELECT 
		SUM(IF(InvoiceType=1,GrandTotal,0)),
		SUM(IF(InvoiceType=2,GrandTotal,0)) INTO v_TotalInvoiceOut_,v_TotalInvoiceIn_
	FROM tmp_Invoices_;
	
	SELECT 
		SUM(IF(PaymentType='Payment In',PaymentAmount,0)),
		SUM(IF(PaymentType='Payment Out',PaymentAmount,0)) INTO v_TotalPaymentIn_,v_TotalPaymentOut_
	FROM tmp_Payment_;

	SELECT (IFNULL(v_TotalInvoiceOut_,0) - IFNULL(v_TotalPaymentIn_,0)) - (IFNULL(v_TotalInvoiceIn_,0) - IFNULL(v_TotalPaymentOut_,0)) INTO v_Outstanding_;
	
	/* Invoice Sent Total */
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_InvoiceSentTotal_
	FROM tmp_Invoices_ 
	WHERE InvoiceType = 1;

	/* Invoice Received Total*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_InvoiceRecvTotal_
	FROM tmp_Invoices_ 
	WHERE  InvoiceType = 2;
	
	/* Payment Received */
	SELECT IFNULL(SUM(PaymentAmount),0) INTO v_PaymentRecvTotal_
	FROM tmp_Payment_ p
	WHERE p.PaymentType = 'Payment In';
	
	/* Payment Sent */
	SELECT IFNULL(SUM(PaymentAmount),0) INTO v_PaymentSentTotal_
	FROM tmp_Payment_ p
	WHERE p.PaymentType = 'Payment Out';
	
	/*Total Unpaid Invoices*/	
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalUnpaidInvoices_
	FROM tmp_Invoices_ 
	WHERE InvoiceType = 1
	AND InvoiceStatus <> 'paid' 
	AND PendingAmount > 0;
		
	/*Total Overdue Invoices*/	
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalOverdueInvoices_
	FROM tmp_Invoices_ 
	WHERE ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting'))
							AND(PendingAmount>0)
						);
	/*Total Paid Invoices*/	
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalPaidInvoices_
	FROM tmp_Invoices_ 
	WHERE (InvoiceStatus IN('Paid') AND (PendingAmount=0));
	
	/*Total Dispute*/	
	SELECT IFNULL(SUM(DisputeAmount),0) INTO v_TotalDispute_
	FROM tmp_Dispute_;
	
	/*Total Estimate*/
	SELECT IFNULL(SUM(EstimateTotal),0) INTO v_TotalEstimate_
	FROM tmp_Estimate_;
	
	SELECT 
			/*ROUND(v_TotalOutstanding_,v_Round_) AS TotalOutstanding,*/
			ROUND(v_Outstanding_,v_Round_) AS Outstanding,
			ROUND(v_PaymentRecvTotal_,v_Round_) AS TotalPaymentsIn,
			ROUND(v_PaymentSentTotal_,v_Round_) AS TotalPaymentsOut,
			ROUND(v_InvoiceRecvTotal_,v_Round_) AS TotalInvoiceIn,
			ROUND(v_InvoiceSentTotal_,v_Round_) AS TotalInvoiceOut,
			ROUND(v_TotalUnpaidInvoices_,v_Round_) as TotalDueAmount,
			ROUND(v_TotalOverdueInvoices_,v_Round_) as TotalOverdueAmount,
			ROUND(v_TotalPaidInvoices_,v_Round_) as TotalPaidAmount,
			ROUND(v_TotalDispute_,v_Round_) as TotalDispute,
			ROUND(v_TotalEstimate_,v_Round_) as TotalEstimate,
			v_Round_ as `Round`;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getDueInvoice`;

DELIMITER |
CREATE PROCEDURE `prc_getDueInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_BillingClassID` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT * FROM(
		SELECT
			inv.InvoiceID AS InvoiceID,
			inv.GrandTotal,
			inv.GrandTotal - (SELECT COALESCE(SUM(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) AS InvoiceOutStanding,
			inv.FullInvoiceNumber as InvoiceNumber,
			DATE_ADD(inv.IssueDate,INTERVAL b.PaymentDueInDays DAY) as DueDate,
			DATEDIFF(NOW(),DATE_ADD(inv.IssueDate,INTERVAL b.PaymentDueInDays DAY)) as DueDay,
			a.AccountID,
			a.created_at as AccountCreationDate
		FROM tblInvoice inv
		INNER JOIN Ratemanagement3.tblAccount a
			ON inv.AccountID = a.AccountID
		INNER JOIN Ratemanagement3.tblAccountBilling ab
			ON ab.AccountID = a.AccountID	AND ab.ServiceID = inv.ServiceID
		INNER JOIN Ratemanagement3.tblBillingClass b
			ON b.BillingClassID = ab.BillingClassID
		WHERE inv.CompanyID = p_CompanyID
		AND ( p_AccountID = 0 OR inv.AccountID =   p_AccountID)
		AND (p_BillingClassID = 0 OR  b.BillingClassID = p_BillingClassID)
		AND InvoiceStatus NOT IN('awaiting','draft','Cancel')
		AND inv.GrandTotal <> 0
	)tbl
	WHERE InvoiceOutStanding > 0 ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getEstimate`;

DELIMITER |
CREATE PROCEDURE `prc_getEstimate`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_EstimateNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_EstimateStatus` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CurrencyID` INT,
	IN `p_isExport` INT
)
BEGIN
    
    DECLARE v_OffSet_ INT;
    DECLARE v_Round_ INT;    
    DECLARE v_CurrencyCode_ VARCHAR(50);
 	 SET sql_mode = 'ALLOW_INVALID_DATES';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	        
 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
 	 SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	 SELECT cr.Symbol INTO v_CurrencyCode_ FROM Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
    IF p_isExport = 0
    THEN
        SELECT 
        ac.AccountName,
        CONCAT(LTRIM(RTRIM(IFNULL(it.EstimateNumberPrefix,''))), LTRIM(RTRIM(inv.EstimateNumber))) AS EstimateNumber,
        inv.IssueDate,
        CONCAT(IFNULL(cr.Symbol,''),ROUND(inv.GrandTotal,v_Round_)) AS GrandTotal2,		
        inv.EstimateStatus,
        inv.EstimateID,
        inv.Description,
        inv.Attachment,
        inv.AccountID,		  
		  IFNULL(ac.BillingEmail,'') AS BillingEmail,
		  ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
		  inv.converted
        FROM tblEstimate inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = ac.AccountID AND ab.ServiceID = 0
        INNER JOIN Ratemanagement3.tblBillingClass b ON ab.BillingClassID = b.BillingClassID
		  LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID))
        ORDER BY
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
            END DESC,
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateStatusDESC') THEN inv.EstimateStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateStatusASC') THEN inv.EstimateStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateNumberASC') THEN inv.EstimateNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateNumberDESC') THEN inv.EstimateNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN inv.IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN inv.IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN inv.GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN inv.GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateIDDESC') THEN inv.EstimateID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateIDASC') THEN inv.EstimateID
            END ASC
        
        LIMIT p_RowspPage OFFSET v_OffSet_;
        
        
        SELECT
            COUNT(*) AS totalcount,  ROUND(SUM(inv.GrandTotal),v_Round_) AS total_grand,v_CurrencyCode_ as currency_symbol
        FROM
        tblEstimate inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = ac.AccountID AND ab.ServiceID = 0
        INNER JOIN Ratemanagement3.tblBillingClass b ON ab.BillingClassID = b.BillingClassID
		  LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF;
    IF p_isExport = 1
    THEN
        SELECT ac.AccountName ,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.EstimateNumber)))) AS EstimateNumber,
        inv.IssueDate,
        ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
        inv.EstimateStatus
        FROM tblEstimate inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = ac.AccountID AND ab.ServiceID = 0
        INNER JOIN Ratemanagement3.tblBillingClass b ON ab.BillingClassID = b.BillingClassID
		  LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF;
     IF p_isExport = 2
    THEN
        SELECT ac.AccountID ,
        ac.AccountName,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.EstimateNumber)))) AS EstimateNumber,
        inv.IssueDate,
		  ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
        inv.EstimateStatus,
        inv.EstimateID
        FROM tblEstimate inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = ac.AccountID AND ab.ServiceID = 0
        INNER JOIN Ratemanagement3.tblBillingClass b ON ab.BillingClassID = b.BillingClassID
		  LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF; 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getInvoice`;

DELIMITER |
CREATE PROCEDURE `prc_getInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_InvoiceType` INT,
	IN `p_InvoiceStatus` VARCHAR(50),
	IN `p_IsOverdue` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CurrencyID` INT,
	IN `p_isExport` INT,
	IN `p_sageExport` INT,
	IN `p_zerovalueinvoice` INT,
	IN `p_InvoiceID` LONGTEXT
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_TotalCount int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT cr.Symbol INTO v_CurrencyCode_ FROM Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		InvoiceType tinyint(1),
		AccountName varchar(100),
		InvoiceNumber varchar(100),
		IssueDate datetime,
		InvoicePeriod varchar(100),
		CurrencySymbol varchar(5),
		GrandTotal decimal(18,6),
		TotalPayment decimal(18,6),
		PendingAmount decimal(18,6),
		InvoiceStatus varchar(50),
		InvoiceID int,
		Description varchar(500),
		Attachment varchar(255),
		AccountID int,
		ItemInvoice tinyint(1),
		BillingEmail varchar(255),
		AccountNumber varchar(100),
		PaymentDueInDays int,
		PaymentDate datetime,
		SubTotal decimal(18,6),
		TotalTax decimal(18,6),
		NominalAnalysisNominalAccountNumber varchar(100)
	);

		INSERT INTO tmp_Invoices_
		SELECT inv.InvoiceType ,
			ac.AccountName,
			FullInvoiceNumber as InvoiceNumber,
			inv.IssueDate,
			IF(invd.StartDate IS NULL ,'',CONCAT('From ',date(invd.StartDate) ,'<br> To ',date(invd.EndDate))) as InvoicePeriod,
			IFNULL(cr.Symbol,'') as CurrencySymbol,
			inv.GrandTotal as GrandTotal,
			(SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) as TotalPayment,
			(inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
			inv.InvoiceStatus,
			inv.InvoiceID,
			inv.Description,
			inv.Attachment,
			inv.AccountID,
			inv.ItemInvoice,
			IFNULL(ac.BillingEmail,'') as BillingEmail,
			ac.Number,
			(SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1) as PaymentDueInDays,
			(SELECT PaymentDate FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID ORDER BY PaymentID DESC LIMIT 1) AS PaymentDate,
			inv.SubTotal,
			inv.TotalTax,
			ac.NominalAnalysisNominalAccountNumber
			FROM tblInvoice inv
			INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
			LEFT JOIN tblInvoiceDetail invd ON invd.InvoiceID = inv.InvoiceID AND invd.ProductType = 2
			LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
			WHERE ac.CompanyID = p_CompanyID
			AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
			AND (p_InvoiceNumber = '' OR (inv.InvoiceNumber like Concat('%',p_InvoiceNumber,'%')))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
			AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
			AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND FIND_IN_SET(inv.InvoiceStatus,p_InvoiceStatus) ))
			AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal != 0))
			AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ))
			AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
	
	IF p_isExport = 0 and p_sageExport = 0
	THEN

		SELECT 
			InvoiceType ,
			AccountName,
			InvoiceNumber,
			IssueDate,
			InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `PendingAmount`,
			InvoiceStatus,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount,
			ItemInvoice,
			BillingEmail,
			GrandTotal
		FROM tmp_Invoices_ 
		WHERE (p_IsOverdue = 0 
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				)
		ORDER BY
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeDESC') THEN InvoiceType
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeASC') THEN InvoiceType
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusDESC') THEN InvoiceStatus
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusASC') THEN InvoiceStatus
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN InvoiceNumber
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN InvoiceNumber
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN IssueDate
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN IssueDate
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodASC') THEN InvoicePeriod
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodDESC') THEN InvoicePeriod
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN GrandTotal
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN GrandTotal
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDDESC') THEN InvoiceID
				END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDASC') THEN InvoiceID
				END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT COUNT(*) INTO v_TotalCount 
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0 
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);

		SELECT
			v_TotalCount AS totalcount,
			ROUND(sum(GrandTotal),v_Round_) as total_grand,
			ROUND(sum(TotalPayment),v_Round_) as `TotalPayment`, 
			ROUND(sum(PendingAmount),v_Round_) as `TotalPendingAmount`,
			v_CurrencyCode_ as currency_symbol
		FROM tmp_Invoices_ 
			WHERE ((InvoiceStatus IS NULL) OR (InvoiceStatus NOT IN('draft','Cancel')))
			AND (p_IsOverdue = 0 
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);

	END IF;
	IF p_isExport = 1
	THEN

		SELECT 
			AccountName ,
			InvoiceNumber,
			IssueDate,
			REPLACE(InvoicePeriod, '<br>', '') as InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
			InvoiceStatus,
			InvoiceType,
			ItemInvoice
		FROM tmp_Invoices_
		WHERE
				(p_IsOverdue = 0 
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);
		END IF;
	IF p_isExport = 2
	THEN

		-- just extra field InvoiceID

		SELECT 
			AccountName ,
			InvoiceNumber,
			IssueDate,
			REPLACE(InvoicePeriod, '<br>', '') as InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
			InvoiceStatus,
			InvoiceType,
			ItemInvoice,
			InvoiceID
		FROM tmp_Invoices_
		WHERE
				(p_IsOverdue = 0 
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);

	END IF;

	IF p_sageExport =1 OR p_sageExport =2
	THEN
			-- mark as paid invoice that are sage export

		IF p_sageExport = 2
		THEN 
			UPDATE tblInvoice  inv
			INNER JOIN Ratemanagement3.tblAccount ac
				ON ac.AccountID = inv.AccountID
			INNER JOIN Ratemanagement3.tblAccountBilling ab
				ON ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID
			INNER JOIN Ratemanagement3.tblBillingClass b
				ON ab.BillingClassID = b.BillingClassID
			INNER JOIN Ratemanagement3.tblCurrency c
				ON c.CurrencyId = ac.CurrencyId
			SET InvoiceStatus = 'paid' 
			WHERE ac.CompanyID = p_CompanyID
				AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
				AND (p_InvoiceNumber = '' OR (inv.InvoiceNumber like Concat('%',p_InvoiceNumber,'%')))
				AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
				AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
				AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
				AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND FIND_IN_SET(inv.InvoiceStatus,p_InvoiceStatus) ))
				AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal != 0))
				AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ))
				AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID)) 
				AND (p_IsOverdue = 0 
					OR ((To_days(NOW()) - To_days(IssueDate)) > IFNULL(b.PaymentDueInDays,0)
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) FROM tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) )>0)
						)
				);
		END IF; 
		SELECT
			AccountNumber,
			DATE_FORMAT(DATE_ADD(IssueDate,INTERVAL PaymentDueInDays DAY), '%Y-%m-%d') AS DueDate,
			GrandTotal AS GoodsValueInAccountCurrency,
			GrandTotal AS SalControlValueInBaseCurrency,
			1 AS DocumentToBaseCurrencyRate,
			1 AS DocumentToAccountCurrencyRate,
			DATE_FORMAT(IssueDate, '%Y-%m-%d') AS PostedDate,
			InvoiceNumber AS TransactionReference,
			'' AS SecondReference,
			'' AS Source,
			4 AS SYSTraderTranType, -- 4 - Sales invoice (SI)
			DATE_FORMAT(PaymentDate ,'%Y-%m-%d') AS TransactionDate,
			TotalTax AS TaxValue,
			SubTotal AS `NominalAnalysisTransactionValue/1`,
			NominalAnalysisNominalAccountNumber AS `NominalAnalysisNominalAccountNumber/1`,
			'NEON' AS `NominalAnalysisNominalAnalysisNarrative/1`,
			'' AS `NominalAnalysisTransactionAnalysisCode/1`,
			1 AS `TaxAnalysisTaxRate/1`,
			SubTotal AS `TaxAnalysisGoodsValueBeforeDiscount/1`,
			TotalTax as   `TaxAnalysisTaxOnGoodsValue/1`
		FROM tmp_Invoices_
		WHERE
				(p_IsOverdue = 0 
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getInvoiceUsage`;

DELIMITER |
CREATE PROCEDURE `prc_getInvoiceUsage`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_ShowZeroCall` INT
)
BEGIN

	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	DECLARE v_CDRType_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;
	
	CALL fnServiceUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_ServiceID,p_StartDate,p_EndDate,v_BillingTime_);

	SELECT 
		it.CDRType  INTO v_CDRType_
	FROM Ratemanagement3.tblAccountBilling ab
	INNER JOIN  Ratemanagement3.tblBillingClass b
		ON b.BillingClassID = ab.BillingClassID
	INNER JOIN tblInvoiceTemplate it
		ON it.InvoiceTemplateID = b.InvoiceTemplateID
	WHERE ab.AccountID = p_AccountID
		AND ab.ServiceID = p_ServiceID
	LIMIT 1;

	IF( v_CDRType_ = 2) 
	THEN

		SELECT
			area_prefix AS AreaPrefix,
			Trunk,
			(SELECT 
			Country
			FROM Ratemanagement3.tblRate r
			INNER JOIN Ratemanagement3.tblCountry c
			ON c.CountryID = r.CountryID
			WHERE  r.Code = ud.area_prefix limit 1)
			AS Country,
			(SELECT Description
			FROM Ratemanagement3.tblRate r
			WHERE  r.Code = ud.area_prefix limit 1 )
			AS Description,
			COUNT(UsageDetailID) AS NoOfCalls,
			CONCAT( FLOOR(SUM(duration ) / 60), ':' , SUM(duration ) % 60) AS Duration,
			CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS BillDuration,
			SUM(cost) AS ChargedAmount,
			SUM(duration ) as DurationInSec,
			SUM(billed_duration ) as BillDurationInSec,
			ud.ServiceID
		FROM tmp_tblUsageDetails_ ud
		GROUP BY ud.area_prefix,ud.Trunk,ud.AccountID,ud.ServiceID;

	ELSE

		SELECT
			trunk AS Trunk,
			area_prefix AS Prefix,
			CONCAT("'",cli) AS CLI,
			CONCAT("'",cld) AS CLD,
			connect_time AS ConnectTime,
			disconnect_time AS DisconnectTime,
			billed_duration AS BilledDuration,
			cost AS ChargedAmount,
			ServiceID
		FROM tmp_tblUsageDetails_ ud
		WHERE ((p_ShowZeroCall =0 AND ud.cost >0 ) OR (p_ShowZeroCall =1 AND ud.cost >= 0))
		ORDER BY connect_time ASC;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getPaymentPendingInvoice`;

DELIMITER |
CREATE PROCEDURE `prc_getPaymentPendingInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_PaymentDueInDays` INT 
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		MAX(i.InvoiceID) AS InvoiceID,
		(IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) AS RemaingAmount
	FROM tblInvoice i
	INNER JOIN Ratemanagement3.tblAccount a
		ON i.AccountID = a.AccountID
	INNER JOIN Ratemanagement3.tblAccountBilling ab 
		ON ab.AccountID = a.AccountID AND ab.ServiceID = i.ServiceID
	INNER JOIN Ratemanagement3.tblBillingClass b
		ON b.BillingClassID = ab.BillingClassID
	LEFT JOIN tblPayment p
		ON p.AccountID = i.AccountID
		AND p.InvoiceID = i.InvoiceID AND p.Status = 'Approved' AND p.AccountID = i.AccountID
		AND p.Status = 'Approved'
		AND p.Recall = 0
	WHERE i.CompanyID = p_CompanyID
	AND i.InvoiceStatus != 'cancel'
	AND i.AccountID = p_AccountID
	AND (p_PaymentDueInDays =0  OR (p_PaymentDueInDays =1 AND TIMESTAMPDIFF(DAY, i.IssueDate, NOW()) >= IFNULL(b.PaymentDueInDays,0) ) )

	GROUP BY i.InvoiceID,
			 p.AccountID
	HAVING (IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) > 0;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_InsertTempReRateCDR`;

DELIMITER |
CREATE PROCEDURE `prc_InsertTempReRateCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AccountID` INT,
	IN `p_ProcessID` VARCHAR(50),
	IN `p_tbltempusagedetail_name` VARCHAR(50),
	IN `p_CDRType` CHAR(1),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50)
)
BEGIN
	DECLARE v_BillingTime_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	SET @stm1 = CONCAT('

	INSERT INTO RMCDR3.`' , p_tbltempusagedetail_name , '` (
		CompanyID,
		CompanyGatewayID,
		GatewayAccountID,
		AccountID,
		ServiceID,
		connect_time,
		disconnect_time,
		billed_duration,
		area_prefix,
		pincode,
		extension,
		cli,
		cld,
		cost,
		remote_ip,
		duration,
		trunk,
		ProcessID,
		ID,
		is_inbound,
		billed_second,
		disposition
	)

	SELECT
	*
	FROM (SELECT
		uh.CompanyID,
		CompanyGatewayID,
		GatewayAccountID,
		uh.AccountID,
		uh.ServiceID,
		connect_time,
		disconnect_time,
		billed_duration,
		"Other" as area_prefix,
		pincode,
		extension,
		cli,
		cld,
		cost,
		remote_ip,
		duration,
		"Other" as trunk,
		"',p_ProcessID,'",
		ID,
		is_inbound,
		billed_second,
		disposition
	FROM RMCDR3.tblUsageDetails  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	INNER JOIN Ratemanagement3.tblAccount a
		ON uh.AccountID = a.AccountID
	WHERE
	( "' , p_CDRType , '" = "" OR  ud.is_inbound =  "' , p_CDRType , '")
	AND  StartDate >= DATE_ADD( "' , p_StartDate , '",INTERVAL -1 DAY)
	AND StartDate <= DATE_ADD( "' , p_EndDate , '",INTERVAL 1 DAY)
	AND uh.CompanyID =  "' , p_CompanyID , '"
	AND uh.AccountID is not null
	AND ( "' , p_AccountID , '" = 0 OR uh.AccountID = "' , p_AccountID , '")
	AND ( "' , p_CompanyGatewayID , '" = 0 OR CompanyGatewayID = "' , p_CompanyGatewayID , '")
	AND ( "' , p_CurrencyID ,'" = "0" OR a.CurrencyId = "' , p_CurrencyID , '")
	AND ( "' , p_CLI , '" = "" OR cli LIKE REPLACE("' , p_CLI , '", "*", "%"))	
	AND ( "' , p_CLD , '" = "" OR cld LIKE REPLACE("' , p_CLD , '", "*", "%"))
	AND ( "' , p_trunk , '" = ""  OR  trunk = "' , p_trunk , '")
	AND ( "' , p_area_prefix , '" = "" OR area_prefix LIKE REPLACE( "' , p_area_prefix , '", "*", "%"))	
	AND ( "' , p_zerovaluecost , '" = 0 OR (  "' , p_zerovaluecost , '" = 1 AND cost = 0) OR (  "' , p_zerovaluecost , '" = 2 AND cost > 0))	
	) tbl
	WHERE 
	("' , v_BillingTime_ , '" =1 and connect_time >=  "' , p_StartDate , '" AND connect_time <=  "' , p_EndDate , '")
	OR 
	("' , v_BillingTime_ , '" =2 and disconnect_time >=  "' , p_StartDate , '" AND disconnect_time <=  "' , p_EndDate , '")
	AND billed_duration > 0;
	');

	
	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_ProcessCDRAccount`;

DELIMITER |
CREATE PROCEDURE `prc_ProcessCDRAccount`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_NameFormat` VARCHAR(50)
)
BEGIN

	DECLARE v_NewAccountIDCount_ INT;
	
	/* insert new account */
	SET @stm = CONCAT('
	INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName,ServiceID)
	SELECT
		DISTINCT
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.GatewayAccountID,
		ud.GatewayAccountID,
		ud.ServiceID
	FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
	LEFT JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = ud.GatewayAccountID
		AND ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.CompanyID = ud.CompanyID
		AND ga.ServiceID = ud.ServiceID
	WHERE ProcessID =  "' , p_processId , '"
		AND ga.GatewayAccountID IS NULL
		AND ud.GatewayAccountID IS NOT NULL;
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	/* active new account */
	CALL  prc_getActiveGatewayAccount(p_CompanyID,p_CompanyGatewayID,p_NameFormat);

	/* update cdr account */
	SET @stm = CONCAT('
	UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.ServiceID = uh.ServiceID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ' ,  p_CompanyID , '
	AND uh.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SELECT COUNT(*) INTO v_NewAccountIDCount_ 
	FROM RMCDR3.tblUsageHeader uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.ServiceID = uh.ServiceID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_CompanyID
	AND uh.CompanyGatewayID = p_CompanyGatewayID;

	IF v_NewAccountIDCount_ > 0
	THEN

		/* update header cdr account */
		UPDATE RMCDR3.tblUsageHeader uh
		INNER JOIN tblGatewayAccount ga
			ON  ga.CompanyID = uh.CompanyID
			AND ga.CompanyGatewayID = uh.CompanyGatewayID
			AND ga.GatewayAccountID = uh.GatewayAccountID
			AND ga.ServiceID = uh.ServiceID
		SET uh.AccountID = ga.AccountID
		WHERE uh.AccountID IS NULL
		AND ga.AccountID is not null
		AND uh.CompanyID = p_CompanyID
		AND uh.CompanyGatewayID = p_CompanyGatewayID;

	END IF;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_ProcessCDRService`;

DELIMITER |
CREATE PROCEDURE `prc_ProcessCDRService`(
	IN `p_CompanyID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	DECLARE v_ServiceAccountID_CLI_Count_ INT;
	DECLARE v_ServiceAccountID_IP_Count_ INT;

	SELECT COUNT(*) INTO v_ServiceAccountID_CLI_Count_ 
	FROM Ratemanagement3.tblAccountAuthenticate aa
	WHERE (CustomerAuthRule = 'CLI' OR VendorAuthRule = 'CLI') AND ServiceID > 0;

	IF v_ServiceAccountID_CLI_Count_ > 0
	THEN

		/* update cdr service */
		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
		INNER JOIN Ratemanagement3.tblCLIRateTable ga
			ON  ga.CompanyID = uh.CompanyID
			AND ga.CLI = uh.GatewayAccountID
		SET uh.ServiceID = ga.ServiceID
		WHERE ga.ServiceID > 0
		AND uh.CompanyID = ' ,  p_CompanyID , '
		AND uh.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;
	
	SELECT COUNT(*) INTO v_ServiceAccountID_IP_Count_ 
	FROM Ratemanagement3.tblAccountAuthenticate aa
	WHERE (CustomerAuthRule = 'IP' OR VendorAuthRule = 'IP') AND ServiceID > 0;

	IF v_ServiceAccountID_IP_Count_ > 0
	THEN

		/* update cdr service */
		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
		INNER JOIN Ratemanagement3.tblAccountAuthenticate ga
			ON  ga.CompanyID = uh.CompanyID
			AND ( FIND_IN_SET(uh.GatewayAccountID,ga.CustomerAuthValue) != 0 OR FIND_IN_SET(uh.GatewayAccountID,ga.VendorAuthValue) != 0 )
		SET uh.ServiceID = ga.ServiceID
		WHERE ga.ServiceID > 0
		AND uh.CompanyID = ' ,  p_CompanyID , '
		AND uh.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_ProcesssCDR`;

DELIMITER |
CREATE PROCEDURE `prc_ProcesssCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_NameFormat` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_OutboundTableID` INT,
	IN `p_InboundTableID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	/* update service against cli or ip */
	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name);

	/* check service enable at gateway*/
	DROP TEMPORARY TABLE IF EXISTS tmp_Service_;
	CREATE TEMPORARY TABLE tmp_Service_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ServiceID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT ServiceID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND ServiceID > 0;
	');
	
	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;
	
	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT tblService.ServiceID 
	FROM Ratemanagement3.tblService 
	LEFT JOIN  RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
	ON tblService.ServiceID = ud.ServiceID AND ProcessID="' , p_processId , '"
	WHERE tblService.ServiceID > 0 AND tblService.CompanyID = "' , p_CompanyID , '" AND tblService.CompanyGatewayID > 0 AND ud.ServiceID IS NULL
	');
	
	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	/* update account and add new accounts and apply authentication rule*/
	CALL prc_ProcessCDRAccount(p_CompanyID,p_CompanyGatewayID,p_processId,p_tbltempusagedetail_name,p_NameFormat);

	IF ( ( SELECT COUNT(*) FROM tmp_Service_ ) > 0 OR p_OutboundTableID > 0)
	THEN

		/* rerate cdr service base */
		CALL prc_RerateOutboundService(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate,p_OutboundTableID);

	ELSE

		/* rerate cdr trunk base */
		CALL prc_RerateOutboundTrunk(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate);
	
	END IF;

	/* if rerate is off and acconts and trunks not setup update prefix from default codedeck*/
	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN 
		/* temp accounts and trunks*/
		DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
		CREATE TEMPORARY TABLE tmp_Accounts_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_Accounts_(AccountID)
		SELECT DISTINCT AccountID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

		/* get default code */
		CALL Ratemanagement3.prc_getDefaultCodes(p_CompanyID);

		/* update prefix from default codes 
		 if rate format is prefix base not charge code*/
		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;

	/* inbound rerate process*/
	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);
	
	/* generate rerate error log*/
	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_ProcesssVCDR`;

DELIMITER |
CREATE PROCEDURE `prc_ProcesssVCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_NameFormat` VARCHAR(50)
)
BEGIN
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_TrunkID_ INT;
	DECLARE v_NewAccountIDCount_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name);
	
	/* check service enable at gateway*/
	DROP TEMPORARY TABLE IF EXISTS tmp_Service_;
	CREATE TEMPORARY TABLE tmp_Service_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ServiceID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT ServiceID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND ServiceID > 0;
	');
	
	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL
	);

	/* insert new account */
	SET @stm = CONCAT('
	INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName,IsVendor)
	SELECT
		DISTINCT
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.GatewayAccountID,
		ud.GatewayAccountID,
		1 as IsVendor
	FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
	LEFT JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = ud.GatewayAccountID
		AND ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.CompanyID = ud.CompanyID
	WHERE ProcessID =  "' , p_processId , '"
		AND ga.GatewayAccountID IS NULL
		AND ud.GatewayAccountID IS NOT NULL;
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	/* active new account */
	CALL  prc_getActiveGatewayAccount(p_CompanyID,p_CompanyGatewayID,p_NameFormat);

	/* update cdr account */
	SET @stm = CONCAT('
	UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.GatewayAccountID = uh.GatewayAccountID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ' ,  p_companyid , '
	AND uh.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SELECT COUNT(*) INTO v_NewAccountIDCount_ 
	FROM RMCDR3.tblVendorCDRHeader uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.GatewayAccountID = uh.GatewayAccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_CompanyID
	AND uh.CompanyGatewayID = p_CompanyGatewayID;

	IF v_NewAccountIDCount_ > 0
	THEN
		/* update header cdr account */
		UPDATE RMCDR3.tblVendorCDRHeader uh
		INNER JOIN tblGatewayAccount ga
			ON  ga.CompanyID = uh.CompanyID
			AND ga.CompanyGatewayID = uh.CompanyGatewayID
			AND ga.GatewayAccountID = uh.GatewayAccountID
		SET uh.AccountID = ga.AccountID
		WHERE uh.AccountID IS NULL
		AND ga.AccountID is not null
		AND uh.CompanyID = p_CompanyID
		AND uh.CompanyGatewayID = p_CompanyGatewayID;

	END IF;

	/* if rate format is prefix base not charge code*/
	IF p_RateFormat = 2
	THEN

		

		/* update trunk with use in billing*/
		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblVendorTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.Status =1 
			AND ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
		INNER JOIN Ratemanagement3.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling,ud.TrunkPrefix = ct.Prefix
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.TrunkID IS NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;
		
		/* update trunk without use in billing*/
		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblVendorTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.Status =1 
			AND ct.UseInBilling = 0 
		INNER JOIN Ratemanagement3.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.TrunkID IS NULL;
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	/* if rerate on */
	IF p_RateCDR = 1
	THEN
		SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET selling_cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND ( AccountID IS NULL OR TrunkID IS NULL ) ') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	/* temp accounts and trunks*/
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunk_;
	CREATE TEMPORARY TABLE tmp_AccountTrunk_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunk_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AccountTrunk_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_TrunkID_ = (SELECT TrunkID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_); 
		SET v_AccountID_ = (SELECT AccountID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_);
		
		/* get outbound rate process*/
		CALL Ratemanagement3.prc_getVendorCodeRate(v_AccountID_,v_TrunkID_,p_RateCDR);
		
		/* update prefix outbound process*/
		/* if rate format is prefix base not charge code*/
		IF p_RateFormat = 2
		THEN
			CALL prc_updateVendorPrefix(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name);
		END IF;
		
		
		/* outbound rerate process*/
		IF p_RateCDR = 1
		THEN
			CALL prc_updateVendorRate(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name);
		END IF;

		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;
	
	/* if rerate is off and acconts and trunks not setup update prefix from default codedeck*/
	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN 
		/* temp accounts and trunks*/
		DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
		CREATE TEMPORARY TABLE tmp_Accounts_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_Accounts_(AccountID)
		SELECT DISTINCT AccountID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;
		
			
		/* get default code */
		CALL Ratemanagement3.prc_getDefaultCodes(p_CompanyID);
		
		/* update prefix from default codes 
		 if rate format is prefix base not charge code*/
		CALL prc_updateDefaultVendorPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;
	
	
	SET @stm = CONCAT('
	INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
	SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,1,  CONCAT( "Account:  " , ga.AccountName ," - Gateway: ",cg.Title," - Doesnt exist in NEON") as Message ,DATE(NOW())
	FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN tblGatewayAccount ga 
		ON ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.CompanyID = ud.CompanyID
		AND ga.GatewayAccountID = ud.GatewayAccountID
	INNER JOIN Ratemanagement3.tblCompanyGateway cg ON cg.CompanyGatewayID = ud.CompanyGatewayID
	WHERE ud.ProcessID = "' , p_processid  , '" and ud.AccountID IS NULL');
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	IF p_RateCDR = 1
	THEN
		SET @stm = CONCAT('
		INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
		SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Trunk: ",ud.trunk," - Unable to Rerate number ",ud.cld," - No Matching prefix found") as Message ,DATE(NOW())
		FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
		WHERE ud.ProcessID = "' , p_processid  , '" AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');
		
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		SET @stm = CONCAT('
		INSERT INTO Ratemanagement3.tblTempRateLog (CompanyID,CompanyGatewayID,MessageType,Message,RateDate,SentStatus,created_at)
		SELECT rt.CompanyID,rt.CompanyGatewayID,rt.MessageType,rt.Message,rt.RateDate,0 as SentStatus,NOW() as created_at FROM tmp_tblTempRateLog_ rt
		LEFT JOIN Ratemanagement3.tblTempRateLog rt2 
			ON rt.CompanyID = rt2.CompanyID
			AND rt.CompanyGatewayID = rt2.CompanyGatewayID
			AND rt.MessageType = rt2.MessageType
			AND rt.Message = rt2.Message
			AND rt.RateDate = rt2.RateDate
		WHERE rt2.TempRateLogID IS NULL;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
	END IF;
	
	SELECT DISTINCT Message FROM tmp_tblTempRateLog_;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_RerateInboundCalls`;

DELIMITER |
CREATE PROCEDURE `prc_RerateInboundCalls`(
	IN `p_CompanyID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_InboundTableID` INT
)
BEGIN

	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_ServiceID_ INT;
	DECLARE v_cld_ VARCHAR(500);

	IF p_RateCDR = 1
	THEN

		IF (SELECT COUNT(*) FROM Ratemanagement3.tblCLIRateTable WHERE CompanyID = p_CompanyID AND RateTableID > 0) > 0
		THEN

			/* temp accounts*/
			DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
			CREATE TEMPORARY TABLE tmp_Account_  (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				AccountID INT,
				ServiceID INT,
				cld VARCHAR(500) NULL DEFAULT NULL
			);
			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,ServiceID,cld)
			SELECT DISTINCT AccountID,ServiceID,cld FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 1;
			');

			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;

		ELSEIF ( SELECT COUNT(*) FROM tmp_Service_ ) > 0
		THEN

			/* temp accounts*/
			DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
			CREATE TEMPORARY TABLE tmp_Account_  (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				AccountID INT,
				ServiceID INT,
				cld VARCHAR(500) NULL DEFAULT NULL
			);
			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,ServiceID,cld)
			SELECT DISTINCT AccountID,ServiceID,"" FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 1;
			');

			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;

		ELSE

			/* temp accounts*/
			DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
			CREATE TEMPORARY TABLE tmp_Account_  (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				AccountID INT,
				ServiceID INT,
				cld VARCHAR(500) NULL DEFAULT NULL
			);
			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,ServiceID,cld)
			SELECT DISTINCT AccountID,ServiceID,"" FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 1;
			');

			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;

		END IF;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Account_);

		IF p_InboundTableID > 0
		THEN 
			/* get inbound rate process*/
			CALL Ratemanagement3.prc_getCustomerInboundRate(v_AccountID_,p_RateCDR,p_RateMethod,p_SpecifyRate,v_cld_,p_InboundTableID);
		END IF;

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_AccountID_ = (SELECT AccountID FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
			SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
			SET v_cld_ = (SELECT cld FROM tmp_Account_ t WHERE t.RowID = v_pointer_);

			IF p_InboundTableID =  0
			THEN 

				SET p_InboundTableID = (SELECT RateTableID FROM Ratemanagement3.tblAccountTariff  WHERE AccountID = v_AccountID_ AND ServiceID = v_ServiceID_ AND Type = 2 LIMIT 1);
				/* get inbound rate process*/
				CALL Ratemanagement3.prc_getCustomerInboundRate(v_AccountID_,p_RateCDR,p_RateMethod,p_SpecifyRate,v_cld_,p_InboundTableID);
			END IF;

			/* update prefix inbound process*/
			CALL prc_updateInboundPrefix(v_AccountID_, p_processId, p_tbltempusagedetail_name,v_cld_,v_ServiceID_);

			/* inbound rerate process*/
			CALL prc_updateInboundRate(v_AccountID_, p_processId, p_tbltempusagedetail_name,v_cld_,v_ServiceID_);

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	END IF;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_RerateOutboundService`;
DELIMITER |
CREATE PROCEDURE `prc_RerateOutboundService`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_OutboundTableID` INT
)
BEGIN
	
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_ServiceID_ INT;
	DECLARE v_RateTableID_ INT;
	
	IF p_RateCDR = 1  
	THEN
	
		/* temp accounts*/
		DROP TEMPORARY TABLE IF EXISTS tmp_AccountService2_;
		CREATE TEMPORARY TABLE tmp_AccountService2_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT,
			ServiceID INT
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_AccountService2_(AccountID,ServiceID)
		SELECT DISTINCT AccountID,ServiceID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 0;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_AccountService2_);
		IF p_OutboundTableID > 0
		THEN
			/* get outbound rate process*/
			CALL Ratemanagement3.prc_getCustomerCodeRate(v_AccountID_,0,p_RateCDR,p_RateMethod,p_SpecifyRate,p_OutboundTableID);
		END IF;

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_AccountID_ = (SELECT AccountID FROM tmp_AccountService2_ t WHERE t.RowID = v_pointer_);
			SET v_ServiceID_ = (SELECT ServiceID FROM tmp_AccountService2_ t WHERE t.RowID = v_pointer_);
			
			
			IF p_OutboundTableID = 0
			THEN
				SET v_RateTableID_ = (SELECT RateTableID FROM Ratemanagement3.tblAccountTariff  WHERE AccountID = v_AccountID_ AND ServiceID = v_ServiceID_ AND Type = 1 LIMIT 1);
				/* get outbound rate process*/
				CALL Ratemanagement3.prc_getCustomerCodeRate(v_AccountID_,0,p_RateCDR,p_RateMethod,p_SpecifyRate,v_RateTableID_);
			END IF;
			
			
			/* update prefix outbound process*/
			/* if rate format is prefix base not charge code*/
			IF p_RateFormat = 2
			THEN
				CALL prc_updatePrefix(v_AccountID_,0, p_processId, p_tbltempusagedetail_name,v_ServiceID_);
			END IF;
			
			/* outbound rerate process*/
			IF p_RateCDR = 1
			THEN
				CALL prc_updateOutboundRate(v_AccountID_,0, p_processId, p_tbltempusagedetail_name,v_ServiceID_);
			END IF;
			
			SET v_pointer_ = v_pointer_ + 1;
			
		END WHILE;

	END IF;


END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_RerateOutboundTrunk`;

DELIMITER |
CREATE PROCEDURE `prc_RerateOutboundTrunk`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN

	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_TrunkID_ INT;
	DECLARE v_CDRUpload_ INT;
	DECLARE v_cld_ VARCHAR(500);

	/* temp accounts and trunks*/
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunkCdrUpload_;
	CREATE TEMPORARY TABLE tmp_AccountTrunkCdrUpload_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunkCdrUpload_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL AND ud.is_inbound = 0;
	');

	SET v_CDRUpload_ = (SELECT COUNT(*) FROM tmp_AccountTrunkCdrUpload_);

	IF v_CDRUpload_ > 0
	THEN
		/* update UseInBilling when cdr upload*/
		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblCustomerTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.TrunkID = ud.TrunkID AND ct.Status =1
		INNER JOIN Ratemanagement3.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.UseInBilling=ct.UseInBilling,ud.TrunkPrefix = ct.Prefix
		WHERE  ud.ProcessID = "' , p_processId , '";
		');
	END IF;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	/* if rate format is prefix base not charge code*/
	IF p_RateFormat = 2
	THEN

		/* update trunk with use in billing*/
		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblCustomerTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.Status =1 
			AND ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
		INNER JOIN Ratemanagement3.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling,ud.TrunkPrefix = ct.Prefix
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.is_inbound = 0 AND ud.TrunkID IS NULL;
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		/* update trunk without use in billing*/
		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblCustomerTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.Status =1 
			AND ct.UseInBilling = 0 
		INNER JOIN Ratemanagement3.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.is_inbound = 0 AND ud.TrunkID IS NULL;
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;
	
	/* if rerate on */
	IF p_RateCDR = 1
	THEN

		SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND ( AccountID IS NULL OR TrunkID IS NULL ) ') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	/* temp accounts and trunks*/
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunk_;
	CREATE TEMPORARY TABLE tmp_AccountTrunk_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunk_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL AND ud.is_inbound = 0;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AccountTrunk_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_TrunkID_ = (SELECT TrunkID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_); 
		SET v_AccountID_ = (SELECT AccountID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_);

		/* get outbound rate process*/
		CALL Ratemanagement3.prc_getCustomerCodeRate(v_AccountID_,v_TrunkID_,p_RateCDR,p_RateMethod,p_SpecifyRate,0);

		/* update prefix outbound process*/
		/* if rate format is prefix base not charge code*/
		IF p_RateFormat = 2
		THEN
			CALL prc_updatePrefix(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name,0);
		END IF;

		/* outbound rerate process*/
		IF p_RateCDR = 1
		THEN
			CALL prc_updateOutboundRate(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name,0);
		END IF;

		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_updateOutboundRate`;

DELIMITER |
CREATE PROCEDURE `prc_updateOutboundRate`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_ServiceID` INT
)
BEGIN

	SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND ServiceID = "',p_ServiceID ,'" AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'") AND is_inbound = 0 ') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('
	UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN Ratemanagement3.tmp_codes_ cr ON cr.Code = ud.area_prefix
	SET cost = 
		CASE WHEN  billed_second >= Interval1
		THEN
			(Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+IFNULL(ConnectionFee,0)
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Rate+IFNULL(ConnectionFee,0)
			ELSE
				0
			END
		END
	,is_rerated=1
	,duration = billed_second
	,billed_duration =
		CASE WHEN  billed_second >= Interval1
		THEN
			Interval1+CEILING((billed_second-Interval1)/IntervalN)*IntervalN
		ElSE 
			CASE WHEN  billed_second > 0
			THEN
				Interval1
			ELSE
				0
			END
		END 
	WHERE ProcessID = "',p_processId,'"
	AND AccountID = "',p_AccountID ,'" 
	AND ServiceID = "',p_ServiceID ,'" 
	AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'")
	AND is_inbound = 0') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_updatePrefix`;

DELIMITER |
CREATE PROCEDURE `prc_updatePrefix`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_ServiceID` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID2(`TempUsageDetailID`)
	);

	IF p_TrunkID > 0
	THEN
		/* find prefix without use in billing */
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 0 
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.TrunkID = ' , p_TrunkID , '
			AND ud.UseInBilling = 0
			AND ud.area_prefix = "Other"
			AND ( extension <> cld or extension IS NULL)
			AND cld REGEXP "^[0-9]+$"
			AND cld like  CONCAT(c.Code,"%");
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		/* find prefix with use in billing */
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 0
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.TrunkID = ' , p_TrunkID , '
			AND ud.UseInBilling = 1 
			AND ud.area_prefix = "Other"
			AND ( extension <> cld or extension IS NULL)
			AND cld REGEXP "^[0-9]+$"
			AND cld like  CONCAT(ud.TrunkPrefix,c.Code,"%");
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	ELSE

		/* find prefix */
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 0 
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.ServiceID = ' , p_ServiceID , '
			AND ud.area_prefix = "Other"
			AND ( extension <> cld or extension IS NULL)
			AND cld REGEXP "^[0-9]+$"
			AND cld like  CONCAT(c.Code,"%");
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempUsageDetailID;

	SET @stm = CONCAT('UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;     

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_updateVendorPrefix`;

DELIMITER |
CREATE PROCEDURE `prc_updateVendorPrefix`(IN `p_AccountID` INT, IN `p_TrunkID` INT, IN `p_processId` INT, IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempVendorCDRID int,
		prefix varchar(50),
		INDEX IX_TempVendorCDRID(`TempVendorCDRID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempVendorCDRID int,
		prefix varchar(50),
		INDEX IX_TempVendorCDRID2(`TempVendorCDRID`)
	);

	
	SET @stm = CONCAT('
	INSERT INTO tmp_TempUsageDetail_
	SELECT
		TempVendorCDRID,
		c.code AS prefix
	FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
	INNER JOIN Ratemanagement3.tmp_vcodes_ c 
	ON ud.ProcessID = ' , p_processId , '
		AND ud.AccountID = ' , p_AccountID , '
		AND ud.TrunkID = ' , p_TrunkID , '
		AND ud.UseInBilling = 0
		AND ud.area_prefix = "Other"
		AND cld like  CONCAT(c.Code,"%");
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	
	SET @stm = CONCAT('
	INSERT INTO tmp_TempUsageDetail_
	SELECT
		TempVendorCDRID,
		c.code AS prefix
	FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
	INNER JOIN Ratemanagement3.tmp_vcodes_ c 
	ON ud.ProcessID = ' , p_processId , '
		AND ud.AccountID = ' , p_AccountID , '
		AND ud.TrunkID = ' , p_TrunkID , '
		AND ud.UseInBilling = 1 
		AND ud.area_prefix = "Other"
		AND cld like  CONCAT(ud.TrunkPrefix,c.Code,"%");
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET @stm = CONCAT('INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempVendorCDRID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempVendorCDRID;');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempVendorCDRID = tbl.TempVendorCDRID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;     

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_updateInboundPrefix`;
DELIMITER |
CREATE PROCEDURE `prc_updateInboundPrefix`(
	IN `p_AccountID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_CLD` VARCHAR(500),
	IN `p_ServiceID` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID2(`TempUsageDetailID`)
	);

	IF p_CLD != ''
	THEN 
		
		/* find prefix */
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud 
		INNER JOIN Ratemanagement3.tmp_inboundcodes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 1 
			AND ud.AccountID = ' , p_AccountID , '
			AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
			AND ud.area_prefix = "Other"
			AND cli like  CONCAT(c.Code,"%");
		');
		
	ELSE
		
		/* find prefix */
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud 
		INNER JOIN Ratemanagement3.tmp_inboundcodes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 1 
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.ServiceID = ' , p_ServiceID , '
			AND ud.area_prefix = "Other"
			AND cld like  CONCAT(c.Code,"%");
		');
	
	END IF;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempUsageDetailID;');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_updateInboundRate`;
DELIMITER |
CREATE PROCEDURE `prc_updateInboundRate`(
	IN `p_AccountID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_CLD` VARCHAR(500),
	IN `p_ServiceID` INT
)
BEGIN

	SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND ServiceID = "',p_ServiceID ,'" AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '") AND is_inbound = 1 ') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('
	UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN Ratemanagement3.tmp_inboundcodes_ cr ON cr.Code = ud.area_prefix
	SET cost = 
		CASE WHEN  billed_second >= Interval1
		THEN
			(Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+IFNULL(ConnectionFee,0)
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Rate+IFNULL(ConnectionFee,0)
			ELSE
				0
			END
		END
	,is_rerated=1
	,duration=billed_second
	,billed_duration =
		CASE WHEN  billed_second >= Interval1
		THEN
			Interval1+CEILING((billed_second-Interval1)/IntervalN)*IntervalN
		ElSE 
			CASE WHEN  billed_second > 0
			THEN
				Interval1
			ELSE
				0
			END
		END 
	WHERE ProcessID = "',p_processId,'"
	AND AccountID = "',p_AccountID ,'"
	AND ServiceID = "',p_ServiceID ,'"
	AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
	AND is_inbound = 1') ;
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_insertDailyData`;

DROP PROCEDURE IF EXISTS `prc_salesDashboard`;