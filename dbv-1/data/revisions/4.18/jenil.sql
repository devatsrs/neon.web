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
CUST_PANEL_PAGE_DYNAMICLINK_MSG_PROBLEM_CREATING_NOTIFICATION  - Problem Creating Dynamic Link.
CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_SUCCESSFULLY_UPDATED - Dynamic Link Successfully Updated
CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_SUCCESSFULLY_DELETED - Dynamic Link Successfully Deleted
CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_DELETING_NOTIFICATION - Problem Deleting Dynamic Link.


*/

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
	

/* Above db changes of taxrate done - staging */
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
			FROM NeonRMDev.tblDynamicFields a
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
		  	  	INNER JOIN NeonRMDev.tblDynamicFieldsValue b ON b.DynamicFieldsID=',@dynamicFieldID,'
		  	  	INNER JOIN NeonRMDev.tblDynamicFields a ON a.DynamicFieldsID=b.DynamicFieldsID
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


INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2649, 'Dispute.email', 'DisputeController.email', 1, 'Sumera Saeed', NULL, '2018-09-14 16:10:48.000', '2018-09-14 16:10:50.000', 1367);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1367, 'Disputes.Email', 1, 7);

INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2648, 'Dispute.send', 'DisputeController.send', 1, 'Sumera Saeed', NULL, '2018-09-14 15:52:32.000', '2018-09-14 15:52:33.000', 1366);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1366, 'Disputes.Send', 1, 7);


/* Authentication - permission*/

INSERT INTO `Ratemanagement3`.`tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1368, 'AuthenticationRule.Delete', 1, 3);
UPDATE `Ratemanagement3`.`tblResourceCategories` SET `CategoryGroupID`='3' WHERE  `ResourceCategoryID`=153;
UPDATE `Ratemanagement3`.`tblResourceCategories` SET `CategoryGroupID`='3' WHERE  `ResourceCategoryID`=152;

INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Authentication.authenticate_store', 'AuthenticationController.authenticate_store', 1, 'Khurram Saeed', NULL, '2016-02-04 13:12:34.000', '2016-02-04 13:12:34.000', 153);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.addIps', 'AuthenticationController.addIps', 1, 'Sumera Saeed', NULL, '2016-06-28 15:01:08.000', '2016-06-28 15:01:08.000', 153);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.deleteips', 'AuthenticationController.deleteips', 1, 'Sumera Saeed', NULL, '2016-06-28 15:01:08.000', '2016-06-28 15:01:08.000', 1368);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.addclis', 'AuthenticationController.addclis', 1, 'Sumera Saeed', NULL, '2016-07-16 15:42:10.000', '2016-07-16 15:42:10.000', 153);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Authentication.deleteclis', 'AuthenticationController.deleteclis', 1, 'Sumera Saeed', NULL, '2016-07-16 15:42:10.000', '2016-07-16 15:42:10.000', 1368);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'Authentication.addipclis', 'AuthenticationController.addipclis', 1, 'Sumera Saeed', NULL, '2016-07-21 13:00:46.000', '2016-07-21 13:00:46.000', 153);

/* Above Done on Staging */




