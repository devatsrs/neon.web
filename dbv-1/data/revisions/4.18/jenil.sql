/* USE NeonRMDev */

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


SELECT `DEFAULT_COLLATION_NAME` FROM `information_schema`.`SCHEMATA` WHERE `SCHEMA_NAME`='NeonRMDev';


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

DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CreateInvoiceFromRecurringInvoice`(
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
		INNER JOIN NeonRMDev.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;

			
		INSERT INTO tblRecurringInvoiceLog (RecurringInvoiceID,Note,RecurringInvoiceLogStatus,created_at)
		SELECT inv.RecurringInvoiceID,CONCAT(v_Note, CONCAT(LTRIM(RTRIM(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.InvoiceNumber)))) as Note,p_LogStatus as InvoiceLogStatus,p_CurrentDate as created_at
		FROM tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN NeonRMDev.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;


		
		UPDATE tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN NeonRMDev.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
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


/* Above Done on Staging */

/* Account PBX Blocking - NeonRMDev */

INSERT INTO `tblDynamicFields` (`CompanyID`, `Type`, `FieldDomType`, `FieldName`, `FieldSlug`, `FieldDescription`, `FieldOrder`, `Status`, `created_at`, `created_by`, `updated_at`, `updated_by`, `ItemTypeID`, `Minimum`, `Maximum`, `DefaultValue`, `SelectVal`) VALUES (1, 'account', 'select', 'Pbx account status', 'pbxaccountstatus', 'PBX Account Status', 0, 1, '2018-08-25 13:44:18', 'System', NULL, NULL, 0, 0, 0, NULL, NULL);
INSERT INTO `tblDynamicFields` (`CompanyID`, `Type`, `FieldDomType`, `FieldName`, `FieldSlug`, `FieldDescription`, `FieldOrder`, `Status`, `created_at`, `created_by`, `updated_at`, `updated_by`, `ItemTypeID`, `Minimum`, `Maximum`, `DefaultValue`, `SelectVal`) VALUES (1, 'account', 'boolean', 'Auto block', 'autoblock', 'PBX Auto Block', 0, 1, '2018-08-25 13:46:10', 'System', NULL, NULL, 0, 0, 0, NULL, NULL);
