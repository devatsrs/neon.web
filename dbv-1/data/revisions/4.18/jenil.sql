/* USE Ratemanagement3 */

/* Dynamic Links */

CREATE TABLE `tblDynamiclink` (
	`DynamicLinkID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL DEFAULT '0',
	`Title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Link` TEXT NULL COLLATE 'utf8_unicode_ci',
	`Currency` INT(11) NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ModifiedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`DynamicLinkID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;


SELECT `DEFAULT_COLLATION_NAME` FROM `information_schema`.`SCHEMATA` WHERE `SCHEMA_NAME`='Ratemanagement3';

DROP PROCEDURE IF EXISTS `prc_getDynamiclinks`;
DELIMITER //
CREATE PROCEDURE `prc_getDynamiclinks`(
	IN `p_CompanyID` INT,
	IN `p_Title` VARCHAR(255),
	IN `p_Currency` INT(11),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT


)

BEGIN
     DECLARE v_OffSet_ int;
     
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
		tblDynamiclink.Title,
		tblDynamiclink.Link,
		tblCurrency.Code as Currency,
		tblDynamiclink.created_at,
		tblDynamiclink.CurrencyID,
		tblDynamiclink.DynamicLinkID
		from tblDynamiclink LEFT JOIN tblCurrency ON tblDynamiclink.CurrencyID = tblCurrency.CurrencyId
		where tblDynamiclink.CompanyID = p_CompanyID
		AND (p_Title ='' OR tblDynamiclink.Title like Concat('%',p_Title,'%'))
		AND (p_Currency ='' OR tblDynamiclink.CurrencyID = p_Currency)
            
        ORDER BY
   
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblDynamiclink.Title
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblDynamiclink.Title
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CurrencyDESC') THEN tblDynamiclink.CurrencyID
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CurrencyASC') THEN tblDynamiclink.CurrencyID
			END ASC,
			
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblDynamiclink.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblDynamiclink.created_at
			END ASC
			
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
		COUNT(tblDynamiclink.DynamicLinkID) AS totalcount
		from tblDynamiclink
		where tblDynamiclink.CompanyID = p_CompanyID
		AND (p_Title ='' OR tblDynamiclink.Title like Concat('%',p_Title,'%'))
		AND (p_Currency ='' OR tblDynamiclink.CurrencyID = p_Currency);

	ELSE
	
		SELECT
			tblDynamiclink.Title,
			tblDynamiclink.Link,
			tblCurrency.Code as Currency,
			tblDynamiclink.created_at as CreatedDate
         from tblDynamiclink LEFT JOIN tblCurrency ON tblDynamiclink.CurrencyID = tblCurrency.CurrencyId
			where tblDynamiclink.CompanyID = p_CompanyID
			AND (p_Title ='' OR tblDynamiclink.Title like Concat('%',p_Title,'%'))
			AND (p_Currency ='' OR tblDynamiclink.CurrencyID = p_Currency);

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


/* 
All Lables
CUST_PANEL_SIDENAV_MENU_DYNAMICLINK - Dynamic Link
CUST_PANEL_PAGE_DYNAMICLINK_TBL_TITLE - Title
CUST_PANEL_PAGE_DYNAMICLINK_TBL_LINK - Link
CUST_PANEL_PAGE_DYNAMICLINK_TBL_CURRENCY - Currency
CUST_PANEL_PAGE_DYNAMICLINK_TBL_CREATED_AT - Created At
CUST_PANEL_PAGE_DYNAMICLINK_TBL_ACTION - Action
CUST_PANEL_PAGE_DYNAMICLINK_BUTTON_ADD_NEW - Add New
CUST_PANEL_PAGE_DYNAMICLINK_MSG_NOTIFICATION_SUCCESSFULLY_CREATED - Dynamic Link Successfully Created
CUST_PANEL_PAGE_DYNAMICLINK_MSG_PROBLEM_CREATING_NOTIFICATION  - Problem Creating Dynamic Link
CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_SUCCESSFULLY_UPDATED - Dynamic Link Successfully Updated
CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_SUCCESSFULLY_DELETED - Dynamic Link Successfully Deleted
CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_DELETING_NOTIFICATION - Problem Deleting Dynamic Link


*/

ALTER TABLE `tblProduct`
	CHANGE COLUMN `Quantity` `Quantity` INT(11) NULL DEFAULT NULL AFTER `Buying_price`,
	CHANGE COLUMN `Low_stock_level` `Low_stock_level` INT(11) NULL DEFAULT NULL AFTER `Quantity`;


INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1353, 'Dynamiclink.All', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1352, 'Dynamiclink.View', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1351, 'Dynamiclink.Delete', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1350, 'Dynamiclink.Edit', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1349, 'Dynamiclink.Add', 1, 9);


INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dynamiclink.delete', 'DynamiclinkController.delete', 1, 'System', NULL, '2018-08-14 13:56:00.000', '2018-08-14 13:56:00.000', 1351);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dynamiclink.update', 'DynamiclinkController.update', 1, 'System', NULL, '2018-08-14 13:56:00.000', '2018-08-14 13:56:00.000', 1350);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dynamiclink.create', 'DynamiclinkController.create', 1, 'System', NULL, '2018-08-14 13:56:00.000', '2018-08-14 13:56:00.000', 1349);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dynamiclink.ajax_datagrid', 'DynamiclinkController.ajax_datagrid', 1, 'System', NULL, '2018-08-14 13:56:00.000', '2018-08-14 13:56:00.000', 1352);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dynamiclink.*', 'DynamiclinkController.*', 1, 'System', NULL, '2018-08-14 13:56:00.000', '2018-08-14 13:56:00.000', 1353);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dynamiclink.index', 'DynamiclinkController.index', 1, 'System', NULL, '2018-08-14 13:56:00.000', '2018-08-14 13:56:00.000', 1352);




/* Report for Tax Rate */


/* For Taxrate Report */

ALTER TABLE `tblInvoiceTaxRate`
	ADD COLUMN `InvoiceDetailID` INT(11) NOT NULL DEFAULT '0' AFTER `InvoiceID`;
	
/* Remove unique key constraints in cols from indexes*/	

ALTER TABLE `tblInvoiceTaxRate`
 DROP INDEX `IX_InvoiceTaxRateUnique`,
 ADD INDEX `IX_InvoiceTaxRateUnique` (`InvoiceID`, `TaxRateID`, `InvoiceTaxType`);
 
 ALTER TABLE `tblInvoiceTaxRate`
	DROP INDEX `IX_InvoiceTaxRateDetailIDUnique`; 

ALTER TABLE `tblRecurringInvoiceTaxRate`
	DROP INDEX `RecurringInvoiceTaxRateUnique`;
	
ALTER TABLE `tblRecurringInvoiceTaxRate`
	ADD COLUMN `RecurringInvoiceDetailID` INT(11) NOT NULL DEFAULT '0' AFTER `RecurringInvoiceID`;

ALTER TABLE `tblEstimateTaxRate`
	DROP INDEX `IX_EstimateTaxRateUnique`;

ALTER TABLE `tblEstimateTaxRate`
	ADD COLUMN `EstimateDetailID` INT(11) NOT NULL DEFAULT '0' AFTER `EstimateID`;
	

DROP PROCEDURE IF EXISTS `prc_CreateInvoiceFromRecurringInvoice`;
DELIMITER //
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

	INSERT INTO tmp_Invoices_ 
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

		
     SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with INVOICE DATE ',DATE(temp.NextInvoiceDate)) separator '\n\r') INTO v_SkippedWIthDate
	  FROM tmp_Invoices_ temp
	  WHERE (DATE(temp.NextInvoiceDate) > DATE(p_CurrentDate));

	  
	  SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with exceding limit Occurrence ',(SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID)) separator '\n\r') INTO v_SkippedWIthOccurence
	  FROM tmp_Invoices_ temp
	  	WHERE (temp.Occurrence > 0
		  	AND (SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID) >= temp.Occurrence);

     
     SELECT CASE
	  				WHEN ((v_SkippedWIthDate IS NOT NULL) OR (v_SkippedWIthOccurence IS NOT NULL))
					THEN CONCAT(IFNULL(v_SkippedWIthDate,''),'\n\r',IFNULL(v_SkippedWIthOccurence,'')) ELSE ''
				END as message INTO v_Message;

	IF(v_Message="") THEN
        

		INSERT INTO tblInvoice (`CompanyID`, `AccountID`, `Address`, `InvoiceNumber`, `IssueDate`, `CurrencyID`, `PONumber`, `InvoiceType`, `SubTotal`, `TotalDiscount`, `TaxRateID`, `TotalTax`, `InvoiceTotal`, `GrandTotal`, `Description`, `Attachment`, `Note`, `Terms`, `InvoiceStatus`, `PDF`, `UsagePath`, `PreviousBalance`, `TotalDue`, `Payment`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ItemInvoice`, `FooterTerm`,`RecurringInvoiceID`,`ProcessID`,`BillingClassID`)
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
		p_ProsessID,
		rinv.BillingClassID
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

		INSERT INTO tblInvoiceTaxRate ( `InvoiceID`,`InvoiceDetailID`, `TaxRateID`, `TaxAmount`,`InvoiceTaxType`,`Title`, `CreatedBy`,`ModifiedBy`)
		SELECT
			inv.InvoiceID,
			rinvt.RecurringInvoiceDetailID,
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

			
		INSERT INTO tblRecurringInvoiceLog (RecurringInvoiceID,Note,RecurringInvoiceLogStatus,created_at)
		SELECT inv.RecurringInvoiceID,CONCAT(v_Note, CONCAT(LTRIM(RTRIM(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.InvoiceNumber)))) as Note,p_LogStatus as InvoiceLogStatus,p_CurrentDate as created_at
		FROM tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;


		
		UPDATE tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		SET FullInvoiceNumber = IF(inv.InvoiceType=1,CONCAT(ltrim(rtrim(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber))),ltrim(rtrim(inv.InvoiceNumber)))
		WHERE inv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;
		
		CALL prc_StockManageRecurringInvoice(p_CompanyID,p_InvoiceIDs,v_InvoiceID,p_ModifiedBy);
		
		DROP TEMPORARY TABLE IF EXISTS tmp_TaxRateDetail_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TaxRateDetail_(
			InvoiceTaxRateID int,
			InvoiceDetailID int
		);
	
		INSERT INTO tmp_TaxRateDetail_
		SELECT 
		tblInvoiceTaxRate.InvoiceTaxRateID,
		tblInvoiceDetail.InvoiceDetailID
		FROM tblInvoiceTaxRate
		JOIN tblRecurringInvoiceDetail on tblRecurringInvoiceDetail.RecurringInvoiceDetailID=tblInvoiceTaxRate.InvoiceDetailID 
		JOIN tblInvoiceDetail on tblInvoiceDetail.InvoiceID=v_InvoiceID AND tblInvoiceDetail.ProductID=tblRecurringInvoiceDetail.ProductID AND tblInvoiceDetail.Price=tblRecurringInvoiceDetail.Price
		WHERE tblInvoiceTaxRate.InvoiceID = v_InvoiceID AND tblInvoiceTaxRate.InvoiceDetailID !=0
		GROUP BY tblInvoiceTaxRate.InvoiceTaxRateID,tblInvoiceDetail.InvoiceDetailID
		;
		
		UPDATE tblInvoiceTaxRate a
		INNER JOIN tmp_TaxRateDetail_ b ON a.InvoiceTaxRateID=b.InvoiceTaxRateID
		SET a.InvoiceDetailID=b.InvoiceDetailID
		WHERE a.InvoiceTaxRateID=b.InvoiceTaxRateID;

	END IF;

	SELECT v_Message as Message, IFNULL(v_InvoiceID,0) as InvoiceID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


/* Inventory Permissions */
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1358, 'ItemType.All', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1357, 'ItemType.View', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1356, 'ItemType.Delete', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1355, 'ItemType.Edit', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1354, 'ItemType.Add', 1, 7);

insert into tblResourceCategories set ResourceCategoryName='DynamicField.Add',CompanyID=1,CategoryGroupID=7;
insert into tblResourceCategories set ResourceCategoryName='DynamicField.Edit',CompanyID=1,CategoryGroupID=7;
insert into tblResourceCategories set ResourceCategoryName='DynamicField.Delete',CompanyID=1,CategoryGroupID=7;
insert into tblResourceCategories set ResourceCategoryName='DynamicField.View',CompanyID=1,CategoryGroupID=7;
insert into tblResourceCategories set ResourceCategoryName='DynamicField.All',CompanyID=1,CategoryGroupID=7;

INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1365, 'StockHistory.All', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1364, 'StockHistory.View', 1, 7);


INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ItemType.UpdateBulkItemTypeStatus', 'ItemTypeController.UpdateBulkItemTypeStatus', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', NULL);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ItemType.delete', 'ItemTypeController.delete', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1356);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ItemType.update', 'ItemTypeController.update', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1355);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ItemType.create', 'ItemTypeController.create', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1354);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ItemType.ajax_datagrid', 'ItemTypeController.ajax_datagrid', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1357);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ItemType.*', 'ItemTypeController.*', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1358);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ItemType.index', 'ItemTypeController.index', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1357);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.DeleteBulkDynamicField', 'DynamicFieldController.DeleteBulkDynamicField', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', NULL);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.UpdateBulkDynamicFieldStatus', 'DynamicFieldController.UpdateBulkDynamicFieldStatus', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', NULL);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.delete', 'DynamicFieldController.delete', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1361);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.update', 'DynamicFieldController.update', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1360);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.create', 'DynamicFieldController.create', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1359);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.ajax_datagrid', 'DynamicFieldController.ajax_datagrid', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1362);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.*', 'DynamicFieldController.*', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1363);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('DynamicField.index', 'DynamicFieldController.index', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1362);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('StockHistory.ajax_datagrid', 'StockHistoryController.ajax_datagrid', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1364);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('StockHistory.*', 'StockHistoryController.*', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1365);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('StockHistory.index', 'StockHistoryController.index', 1, 'Sumera Khan', NULL, '2018-08-14 13:56:01.000', '2018-08-14 13:56:01.000', 1364);


/* Account PBX Blocking - Ratemanagement3 */

INSERT INTO `tblDynamicFields` (`CompanyID`, `Type`, `FieldDomType`, `FieldName`, `FieldSlug`, `FieldDescription`, `FieldOrder`, `Status`, `created_at`, `created_by`, `updated_at`, `updated_by`, `ItemTypeID`, `Minimum`, `Maximum`, `DefaultValue`, `SelectVal`) VALUES (1, 'account', 'select', 'Pbx Account Status', 'pbxaccountstatus', 'PBX Account Status', 0, 1, '2018-08-25 13:44:18', 'System', NULL, NULL, 0, 0, 0, NULL, NULL);
INSERT INTO `tblDynamicFields` (`CompanyID`, `Type`, `FieldDomType`, `FieldName`, `FieldSlug`, `FieldDescription`, `FieldOrder`, `Status`, `created_at`, `created_by`, `updated_at`, `updated_by`, `ItemTypeID`, `Minimum`, `Maximum`, `DefaultValue`, `SelectVal`) VALUES (1, 'account', 'boolean', 'Auto Block', 'autoblock', 'PBX Auto Block', 0, 1, '2018-08-25 13:46:10', 'System', NULL, NULL, 0, 0, 0, NULL, NULL);


/* Mysql process kill on Cron Job Termination */
USE Ratemanagement3;
ALTER TABLE `tblCronJob`
	ADD COLUMN `ProcessID` VARCHAR(200) NULL DEFAULT NULL AFTER `CdrBehindDuration`,
	ADD COLUMN `MysqlPID` VARCHAR(200) NULL DEFAULT NULL AFTER `ProcessID`;

USE Ratemanagement3;	
DROP PROCEDURE IF EXISTS `prc_UpdateMysqlPID`;
DELIMITER //
CREATE PROCEDURE `prc_UpdateMysqlPID`(
	IN `p_processId` VARCHAR(200)

)
BEGIN
	DECLARE MysqlPID VARCHAR(200);
	  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		
		SELECT CONNECTION_ID() into MysqlPID;
		
		UPDATE tblCronJob
			SET MysqlPID=MysqlPID
		WHERE ProcessID=p_processId;

	  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
	

USE Ratemanagement3;	
DROP PROCEDURE IF EXISTS `prc_GetActiveCronJob`;	
DELIMITER //
CREATE PROCEDURE `prc_GetActiveCronJob`(
	IN `p_companyid` INT,
	IN `p_Title` VARCHAR(500),
	IN `p_Status` INT,
	IN `p_Active` INT,
	IN `p_Type` INT,
	IN `p_CurrentDateTime` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT 


)
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	
    IF p_isExport = 0
    THEN
         
		SELECT
			jb.Active,
			CONCAT(jb.PID,CASE WHEN jb.MysqlPID !='' THEN '-' ELSE '' END,CASE when jb.MysqlPID!='' THEN jb.MysqlPID ELSE '' END) as PID,
			jb.JobTitle,                
			CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,p_CurrentDateTime),' Hours, ',TIMESTAMPDIFF(minute,LastRunTime,p_CurrentDateTime)%60,' Minutes, ',TIMESTAMPDIFF(second,LastRunTime,p_CurrentDateTime)%60 , ' Seconds' ) AS RunningTime,
			
 			jb.LastRunTime,
			jb.NextRunTime,			
			jb.CronJobID,
			jb.Status,	                 	
          jb.CronJobCommandID,
          jb.Settings	,
 		   (select CronJobStatus from tblCronJobLog where tblCronJobLog.CronJobID = jb.CronJobID order by  CronJobLogID desc limit 1)  as  CronJobStatus
			 	                 							
		FROM tblCronJob jb
		INNER JOIN tblCronJobCommand cm ON cm.CronJobCommandID = jb.CronJobCommandID
      WHERE jb.CompanyID = p_companyid 
		AND (p_Title = '' OR (p_Title != '' AND jb.JobTitle like concat('%', p_Title , '%') ) )
		AND  (p_Status = -1 OR (p_Status != -1 and jb.Status = p_Status ) )
		 AND (p_Active = -1 OR (p_Active != -1 and  jb.Active = p_Active ) )
		 AND (p_Type = 0 OR (p_Type != 0 and  jb.CronJobCommandID = p_Type ) )
      ORDER BY                
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleDESC') THEN jb.JobTitle
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleASC') THEN jb.JobTitle
			END ASC,				
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDDESC') THEN jb.PID
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDASC') THEN jb.PID
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeASC') THEN jb.LastRunTime
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeDESC') THEN jb.LastRunTime
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextRunTimeASC') THEN jb.NextRunTime
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextRunTimeDESC') THEN jb.NextRunTime
			END DESC
		
		LIMIT p_RowspPage OFFSET v_OffSet_;


     SELECT
         COUNT(jb.CronJobID) as totalcount
     FROM tblCronJob jb
		INNER JOIN tblCronJobCommand cm ON cm.CronJobCommandID = jb.CronJobCommandID
      WHERE jb.CompanyID = p_companyid 
		AND (p_Title = '' OR (p_Title != '' AND jb.JobTitle like concat('%', p_Title , '%') ) )
		AND  (p_Status = -1 OR (p_Status != -1 and jb.Status = p_Status ) )
		AND (p_Active = -1 OR (p_Active != -1 and  jb.Active = p_Active ) )
		AND (p_Type = 0 OR (p_Type != 0 and  jb.CronJobCommandID = p_Type ) );
		 
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
			jb.Active,
			jb.PID,
			jb.JobTitle,                
			CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,p_CurrentDateTime),' Hours, ',TIMESTAMPDIFF(minute,LastRunTime,p_CurrentDateTime)%60,' Minutes, ',TIMESTAMPDIFF(second,LastRunTime,p_CurrentDateTime)%60 , ' Seconds' ) AS RunningTime,
 			jb.LastRunTime,
			jb.NextRunTime
        FROM tblCronJob jb
			INNER JOIN tblCronJobCommand cm ON cm.CronJobCommandID = jb.CronJobCommandID
	      WHERE jb.CompanyID = p_companyid 
			AND (p_Title = '' OR (p_Title != '' AND jb.JobTitle like concat('%', p_Title , '%') ) )
			AND  (p_Status = -1 OR (p_Status != -1 and jb.Status = p_Status ) )
			AND (p_Active = -1 OR (p_Active != -1 and  jb.Active = p_Active ) )
			AND (p_Type = 0 OR (p_Type != 0 and  jb.CronJobCommandID = p_Type ) );
		 	
		 	
    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
	
USE RMBilling3;	
DROP PROCEDURE IF EXISTS `prc_ProcesssCDR`;	
DELIMITER //
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
	IN `p_InboundTableID` INT,
	IN `p_RerateAccounts` INT

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);

	CALL prc_autoAddIP(p_CompanyID,p_CompanyGatewayID); -- only if AutoAddIP is on
	
	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name); -- update service ID based on IP or cli


	DROP TEMPORARY TABLE IF EXISTS tmp_Customers_;
	CREATE TEMPORARY TABLE tmp_Customers_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		CompanyGatewayID INT
	);

	IF p_RerateAccounts!=0
	THEN
		-- selected customer 
      SET @sql1 = concat("insert into tmp_Customers_ (AccountID) values ('", replace(( select TRIM(REPLACE(group_concat(distinct IFNULL(REPLACE(REPLACE(json_extract(Settings, '$.Accounts'), '[', ''), ']', ''),0)),'"','')) as AccountID from Ratemanagement3.tblCompanyGateway), ",", "'),('"),"');");
      PREPARE stmt1 FROM @sql1;
      EXECUTE stmt1;
      DEALLOCATE PREPARE stmt1;
      DELETE FROM tmp_Customers_ WHERE AccountID=0;
      UPDATE tmp_Customers_ SET CompanyGatewayID=p_CompanyGatewayID WHERE 1;
  END IF;

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
	WHERE tblService.ServiceID > 0 AND tblService.CompanyGatewayID > 0 AND ud.ServiceID IS NULL
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;



	CALL prc_ProcessCDRAccount(p_CompanyID,p_CompanyGatewayID,p_processId,p_tbltempusagedetail_name,p_NameFormat);

	

	-- p_OutboundTableID is for cdr upload
	IF ( ( SELECT COUNT(*) FROM tmp_Service_ ) > 0 OR p_OutboundTableID > 0)
	THEN


		CALL prc_RerateOutboundService(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate,p_OutboundTableID);

	ELSE


		CALL prc_RerateOutboundTrunk(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate);


		CALL prc_autoUpdateTrunk(p_CompanyID,p_CompanyGatewayID);

	END IF;

	 -- no rerate and prefix format

	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN

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


		CALL Ratemanagement3.prc_getDefaultCodes(p_CompanyID);


		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;


	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);


	-- for mirta only
	IF (  p_RateCDR = 1 )
	THEN
		-- update cost = 0 where cc_type = 4 (OUTNOCHARGE)
		SET @stm = CONCAT('
	UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN  RMCDR3.`' , p_tbltempusagedetail_name ,'_Retail' , '` udr ON ud.TempUsageDetailID = udr.TempUsageDetailID AND ud.ProcessID = udr.ProcessID
	SET cost = 0
  WHERE ud.ProcessID="' , p_processId , '" AND udr.cc_type = 4 ;
	');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

	END IF;
	
	
	
	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

USE RMCDR3;
DROP PROCEDURE IF EXISTS `prc_insertCDR`;
DELIMITER //
CREATE PROCEDURE `prc_insertCDR`(
	IN `p_processId` varchar(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;

	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);

	-- Find Gateway Name for Mirta only
	SET @gateway_name  = '';
	SET @stm1 = CONCAT('select g.Name into @gateway_name   FROM  `' , p_tbltempusagedetail_name , '` d
			INNER JOIN Ratemanagement3.tblCompanyGateway cg on d.CompanyGatewayID = cg.CompanyGatewayID
			INNER JOIN Ratemanagement3.tblGateway g on g.GatewayID = cg.GatewayID
			WHERE processid = "' , p_processId , '" AND g.Name = "PBX" limit 1 ');
	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;
 


	SET @stm2 = CONCAT('
	INSERT INTO   tblUsageHeader (CompanyID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountID,StartDate,created_at,ServiceID)
	SELECT DISTINCT d.CompanyID,d.CompanyGatewayID,d.GatewayAccountPKID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW(),d.ServiceID
	FROM `' , p_tbltempusagedetail_name , '` d
	LEFT JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	WHERE h.GatewayAccountID IS NULL AND processid = "' , p_processId , '";
	');

	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;

	SET @stm3 = CONCAT('
	INSERT INTO tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
	SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
	FROM  `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	WHERE   processid = "' , p_processId , '"
		AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL );

	');

	PREPARE stmt3 FROM @stm3;
	EXECUTE stmt3;
	DEALLOCATE PREPARE stmt3;

	SET @stm4 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL);
	');

	PREPARE stmt4 FROM @stm4;
	EXECUTE stmt4;
	DEALLOCATE PREPARE stmt4;


	-- FOR MIRTA ONLY 

	 IF (@gateway_name = 'PBX') THEN
   	
			SET @stm31 = CONCAT('
			INSERT INTO tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
			SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
			FROM  `' , p_tbltempusagedetail_name , '` d
			INNER JOIN tblUsageHeader h
			ON h.GatewayAccountPKID = d.GatewayAccountPKID
				AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
			WHERE   processid = "' , p_processId , '"
				AND disposition IS NOT NULL AND disposition <> "ANSWERED"; 
		
			');
		
			PREPARE stmt31 FROM @stm31;
			EXECUTE stmt31;
			DEALLOCATE PREPARE stmt31;
		
			SET @stm41 = CONCAT('
			DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND disposition IS NOT NULL AND disposition <> "ANSWERED";
			'); 
		
			PREPARE stmt41 FROM @stm41;
			EXECUTE stmt41;
			DEALLOCATE PREPARE stmt41; 
	END IF;
	-- for mirta only over
	 
	


	SET @stm5 = CONCAT('
	INSERT INTO tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
	SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
	FROM  `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	WHERE   processid = "' , p_processId , '" ;
	');

	PREPARE stmt5 FROM @stm5;
	EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;
	
	
	-- for Mirta retail only
   IF (@gateway_name = 'PBX') THEN
   
		SET @stm51 = CONCAT('
		INSERT INTO  tblRetailUsageDetail (UsageDetailID,ID,cc_type,ProcessID)
		SELECT Distinct d.UsageDetailID,rd.ID,rd.cc_type,rd.ProcessID
		FROM   `' , p_tbltempusagedetail_name , '_Retail` rd
		INNER JOIN tblUsageDetails d
		ON d.ProcessID = rd.ProcessID  AND d.ID = rd.ID 
		WHERE   d.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt51 FROM @stm51;
		EXECUTE stmt51;
		DEALLOCATE PREPARE stmt51;

   END IF;

	SET @stm6 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt6 FROM @stm6;
	EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

	
USE RMBilling3;
DROP PROCEDURE IF EXISTS `prc_reseller_ProcesssCDR`;
DELIMITER //
CREATE PROCEDURE `prc_reseller_ProcesssCDR`(
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
	IN `p_InboundTableID` INT,
	IN `p_RerateAccounts` INT

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Customers_;
	CREATE TEMPORARY TABLE tmp_Customers_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		CompanyGatewayID INT
	);


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
	WHERE tblService.ServiceID > 0 AND tblService.CompanyGatewayID > 0 AND ud.ServiceID IS NULL
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;
	
	IF ( ( SELECT COUNT(*) FROM tmp_Service_ ) > 0 OR p_OutboundTableID > 0)
	THEN
		CALL prc_RerateOutboundService(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate,p_OutboundTableID);
	ELSE
		CALL prc_RerateOutboundTrunk(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate);
		CALL prc_autoUpdateTrunk(p_CompanyID,p_CompanyGatewayID);
	END IF;	 

	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN

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
		
		CALL Ratemanagement3.prc_getDefaultCodes(p_CompanyID);
		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;

	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);		-- for mirta only	
	IF (  p_RateCDR = 1 )
	THEN
		
		SET @stm = CONCAT('
			UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN  RMCDR3.`' , p_tbltempusagedetail_name ,'_Retail' , '` udr ON ud.TempUsageDetailID = udr.TempUsageDetailID AND ud.ProcessID = udr.ProcessID
			SET cost = 0
			WHERE ud.ProcessID="' , p_processId , '" AND udr.cc_type = 4 ;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

	END IF;
	
	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
	

USE RMCDR3;
DROP PROCEDURE IF EXISTS `prc_reseller_insertCDR`;
DELIMITER //
CREATE PROCEDURE `prc_reseller_insertCDR`(
	IN `p_processId` varchar(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);

	SET @stm2 = CONCAT('
	INSERT INTO   tblUsageHeader (CompanyID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountID,StartDate,created_at,ServiceID)
	SELECT DISTINCT d.CompanyID,d.CompanyGatewayID,d.GatewayAccountPKID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW(),d.ServiceID
	FROM `' , p_tbltempusagedetail_name , '` d
	LEFT JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		AND h.AccountID = d.AccountID
	WHERE h.GatewayAccountID IS NULL AND processid = "' , p_processId , '";
	');

	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;
	

	SET @stm4 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL);
	');

	PREPARE stmt4 FROM @stm4;
	EXECUTE stmt4;
	DEALLOCATE PREPARE stmt4;

	SET @stm5 = CONCAT('
	INSERT INTO tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
	SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
	FROM  `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		AND h.AccountID = d.AccountID
	WHERE   processid = "' , p_processId , '" ;
	');

	PREPARE stmt5 FROM @stm5;
	EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;

	-- for Mirta retail only
	
	SET @stm51 = CONCAT('
	INSERT INTO  tblRetailUsageDetail (UsageDetailID,ID,cc_type,ProcessID)
	SELECT Distinct d.UsageDetailID,rd.ID,rd.cc_type,rd.ProcessID
	FROM   `' , p_tbltempusagedetail_name , '_Retail` rd
	INNER JOIN tblUsageDetails d
	ON d.ProcessID = rd.ProcessID AND rd.ID=d.ID
	WHERE   d.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt51 FROM @stm51;
	EXECUTE stmt51;
	DEALLOCATE PREPARE stmt51;


	SET @stm52 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '_Retail` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stm52 FROM @stm52;
	EXECUTE stm52;
	DEALLOCATE PREPARE stm52;

	SET @stm6 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt6 FROM @stm6;
	EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;	



USE StagingReport;
DROP PROCEDURE IF EXISTS `prc_updateLiveTables`;
DELIMITER //
CREATE PROCEDURE `prc_updateLiveTables`(
	IN `p_CompanyID` INT,
	IN `p_UniqueID` VARCHAR(50)

)
BEGIN

	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);

	SET @stmt = CONCAT('
	UPDATE tmp_tblUsageDetailsReport_',p_UniqueID,' uh
	INNER JOIN RMBilling3.tblGatewayAccount ga
		ON  uh.GatewayAccountPKID = ga.GatewayAccountPKID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ',p_CompanyID,';
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	UPDATE tmp_tblVendorUsageDetailsReport_',p_UniqueID,' uh
	INNER JOIN RMBilling3.tblGatewayAccount ga
		ON  uh.GatewayVAccountPKID = ga.GatewayAccountPKID
	SET uh.VAccountID = ga.AccountID
	WHERE uh.VAccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ',p_CompanyID,';
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//


USE StagingReport;
DROP PROCEDURE IF EXISTS `prc_generateSummaryLive`;
DELIMITER //
CREATE PROCEDURE `prc_generateSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)

)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);
	
	CALL fngetDefaultCodes(p_CompanyID); 
	CALL fnGetUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
	CALL fnGetVendorUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
	CALL fnUpdateCustomerLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
	CALL fnUpdateVendorLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);

	DELETE FROM tmp_UsageSummaryLive WHERE CompanyID = p_CompanyID;

	SET @stmt = CONCAT('
	INSERT INTO tmp_UsageSummaryLive(
		DateID,
		TimeID,
		CompanyID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		d.DateID,
		t.TimeID,
		a.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ud.GatewayAccountPKID,
		ud.GatewayVAccountPKID,
		ud.AccountID,
		ud.VAccountID,
		ud.trunk,
		ud.area_prefix,
		ud.userfield,
		COALESCE(SUM(ud.cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCost ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailsReport_',p_UniqueID,' ud  
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = ud.AccountID	
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	WHERE a.CompanyID = ',p_CompanyID,'
		AND ud.AccountID IS NOT NULL
	GROUP BY d.DateID,t.TimeID,ud.CompanyID,ud.CompanyGatewayID,ud.ServiceID,ud.GatewayAccountPKID,ud.GatewayVAccountPKID,ud.AccountID,ud.VAccountID,ud.area_prefix,ud.trunk,ud.userfield;
	');


	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	UPDATE tmp_UsageSummaryLive
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_UsageSummaryLive.CountryID =code.CountryID
	WHERE tmp_UsageSummaryLive.CompanyID = p_CompanyID AND code.CountryID > 0;

	START TRANSACTION;

	DELETE us FROM tblUsageSummaryDayLive us 
	INNER JOIN tblHeader sh ON us.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	DELETE usd FROM tblUsageSummaryHourLive usd
	INNER JOIN tblHeader sh ON usd.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	DELETE h FROM tblHeader h 
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)u
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;

	INSERT INTO tblHeader (
		DateID,
		CompanyID,
		AccountID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		DateID,
		CompanyID,
		AccountID,
		SUM(TotalCharges) as TotalCharges,
		SUM(TotalCost) as TotalCost,
		SUM(TotalBilledDuration) as TotalBilledDuration,
		SUM(TotalDuration) as TotalDuration,
		SUM(NoOfCalls) as NoOfCalls,
		SUM(NoOfFailCalls) as NoOfFailCalls
	FROM tmp_UsageSummaryLive 
	WHERE CompanyID = p_CompanyID
	GROUP BY DateID,CompanyID,AccountID;

	DELETE FROM tmp_SummaryHeaderLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeaderLive (HeaderID,DateID,CompanyID,AccountID)
	SELECT 
		sh.HeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID
	FROM tblHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	INSERT INTO tblUsageSummaryDayLive (
		HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT
		sh.HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		SUM(us.TotalCharges),
		SUM(us.TotalCost),
		SUM(us.TotalBilledDuration),
		SUM(us.TotalDuration),
		SUM(us.NoOfCalls),
		SUM(us.NoOfFailCalls)
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID
	GROUP BY us.DateID,us.CompanyID,us.CompanyGatewayID,us.ServiceID,us.GatewayAccountPKID,us.GatewayVAccountPKID,us.AccountID,us.VAccountID,us.AreaPrefix,us.Trunk,us.CountryID,sh.HeaderID,us.userfield;

	INSERT INTO tblUsageSummaryHourLive (
		HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		sh.HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		us.TotalCharges,
		us.TotalCost,
		us.TotalBilledDuration,
		us.TotalDuration,
		us.NoOfCalls,
		us.NoOfFailCalls
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID;

	COMMIT;

END//
DELIMITER ;

USE StagingReport;
DROP PROCEDURE IF EXISTS `fnGetUsageForSummary`;
DELIMITER //
CREATE PROCEDURE `fnGetUsageForSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);
	
	SET @stmt = CONCAT('
	INSERT IGNORE INTO tmp_tblUsageDetailsReport_' , p_UniqueID , ' (
		UsageDetailID,
		AccountID,
		CompanyID,
		CompanyGatewayID,
		GatewayAccountPKID,
		connect_time,
		connect_date,
		billed_duration,
		area_prefix,
		cost,
		duration,
		trunk,
		call_status,
		ServiceID,
		disposition,
		userfield,
		pincode,
		extension,
		ID
	)
	SELECT 
		ud.UsageDetailID,
		uh.AccountID,
		a.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountPKID,
		CONCAT(DATE_FORMAT(ud.connect_time,"%H"),":",IF(MINUTE(ud.connect_time)<30,"00","30"),":00"),
		DATE_FORMAT(ud.connect_time,"%Y-%m-%d"),
		billed_duration,
		area_prefix,
		cost,
		duration,
		trunk,
		1 as call_status,
		uh.ServiceID,
		disposition,
		userfield,
		pincode,
		extension,
		ID
	FROM RMCDR3.tblUsageDetails  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = uh.AccountID	
	WHERE
		a.CompanyID = ' , p_CompanyID , '
		AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN "' , p_StartDate , '" AND "' , p_EndDate , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT IGNORE INTO tmp_tblUsageDetailsReport_' , p_UniqueID , ' (
		UsageDetailID,
		AccountID,
		CompanyID,
		CompanyGatewayID,
		GatewayAccountPKID,
		connect_time,
		connect_date,
		billed_duration,
		area_prefix,
		cost,
		duration,
		trunk,
		call_status,
		ServiceID,
		disposition,
		userfield,
		pincode,
		extension,
		ID
	)
	SELECT 
		ud.UsageDetailFailedCallID,
		uh.AccountID,
		a.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountPKID,
		CONCAT(DATE_FORMAT(ud.connect_time,"%H"),":",IF(MINUTE(ud.connect_time)<30,"00","30"),":00"),
		DATE_FORMAT(ud.connect_time,"%Y-%m-%d"),
		billed_duration,
		area_prefix,
		cost,
		duration,
		trunk,
		2 as call_status,
		uh.ServiceID,
		disposition,
		userfield,
		pincode,
		extension,
		ID
	FROM RMCDR3.tblUsageDetailFailedCall  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = uh.AccountID	
	WHERE
		a.CompanyID = ' , p_CompanyID , '
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN "' , p_StartDate , '" AND "' , p_EndDate , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END//
DELIMITER ;

USE StagingReport;
DROP PROCEDURE IF EXISTS `fnGetVendorUsageForSummary`;
DELIMITER //
CREATE PROCEDURE `fnGetVendorUsageForSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);
	
	SET @stmt = CONCAT('
	INSERT IGNORE INTO tmp_tblVendorUsageDetailsReport_' , p_UniqueID , ' (
		VendorCDRID,
		VAccountID,
		CompanyID,
		CompanyGatewayID,
		GatewayVAccountPKID,
		ServiceID,
		connect_time,
		connect_date,
		billed_duration,
		duration,
		selling_cost,
		buying_cost,
		trunk,
		area_prefix,
		call_status_v,
		ID
	)
	SELECT 
		ud.VendorCDRID,
		uh.AccountID,
		a.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountPKID,
		uh.ServiceID,
		CONCAT(DATE_FORMAT(ud.connect_time,"%H"),":",IF(MINUTE(ud.connect_time)<30,"00","30"),":00"),
		DATE_FORMAT(ud.connect_time,"%Y-%m-%d"),
		billed_duration,
		duration,
		selling_cost,
		buying_cost,
		trunk,
		area_prefix,
		1 AS call_status,
		ID
	FROM RMCDR3.tblVendorCDR  ud
	INNER JOIN RMCDR3.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = uh.AccountID		
	WHERE
		a.CompanyID = ' , p_CompanyID , '
		AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN "' , p_StartDate , '" AND "' , p_EndDate , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT IGNORE INTO tmp_tblVendorUsageDetailsReport_' , p_UniqueID , ' (
		VendorCDRID,
		VAccountID,
		CompanyID,
		CompanyGatewayID,
		GatewayVAccountPKID,
		ServiceID,
		connect_time,
		connect_date,
		billed_duration,
		duration,
		selling_cost,
		buying_cost,
		trunk,
		area_prefix,
		call_status_v,
		ID
	)
	SELECT 
		ud.VendorCDRFailedID,
		uh.AccountID,
		a.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountPKID,
		uh.ServiceID,
		CONCAT(DATE_FORMAT(ud.connect_time,"%H"),":",IF(MINUTE(ud.connect_time)<30,"00","30"),":00"),
		DATE_FORMAT(ud.connect_time,"%Y-%m-%d"),
		billed_duration,
		duration,
		selling_cost,
		buying_cost,
		trunk,
		area_prefix,
		2 AS call_status,
		ID
	FROM RMCDR3.tblVendorCDRFailed  ud
	INNER JOIN RMCDR3.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = uh.AccountID	
	WHERE
		a.CompanyID = ' , p_CompanyID , '
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN "' , p_StartDate , '" AND "' , p_EndDate , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END//
DELIMITER ;


USE StagingReport;
DROP PROCEDURE IF EXISTS `prc_generateSummary`;
DELIMITER //
CREATE PROCEDURE `prc_generateSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)

)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);
	
	CALL fngetDefaultCodes(p_CompanyID); 
	
	
	CALL fnUpdateCustomerLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);

	DELETE FROM tmp_UsageSummary WHERE CompanyID = p_CompanyID;

	SET @stmt = CONCAT('
	INSERT INTO tmp_UsageSummary(
		DateID,
		TimeID,
		CompanyID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		d.DateID,
		t.TimeID,
		a.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ud.GatewayAccountPKID,
		ud.GatewayVAccountPKID,
		ud.AccountID,
		ud.VAccountID,
		ud.trunk,
		ud.area_prefix,
		ud.userfield,
		COALESCE(SUM(ud.cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCost ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailsReport_',p_UniqueID,' ud  
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = ud.AccountID
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	WHERE a.CompanyID = ',p_CompanyID,'
		AND ud.AccountID IS NOT NULL
	GROUP BY d.DateID,t.TimeID,ud.CompanyID,ud.CompanyGatewayID,ud.ServiceID,ud.GatewayAccountPKID,ud.GatewayVAccountPKID,ud.AccountID,ud.VAccountID,ud.area_prefix,ud.trunk,ud.userfield;
	');


	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	UPDATE tmp_UsageSummary 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_UsageSummary.CountryID =code.CountryID
	WHERE tmp_UsageSummary.CompanyID = p_CompanyID AND code.CountryID > 0;

	START TRANSACTION;
	
	DELETE us FROM tblUsageSummaryDay us 
	INNER JOIN tblHeader sh ON us.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	DELETE usd FROM tblUsageSummaryHour usd
	INNER JOIN tblHeader sh ON usd.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	DELETE h FROM tblHeader h 
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummary)u
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;
	
	INSERT INTO tblHeader (
		DateID,
		CompanyID,
		AccountID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		DateID,
		CompanyID,
		AccountID,
		SUM(TotalCharges) as TotalCharges,
		SUM(TotalCost) as TotalCost,
		SUM(TotalBilledDuration) as TotalBilledDuration,
		SUM(TotalDuration) as TotalDuration,
		SUM(NoOfCalls) as NoOfCalls,
		SUM(NoOfFailCalls) as NoOfFailCalls
	FROM tmp_UsageSummary 
	WHERE CompanyID = p_CompanyID
	GROUP BY DateID,CompanyID,AccountID;
	
	DELETE FROM tmp_SummaryHeader WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeader (HeaderID,DateID,CompanyID,AccountID)
	SELECT 
		sh.HeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID
	FROM tblHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummary)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	INSERT INTO tblUsageSummaryDay (
		HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT
		sh.HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		SUM(us.TotalCharges),
		SUM(us.TotalCost),
		SUM(us.TotalBilledDuration),
		SUM(us.TotalDuration),
		SUM(us.NoOfCalls),
		SUM(us.NoOfFailCalls)
	FROM tmp_SummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)	 
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID
	GROUP BY us.DateID,us.CompanyID,us.CompanyGatewayID,us.ServiceID,us.GatewayAccountPKID,us.GatewayVAccountPKID,us.AccountID,us.VAccountID,us.AreaPrefix,us.Trunk,us.CountryID,sh.HeaderID,us.userfield;
	
	INSERT INTO tblUsageSummaryHour (
		HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls	
	)
	SELECT 
		sh.HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		CountryID,
		us.TotalCharges,
		us.TotalCost,
		us.TotalBilledDuration,
		us.TotalDuration,
		us.NoOfCalls,
		us.NoOfFailCalls
	FROM tmp_SummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID;
	
	CALL fnDistinctList(p_CompanyID);

	COMMIT;
	
 	DELETE FROM tmp_UsageSummary WHERE CompanyID = p_CompanyID;
	
END//
DELIMITER ;

USE StagingReport;
DROP PROCEDURE IF EXISTS `prc_generateVendorSummary`;
DELIMITER //
CREATE PROCEDURE `prc_generateVendorSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)



)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);
	
	CALL fngetDefaultCodes(p_CompanyID);
-- 	CALL fnGetUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID); 
-- 	CALL fnGetVendorUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
	CALL fnUpdateVendorLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);

	DELETE FROM tmp_VendorUsageSummary WHERE CompanyID = p_CompanyID;

	SET @stmt = CONCAT('
	INSERT INTO tmp_VendorUsageSummary(
		DateID,
		TimeID,
		CompanyID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		Trunk,
		AreaPrefix,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ud.GatewayAccountPKID,
		ud.GatewayVAccountPKID,
		ud.AccountID,
		ud.VAccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.selling_cost),0)  AS TotalSales ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status_v=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status_v=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblVendorUsageDetailsReport_',p_UniqueID,' ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	WHERE ud.CompanyID = ',p_CompanyID,'
		AND ud.VAccountID IS NOT NULL
	GROUP BY d.DateID,t.TimeID,ud.CompanyID,ud.CompanyGatewayID,ud.ServiceID,ud.GatewayAccountPKID,ud.GatewayVAccountPKID,ud.AccountID,ud.VAccountID,ud.area_prefix,ud.trunk;	
	');


	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	UPDATE tmp_VendorUsageSummary 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_VendorUsageSummary.CountryID =code.CountryID
	WHERE tmp_VendorUsageSummary.CompanyID = p_CompanyID AND code.CountryID > 0;

	START TRANSACTION;
	
	DELETE us FROM tblVendorSummaryDay us 
	INNER JOIN tblHeaderV sh ON us.HeaderVID = sh.HeaderVID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	DELETE usd FROM tblVendorSummaryHour usd
	INNER JOIN tblHeaderV sh ON usd.HeaderVID = sh.HeaderVID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	DELETE h FROM tblHeaderV h 
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummary)u
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;
	
	INSERT INTO tblHeaderV (
		DateID,
		CompanyID,
		VAccountID,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		DateID,
		CompanyID,
		VAccountID,
		SUM(TotalCharges) as TotalCharges,
		SUM(TotalSales) as TotalSales,		
		SUM(TotalBilledDuration) as TotalBilledDuration,
		SUM(TotalDuration) as TotalDuration,
		SUM(NoOfCalls) as NoOfCalls,
		SUM(NoOfFailCalls) as NoOfFailCalls
	FROM tmp_VendorUsageSummary 
	WHERE CompanyID = p_CompanyID
	GROUP BY DateID,CompanyID,VAccountID;
	
	DELETE FROM tmp_SummaryVendorHeader WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryVendorHeader (HeaderVID,DateID,CompanyID,VAccountID)
	SELECT 
		sh.HeaderVID,
		sh.DateID,
		sh.CompanyID,
		sh.VAccountID
	FROM tblHeaderV sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummary)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	INSERT INTO tblVendorSummaryDay (
		HeaderVID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT
		sh.HeaderVID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		SUM(us.TotalCharges),
		SUM(us.TotalSales),
		SUM(us.TotalBilledDuration),
		SUM(us.TotalDuration),
		SUM(us.NoOfCalls),
		SUM(us.NoOfFailCalls)
	FROM tmp_SummaryVendorHeader sh
	INNER JOIN tmp_VendorUsageSummary us FORCE INDEX (Unique_key)	 
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.VAccountID = sh.VAccountID
	WHERE us.CompanyID = p_CompanyID
	GROUP BY us.DateID,us.CompanyID,us.CompanyGatewayID,us.ServiceID,us.GatewayAccountPKID,us.GatewayVAccountPKID,us.AccountID,us.VAccountID,us.AreaPrefix,us.Trunk,us.CountryID,sh.HeaderVID;
	
	INSERT INTO tblVendorSummaryHour (
		HeaderVID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls	
	)
	SELECT 
		sh.HeaderVID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		us.TotalCharges,
		us.TotalSales,
		us.TotalBilledDuration,
		us.TotalDuration,
		us.NoOfCalls,
		us.NoOfFailCalls
	FROM tmp_SummaryVendorHeader sh
	INNER JOIN tmp_VendorUsageSummary us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.VAccountID = sh.VAccountID
	WHERE us.CompanyID = p_CompanyID;

	CALL fnDistinctList(p_CompanyID);

	COMMIT;
	
	SET @stmt = CONCAT('TRUNCATE TABLE tmp_tblUsageDetailsReport_',p_UniqueID,';');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET @stmt = CONCAT('TRUNCATE TABLE tmp_tblVendorUsageDetailsReport_',p_UniqueID,';');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	
	/*SET @stmt = CONCAT('TRUNCATE TABLE tblTempCallDetail_2_',p_UniqueID,';');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;*/
	
	DELETE FROM tmp_VendorUsageSummary WHERE CompanyID = p_CompanyID;
	
	
END//
DELIMITER ;

USE StagingReport;
DROP PROCEDURE IF EXISTS `prc_generateVendorSummaryLive`;
DELIMITER //
CREATE PROCEDURE `prc_generateVendorSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)

)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(CONCAT(p_UniqueID,'vendor'));
	
	CALL fngetDefaultCodes(p_CompanyID);
--	CALL fnGetUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID); 
--	CALL fnGetVendorUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
--	CALL fnUpdateVendorLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);

	DELETE FROM tmp_VendorUsageSummaryLive WHERE CompanyID = p_CompanyID;

	SET @stmt = CONCAT('
	INSERT INTO tmp_VendorUsageSummaryLive(
		DateID,
		TimeID,
		CompanyID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		Trunk,
		AreaPrefix,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ud.GatewayAccountPKID,
		ud.GatewayVAccountPKID,
		ud.AccountID,
		ud.VAccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.selling_cost),0)  AS TotalSales ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status_v=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status_v=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblVendorUsageDetailsReport_',p_UniqueID,' ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	WHERE ud.CompanyID = ',p_CompanyID,'
		AND ud.VAccountID IS NOT NULL
	GROUP BY d.DateID,t.TimeID,ud.CompanyID,ud.CompanyGatewayID,ud.ServiceID,ud.GatewayAccountPKID,ud.GatewayVAccountPKID,ud.AccountID,ud.VAccountID,ud.area_prefix,ud.trunk;	
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	UPDATE tmp_VendorUsageSummaryLive 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_VendorUsageSummaryLive.CountryID =code.CountryID
	WHERE tmp_VendorUsageSummaryLive.CompanyID = p_CompanyID AND code.CountryID > 0;

	START TRANSACTION;

	DELETE us FROM tblVendorSummaryDayLive us 
	INNER JOIN tblHeaderV sh ON us.HeaderVID = sh.HeaderVID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	DELETE usd FROM tblVendorSummaryHourLive usd
	INNER JOIN tblHeaderV sh ON usd.HeaderVID = sh.HeaderVID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	DELETE h FROM tblHeaderV h 
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummaryLive)u
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;

	INSERT INTO tblHeaderV (
		DateID,
		CompanyID,
		VAccountID,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT
		DateID,
		CompanyID,
		VAccountID,
		SUM(TotalCharges) as TotalCharges,
		SUM(TotalSales) as TotalSales,
		SUM(TotalBilledDuration) as TotalBilledDuration,
		SUM(TotalDuration) as TotalDuration,
		SUM(NoOfCalls) as NoOfCalls,
		SUM(NoOfFailCalls) as NoOfFailCalls
	FROM tmp_VendorUsageSummaryLive 
	WHERE CompanyID = p_CompanyID
	GROUP BY DateID,CompanyID,VAccountID;

	DELETE FROM tmp_SummaryVendorHeaderLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryVendorHeaderLive (HeaderVID,DateID,CompanyID,VAccountID)
	SELECT 
		sh.HeaderVID,
		sh.DateID,
		sh.CompanyID,
		sh.VAccountID
	FROM tblHeaderV sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	INSERT INTO tblVendorSummaryDayLive (
		HeaderVID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT
		sh.HeaderVID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		SUM(us.TotalCharges),
		SUM(us.TotalSales),
		SUM(us.TotalBilledDuration),
		SUM(us.TotalDuration),
		SUM(us.NoOfCalls),
		SUM(us.NoOfFailCalls)
	FROM tmp_SummaryVendorHeaderLive sh
	INNER JOIN tmp_VendorUsageSummaryLive us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.VAccountID = sh.VAccountID
	WHERE us.CompanyID = p_CompanyID
	GROUP BY us.DateID,us.CompanyID,us.CompanyGatewayID,us.ServiceID,us.GatewayAccountPKID,us.GatewayVAccountPKID,us.AccountID,us.VAccountID,us.AreaPrefix,us.Trunk,us.CountryID,sh.HeaderVID;

	INSERT INTO tblVendorSummaryHourLive (
		HeaderVID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		TotalCharges,
		TotalSales,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		sh.HeaderVID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		Trunk,
		AreaPrefix,
		CountryID,
		us.TotalCharges,
		us.TotalSales,
		us.TotalBilledDuration,
		us.TotalDuration,
		us.NoOfCalls,
		us.NoOfFailCalls
	FROM tmp_SummaryVendorHeaderLive sh
	INNER JOIN tmp_VendorUsageSummaryLive us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.VAccountID = sh.VAccountID
	WHERE us.CompanyID = p_CompanyID;

	COMMIT;

END//
DELIMITER ;



/* For API */


USE `RMBilling3`;
DROP PROCEDURE IF EXISTS `prc_getProductsByItemType`;
DELIMITER //
CREATE PROCEDURE `prc_getProductsByItemType`(
	IN `p_CompanyID` INT,
	IN `p_ItemType` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT



)
    DETERMINISTIC
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_fieldName VARCHAR(255);
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ProductID INT,
		ItemTypeID INT		
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products_fields;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products_fields(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		DynamicFieldsID INT(11),
		FieldName Varchar(100),
		FieldType Varchar(100)
	);
		

	IF p_ItemType!='' THEN
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
					tblProduct.ProductID,
					tblProduct.ItemTypeID
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
			 LIMIT p_RowspPage OFFSET v_OffSet_;
			 
			 
			 SELECT 
			 	count(*) as totalcount
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1;
			 
			 
			 
	ELSE 
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
				tblProduct.ProductID,
				tblProduct.ItemTypeID
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
			LIMIT p_RowspPage OFFSET v_OffSet_;
			
			SELECT 
				count(*) as totalcount
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1;
			
	END IF;
			 
				
			INSERT INTO tmp_products_fields (FieldName,DynamicFieldsID,FieldType)
			SELECT 
				DISTINCT a.FieldName,
				a.DynamicFieldsID,
				a.FieldDomType
			FROM Ratemanagement3.tblDynamicFields a
			INNER JOIN tmp_products b
			ON a.ItemTypeID=b.ItemTypeID
			WHERE 
				a.Type='product'
				AND a.CompanyID=p_CompanyID
				AND a.`Status`=1;	
						 
			SET @v_pointer_1 = 1;
			SET @v_rowCount_1 = (SELECT COUNT(*) FROM tmp_products_fields);
		
		 WHILE @v_pointer_1 <= @v_rowCount_1
		  DO
		
			SET @fieldname = (SELECT FieldName from tmp_products_fields where RowID = @v_pointer_1);
			 
			SET @statement2 =  CONCAT('ALTER TABLE tmp_products ADD COLUMN `', @fieldname ,'` TEXT NULL;');
	 	
			PREPARE stm_query FROM @statement2;
			EXECUTE stm_query;
			DEALLOCATE PREPARE stm_query;
			
			SET @dynamicFieldID=	(SELECT DynamicFieldsID from tmp_products_fields where RowID = @v_pointer_1);
		
			SET @v_pointer_ = 1;
			SET @v_rowCount_ = (SELECT COUNT(*) FROM tmp_products);
			WHILE @v_pointer_ <= @v_rowCount_
		  	DO	
		  	
			 SET @ProductID=(select ProductID FROM tmp_products WHERE RowID=@v_pointer_);
			 
				SET @upateq=CONCAT('UPDATE tmp_products prod
		  	  	INNER JOIN Ratemanagement3.tblDynamicFieldsValue b ON b.DynamicFieldsID=',@dynamicFieldID,'
		  	  	INNER JOIN Ratemanagement3.tblDynamicFields a ON a.DynamicFieldsID=b.DynamicFieldsID
		  		SET `prod`.`',@fieldname,'`=b.FieldValue
		  		
		  		WHERE 
				  prod.ProductID=',@ProductID,'
				  AND b.ParentID=',@ProductID);
				  
				   -- select @upateq;
				  
				  PREPARE stm_query1 FROM @upateq;
				  EXECUTE stm_query1;
				  DEALLOCATE PREPARE stm_query1;
		  		
		  	SET @v_pointer_ = @v_pointer_ + 1;
		  	
		  	END WHILE;
		
			SET @v_pointer_1 = @v_pointer_1 + 1;
	
	  END WHILE;
	  

		SELECT 
		 	tblProduct.CompanyId,	
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Description,
			tblProduct.Amount,
			tblProduct.AppliedTo,
			tblProduct.Note,
			tblProduct.Buying_price,	
			tblProduct.Quantity,
			tblProduct.Low_stock_level,
			tblProduct.Enable_stock,
			tmp_products.*	 	
		FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;
		
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



/* Dispute NEON-1546 */
USE RMBilling3;
DELIMITER //
DROP PROCEDURE IF EXISTS `prc_getDisputes`;
CREATE PROCEDURE `prc_getDisputes`(
	IN `p_CompanyID` INT,
	IN `p_InvoiceType` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(100),
	IN `p_Status` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Export` INT
)
BEGIN

     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    			SELECT   
    			ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.DisputeID,
		 	   ds.Attachment,
		 	   a.AccountID,
		 		ds.Notes,
		 		ds.Ref
		 		
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID

				where ds.CompanyID = p_CompanyID

			   AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) 
            
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountDESC') THEN DisputeAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountASC') THEN DisputeAmount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN ds.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN ds.created_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN ds.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN ds.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDDESC') THEN DisputeID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDASC') THEN DisputeID
                END ASC 			 
					 					 					    	             
                
            LIMIT p_RowspPage OFFSET v_OffSet_;

		 

				 SELECT   
		 		COUNT(ds.DisputeID) AS totalcount,
		 		sum(ds.DisputeAmount) as TotalDisputeAmount
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
				where ds.CompanyID = p_CompanyID

				AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;
            
            
	ELSE

				SELECT   
				ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.Notes
				

            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
            AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
				AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


INSERT INTO `tblJobType` (`Code`, `Title`, `Description`, `CreatedDate`, `CreatedBy`, `ModifiedDate`, `ModifiedBy`) VALUES ('DR', 'Dispute Bulk Email', NULL, '2018-07-27 18:20:26', 'RateManagementSystem', NULL, NULL);


USE Ratemanagement3;
DELIMITER //
DROP PROCEDURE IF EXISTS `prc_CronJobAllPending`;
CREATE PROCEDURE `prc_CronJobAllPending`(
	IN `p_CompanyID` INT

)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

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



	SELECT
		tblCronJobCommand.Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Status = 1
	AND tblCronJob.Active = 0;






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
		WHERE jt.Code = 'RCV'
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
		WHERE jt.Code = 'RCV'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;





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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'MGA'
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
		WHERE jt.Code = 'MGA'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


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
		WHERE jt.Code = 'DSU'
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
		WHERE jt.Code = 'DSU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'QIP'
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
		WHERE jt.Code = 'QIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'ICU'
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
		WHERE jt.Code = 'ICU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'IU'
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
		WHERE jt.Code = 'IU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		AND j.Options like '%"Format":"Mor"%'
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
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		AND j.Options like '%"Format":"Mor"%'
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
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	-- Xero Invoice Post

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
		WHERE jt.Code = 'XIP'
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
		WHERE jt.Code = 'XIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	-- M2 coustomer rate sehet download

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
		AND j.Options like '%"Format":"M2"%'
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
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	-- M2 vendor rate sehet download

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
		AND j.Options like '%"Format":"M2"%'
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
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;
	
	
	
	
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
        WHERE jt.Code = 'DR'
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
        WHERE jt.Code = 'DR'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dispute.email', 'DisputeController.email', 1, 'Sumera Saeed', NULL, '2018-09-14 16:10:48.000', '2018-09-14 16:10:50.000', 1367);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1367, 'Disputes.Email', 1, 7);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dispute.send', 'DisputeController.send', 1, 'Sumera Saeed', NULL, '2018-09-14 15:52:32.000', '2018-09-14 15:52:33.000', 1366);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1366, 'Disputes.Send', 1, 7);

use RMBilling3;
DROP PROCEDURE IF EXISTS `prc_getDisputes`;
DELIMITER //
CREATE PROCEDURE `prc_getDisputes`(
	IN `p_CompanyID` INT,
	IN `p_InvoiceType` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(100),
	IN `p_Status` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Export` INT

)
BEGIN

     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    			SELECT   
    			ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.DisputeID,
		 	   ds.Attachment,
		 	   a.AccountID,
		 		ds.Notes,
		 		ds.Ref
		 		
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID

				where ds.CompanyID = p_CompanyID

			   AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) 
            
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountDESC') THEN DisputeAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountASC') THEN DisputeAmount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN ds.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN ds.created_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN ds.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN ds.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDDESC') THEN DisputeID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDASC') THEN DisputeID
                END ASC 			 
					 					 					    	             
                
            LIMIT p_RowspPage OFFSET v_OffSet_;

		 

				 SELECT   
		 		COUNT(ds.DisputeID) AS totalcount,
		 		sum(ds.DisputeAmount) as TotalDisputeAmount
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
				where ds.CompanyID = p_CompanyID

				AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;
            
            
	ELSE

				SELECT   
				ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
		 		ds.Notes
				

            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
            AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
				AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;

/* Authentication - permission*/

INSERT INTO `Ratemanagement3`.`tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1368, 'AuthenticationRule.Delete', 1, 3);
UPDATE `Ratemanagement3`.`tblResourceCategories` SET `CategoryGroupID`='3' WHERE  `ResourceCategoryName`='AuthenticationRule.Add';
UPDATE `Ratemanagement3`.`tblResourceCategories` SET `CategoryGroupID`='3' WHERE  `ResourceCategoryName`='AuthenticationRule.View';

INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Authentication.authenticate_store', 'AuthenticationController.authenticate_store', 1, 'Khurram Saeed', NULL, '2016-02-04 13:12:34.000', '2016-02-04 13:12:34.000', 153);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.addIps', 'AuthenticationController.addIps', 1, 'Sumera Saeed', NULL, '2016-06-28 15:01:08.000', '2016-06-28 15:01:08.000', 153);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.deleteips', 'AuthenticationController.deleteips', 1, 'Sumera Saeed', NULL, '2016-06-28 15:01:08.000', '2016-06-28 15:01:08.000', 1368);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.addclis', 'AuthenticationController.addclis', 1, 'Sumera Saeed', NULL, '2016-07-16 15:42:10.000', '2016-07-16 15:42:10.000', 153);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Authentication.deleteclis', 'AuthenticationController.deleteclis', 1, 'Sumera Saeed', NULL, '2016-07-16 15:42:10.000', '2016-07-16 15:42:10.000', 1368);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.addipclis', 'AuthenticationController.addipclis', 1, 'Sumera Saeed', NULL, '2016-07-21 13:00:46.000', '2016-07-21 13:00:46.000', 153);



/* change of api*/

DROP PROCEDURE IF EXISTS `prc_getProductsByItemType`;
DELIMITER //
CREATE PROCEDURE `prc_getProductsByItemType`(
	IN `p_CompanyID` INT,
	IN `p_ItemType` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_Name` VARCHAR(255),
	IN `p_Description` VARCHAR(255)

)
    DETERMINISTIC
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_fieldName VARCHAR(255);
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ProductID INT,
		ItemTypeID INT		
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products_fields;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products_fields(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		DynamicFieldsID INT(11),
		FieldName Varchar(100),
		FieldType Varchar(100)
	);
		

	IF p_ItemType!='' THEN
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
					tblProduct.ProductID,
					tblProduct.ItemTypeID
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND((p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%')))
				AND((p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%')))
			 LIMIT p_RowspPage OFFSET v_OffSet_;
			 
			 
			 SELECT 
			 	count(*) as totalcount
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND((p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%')))
				AND((p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%')));
			 
			 
			 
	ELSE 
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
				tblProduct.ProductID,
				tblProduct.ItemTypeID
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND((p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%')))
				AND((p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%')))
			LIMIT p_RowspPage OFFSET v_OffSet_;
			
			SELECT 
				count(*) as totalcount
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND((p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%')))
				AND((p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%')));
			
	END IF;
			 
				
			INSERT INTO tmp_products_fields (FieldName,DynamicFieldsID,FieldType)
			SELECT 
				DISTINCT a.FieldName,
				a.DynamicFieldsID,
				a.FieldDomType
			FROM Ratemanagement3.tblDynamicFields a
			INNER JOIN tmp_products b
			ON a.ItemTypeID=b.ItemTypeID
			WHERE 
				a.Type='product'
				AND a.CompanyID=p_CompanyID
				AND a.`Status`=1;	
						 
			SET @v_pointer_1 = 1;
			SET @v_rowCount_1 = (SELECT COUNT(*) FROM tmp_products_fields);
		
		 WHILE @v_pointer_1 <= @v_rowCount_1
		  DO
		
			SET @fieldname = (SELECT FieldName from tmp_products_fields where RowID = @v_pointer_1);
			 
			SET @statement2 =  CONCAT('ALTER TABLE tmp_products ADD COLUMN `', @fieldname ,'` TEXT NULL;');
	 	
			PREPARE stm_query FROM @statement2;
			EXECUTE stm_query;
			DEALLOCATE PREPARE stm_query;
			
			SET @dynamicFieldID=	(SELECT DynamicFieldsID from tmp_products_fields where RowID = @v_pointer_1);
		
			SET @v_pointer_ = 1;
			SET @v_rowCount_ = (SELECT COUNT(*) FROM tmp_products);
			WHILE @v_pointer_ <= @v_rowCount_
		  	DO	
		  	
			 SET @ProductID=(select ProductID FROM tmp_products WHERE RowID=@v_pointer_);
			 
				SET @upateq=CONCAT('UPDATE tmp_products prod
		  	  	INNER JOIN Ratemanagement3.tblDynamicFieldsValue b ON b.DynamicFieldsID=',@dynamicFieldID,'
		  	  	INNER JOIN Ratemanagement3.tblDynamicFields a ON a.DynamicFieldsID=b.DynamicFieldsID
		  		SET `prod`.`',@fieldname,'`=b.FieldValue
		  		
		  		WHERE 
				  prod.ProductID=',@ProductID,'
				  AND b.ParentID=',@ProductID);
				  
				   -- select @upateq;
				  
				  PREPARE stm_query1 FROM @upateq;
				  EXECUTE stm_query1;
				  DEALLOCATE PREPARE stm_query1;
		  		
		  	SET @v_pointer_ = @v_pointer_ + 1;
		  	
		  	END WHILE;
		
			SET @v_pointer_1 = @v_pointer_1 + 1;
	
	  END WHILE;
	  

		SELECT 
		 	tblProduct.CompanyId,	
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Description,
			tblProduct.Amount,
			tblProduct.AppliedTo,
			tblProduct.Note,
			tblProduct.Buying_price,	
			tblProduct.Quantity,
			tblProduct.Low_stock_level,
			tblProduct.Enable_stock,
			tmp_products.*	 	
		FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;
			
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


/* dispute changes */
INSERT INTO `tblJobType` (`Code`, `Title`, `Description`, `CreatedDate`, `CreatedBy`, `ModifiedDate`, `ModifiedBy`) VALUES ('BDS', 'Bulk Dispute Send', NULL, '2018-09-17 17:33:45', 'System', NULL, NULL);

USE `RMBilling3`;
DROP PROCEDURE IF EXISTS `prc_getDisputes`;
DELIMITER //
CREATE PROCEDURE `prc_getDisputes`(
	IN `p_CompanyID` INT,
	IN `p_InvoiceType` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(100),
	IN `p_Status` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Export` INT



)
BEGIN

     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    			SELECT   
    			ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.DisputeID,
		 	   ds.Attachment,
		 	   a.AccountID,
		 		ds.Notes,
		 		ds.Ref
		 		
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID

				where ds.CompanyID = p_CompanyID

			   AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) 
            
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountDESC') THEN DisputeAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountASC') THEN DisputeAmount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN ds.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN ds.created_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN ds.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN ds.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDDESC') THEN DisputeID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDASC') THEN DisputeID
                END ASC 			 
					 					 					    	             
                
            LIMIT p_RowspPage OFFSET v_OffSet_;

		 

				 SELECT   
		 		COUNT(ds.DisputeID) AS totalcount,
		 		sum(ds.DisputeAmount) as TotalDisputeAmount
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
				where ds.CompanyID = p_CompanyID

				AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;
            
   END IF;         
	IF p_Export = 1
	THEN

				SELECT   
				ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
		 		ds.Notes
				

            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
            AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
				AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;

	END IF;
	
	IF p_Export = 2
	THEN

				SELECT   
					ds.DisputeID,
				ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
		 		ds.Notes,
		 		a.AccountID
				

            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
            AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
				AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;



USE `Ratemanagement3`;
DROP PROCEDURE IF EXISTS `prc_CronJobAllPending`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobAllPending`(
	IN `p_CompanyID` INT



)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

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



	SELECT
		tblCronJobCommand.Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Status = 1
	AND tblCronJob.Active = 0;






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
		WHERE jt.Code = 'RCV'
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
		WHERE jt.Code = 'RCV'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;





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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
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
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'MGA'
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
		WHERE jt.Code = 'MGA'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


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
		WHERE jt.Code = 'DSU'
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
		WHERE jt.Code = 'DSU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'QIP'
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
		WHERE jt.Code = 'QIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'ICU'
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
		WHERE jt.Code = 'ICU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		WHERE jt.Code = 'IU'
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
		WHERE jt.Code = 'IU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		AND j.Options like '%"Format":"Mor"%'
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
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



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
		AND j.Options like '%"Format":"Mor"%'
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
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	-- Xero Invoice Post

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
		WHERE jt.Code = 'XIP'
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
		WHERE jt.Code = 'XIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	-- M2 coustomer rate sehet download

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
		AND j.Options like '%"Format":"M2"%'
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
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	-- M2 vendor rate sehet download

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
		AND j.Options like '%"Format":"M2"%'
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
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;
	
	
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
		WHERE jt.Code = 'QPP'
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
		WHERE jt.Code = 'QPP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;
	
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
        WHERE jt.Code = 'BDS'
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
        WHERE jt.Code = 'BDS'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;
    
    
    
    
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
        WHERE jt.Code = 'DR'
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
        WHERE jt.Code = 'DR'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;



INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1370, 'Disputes.Delete', 1, 7);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Dispute.delete', 'DisputeController.delete', 1, 'Sumera Saeed', NULL, '2016-05-23 14:46:01.000', '2016-05-23 14:46:01.000', 1370);



/* Stock History*/

DROP PROCEDURE IF EXISTS `prc_getStockHistory`;
DELIMITER //
CREATE PROCEDURE `prc_getStockHistory`(
	IN `p_CompanyID` INT,
	IN `p_Name` VARCHAR(50),
	IN `p_ItemTypeID` INT(11),
	IN `p_InvoiceNumber` VARCHAR(255),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT



)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
			tblItemType.title,
			tblProduct.Name,
			tblStockHistory.Stock,
			tblStockHistory.Quantity,
			tblStockHistory.InvoiceNumber,
			tblStockHistory.Reason,
			tblStockHistory.created_at
            from tblStockHistory LEFT JOIN tblProduct ON tblStockHistory.ProductID = tblProduct.ProductID
            LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            where tblStockHistory.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.ProductID=p_Name)
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID = p_ItemTypeID))
            AND((p_ItemTypeID ='' OR tblItemType.ItemTypeID = p_ItemTypeID))
            AND((p_InvoiceNumber = '' OR tblStockHistory.InvoiceNumber like CONCAT(p_InvoiceNumber,'%')))
         ORDER BY
         	CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StockHistoryIDDESC') THEN tblStockHistory.StockHistoryID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StockHistoryIDASC') THEN tblStockHistory.StockHistoryID
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'titleDESC') THEN tblItemType.title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'titleASC') THEN tblItemType.title
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN tblProduct.Name
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN tblProduct.Name
                END ASC,
         	CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StockDESC') THEN tblStockHistory.Stock
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StockASC') THEN tblStockHistory.Stock
                END ASC,
            CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuantityDESC') THEN tblStockHistory.Quantity
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuantityASC') THEN tblStockHistory.Quantity
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblStockHistory.StockHistoryID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblStockHistory.StockHistoryID
                END ASC
				
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(tblStockHistory.StockHistoryID) AS totalcount
            from tblStockHistory LEFT JOIN tblProduct ON tblStockHistory.ProductID = tblProduct.ProductID
            LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            where tblStockHistory.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.ProductID=p_Name)
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID = p_ItemTypeID))
            AND((p_ItemTypeID ='' OR tblItemType.ItemTypeID = p_ItemTypeID))
            AND((p_InvoiceNumber = '' OR tblStockHistory.InvoiceNumber like CONCAT(p_InvoiceNumber,'%')));

	ELSE

			SELECT
			tblItemType.title,
			tblProduct.Name,
			tblStockHistory.Stock,
			tblStockHistory.Quantity,
			tblStockHistory.InvoiceNumber,
			tblStockHistory.Reason,
			tblStockHistory.created_at
            from tblStockHistory LEFT JOIN tblProduct ON tblStockHistory.ProductID = tblProduct.ProductID
            LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            where tblStockHistory.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.ProductID =p_Name)
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID = p_ItemTypeID))
            AND((p_ItemTypeID ='' OR tblItemType.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
            AND((p_InvoiceNumber = '' OR tblStockHistory.InvoiceNumber = p_InvoiceNumber))
				order by tblStockHistory.StockHistoryID desc;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


/* DB Cleanup - Retension */


DROP PROCEDURE IF EXISTS `prc_deleteArchiveOldRate`;
DELIMITER //
CREATE PROCEDURE `prc_deleteArchiveOldRate`(
	IN `p_CompanyID` INT,
	IN `p_DeleteDate` DATETIME


)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	  
	  DELETE
			tblCustomerRateArchive
	  FROM tblCustomerRateArchive 
	  INNER JOIN tblAccount 
		ON tblAccount.AccountID=tblCustomerRateArchive.AccountId
	  WHERE 
	  tblAccount.CompanyId=p_CompanyID
	  AND tblCustomerRateArchive.EffectiveDate <= p_DeleteDate;

		
	  DELETE
	  		tblVendorRateArchive
	  FROM tblVendorRateArchive 
	  INNER JOIN tblAccount 
		ON tblAccount.AccountID=tblVendorRateArchive.AccountId
	  WHERE
		tblAccount.CompanyId=p_CompanyID
	  AND EffectiveDate <= p_DeleteDate;


	  DELETE
	  		tblRateTableRateArchive
	  FROM tblRateTableRateArchive 
	  INNER JOIN tblRateTable 
	  ON tblRateTable.RateTableId=tblRateTableRateArchive.RateTableId
	  WHERE 
	  tblRateTable.CompanyId=p_CompanyID
	  AND tblRateTableRateArchive.EffectiveDate <= p_DeleteDate;	  	  
	  
	 
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_deleteTickets`;
DELIMITER //
CREATE PROCEDURE `prc_deleteTickets`(
	IN `p_CompanyID` INT,
	IN `p_DeleteDate` DATETIME






)
BEGIN
 	DECLARE p_status INT;
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 	
 	DROP TEMPORARY TABLE IF EXISTS tmp_attachments_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_attachments_(
		attachment LONGTEXT
	);
	  
	  -- get closed StatusID
	  SELECT 
	  		tblTicketfieldsValues.ValuesID INTO p_status 
	  FROM tblTicketfields 
	  INNER JOIN tblTicketfieldsValues 
	 	 	ON tblTicketfields.TicketFieldsID=tblTicketfieldsValues.FieldsID 
	  WHERE tblTicketfields.FieldType='default_status' 
		  AND tblTicketfieldsValues.FieldValueAgent='Closed';
	  
	  
	  DELETE
	  		tblJunkTicketEmail
	  FROM tblJunkTicketEmail 
	  INNER JOIN tblTickets
	  ON tblTickets.TicketID=tblJunkTicketEmail.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  	  
	  DELETE
	  		tblTicketLog
	  FROM tblTicketLog 
	  INNER JOIN tblTickets
	  ON tblTickets.TicketID=tblTicketLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  -- get attachment to delete files later
	  INSERT INTO tmp_attachments_
	  SELECT 
	  tblTicketsDeletedLog.AttachmentPaths
	  FROM tblTicketsDeletedLog
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=tblTicketsDeletedLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTicketsDeletedLog.AttachmentPaths!=''
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  DELETE
	  		tblTicketsDeletedLog
	  FROM tblTicketsDeletedLog 
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=tblTicketsDeletedLog.TicketID
	  WHERE 
	  	tblTickets.CompanyID=p_CompanyID
	  	AND tblTickets.`Status`=p_status
	  	AND tblTickets.updated_at <= p_DeleteDate;
	  
	  
	  	
	  
	  INSERT INTO tmp_attachments_
	  SELECT 
	  AccountEmailLogDeletedLog.AttachmentPaths
	  FROM AccountEmailLogDeletedLog
	  INNER JOIN tblTickets
	  ON tblTickets.TicketID=AccountEmailLogDeletedLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND AccountEmailLogDeletedLog.AttachmentPaths!=''
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  DELETE
	  		AccountEmailLogDeletedLog
	  FROM AccountEmailLogDeletedLog 
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=AccountEmailLogDeletedLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	   
	  
	  INSERT INTO tmp_attachments_
	  SELECT 
	  AccountEmailLog.AttachmentPaths
	  FROM AccountEmailLog
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=AccountEmailLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND AccountEmailLog.AttachmentPaths!=''
	  AND tblTickets.updated_at <= p_DeleteDate;
	  	  
	  DELETE
	  		AccountEmailLog
	  FROM AccountEmailLog 
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=AccountEmailLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  
	  INSERT INTO tmp_attachments_
	  SELECT
	  		AttachmentPaths
	  FROM tblTickets
	  WHERE 
	  CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND AttachmentPaths!=''
	  AND updated_at <= p_DeleteDate;
	  
	  DELETE 
	  FROM tblTickets
	  WHERE 
	  CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND updated_at <= p_DeleteDate; 
	  
	  	  
	   
	  SELECT * FROM tmp_attachments_;

		 
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getProductsByItemType`;
DELIMITER //
CREATE PROCEDURE `prc_getProductsByItemType`(
	IN `p_CompanyID` INT,
	IN `p_ItemType` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_Name` VARCHAR(255),
	IN `p_Description` VARCHAR(255)



)
    DETERMINISTIC
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_fieldName VARCHAR(255);
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ProductID INT,
		ItemTypeID INT		
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products_fields;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products_fields(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		DynamicFieldsID INT(11),
		FieldName Varchar(100),
		FieldType Varchar(100)
	);
		

	IF p_ItemType!='' THEN
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
					tblProduct.ProductID,
					tblProduct.ItemTypeID
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
					OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))	
					)
				
			 LIMIT p_RowspPage OFFSET v_OffSet_;
			 
			 
			 SELECT 
			 	count(*) as totalcount
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
					OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))	
					);
			 
			 
			 
	ELSE 
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
				tblProduct.ProductID,
				tblProduct.ItemTypeID
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
						OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))
					)
				
			LIMIT p_RowspPage OFFSET v_OffSet_;
			
			SELECT 
				count(*) as totalcount
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
						OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))
					);
			
	END IF;
			 
				
			INSERT INTO tmp_products_fields (FieldName,DynamicFieldsID,FieldType)
			SELECT 
				DISTINCT a.FieldName,
				a.DynamicFieldsID,
				a.FieldDomType
			FROM Ratemanagement3.tblDynamicFields a
			INNER JOIN tmp_products b
			ON a.ItemTypeID=b.ItemTypeID
			WHERE 
				a.Type='product'
				AND a.CompanyID=p_CompanyID
				AND a.`Status`=1;	
						 
			SET @v_pointer_1 = 1;
			SET @v_rowCount_1 = (SELECT COUNT(*) FROM tmp_products_fields);
		
		 WHILE @v_pointer_1 <= @v_rowCount_1
		  DO
		
			SET @fieldname = (SELECT FieldName from tmp_products_fields where RowID = @v_pointer_1);
			 
			SET @statement2 =  CONCAT('ALTER TABLE tmp_products ADD COLUMN `', @fieldname ,'` TEXT NULL;');
	 	
			PREPARE stm_query FROM @statement2;
			EXECUTE stm_query;
			DEALLOCATE PREPARE stm_query;
			
			SET @dynamicFieldID=	(SELECT DynamicFieldsID from tmp_products_fields where RowID = @v_pointer_1);
		
			SET @v_pointer_ = 1;
			SET @v_rowCount_ = (SELECT COUNT(*) FROM tmp_products);
			WHILE @v_pointer_ <= @v_rowCount_
		  	DO	
		  	
			 SET @ProductID=(select ProductID FROM tmp_products WHERE RowID=@v_pointer_);
			 
				SET @upateq=CONCAT('UPDATE tmp_products prod
		  	  	INNER JOIN Ratemanagement3.tblDynamicFieldsValue b ON b.DynamicFieldsID=',@dynamicFieldID,'
		  	  	INNER JOIN Ratemanagement3.tblDynamicFields a ON a.DynamicFieldsID=b.DynamicFieldsID
		  		SET `prod`.`',@fieldname,'`=b.FieldValue
		  		
		  		WHERE 
				  prod.ProductID=',@ProductID,'
				  AND b.ParentID=',@ProductID);
				  
				   -- select @upateq;
				  
				  PREPARE stm_query1 FROM @upateq;
				  EXECUTE stm_query1;
				  DEALLOCATE PREPARE stm_query1;
		  		
		  	SET @v_pointer_ = @v_pointer_ + 1;
		  	
		  	END WHILE;
		
			SET @v_pointer_1 = @v_pointer_1 + 1;
	
	  END WHILE;
	  

		SELECT 
		 	tblProduct.CompanyId,	
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Description,
			tblProduct.Amount,
			tblProduct.AppliedTo,
			tblProduct.Note,
			tblProduct.Buying_price,	
			tblProduct.Quantity,
			tblProduct.Low_stock_level,
			tblProduct.Enable_stock,
			tmp_products.*	 	
		FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;
		
		/*select count(*) as totalcount
			FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;*/
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



USE Ratemanagement3;
DROP PROCEDURE IF EXISTS `prc_deleteArchiveOldRate`;
DELIMITER //
CREATE PROCEDURE `prc_deleteArchiveOldRate`(
	IN `p_CompanyID` INT,
	IN `p_DeleteDate` DATETIME,
	IN `p_processID` VARCHAR(200)

)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 	
 	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processID);
	  
	  DELETE
			tblCustomerRateArchive
	  FROM tblCustomerRateArchive 
	  INNER JOIN tblAccount 
		ON tblAccount.AccountID=tblCustomerRateArchive.AccountId
	  WHERE 
	  tblAccount.CompanyId=p_CompanyID
	  AND tblCustomerRateArchive.EffectiveDate <= p_DeleteDate;

		
	  DELETE
	  		tblVendorRateArchive
	  FROM tblVendorRateArchive 
	  INNER JOIN tblAccount 
		ON tblAccount.AccountID=tblVendorRateArchive.AccountId
	  WHERE
		tblAccount.CompanyId=p_CompanyID
	  AND EffectiveDate <= p_DeleteDate;


	  DELETE
	  		tblRateTableRateArchive
	  FROM tblRateTableRateArchive 
	  INNER JOIN tblRateTable 
	  ON tblRateTable.RateTableId=tblRateTableRateArchive.RateTableId
	  WHERE 
	  tblRateTable.CompanyId=p_CompanyID
	  AND tblRateTableRateArchive.EffectiveDate <= p_DeleteDate;	  	  
	  
	 
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_deleteTickets`;
DELIMITER //
CREATE PROCEDURE `prc_deleteTickets`(
	IN `p_CompanyID` INT,
	IN `p_DeleteDate` DATETIME,
	IN `p_processID` VARCHAR(200)

)
BEGIN
 	DECLARE p_status INT;
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 	
 	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processID);
 	
 	DROP TEMPORARY TABLE IF EXISTS tmp_attachments_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_attachments_(
		attachment LONGTEXT
	);
	  
	  -- get closed StatusID
	  SELECT 
	  		tblTicketfieldsValues.ValuesID INTO p_status 
	  FROM tblTicketfields 
	  INNER JOIN tblTicketfieldsValues 
	 	 	ON tblTicketfields.TicketFieldsID=tblTicketfieldsValues.FieldsID 
	  WHERE tblTicketfields.FieldType='default_status' 
		  AND tblTicketfieldsValues.FieldValueAgent='Closed';
	  
	  
	  DELETE
	  		tblJunkTicketEmail
	  FROM tblJunkTicketEmail 
	  INNER JOIN tblTickets
	  ON tblTickets.TicketID=tblJunkTicketEmail.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  	  
	  DELETE
	  		tblTicketLog
	  FROM tblTicketLog 
	  INNER JOIN tblTickets
	  ON tblTickets.TicketID=tblTicketLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  -- get attachment to delete files later
	  INSERT INTO tmp_attachments_
	  SELECT 
	  tblTicketsDeletedLog.AttachmentPaths
	  FROM tblTicketsDeletedLog
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=tblTicketsDeletedLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTicketsDeletedLog.AttachmentPaths!=''
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  DELETE
	  		tblTicketsDeletedLog
	  FROM tblTicketsDeletedLog 
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=tblTicketsDeletedLog.TicketID
	  WHERE 
	  	tblTickets.CompanyID=p_CompanyID
	  	AND tblTickets.`Status`=p_status
	  	AND tblTickets.updated_at <= p_DeleteDate;
	  
	  
	  	
	  
	  INSERT INTO tmp_attachments_
	  SELECT 
	  AccountEmailLogDeletedLog.AttachmentPaths
	  FROM AccountEmailLogDeletedLog
	  INNER JOIN tblTickets
	  ON tblTickets.TicketID=AccountEmailLogDeletedLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND AccountEmailLogDeletedLog.AttachmentPaths!=''
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  DELETE
	  		AccountEmailLogDeletedLog
	  FROM AccountEmailLogDeletedLog 
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=AccountEmailLogDeletedLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	   
	  
	  INSERT INTO tmp_attachments_
	  SELECT 
	  AccountEmailLog.AttachmentPaths
	  FROM AccountEmailLog
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=AccountEmailLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND AccountEmailLog.AttachmentPaths!=''
	  AND tblTickets.updated_at <= p_DeleteDate;
	  	  
	  DELETE
	  		AccountEmailLog
	  FROM AccountEmailLog 
	  INNER JOIN tblTickets
	  	ON tblTickets.TicketID=AccountEmailLog.TicketID
	  WHERE 
	  tblTickets.CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND tblTickets.updated_at <= p_DeleteDate;
	  
	  
	  INSERT INTO tmp_attachments_
	  SELECT
	  		AttachmentPaths
	  FROM tblTickets
	  WHERE 
	  CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND AttachmentPaths!=''
	  AND updated_at <= p_DeleteDate;
	  
	  DELETE 
	  FROM tblTickets
	  WHERE 
	  CompanyID=p_CompanyID
	  AND tblTickets.`Status`=p_status
	  AND updated_at <= p_DeleteDate; 
	  
	  	  
	   
	  SELECT * FROM tmp_attachments_;

		 
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_CronJobGenerateM2Sheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateM2Sheet`(
	IN `p_CustomerID` INT ,
	IN `p_trunks` VARCHAR(200) ,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE

)
BEGIN

	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate);

	SELECT DISTINCT
		CONCAT(IF(tblCountry.Country IS NULL,'',CONCAT(tblCountry.Country,' - ')),tmpRate.Description) as `Destination`,
		tmpRate.Code as `Prefix`,
		tmpRate.Rate as `Rate(USD)`,
		tmpRate.ConnectionFee as `Connection Fee(USD)`,
		tmpRate.Interval1 as `Increment`,
		tmpRate.IntervalN as `Minimal Time`,
		'0:00:00 'as `Start Time`,
		'23:59:59' as `End Time`,
		'' as `Week Day`,
		tmpRate.EffectiveDate  as `Effective from`,
		tmpRate.RoutinePlanName as `Routing through`,
		TRUNCATE((tmpRate.Rate * 2.041)/100,6) as `Rate(USD) with Tax`
	FROM
		tmp_customerrateall_ tmpRate
	JOIN
		tblRate ON tblRate.RateID = tmpRate.RateID
	LEFT JOIN
		tblCountry ON tblCountry.CountryID = tblRate.CountryID
	;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_CronJobGenerateM2VendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateM2VendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE

)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
        TimezonesID INT,
   	  RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        INDEX tmp_RateTable_RateId (`RateId`)
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `TimezonesID`, `RateId`, `Rate`, `EffectiveDate`, `Interval1`, `IntervalN`, `ConnectionFee`
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0
								AND tblVendorRate.TimezonesID = p_TimezonesID
                        AND
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW())
								  	OR
								  	(p_Effective = 'Future' AND EffectiveDate > NOW())
								  	OR
								  	(p_Effective = 'CustomDate' AND EffectiveDate <= p_CustomDate AND (EndDate IS NULL OR EndDate > p_CustomDate))
								  	OR
								  	(p_Effective = 'All')
								);

		 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);

      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
 	   AND n1.TimezonesID=n2.TimezonesID
	   AND  n1.RateId = n2.RateId
	   AND
	   (
			(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
		  	OR
		  	(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_m2rateall_;
    CREATE TEMPORARY TABLE tmp_m2rateall_ (
        RateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE
    );

     INSERT INTO tmp_m2rateall_
     SELECT Distinct
			tblRate.RateID as `RateID`,
			tblRate.Code as `Code`,
			tblRate.Description as `Description` ,
			CASE WHEN tblVendorRate.Interval1 IS NOT NULL
			   THEN tblVendorRate.Interval1
			   ElSE tblRate.Interval1
			END AS `Interval1`,
			CASE WHEN tblVendorRate.IntervalN IS NOT NULL
			   THEN tblVendorRate.IntervalN
			   ElSE tblRate.IntervalN
			END  AS `IntervalN`,
			tblVendorRate.ConnectionFee as `ConnectionFee`,
			Abs(tblVendorRate.Rate) as `Rate`,
			tblVendorRate.EffectiveDate as `EffectiveDate`
        FROM    tmp_VendorRate_ as tblVendorRate
            JOIN tblRate on tblVendorRate.RateId =tblRate.RateID;

		SELECT DISTINCT
			CONCAT(IF(tblCountry.Country IS NULL,'',CONCAT(tblCountry.Country,' - ')),tmpRate.Description) as `Destination`,
			tmpRate.Code as `Prefix`,
			tmpRate.Rate as `Rate(USD)`,
			tmpRate.ConnectionFee as `Connection Fee(USD)`,
			tmpRate.Interval1 as `Increment`,
			tmpRate.IntervalN as `Minimal Time`,
			'0:00:00 'as `Start Time`,
			'23:59:59' as `End Time`,
			'' as `Week Day`,
			tmpRate.EffectiveDate  as `Effective from`,
			TRUNCATE((tmpRate.Rate * 2.041)/100,6) as `Rate(USD) with Tax`
		FROM
			tmp_m2rateall_ AS tmpRate
		JOIN
			tblRate ON tblRate.RateID = tmpRate.RateID
		LEFT JOIN
			tblCountry ON tblCountry.CountryID = tblRate.CountryID;

      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getProductsByItemType`;
DELIMITER //
CREATE PROCEDURE `prc_getProductsByItemType`(
	IN `p_CompanyID` INT,
	IN `p_ItemType` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_Name` VARCHAR(255),
	IN `p_Description` VARCHAR(255)


)
    DETERMINISTIC
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_fieldName VARCHAR(255);
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ProductID INT,
		ItemTypeID INT		
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products_fields;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products_fields(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		DynamicFieldsID INT(11),
		FieldName Varchar(100),
		FieldType Varchar(100)
	);
		

	IF p_ItemType!='' THEN
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
					tblProduct.ProductID,
					tblProduct.ItemTypeID
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
					OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))	
					)
				
			 LIMIT p_RowspPage OFFSET v_OffSet_;
			 
			 
			 
	ELSE 
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
				tblProduct.ProductID,
				tblProduct.ItemTypeID
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
						OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))
					)
				
			LIMIT p_RowspPage OFFSET v_OffSet_;
			
			
			
	END IF;
			 
				
			INSERT INTO tmp_products_fields (FieldName,DynamicFieldsID,FieldType)
			SELECT 
				DISTINCT a.FieldName,
				a.DynamicFieldsID,
				a.FieldDomType
			FROM Ratemanagement3.tblDynamicFields a
			INNER JOIN tmp_products b
			ON a.ItemTypeID=b.ItemTypeID
			WHERE 
				a.Type='product'
				AND a.CompanyID=p_CompanyID
				AND a.`Status`=1;	
						 
			SET @v_pointer_1 = 1;
			SET @v_rowCount_1 = (SELECT COUNT(*) FROM tmp_products_fields);
		
		 WHILE @v_pointer_1 <= @v_rowCount_1
		  DO
		
			SET @fieldname = (SELECT FieldName from tmp_products_fields where RowID = @v_pointer_1);
			 
			SET @statement2 =  CONCAT('ALTER TABLE tmp_products ADD COLUMN `', @fieldname ,'` TEXT NULL;');
	 	
			PREPARE stm_query FROM @statement2;
			EXECUTE stm_query;
			DEALLOCATE PREPARE stm_query;
			
			SET @dynamicFieldID=	(SELECT DynamicFieldsID from tmp_products_fields where RowID = @v_pointer_1);
		
			SET @v_pointer_ = 1;
			SET @v_rowCount_ = (SELECT COUNT(*) FROM tmp_products);
			WHILE @v_pointer_ <= @v_rowCount_
		  	DO	
		  	
			 SET @ProductID=(select ProductID FROM tmp_products WHERE RowID=@v_pointer_);
			 
				SET @upateq=CONCAT('UPDATE tmp_products prod
		  	  	INNER JOIN Ratemanagement3.tblDynamicFieldsValue b ON b.DynamicFieldsID=',@dynamicFieldID,'
		  	  	INNER JOIN Ratemanagement3.tblDynamicFields a ON a.DynamicFieldsID=b.DynamicFieldsID
		  		SET `prod`.`',@fieldname,'`=b.FieldValue
		  		
		  		WHERE 
				  prod.ProductID=',@ProductID,'
				  AND b.ParentID=',@ProductID);
				  
				   -- select @upateq;
				  
				  PREPARE stm_query1 FROM @upateq;
				  EXECUTE stm_query1;
				  DEALLOCATE PREPARE stm_query1;
		  		
		  	SET @v_pointer_ = @v_pointer_ + 1;
		  	
		  	END WHILE;
		
			SET @v_pointer_1 = @v_pointer_1 + 1;
	
	  END WHILE;
	  

		SELECT 
		 	tblProduct.CompanyId,	
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Description,
			tblProduct.Amount,
			tblProduct.AppliedTo,
			tblProduct.Note,
			tblProduct.Buying_price,	
			tblProduct.Quantity,
			tblProduct.Low_stock_level,
			tblProduct.Enable_stock,
			tmp_products.*	 	
		FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;
		
		select count(*) as totalcount
			FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getLowStockItemsAlert`;
DELIMITER //
CREATE PROCEDURE `prc_getLowStockItemsAlert`(
	IN `p_CompanyID` INT(11)





)
BEGIN
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT  
		it.title AS title,
		p.Name,
		p.Code,
		p.Quantity as Stock,
		p.Low_stock_level 
	FROM tblProduct p
		LEFT join tblItemType it on it.ItemTypeID=p.ItemTypeID 
	WHERE p.Enable_stock=1
		AND p.Low_stock_level >= 0 
		AND p.CompanyId=p_CompanyID 
		AND p.Quantity <= p.Low_stock_level 
		AND p.Active=1;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getInvoice`;
DELIMITER //
CREATE PROCEDURE `prc_getInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_InvoiceType` INT,
	IN `p_InvoiceStatus` LONGTEXT,
	IN `p_IsOverdue` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CurrencyID` INT,
	IN `p_isExport` INT,
	IN `p_sageExport` INT,
	IN `p_zerovalueinvoice` INT,
	IN `p_InvoiceID` LONGTEXT,
	IN `p_userID` INT,
	IN `p_tag` VARCHAR(100)

)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_TotalCount int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT cr.Symbol INTO v_CurrencyCode_ from Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		InvoiceType tinyint(1),
		AccountName varchar(100),
		InvoiceNumber varchar(100),
		IssueDate datetime,
		InvoicePeriod varchar(100),
		CurrencySymbol varchar(5),
		Currency varchar(50),
		GrandTotal decimal(18,6),
		TotalPayment decimal(18,6),
		PendingAmount decimal(18,6),
		InvoiceStatus varchar(50),
		CreditNoteAmount decimal(18,6),
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
		NominalAnalysisNominalAccountNumber varchar(100),
		TotalMinutes BIGINT(20)
	);

		INSERT INTO tmp_Invoices_
		SELECT inv.InvoiceType ,
			ac.AccountName,
			FullInvoiceNumber as InvoiceNumber,
			inv.IssueDate,
			IF(invd.StartDate IS NULL ,'',CONCAT('From ',date(invd.StartDate) ,'<br> To ',date(invd.EndDate))) as InvoicePeriod,
			IFNULL(cr.Symbol,'') as CurrencySymbol,
			cr.Code AS Currency,
			inv.GrandTotal as GrandTotal,
			(SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) as TotalPayment,
			(inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
			inv.InvoiceStatus,
			(SELECT IFNULL(sum(GrandTotal) - sum(PaidAmount),0)  FROM tblCreditNotes c WHERE c.AccountID = inv.AccountID and c.CreditNotesStatus='open' and inv.InvoiceType=1) as CreditNoteAmount,
			inv.InvoiceID,
			inv.Description,
			inv.Attachment,
			inv.AccountID,
			inv.ItemInvoice,
			IFNULL(ac.BillingEmail,'') as BillingEmail,
			ac.Number,
			if (inv.BillingClassID > 0,
				 (SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblBillingClass b where  b.BillingClassID =inv.BillingClassID),
				 (SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1)
			) as PaymentDueInDays,
			(SELECT PaymentDate FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID ORDER BY PaymentID DESC LIMIT 1) AS PaymentDate,
			inv.SubTotal,
			inv.TotalTax,
			ac.NominalAnalysisNominalAccountNumber,
			IFNULL((SELECT SUM(IFNULL(TotalMinutes,0)) FROM tblInvoiceDetail tid WHERE tid.InvoiceID=inv.InvoiceID GROUP BY tid.InvoiceID),0) AS TotalMinutes
			FROM tblInvoice inv
			INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
			LEFT JOIN tblInvoiceDetail invd ON invd.InvoiceID = inv.InvoiceID AND (invd.ProductType = 5 OR inv.InvoiceType = 2)
			LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId
			WHERE ac.CompanyID = p_CompanyID
			AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
			AND (p_userID = 0 OR ac.Owner = p_userID)
			AND (p_InvoiceNumber = '' OR (inv.FullInvoiceNumber like Concat('%',p_InvoiceNumber,'%')))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
			AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
			AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND FIND_IN_SET(inv.InvoiceStatus,p_InvoiceStatus) ))
			AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal != 0))
			AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ))
			AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID))
			AND (p_tag = '' OR (ac.tags like Concat(p_tag,'%')));	

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
			DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY)) AS DueDate,
			IF(InvoiceStatus IN ('send','awaiting'), IF(DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))) > 0,DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))),''), '') AS DueDays,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount,
			ItemInvoice,
			BillingEmail,
			GrandTotal,
			CreditNoteAmount
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
			CreditNoteAmount,
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

		

		SELECT
			AccountName ,
			InvoiceNumber,
			IssueDate,
			REPLACE(InvoicePeriod, '<br>', '') as InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
			InvoiceStatus,
			CreditNoteAmount,
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
	
	IF p_isExport = 3 -- api
	THEN

		SELECT
			InvoiceID,
			AccountName,
			InvoiceNumber,
			IssueDate,
			InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `PendingAmount`,
			InvoiceStatus,
			DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY)) AS DueDate,
			IF(InvoiceStatus IN ('send','awaiting'), IF(DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))) > 0,DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))),''), '') AS DueDays,
			Currency,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount,
			ItemInvoice,
			BillingEmail,
			GrandTotal,
			TotalMinutes
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

	IF p_sageExport =1 OR p_sageExport =2
	THEN
			

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
				AND (p_userID = 0 OR ac.Owner = p_userID)
				AND (p_InvoiceNumber = '' OR (inv.FullInvoiceNumber like Concat('%',p_InvoiceNumber,'%')))
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
							AND((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) )>0)
						)
				)
				AND (p_tag = '' OR (ac.tags like Concat(p_tag,'%')));
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
			4 AS SYSTraderTranType, 
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
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getPayments`;
DELIMITER //
CREATE PROCEDURE `prc_getPayments`(
	IN `p_CompanyID` INT,
	IN `p_accountID` INT,
	IN `p_InvoiceNo` VARCHAR(30),
	IN `p_FullInvoiceNumber` VARCHAR(50),
	IN `p_Status` VARCHAR(20),
	IN `p_PaymentType` VARCHAR(20),
	IN `p_PaymentMethod` VARCHAR(20),
	IN `p_RecallOnOff` INT,
	IN `p_CurrencyID` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isCustomer` INT ,
	IN `p_paymentStartDate` DATETIME,
	IN `p_paymentEndDate` DATETIME,
	IN `p_isExport` INT,
	IN `p_userID` INT,
	IN `p_tag` VARCHAR(100)

)
BEGIN
		
	DECLARE v_OffSet_ INT;
	DECLARE v_Round_ INT;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	SET sql_mode = '';

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	SELECT cr.Symbol INTO v_CurrencyCode_ FROM Ratemanagement3.tblCurrency cr WHERE cr.CurrencyId =p_CurrencyID;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT
			tblPayment.PaymentID,
			tblAccount.AccountName,
			tblPayment.AccountID,
			ROUND(tblPayment.Amount,v_Round_) AS Amount,
			CASE WHEN p_isCustomer = 1 THEN
				CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
				END
			ELSE
				PaymentType
			END as PaymentType,
			tblPayment.CurrencyID,
			tblPayment.PaymentDate,
			CASE WHEN p_RecallOnOff = -1 AND tblPayment.Recall=1  THEN 'Recalled' ELSE tblPayment.Status END as `Status`,
			tblPayment.CreatedBy,
			tblPayment.PaymentProof,
			tblPayment.InvoiceNo,
			tblPayment.PaymentMethod,
			tblPayment.Notes,
			tblPayment.Recall,
			tblPayment.RecallReasoan,
			tblPayment.RecallBy,
			CONCAT(IFNULL(v_CurrencyCode_,''),ROUND(tblPayment.Amount,v_Round_)) AS AmountWithSymbol
		FROM tblPayment
		LEFT JOIN Ratemanagement3.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
		WHERE tblPayment.CompanyID = p_CompanyID
			AND(p_RecallOnOff = -1 OR tblPayment.Recall = p_RecallOnOff)
			AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo like Concat('%',p_InvoiceNo,'%')))
			AND((p_FullInvoiceNumber = '' OR tblPayment.InvoiceNo = p_FullInvoiceNumber))
			AND((p_Status IS NULL OR tblPayment.Status = p_Status))
			AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
			AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
			AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate))
			AND (p_CurrencyID = 0 OR tblPayment.CurrencyId = p_CurrencyID)
			AND (p_tag = '' OR (tblAccount.tags like Concat(p_tag,'%')))
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
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NotesDESC') THEN tblPayment.Notes
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NotesASC') THEN tblPayment.Notes
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(tblPayment.PaymentID) AS totalcount,
			CONCAT(IFNULL(v_CurrencyCode_,''),ROUND(sum(Amount),v_Round_)) AS total_grand
		FROM tblPayment
		LEFT JOIN Ratemanagement3.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
		WHERE tblPayment.CompanyID = p_CompanyID
			AND(p_RecallOnOff = -1 OR tblPayment.Recall = p_RecallOnOff)
			AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo like Concat('%',p_InvoiceNo,'%')))
			AND((p_FullInvoiceNumber = '' OR tblPayment.InvoiceNo = p_FullInvoiceNumber))
			AND((p_Status IS NULL OR tblPayment.Status = p_Status))
			AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
			AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
			AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate))
			AND (p_CurrencyID = 0 OR tblPayment.CurrencyId = p_CurrencyID)
			AND (p_tag = '' OR (tblAccount.tags like Concat(p_tag,'%')));

	END IF;
	IF p_isExport = 1
	THEN

		SELECT 
			AccountName,
			CONCAT(IFNULL(v_CurrencyCode_,''),ROUND(tblPayment.Amount,v_Round_)) AS Amount,
			CASE WHEN p_isCustomer = 1 THEN
				CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
				END
			ELSE  PaymentType
			END AS PaymentType,
			PaymentDate,
			tblPayment.Status,
			tblPayment.CreatedBy,
			InvoiceNo,
			tblPayment.PaymentMethod,
			Notes 
		FROM tblPayment
		LEFT JOIN Ratemanagement3.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
		WHERE tblPayment.CompanyID = p_CompanyID
			AND(p_RecallOnOff = -1 OR tblPayment.Recall = p_RecallOnOff)
			AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo like Concat('%',p_InvoiceNo,'%')))
			AND((p_FullInvoiceNumber = '' OR tblPayment.InvoiceNo = p_FullInvoiceNumber))
			AND((p_Status IS NULL OR tblPayment.Status = p_Status))
			AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
			AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
			AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate))
			AND (p_CurrencyID = 0 OR tblPayment.CurrencyId = p_CurrencyID)
			AND (p_tag = '' OR (tblAccount.tags like Concat(p_tag,'%')));
	END IF;
	
	
	IF p_isExport = 2
	THEN

		SELECT 
			AccountName,
			CONCAT(IFNULL(v_CurrencyCode_,''),ROUND(tblPayment.Amount,v_Round_)) as Amount,
			CASE WHEN p_isCustomer = 1 THEN
				CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
				END
			ELSE  PaymentType
			END as PaymentType,
			PaymentDate,
			tblPayment.Status,
			InvoiceNo,
			tblPayment.PaymentMethod,
			Notes 
		FROM tblPayment
		LEFT JOIN Ratemanagement3.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
		WHERE tblPayment.CompanyID = p_CompanyID
			AND(tblPayment.Recall = p_RecallOnOff)
			AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
			AND((p_FullInvoiceNumber = '' OR tblPayment.InvoiceNo = p_FullInvoiceNumber))
			AND((p_Status IS NULL OR tblPayment.Status = p_Status))
			AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
			AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
			AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate))
			AND (p_CurrencyID = 0 OR tblPayment.CurrencyId = p_CurrencyID)
			AND (p_tag = '' OR (tblAccount.tags like Concat(p_tag,'%')));
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_GetCDR`;
DELIMITER //
CREATE PROCEDURE `prc_GetCDR`(
	IN `p_company_id` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_start_date` DATETIME,
	IN `p_end_date` DATETIME,
	IN `p_AccountID` INT ,
	IN `p_ResellerID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(100)

)
BEGIN

	DECLARE v_OffSet_ INT;
	DECLARE v_BillingTime_ INT;
	DECLARE v_Round_ INT;
	DECLARE v_raccountids TEXT;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	SET v_raccountids = '';

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_company_id) INTO v_Round_;

	SELECT cr.Symbol INTO v_CurrencyCode_ FROM Ratemanagement3.tblCurrency cr WHERE cr.CurrencyId =p_CurrencyID;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	Call fnUsageDetail(p_company_id,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType,p_CLI,p_CLD,p_zerovaluecost);
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;

	IF p_isExport = 0
	THEN
		SELECT
			uh.UsageDetailID,
			uh.AccountName,
			uh.connect_time,
			uh.disconnect_time,
			uh.billed_duration,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(uh.cost)+0) AS cost,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(ROUND((uh.cost/uh.billed_duration)*60.0,6))+0) AS rate,
			uh.cli,
			uh.cld,
			uh.area_prefix,
			uh.trunk,
			s.ServiceName,
			uh.AccountID,
			p_CompanyGatewayID AS CompanyGatewayID,
			p_start_date AS StartDate,
			p_end_date AS EndDate,
			uh.is_inbound AS CDRType
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		LEFT JOIN Ratemanagement3.tblService s
			ON uh.ServiceID = s.ServiceID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount,
			fnFormateDuration(sum(billed_duration)) AS total_duration,
			sum(cost) AS total_cost,
			v_CurrencyCode_ AS CurrencyCode
		FROM  tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

	END IF;

	IF p_isExport = 1
	THEN

		SELECT
			uh.AccountName AS 'Account Name',
			uh.connect_time AS 'Connect Time',
			uh.disconnect_time AS 'Disconnect Time',
			uh.billed_duration AS 'Billed Duration (sec)' ,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(uh.cost)+0) AS 'Cost',
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(ROUND((uh.cost/uh.billed_duration)*60.0,6))+0) AS 'Avg. Rate/Min',
			uh.cli AS 'CLI',
			uh.cld AS 'CLD',
			uh.area_prefix AS 'Prefix',
			uh.trunk AS 'Trunk',
			IF(uh.is_inbound = 0 , 'OutBound','InBound') AS 'Type'
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_GetVendorCDR`;
DELIMITER //
CREATE PROCEDURE `prc_GetVendorCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_start_date` DATETIME,
	IN `p_end_date` DATETIME,
	IN `p_AccountID` INT,
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_ZeroValueBuyingCost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(100)

)
BEGIN 

	DECLARE v_OffSet_ int;
	DECLARE v_BillingTime_ int;
	DECLARE v_Round_ INT;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	
	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	Call fnVendorUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CLI,p_CLD,p_ZeroValueBuyingCost);

	IF p_isExport = 0
	THEN 
	
		SELECT
			uh.VendorCDRID,	
			uh.AccountName as AccountName,
			uh.connect_time,
			uh.disconnect_time,
			uh.billed_duration,
			CONCAT(IFNULL(v_CurrencyCode_,''),uh.buying_cost) AS buying_cost,
			uh.cli,
			uh.cld,
			uh.area_prefix,
			uh.trunk,
			uh.AccountID,
			p_CompanyGatewayID as CompanyGatewayID,
			p_start_date as StartDate,
			p_end_date as EndDate  
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE 	(p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
		LIMIT p_RowspPage OFFSET v_OffSet_;
	 
		SELECT
			COUNT(*) AS totalcount,
			fnFormateDuration(SUM(uh.billed_duration)) as total_billed_duration,
			SUM(uh.buying_cost) as total_cost,
			v_CurrencyCode_ as CurrencyCode
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));
	END IF;
	
	IF p_isExport = 1
	THEN
	
		SELECT
			uh.AccountName,        
			uh.connect_time,
			uh.disconnect_time,        
			uh.billed_duration,
			CONCAT(IFNULL(v_CurrencyCode_,''),uh.buying_cost) AS Cost,
			uh.cli,
			uh.cld,
			uh.area_prefix,
			uh.trunk
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getDisputes`;
DELIMITER //
CREATE PROCEDURE `prc_getDisputes`(
	IN `p_CompanyID` INT,
	IN `p_InvoiceType` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(100),
	IN `p_Status` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Export` INT,
	IN `p_tag` VARCHAR(100)
)
BEGIN

     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    			SELECT   
    			ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.DisputeID,
		 	   ds.Attachment,
		 	   a.AccountID,
		 		ds.Notes,
		 		ds.Ref
		 		
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID

				where ds.CompanyID = p_CompanyID

			   AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) 
            AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
            
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountDESC') THEN DisputeAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountASC') THEN DisputeAmount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN ds.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN ds.created_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN ds.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN ds.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDDESC') THEN DisputeID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDASC') THEN DisputeID
                END ASC 			 
					 					 					    	             
                
            LIMIT p_RowspPage OFFSET v_OffSet_;

		 

				 SELECT   
		 		COUNT(ds.DisputeID) AS totalcount,
		 		sum(ds.DisputeAmount) as TotalDisputeAmount
            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
				where ds.CompanyID = p_CompanyID

				AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate)
				AND (p_tag = '' OR (a.tags like Concat(p_tag,'%'))) ;
            
   END IF;         
	IF p_Export = 1
	THEN

				SELECT   
				ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
		 		ds.Notes
				

            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
            AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
				AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate)
				AND (p_tag = '' OR (a.tags like Concat(p_tag,'%'))) ;

	END IF;
	
	IF p_Export = 2
	THEN

				SELECT   
					ds.DisputeID,
				ds.InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
		 		ds.Notes,
		 		a.AccountID
				

            from tblDispute ds
            inner join Ratemanagement3.tblAccount a on a.AccountID = ds.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
            AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND ds.InvoiceType = p_InvoiceType))
				AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate)
				AND (p_tag = '' OR (a.tags like Concat(p_tag,'%'))) ;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


USE StagingReport;

DROP PROCEDURE IF EXISTS `fnUsageSummary`;
DELIMITER //
CREATE PROCEDURE `fnUsageSummary`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT ,
	IN `p_isAdmin` INT,
	IN `p_Detail` INT,
	IN `p_ResellerID` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN
	DECLARE v_TimeId_ INT;
	DECLARE v_raccountids TEXT;
	SET v_raccountids ='';
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;
	

	IF DATEDIFF(p_EndDate,p_StartDate) > 31 AND p_Detail = 2
	THEN
		SET p_Detail = 1;
	END IF;

	IF p_Detail = 1 
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`userfield` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',				
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalCost` DOUBLE NULL DEFAULT NULL,
				`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
				`TotalDuration` INT(11) NULL DEFAULT NULL,
				`NoOfCalls` INT(11) NULL DEFAULT NULL,
				`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
				`AccountName` varchar(100),
				INDEX `tblUsageSummary_dim_date` (`DateID`)
		);
		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.AccountID,
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.userfield,
			us.CountryID,
			us.TotalCharges,
			us.TotalCost,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryDay  us
			ON us.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CDRType = '' OR us.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));
		

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.AccountID,
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.userfield,
			us.CountryID,
			us.TotalCharges,
			us.TotalCost,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryDayLive  us
			ON us.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CDRType = '' OR us.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

	END IF;

	IF p_Detail = 2
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`TimeID` INT(11) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`userfield` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',				
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalCost` DOUBLE NULL DEFAULT NULL,
				`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
				`TotalDuration` INT(11) NULL DEFAULT NULL,
				`NoOfCalls` INT(11) NULL DEFAULT NULL,
				`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
				`AccountName` varchar(100),
				INDEX `tblUsageSummary_dim_date` (`DateID`)
		);

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
			sh.CompanyID,
			sh.AccountID,
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.userfield,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalCost,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryHour  usd
			ON usd.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CDRType = '' OR usd.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
			sh.CompanyID,
			sh.AccountID,
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.userfield,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalCost,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryHourLive  usd
			ON usd.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CDRType = '' OR usd.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

	END IF;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getDestinationReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getDestinationReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnGetCountry();
		 
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* country by call count */	
		
	SELECT IFNULL(Country,'Other') as Country ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageSummary_ us
	LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
	WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
	GROUP BY c.Country   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN IF(SUM(NoOfCalls)>0,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN IF(SUM(NoOfCalls)>0,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	

	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM(
		SELECT IFNULL(Country,'Other') as Country ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY c.Country
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			SQL_CALC_FOUND_ROWS IFNULL(Country,'Other') as  Country,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 country by call count */	
			
		SELECT Country as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 country by call cost */	
			
		SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 country by call minutes */	
			
		SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getReportByTime`;
DELIMITER //
CREATE PROCEDURE `prc_getReportByTime`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_ReportType` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ INT;
	DECLARE V_Detail INT;

	SET V_Detail = 2;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,V_Detail,p_ResellerID,p_tag);

	/* hourly report */
	IF p_ReportType =1
	THEN
	
		SELECT 
			CONCAT(dt.hour,' Hour') as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimTime dt on dt.TimeID = us.TimeID
		GROUP BY  us.DateID,dt.hour;

	END IF;

	/* daily report */
	IF p_ReportType =2
	THEN

		SELECT 
			dd.date as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  us.DateID;
	END IF;

	/* weekly report */
	IF p_ReportType =3
	THEN

		SELECT 
			ANY_VALUE(dd.week_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year,dd.week_of_year
		ORDER BY dd.year,dd.week_of_year;

	END IF;

	/* monthly report */
	IF p_ReportType =4
	THEN

		SELECT 
			ANY_VALUE(dd.month_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year,dd.month_of_year
		ORDER BY dd.year,dd.month_of_year;

	END IF;

	/* queterly report */
	IF p_ReportType =5
	THEN

		SELECT 
			CONCAT('Q-',ANY_VALUE(dd.quarter_year_name)) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year,dd.quarter_of_year
		ORDER BY dd.year,dd.quarter_of_year;

	END IF;

	/* yearly report */
	IF p_ReportType =6
	THEN

		SELECT 
			dd.year as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getWorldMap`;
DELIMITER //
CREATE PROCEDURE `prc_getWorldMap`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnGetCountry();

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);

	/* get all country call counts*/
	SELECT 
		Country,
		SUM(NoOfCalls) AS CallCount,
		ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
		ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,
		IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD,
		ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage,
		MAX(ISO2) AS ISO_Code,
		tblCountry.CountryID
	FROM tmp_tblUsageSummary_
	INNER JOIN temptblCountry AS tblCountry 
		ON tblCountry.CountryID = tmp_tblUsageSummary_.CountryID
	GROUP BY Country,tblCountry.CountryID 
	HAVING SUM(NoOfCalls) > 0
	ORDER BY CallCount DESC;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getPrefixReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getPrefixReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* AreaPrefix by call count */	
		
	SELECT AreaPrefix ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageSummary_ us
	GROUP BY AreaPrefix   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixDESC') THEN AreaPrefix
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixASC') THEN AreaPrefix
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT AreaPrefix ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AreaPrefix
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			AreaPrefix,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY AreaPrefix;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 AreaPrefix by call count */	
			
		SELECT AreaPrefix as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call cost */	
			
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call minutes */	
			
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getTrunkReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getTrunkReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* Trunk by call count */	
		
	SELECT Trunk ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,	
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageSummary_ us
	GROUP BY Trunk   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkDESC') THEN Trunk
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkASC') THEN Trunk
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT Trunk ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY Trunk
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			Trunk,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY Trunk;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 Trunk by call count */	
			
		SELECT Trunk as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 Trunk by call cost */	
			
		SELECT Trunk as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 Trunk by call minutes */	
			
		SELECT Trunk as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getGatewayReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getGatewayReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* CompanyGatewayID by call count */	
		
	SELECT fnGetCompanyGatewayName(CompanyGatewayID) ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage,
		CompanyGatewayID
	FROM tmp_tblUsageSummary_ us
	GROUP BY CompanyGatewayID   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayDESC') THEN fnGetCompanyGatewayName(CompanyGatewayID)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayASC') THEN fnGetCompanyGatewayName(CompanyGatewayID)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY CompanyGatewayID
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			fnGetCompanyGatewayName(CompanyGatewayID)  as Name,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY CompanyGatewayID;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 CompanyGatewayID by call count */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 CompanyGatewayID by call cost */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 CompanyGatewayID by call minutes */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getAccountReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getAccountReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* account by call count */	
	SELECT AccountName ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage,
	MAX(AccountID) as AccountID
	FROM tmp_tblUsageSummary_ us
	GROUP BY AccountName   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,
		FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin
	FROM (
		SELECT AccountName ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			AccountName  as Name,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 account by call count */	
		SELECT AccountName as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 account by call cost */	
		SELECT AccountName as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 account by call minutes */	
		SELECT AccountName as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getDescReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getDescReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ INT;
	DECLARE v_OffSet_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fngetDefaultCodes(p_CompanyID);

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);

	/* grid display*/
	IF p_isExport = 0
	THEN

		/* Description by call count */	
			
		SELECT IFNULL(Description,'Other') AS Description ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) AS TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) AS ASR,
			FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY c.Description
		ORDER BY
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
		END ASC,
		CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
		END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM(
			SELECT IFNULL(Description,'Other') AS Description ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) AS TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) AS ASR,
				ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
				ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
			FROM tmp_tblUsageSummary_ us
			LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
			GROUP BY c.Description
		)tbl;

	END IF;

	/* export data*/
	IF p_isExport = 1
	THEN

		SELECT
			SQL_CALC_FOUND_ROWS IFNULL(Description,'Other') AS Description,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description;

	END IF;

	/* chart display*/
	IF p_isExport = 2
	THEN

		/* top 10 Description by call count */
		
		SELECT Description AS ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;

		/* top 10 Description by call cost */
		
		SELECT Description AS ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;

		/* top 10 Description by call minutes */
		
		SELECT Description AS ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) AS TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_verifySummary`;
DELIMITER //
CREATE PROCEDURE `prc_verifySummary`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_verify_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_verify_(
	   AccountID INT,
	   TotalCall INT,
	   TotalCharges decimal(18,6),
	   TotalSecond BIGINT,
	   Summary INT,
	   CDR INT
   );
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	CALL RMBilling3.fnUsageDetail(p_CompanyID,p_AccountID,0,p_StartDate,p_EndDate,0,1,1,'','','',0);
	
	INSERT INTO tmp_verify_ (AccountID,TotalCall,TotalCharges,TotalSecond,Summary,CDR)
	SELECT p_AccountID,COUNT(*) as TotalCall,SUM(cost) as TotalCost,SUM(billed_duration) as TotalSecond,0,1 FROM RMBilling3.tmp_tblUsageDetails_;
	
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,0,p_StartDate,p_EndDate,'','',0,0,1,1,'');
	
	INSERT INTO tmp_verify_ (AccountID,TotalCall,TotalCharges,TotalSecond,Summary,CDR)
	SELECT p_AccountID,SUM(NoOfCalls) as TotalCall,SUM(TotalCharges) as TotalCost,SUM(TotalBilledDuration) as TotalSecond,1,0 FROM tmp_tblUsageSummary_;
	
	SELECT * FROM tmp_verify_;

	


END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getHourlyReport`;
DELIMITER //
CREATE PROCEDURE `prc_getHourlyReport`(
	IN `p_CompanyID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_CDRType` VARCHAR(50)



)
BEGIN
	
	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,0,p_StartDate,p_EndDate,'','',0,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,'');
	
	/* total cost */
	SELECT ROUND(COALESCE(SUM(TotalCharges),0),v_Round_) as TotalCost FROM tmp_tblUsageSummary_;
	
	/* cost per hour*/
	SELECT dt.hour as HOUR ,ROUND(COALESCE(SUM(TotalCharges),0),v_Round_) as TotalCost FROM tmp_tblUsageSummary_ us INNER JOIN tblDimTime dt on us.TimeID =  dt.TimeID GROUP BY dt.hour;
	
	/* total duration or minutes*/
	SELECT ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes FROM tmp_tblUsageSummary_;
	
	/* minutes pre hour*/
	SELECT dt.hour as HOUR ,ROUND(COALESCE(SUM(TotalBilledDuration),0) / 60,0) as TotalMinutes FROM tmp_tblUsageSummary_ us INNER JOIN tblDimTime dt on us.TimeID =  dt.TimeID GROUP BY dt.hour;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `fnUsageVendorSummary`;
DELIMITER //
CREATE PROCEDURE `fnUsageVendorSummary`(
	IN `p_CompanyID` int ,
	IN `p_CompanyGatewayID` int ,
	IN `p_AccountID` int ,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` datetime ,
	IN `p_EndDate` datetime ,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT ,
	IN `p_isAdmin` INT,
	IN `p_Detail` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN
	DECLARE v_TimeId_ INT;

	IF DATEDIFF(p_EndDate,p_StartDate) > 31 AND p_Detail =2
	THEN
		SET p_Detail = 1;
	END IF;

	IF p_Detail = 1 
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageVendorSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageVendorSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalSales` DOUBLE NULL DEFAULT NULL,
				`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
				`TotalDuration` INT(11) NULL DEFAULT NULL,
				`NoOfCalls` INT(11) NULL DEFAULT NULL,
				`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
				`AccountName` varchar(100),
				INDEX `tblUsageSummary_dim_date` (`DateID`)
		);
		INSERT INTO tmp_tblUsageVendorSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.VAccountID,
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.CountryID,
			us.TotalCharges,
			us.TotalSales,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeaderV sh
		INNER JOIN tblVendorSummaryDay us
			ON us.HeaderVID = sh.HeaderVID 
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.VAccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.VAccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

		INSERT INTO tmp_tblUsageVendorSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.VAccountID,
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.CountryID,
			us.TotalCharges,
			us.TotalSales,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeaderV sh
		INNER JOIN tblVendorSummaryDayLive us
			ON us.HeaderVID = sh.HeaderVID 
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.VAccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.VAccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

	END IF;

	IF p_Detail = 2 
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageVendorSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageVendorSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`TimeID` INT(11) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalSales` DOUBLE NULL DEFAULT NULL,
				`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
				`TotalDuration` INT(11) NULL DEFAULT NULL,
				`NoOfCalls` INT(11) NULL DEFAULT NULL,
				`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
				`AccountName` varchar(100),
				INDEX `tblUsageSummary_dim_date` (`DateID`)
		);

		INSERT INTO tmp_tblUsageVendorSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
			sh.CompanyID,
			sh.VAccountID,
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalSales,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeaderV sh
		INNER JOIN tblVendorSummaryHour usd
			ON usd.HeaderVID = sh.HeaderVID 
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.VAccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.VAccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

		INSERT INTO tmp_tblUsageVendorSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
			sh.CompanyID,
			sh.VAccountID,
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalSales,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeaderV sh
		INNER JOIN tblVendorSummaryHourLive usd
			ON usd.HeaderVID = sh.HeaderVID 
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.VAccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.VAccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

	END IF;
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getVendorDestinationReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorDestinationReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnGetCountry();
		 
	
	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,2,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* country by call count */	
		
	SELECT IFNULL(Country,'Other') as Country ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) as ASR,
		FORMAT(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageVendorSummary_ us
	LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
	WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
	GROUP BY c.Country   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT IFNULL(Country,'Other') as Country ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY c.Country
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			SQL_CALC_FOUND_ROWS IFNULL(Country,'Other') as Country,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageVendorSummary_ us
		LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 country by call count */	
			
		SELECT Country as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 country by call cost */	
			
		SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 country by call minutes */	
			
		SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getVendorReportByTime`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorReportByTime`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_ReportType` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ INT;
	DECLARE V_Detail INT;

	SET V_Detail = 2;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,V_Detail,p_tag);

	/* hourly report */
	IF p_ReportType =1
	THEN

		SELECT 
			CONCAT(dt.hour,' Hour') as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimTime dt on dt.TimeID = us.TimeID
		GROUP BY  us.DateID,dt.hour;

	END IF;

	/* daily report */
	IF p_ReportType =2
	THEN

		SELECT 
			dd.date as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  us.DateID;

	END IF;

	/* weekly report */
	IF p_ReportType =3
	THEN

		SELECT 
			ANY_VALUE(dd.week_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year,dd.week_of_year
		ORDER BY dd.year,dd.week_of_year;

	END IF;

	/* monthly report */
	IF p_ReportType =4
	THEN

		SELECT 
			ANY_VALUE(dd.month_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year,dd.month_of_year
		ORDER BY dd.year,dd.month_of_year;

	END IF;

	/* queterly report */
	IF p_ReportType =5
	THEN

		SELECT 
			CONCAT('Q-',ANY_VALUE(dd.quarter_year_name)) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year,dd.quarter_of_year
		ORDER BY dd.year,dd.quarter_of_year;

	END IF;

	/* yearly report */
	IF p_ReportType =6
	THEN

		SELECT 
			dd.year as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getVendorWorldMap`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorWorldMap`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnGetCountry();

	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,2,p_tag);

	/* get all country call counts*/
	SELECT 
		Country,
		SUM(NoOfCalls) AS CallCount,
		ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
		ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,
		IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD,
		ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
		ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage,
		MAX(ISO2) AS ISO_Code,
		tblCountry.CountryID
	FROM tmp_tblUsageVendorSummary_ AS us
	INNER JOIN temptblCountry AS tblCountry 
		ON tblCountry.CountryID = us.CountryID
	GROUP BY Country,tblCountry.CountryID 
	HAVING SUM(NoOfCalls) > 0
	ORDER BY CallCount DESC;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getVendorPrefixReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorPrefixReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,2,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* AreaPrefix by call count */	
		
	SELECT AreaPrefix ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageVendorSummary_ us
	GROUP BY AreaPrefix   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixDESC') THEN AreaPrefix
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixASC') THEN AreaPrefix
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT AreaPrefix ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY AreaPrefix
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			AreaPrefix,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY AreaPrefix;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 AreaPrefix by call count */	
			
		SELECT AreaPrefix as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call cost */	
			
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call minutes */	
			
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getVendorTrunkReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorTrunkReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)

)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,2,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* Trunk by call count */	
		
	SELECT Trunk ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageVendorSummary_ us
	GROUP BY Trunk   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkDESC') THEN Trunk
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkASC') THEN Trunk
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT Trunk ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY Trunk
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			Trunk,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY Trunk;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 Trunk by call count */	
			
		SELECT Trunk as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 Trunk by call cost */	
			
		SELECT Trunk as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 Trunk by call minutes */	
			
		SELECT Trunk as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getVendorGatewayReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorGatewayReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,2,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* CompanyGatewayID by call count */	
		
	SELECT fnGetCompanyGatewayName(CompanyGatewayID) ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage,
		CompanyGatewayID
	FROM tmp_tblUsageVendorSummary_ us
	GROUP BY CompanyGatewayID   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayDESC') THEN fnGetCompanyGatewayName(CompanyGatewayID)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayASC') THEN fnGetCompanyGatewayName(CompanyGatewayID)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY CompanyGatewayID
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			fnGetCompanyGatewayName(CompanyGatewayID)  as Name,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as `Margin (%)`		
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY CompanyGatewayID;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 CompanyGatewayID by call count */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 CompanyGatewayID by call cost */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 CompanyGatewayID by call minutes */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getVendorAccountReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorAccountReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,2,p_tag);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* account by call count */	
		
	SELECT AccountName ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage,
		MAX(AccountID) as AccountID
	FROM tmp_tblUsageVendorSummary_ us
	GROUP BY AccountName   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT AccountName ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY AccountName
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			AccountName  as Name,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY AccountName;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 account by call count */	
			
		SELECT AccountName as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY AccountName HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 account by call cost */	
			
		SELECT AccountName as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY AccountName HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 account by call minutes */	
			
		SELECT AccountName as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		GROUP BY AccountName HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_getVendorDescReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorDescReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)
)
BEGIN

	DECLARE v_Round_ INT;
	DECLARE v_OffSet_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fngetDefaultCodes(p_CompanyID);

	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,2,p_tag);

	/* grid display*/
	IF p_isExport = 0
	THEN

		/* Description by call count */	
			
		SELECT IFNULL(Description,'Other') AS Description ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) AS TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) AS ASR,
			FORMAT(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			FORMAT( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY c.Description   
		ORDER BY
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_)
		END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
			SELECT IFNULL(Description,'Other') AS Description ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) AS TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) AS ASR,
				ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
				ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
			FROM tmp_tblUsageVendorSummary_ us
			LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
			GROUP BY c.Description
		)tbl;

	END IF;

	/* export data*/
	IF p_isExport = 1
	THEN

		SELECT
			SQL_CALC_FOUND_ROWS IFNULL(Description,'Other') AS Description,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageVendorSummary_ us
		LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description;

	END IF;

	/* chart display*/
	IF p_isExport = 2
	THEN

		/* top 10 Description by call count */
		
		SELECT Description AS ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;

		/* top 10 Description by call cost */
		
		SELECT Description AS ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;

		/* top 10 Description by call minutes */
		
		SELECT Description AS ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) AS TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalSales),0) - COALESCE(SUM(TotalCharges),0)) / SUM(TotalSales)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


USE RMCDR3;

DROP PROCEDURE IF EXISTS `prc_RetailMonitorCalls`;
DELIMITER //
CREATE PROCEDURE `prc_RetailMonitorCalls`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Type` VARCHAR(50),
	IN `p_tag` VARCHAR(250)


)
BEGIN

	DECLARE v_raccountids TEXT;
	SET v_raccountids ='';
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;
	
		
	IF p_Type = 'call_duraition'
	THEN

		SELECT 
			cli,
			cld,
			billed_duration  
		FROM tblUsageDetails  ud
		INNER JOIN tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID	
		WHERE a.CompanyID = p_CompanyID
			AND uh.AccountID IS NOT NULL
			AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND StartDate BETWEEN p_StartDate AND p_EndDate
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
		ORDER BY billed_duration DESC LIMIT 10;

	END IF;

		
	IF p_Type = 'call_cost'
	THEN

		SELECT 
			cli,
			cld,
			cost,
			billed_duration
		FROM tblUsageDetails  ud
		INNER JOIN tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID	
		WHERE a.CompanyID = p_CompanyID
			AND uh.AccountID IS NOT NULL
			AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND StartDate BETWEEN p_StartDate AND p_EndDate
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
		ORDER BY cost DESC LIMIT 10;

	END IF;
	
	
	IF p_Type = 'most_dialed'
	THEN

		SELECT 
			cld,
			count(*) AS dail_count,
			SUM(billed_duration) AS billed_duration
		FROM tblUsageDetails  ud
		INNER JOIN tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID	
		WHERE a.CompanyID = p_CompanyID
			AND uh.AccountID IS NOT NULL
			AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND StartDate BETWEEN p_StartDate AND p_EndDate
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
		GROUP BY cld
		ORDER BY dail_count DESC
		LIMIT 10;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END//
DELIMITER ;



ALTER TABLE `tblProduct`
	ADD COLUMN `ProductImage` TEXT NULL DEFAULT NULL AFTER `Enable_stock`;
	

DROP PROCEDURE IF EXISTS `prc_getProducts`;
DELIMITER //
CREATE PROCEDURE `prc_getProducts`(
	IN `p_CompanyID` INT,
	IN `p_Name` VARCHAR(50),
	IN `p_Code` VARCHAR(50),
	IN `p_Active` VARCHAR(1),
	IN `p_AppliedTo` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_ItemTypeID` INT,
	IN `p_Export` INT,
	IN `SearchStock` VARCHAR(50),
	IN `SearchDynamicFields` VARCHAR(255)





)
    DETERMINISTIC
BEGIN
     DECLARE v_OffSet_ int;
	  DECLARE p_cntProduct int;
	  DECLARE v_Round_ int;
	  DECLARE v_TotalCount int;
	  
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_SearchProducts_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_SearchProducts_(
		ProductID INT		
	);
	
	IF SearchDynamicFields !='' THEN
		INSERT INTO tmp_SearchProducts_(ProductID)
		SELECT 
			dfv.ParentID
		FROM  Ratemanagement3.tblDynamicFieldsValue dfv
		INNER JOIN Ratemanagement3.tblDynamicFields df
		ON df.DynamicFieldsID=dfv.DynamicFieldsID
		INNER JOIN tblProduct b
		ON b.ProductID = dfv.ParentID
		WHERE 
		dfv.FieldValue like  CONCAT('%',SearchDynamicFields,'%')
		AND df.`Type`='product'
		AND df.Status=1
		AND dfv.CompanyID=p_CompanyID
		AND df.FieldDomType!='file'
		GROUP BY dfv.ParentID;
		
	END IF;
	
	SELECT COUNT(*) as cnt INTO p_cntProduct FROM tmp_SearchProducts_;  	
	
	 if p_Export = 0
	THEN
	
    SELECT   
			tblProduct.ProductID,
			tblItemType.title,
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Buying_price,
			tblProduct.Amount,
			tblProduct.Quantity,
			tblProduct.updated_at,
			tblProduct.Active,
			tblProduct.Description,
			tblProduct.Note,
			tblProduct.AppliedTo,
			tblProduct.Low_stock_level,
			tblProduct.ItemTypeID,
			tblProduct.ProductImage	
            from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
            where tblProduct.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
            AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
            AND (SearchStock='' OR 
			 	(
					 CASE WHEN SearchStock='Instock' THEN 
					 	tblProduct.Quantity > 0 
					 WHEN SearchStock='LowLevel' THEN 
					 	tblProduct.Quantity < tblProduct.Low_stock_level 
				    WHEN SearchStock='Outstock' THEN 
					 	tblProduct.Quantity < 0 
					 ELSE 
					 	SearchStock=''  
					 END 
			   )
		     )
		       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL)
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Buying_priceDESC') THEN Buying_price
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Buying_priceASC') THEN Buying_price
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuantityDESC') THEN Quantity
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuantityASC') THEN Quantity
                END ASC,
                
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN tblProduct.updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN tblProduct.updated_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveDESC') THEN tblProduct.Active
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveASC') THEN tblProduct.Active
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;
            
				
				SELECT
            COUNT(tblProduct.ProductID) INTO v_TotalCount
            from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
            where tblProduct.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
            AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
            AND (SearchStock='' OR 
			 	(
					 CASE WHEN SearchStock='Instock' THEN 
					 	tblProduct.Quantity > 0 
					 WHEN SearchStock='LowLevel' THEN 
					 	tblProduct.Quantity < tblProduct.Low_stock_level 
				    WHEN SearchStock='Outstock' THEN 
					 	tblProduct.Quantity < 0 
					 ELSE 
					 	SearchStock=''  
					 END 
			   )
		     )
		       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL);
		       
	   SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	   
	   SELECT 
	   	v_TotalCount as totalcount,
	   	ROUND(sum(tblProduct.Quantity),v_Round_) as total_grand
   	from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
         LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
         where tblProduct.CompanyID = p_CompanyID
		AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
			AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
         AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
         AND((p_Active = '' OR tblProduct.Active = p_Active))
         AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
         AND (SearchStock='' OR 
		 	(
				 CASE WHEN SearchStock='Instock' THEN 
				 	tblProduct.Quantity > 0 
				 WHEN SearchStock='LowLevel' THEN 
				 	tblProduct.Quantity < tblProduct.Low_stock_level 
			    WHEN SearchStock='Outstock' THEN 
				 	tblProduct.Quantity < 0 
				 ELSE 
				 	SearchStock=''  
				 END 
		   )
	     )
	       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL);
	   	
	   	

	ELSE

			SELECT
			tblProduct.ProductID,
			tblItemType.title,
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Buying_price,
			tblProduct.Amount,
			tblProduct.Quantity,
			tblProduct.updated_at,
			tblProduct.Active,
			tblProduct.Description,
			tblProduct.Note,
			tblProduct.AppliedTo,
			tblProduct.Low_stock_level,
			tblProduct.ItemTypeID	
            from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
			where tblProduct.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
            AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
            AND (SearchStock='' OR 
			 	(
					 CASE WHEN SearchStock='Instock' THEN 
					 	tblProduct.Quantity > 0 
					 WHEN SearchStock='LowLevel' THEN 
					 	tblProduct.Quantity < tblProduct.Low_stock_level 
				    WHEN SearchStock='Outstock' THEN 
					 	tblProduct.Quantity < 0 
					 ELSE 
					 	SearchStock=''  
					 END 
			   )
		     )
		       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL);

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;



ALTER TABLE `tblProduct`
	CHANGE COLUMN `Buying_price` `BuyingPrice` DECIMAL(18,2) NULL DEFAULT NULL AFTER `ItemTypeID`;
	
ALTER TABLE `tblProduct`
	CHANGE COLUMN `Low_stock_level` `LowStockLevel` INT(11) NULL DEFAULT NULL AFTER `Quantity`;	

ALTER TABLE `tblProduct`
	CHANGE COLUMN `Enable_stock` `EnableStock` TINYINT(1) NULL DEFAULT '0' AFTER `LowStockLevel`;
	
DROP PROCEDURE IF EXISTS `prc_getProductsByItemType`;
DELIMITER //
CREATE PROCEDURE `prc_getProductsByItemType`(
	IN `p_CompanyID` INT,
	IN `p_ItemType` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_Name` VARCHAR(255),
	IN `p_Description` VARCHAR(255)

)
    DETERMINISTIC
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_fieldName VARCHAR(255);
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ProductID INT,
		ItemTypeID INT		
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_products_fields;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_products_fields(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		DynamicFieldsID INT(11),
		FieldName Varchar(100),
		FieldType Varchar(100)
	);
		

	IF p_ItemType!='' THEN
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
					tblProduct.ProductID,
					tblProduct.ItemTypeID
			FROM tblItemType
			INNER JOIN  tblProduct 
				ON tblProduct.ItemTypeID=tblItemType.ItemTypeID
			WHERE 
				tblProduct.CompanyId=p_CompanyID
				AND tblItemType.title=p_ItemType
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
					OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))	
					)
				
			 LIMIT p_RowspPage OFFSET v_OffSet_;
			 
			 
			 
	ELSE 
	
		INSERT INTO tmp_products (ProductID,ItemTypeID)
			SELECT
				tblProduct.ProductID,
				tblProduct.ItemTypeID
			FROM tblProduct
			WHERE
				tblProduct.CompanyId=p_CompanyID
				AND tblProduct.Quantity > 0
				AND tblProduct.Active=1
				AND(
						(p_Name ='' OR tblProduct.Name like CONCAT(p_Name,'%'))
						OR
						(p_Description ='' OR tblProduct.Description like CONCAT('%',p_Description,'%'))
					)
				
			LIMIT p_RowspPage OFFSET v_OffSet_;
			
			
			
	END IF;
			 
				
			INSERT INTO tmp_products_fields (FieldName,DynamicFieldsID,FieldType)
			SELECT 
				DISTINCT 
				CASE WHEN c.title!=null or c.title!='' THEN CONCAT(c.title,'_',a.FieldName) ELSE  a.FieldName END as FieldName,
				a.DynamicFieldsID,
				a.FieldDomType
			FROM Ratemanagement3.tblDynamicFields a
			INNER JOIN tmp_products b
			ON a.ItemTypeID=b.ItemTypeID
			LEFT JOIN tblItemType c
			ON c.ItemTypeID=a.ItemTypeID
			WHERE 
				a.Type='product'
				AND a.CompanyID=p_CompanyID
				AND a.`Status`=1;	
						 
			SET @v_pointer_1 = 1;
			SET @v_rowCount_1 = (SELECT COUNT(*) FROM tmp_products_fields);
		
		 WHILE @v_pointer_1 <= @v_rowCount_1
		  DO
		
			SET @fieldname = (SELECT FieldName from tmp_products_fields where RowID = @v_pointer_1);
			 
			SET @statement2 =  CONCAT('ALTER TABLE tmp_products ADD COLUMN `', @fieldname ,'` TEXT NULL;');
	 	
			PREPARE stm_query FROM @statement2;
			EXECUTE stm_query;
			DEALLOCATE PREPARE stm_query;
			
			SET @dynamicFieldID=	(SELECT DynamicFieldsID from tmp_products_fields where RowID = @v_pointer_1);
		
			SET @v_pointer_ = 1;
			SET @v_rowCount_ = (SELECT COUNT(*) FROM tmp_products);
			WHILE @v_pointer_ <= @v_rowCount_
		  	DO	
		  	
			 SET @ProductID=(select ProductID FROM tmp_products WHERE RowID=@v_pointer_);
			 
				SET @upateq=CONCAT('UPDATE tmp_products prod
		  	  	INNER JOIN Ratemanagement3.tblDynamicFieldsValue b ON b.DynamicFieldsID=',@dynamicFieldID,'
		  	  	INNER JOIN Ratemanagement3.tblDynamicFields a ON a.DynamicFieldsID=b.DynamicFieldsID
		  		SET `prod`.`',@fieldname,'`=b.FieldValue
		  		
		  		WHERE 
				  prod.ProductID=',@ProductID,'
				  AND b.ParentID=',@ProductID);
				  
				   -- select @upateq;
				  
				  PREPARE stm_query1 FROM @upateq;
				  EXECUTE stm_query1;
				  DEALLOCATE PREPARE stm_query1;
		  		
		  	SET @v_pointer_ = @v_pointer_ + 1;
		  	
		  	END WHILE;
		
			SET @v_pointer_1 = @v_pointer_1 + 1;
	
	  END WHILE;
	  

		SELECT 
		 	tblProduct.CompanyId,	
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Description,
			tblProduct.Amount,
			tblProduct.AppliedTo,
			tblProduct.Note,
			tblProduct.BuyingPrice,	
			tblProduct.Quantity,
			tblProduct.LowStockLevel,
			tblProduct.EnableStock,
			tblProduct.Image,
			tmp_products.*	 	
		FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;
		
		select count(*) as totalcount
			FROM 
		tmp_products
		INNER JOIN tblProduct ON tblProduct.ProductID=tmp_products.ProductID;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getProducts`;
DELIMITER //
CREATE PROCEDURE `prc_getProducts`(
	IN `p_CompanyID` INT,
	IN `p_Name` VARCHAR(50),
	IN `p_Code` VARCHAR(50),
	IN `p_Active` VARCHAR(1),
	IN `p_AppliedTo` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_ItemTypeID` INT,
	IN `p_Export` INT,
	IN `SearchStock` VARCHAR(50),
	IN `SearchDynamicFields` VARCHAR(255)

)
    DETERMINISTIC
BEGIN
     DECLARE v_OffSet_ int;
	  DECLARE p_cntProduct int;
	  DECLARE v_Round_ int;
	  DECLARE v_TotalCount int;
	  
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_SearchProducts_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_SearchProducts_(
		ProductID INT		
	);
	
	IF SearchDynamicFields !='' THEN
		INSERT INTO tmp_SearchProducts_(ProductID)
		SELECT 
			dfv.ParentID
		FROM  Ratemanagement3.tblDynamicFieldsValue dfv
		INNER JOIN Ratemanagement3.tblDynamicFields df
		ON df.DynamicFieldsID=dfv.DynamicFieldsID
		INNER JOIN tblProduct b
		ON b.ProductID = dfv.ParentID
		WHERE 
		dfv.FieldValue like  CONCAT('%',SearchDynamicFields,'%')
		AND df.`Type`='product'
		AND df.Status=1
		AND dfv.CompanyID=p_CompanyID
		AND df.FieldDomType!='file'
		GROUP BY dfv.ParentID;
		
	END IF;
	
	SELECT COUNT(*) as cnt INTO p_cntProduct FROM tmp_SearchProducts_;  	
	
	 if p_Export = 0
	THEN
	
    SELECT   
			tblProduct.ProductID,
			tblItemType.title,
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.BuyingPrice,
			tblProduct.Amount,
			tblProduct.Quantity,
			tblProduct.updated_at,
			tblProduct.Active,
			tblProduct.Description,
			tblProduct.Note,
			tblProduct.AppliedTo,
			tblProduct.LowStockLevel,
			tblProduct.ItemTypeID,
			tblProduct.Image	
            from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
            where tblProduct.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
            AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
            AND (SearchStock='' OR 
			 	(
					 CASE WHEN SearchStock='Instock' THEN 
					 	tblProduct.Quantity > 0 
					 WHEN SearchStock='LowLevel' THEN 
					 	tblProduct.Quantity < tblProduct.LowStockLevel 
				    WHEN SearchStock='Outstock' THEN 
					 	tblProduct.Quantity < 0 
					 ELSE 
					 	SearchStock=''  
					 END 
			   )
		     )
		       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL)
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'BuyingPriceDESC') THEN BuyingPrice
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'BuyingPriceASC') THEN BuyingPrice
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuantityDESC') THEN Quantity
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuantityASC') THEN Quantity
                END ASC,
                
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN tblProduct.updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN tblProduct.updated_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveDESC') THEN tblProduct.Active
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveASC') THEN tblProduct.Active
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;
            
				
				SELECT
            COUNT(tblProduct.ProductID) INTO v_TotalCount
            from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
            where tblProduct.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
            AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
            AND (SearchStock='' OR 
			 	(
					 CASE WHEN SearchStock='Instock' THEN 
					 	tblProduct.Quantity > 0 
					 WHEN SearchStock='LowLevel' THEN 
					 	tblProduct.Quantity < tblProduct.LowStockLevel 
				    WHEN SearchStock='Outstock' THEN 
					 	tblProduct.Quantity < 0 
					 ELSE 
					 	SearchStock=''  
					 END 
			   )
		     )
		       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL);
		       
	   SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	   
	   SELECT 
	   	v_TotalCount as totalcount,
	   	ROUND(sum(tblProduct.Quantity),v_Round_) as total_grand
   	from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
         LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
         where tblProduct.CompanyID = p_CompanyID
		AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
			AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
         AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
         AND((p_Active = '' OR tblProduct.Active = p_Active))
         AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
         AND (SearchStock='' OR 
		 	(
				 CASE WHEN SearchStock='Instock' THEN 
				 	tblProduct.Quantity > 0 
				 WHEN SearchStock='LowLevel' THEN 
				 	tblProduct.Quantity < tblProduct.LowStockLevel 
			    WHEN SearchStock='Outstock' THEN 
				 	tblProduct.Quantity < 0 
				 ELSE 
				 	SearchStock=''  
				 END 
		   )
	     )
	       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL);
	   	
	   	

	ELSE

			SELECT
			tblProduct.ProductID,
			tblItemType.title,
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.BuyingPrice,
			tblProduct.Amount,
			tblProduct.Quantity,
			tblProduct.updated_at,
			tblProduct.Active,
			tblProduct.Description,
			tblProduct.Note,
			tblProduct.AppliedTo,
			tblProduct.LowStockLevel,
			tblProduct.ItemTypeID	
            from tblProduct LEFT JOIN tblItemType ON tblProduct.ItemTypeID = tblItemType.ItemTypeID
            LEFT JOIN tmp_SearchProducts_ ON tmp_SearchProducts_.ProductID=tblProduct.ProductID
			where tblProduct.CompanyID = p_CompanyID
			AND (p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
				AND ((p_ItemTypeID ='' OR tblProduct.ItemTypeID like CONCAT(p_ItemTypeID,'%')))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
            AND((p_AppliedTo is null OR tblProduct.AppliedTo = p_AppliedTo))
            AND (SearchStock='' OR 
			 	(
					 CASE WHEN SearchStock='Instock' THEN 
					 	tblProduct.Quantity > 0 
					 WHEN SearchStock='LowLevel' THEN 
					 	tblProduct.Quantity < tblProduct.LowStockLevel 
				    WHEN SearchStock='Outstock' THEN 
					 	tblProduct.Quantity < 0 
					 ELSE 
					 	SearchStock=''  
					 END 
			   )
		     )
		       AND(p_cntProduct = 0 OR tmp_SearchProducts_.ProductID IS NOT NULL);

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getLowStockItemsAlert`;
DELIMITER //
CREATE PROCEDURE `prc_getLowStockItemsAlert`(
	IN `p_CompanyID` INT(11)

)
BEGIN
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT  
		it.title AS title,
		p.Name,
		p.Code,
		p.Quantity as Stock,
		p.LowStockLevel 
	FROM tblProduct p
		LEFT join tblItemType it on it.ItemTypeID=p.ItemTypeID 
	WHERE p.EnableStock=1
		AND p.LowStockLevel >= 0 
		AND p.CompanyId=p_CompanyID 
		AND p.Quantity <= p.LowStockLevel 
		AND p.Active=1;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/* Above Done on Staging */