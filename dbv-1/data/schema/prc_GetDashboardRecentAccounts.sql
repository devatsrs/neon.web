CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardRecentAccounts`(IN `p_companyId` int , IN `p_userId` int)
BEGIN
  DECLARE v_isAccountManage int;
    select  AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount
          where
          `AccountType` = '1' and CompanyID = p_companyId and `Status` = '1'
          AND (  v_isAccountManage = 0 OR (v_isAccountManage = 1 AND tblAccount.Owner = p_userId ) )
    order by `tblAccount`.`AccountID` desc
	 LIMIT 10;
END