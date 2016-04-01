CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardRecentLeads`(IN `p_companyId` int)
BEGIN
    select AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount where (`AccountType` = '0' and `CompanyID` = p_companyId and `Status` = '1') order by `tblAccount`.`AccountID` desc LIMIT 10;
END