/* DB:- NeonRMDev */

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
		tblDynamiclink.DynamicLinkID,
		tblDynamiclink.Title,
		tblDynamiclink.Link,
		tblCurrency.Code,
		tblDynamiclink.created_at
		from tblDynamiclink LEFT JOIN tblCurrency ON tblDynamiclink.Currency = tblCurrency.CurrencyId
		where tblDynamiclink.CompanyID = p_CompanyID
		AND (p_Title ='' OR tblDynamiclink.Title like Concat('%',p_Title,'%'))
		AND (p_Currency ='' OR tblDynamiclink.Currency = p_Currency)
            
        ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblDynamiclink.Title
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblDynamiclink.Title
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CurrencyDESC') THEN tblDynamiclink.Currency
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CurrencyASC') THEN tblDynamiclink.Currency
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
		AND (p_Currency ='' OR tblDynamiclink.Currency = p_Currency);

	ELSE
	
		SELECT
			tblDynamiclink.DynamicLinkID,
			tblDynamiclink.Title,
			tblDynamiclink.Link,
			tblCurrency.Code,
			tblDynamiclink.created_at
            from tblDynamiclink LEFT JOIN tblCurrency ON tblDynamiclink.Currency = tblCurrency.CurrencyId
			where tblDynamiclink.CompanyID = p_CompanyID
			AND (p_Title ='' OR tblDynamiclink.Title like Concat('%',p_Title,'%'))
			AND (p_Currency ='' OR tblDynamiclink.Currency = p_Currency);

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