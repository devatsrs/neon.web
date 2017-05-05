USE Ratemanagement3;
-- ###############################################################################################
ALTER TABLE `tblResourceCategories`	ADD COLUMN `CategoryGroupID` INT NULL DEFAULT '0' AFTER `CompanyID`;
-- ###############################################################################################
CREATE TABLE IF NOT EXISTS `tblResourceCategoriesGroups` (
  `CategoriesGroupID` int(11) NOT NULL AUTO_INCREMENT,
  `GroupName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `Index 1` (`CategoriesGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `tblResourceCategoriesGroups` (`CategoriesGroupID`, `GroupName`) VALUES
	(1, 'All'),
	(2, 'Dashboard'),
	(3, 'Accounts'),
	(4, 'Ticket Management'),
	(5, 'Rate Management'),
	(6, 'CRM'),
	(7, 'Billing'),
	(8, 'Settings'),
	(9, 'Admin'),
	(10, 'Jobs'),
	(11, 'Others'),
	(12, 'Integration');
	
-- ###############################################################################################
DELIMITER //
CREATE  PROCEDURE `prc_GetAjaxResourceList`(
	IN `p_CompanyID` INT,
	IN `p_userid` LONGTEXT,
	IN `p_roleids` LONGTEXT,
	IN `p_action` int
)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	
	IF p_action = 1
    THEN

        select distinct tblResourceCategories.ResourceCategoryID,tblResourceCategories.ResourceCategoryName,tblResourceCategories.CategoryGroupID,usrper.AddRemove,rolres.Checked
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

		select  distinct `tblResourceCategories`.`ResourceCategoryID`, `tblResourceCategories`.`ResourceCategoryName`,tblResourceCategories.CategoryGroupID, tblRolePermission.resourceID as Checked ,case when tblRolePermission.resourceID>0
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
-- ###############################################################################################
update tblResourceCategories trc set trc.CategoryGroupID = '3' where trc.ResourceCategoryName like '%Account.%';

update tblResourceCategories trc set trc.CategoryGroupID = '2' where trc.ResourceCategoryName like '%Dashboard.%';

update tblResourceCategories trc set trc.CategoryGroupID = '4' where trc.ResourceCategoryName like '%ticket%';

-- ----------------------------------------------------------------------------------------------------------------------
update tblResourceCategories trc set trc.CategoryGroupID = '4' where trc.ResourceCategoryName like '%TicketDashboard%';
update tblResourceCategories trc set trc.CategoryGroupID = '4' where trc.ResourceCategoryName like '%TicketsFields%';
update tblResourceCategories trc set trc.CategoryGroupID = '4' where trc.ResourceCategoryName like '%TicketsGroups%';
update tblResourceCategories trc set trc.CategoryGroupID = '4' where trc.ResourceCategoryName like '%TicketsSla%';
update tblResourceCategories trc set trc.CategoryGroupID = '4' where trc.ResourceCategoryName like '%TicketsBusinessHours%';
-- ----------------------------------------------------------------------------------------------------------------------

update tblResourceCategories trc set trc.CategoryGroupID = '5' where trc.ResourceCategoryName like '%RateTables%';

update tblResourceCategories trc set trc.CategoryGroupID = '5' where trc.ResourceCategoryName like '%LCR.%';

update tblResourceCategories trc set trc.CategoryGroupID = '5' where trc.ResourceCategoryName like '%VendorProfiling%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%Estimates%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%Invoices%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%RecurringInvoice%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%Dispute%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%BillingSubscription%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%Payments%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%AccountStatement%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%Products%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%InvoiceTemplates%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%TaxRates%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%CDR%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%Discount%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%BillingClass%';

update tblResourceCategories trc set trc.CategoryGroupID = '6' where trc.ResourceCategoryName like '%OpportunityBoard%';

update tblResourceCategories trc set trc.CategoryGroupID = '6' where trc.ResourceCategoryName like '%Task%';

update tblResourceCategories trc set trc.CategoryGroupID = '6' where trc.ResourceCategoryName like '%Dashboard%';

update tblResourceCategories trc set trc.CategoryGroupID = '5' where trc.ResourceCategoryName like '%RateGenerator%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%invoice%';

update tblResourceCategories trc set trc.CategoryGroupID = '8' where trc.ResourceCategoryName like '%Currency%';

update tblResourceCategories trc set trc.CategoryGroupID = '6' where trc.ResourceCategoryName like '%Leads%';

update tblResourceCategories trc set trc.CategoryGroupID = '8' where trc.ResourceCategoryName like '%CodeDecks%';

update tblResourceCategories trc set trc.CategoryGroupID = '10' where trc.ResourceCategoryName like '%Jobs%';

update tblResourceCategories trc set trc.CategoryGroupID = '10' where trc.ResourceCategoryName like '%CronJob%';

update tblResourceCategories trc set trc.CategoryGroupID = '10' where trc.ResourceCategoryName like '%ActiveCronJob%';

update tblResourceCategories trc set trc.CategoryGroupID = '6' where trc.ResourceCategoryName like '%Opportunity%';

update tblResourceCategories trc set trc.CategoryGroupID = '7' where trc.ResourceCategoryName like '%Estimate%';

update tblResourceCategories trc set trc.CategoryGroupID = '9' where trc.ResourceCategoryName like '%ServerInfo%';

update tblResourceCategories trc set trc.CategoryGroupID = '11' where trc.ResourceCategoryName like '%MyProfile%';

update tblResourceCategories trc set trc.CategoryGroupID = '9' where trc.ResourceCategoryName like '%Retention%';

update tblResourceCategories trc set trc.CategoryGroupID = '8' where trc.ResourceCategoryName like '%Destination%';

update tblResourceCategories trc set trc.CategoryGroupID = '12' where trc.ResourceCategoryName like '%Integration%';

update tblResourceCategories trc set trc.CategoryGroupID = '12' where trc.ResourceCategoryName like '%Gateway%';

update tblResourceCategories trc set trc.CategoryGroupID = '11' where trc.ResourceCategoryName like '%Company%';

update tblResourceCategories trc set trc.CategoryGroupID = '8' where trc.ResourceCategoryName like '%Trunk%';

update tblResourceCategories trc set trc.CategoryGroupID = '11' where trc.ResourceCategoryName like '%Users%';

update tblResourceCategories trc set trc.CategoryGroupID = '3' where trc.ResourceCategoryName like '%CustomersRates%';

update tblResourceCategories trc set trc.CategoryGroupID = '3' where trc.ResourceCategoryName like '%VendorRates%';

update tblResourceCategories trc set trc.CategoryGroupID = '8' where trc.ResourceCategoryName like '%DialStrings%';

update tblResourceCategories trc set trc.CategoryGroupID = '9' where trc.ResourceCategoryName like '%Alert%';

update tblResourceCategories trc set trc.CategoryGroupID = '2' where trc.ResourceCategoryName like '%analysis%';

update tblResourceCategories trc set trc.CategoryGroupID = '11' where trc.ResourceCategoryName like '%Pages.%';

update tblResourceCategories trc set trc.CategoryGroupID = 2 where trc.ResourceCategoryName like '%MonitorDashboard%';

DELETE from tblResourceCategories where ResourceCategoryName like '%Messages%';