CREATE DEFINER=`neon-user`@`104.47.140.143` PROCEDURE `prc_GetDashboardRecentAccounts`(
	IN `p_companyId` int ,
	IN `p_userId` VARCHAR(500),
	IN `p_AccountManager` INT

)
BEGIN

    select  AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount
          where
          `AccountType` = '1' and CompanyID = p_companyId and `Status` = '1'
          AND (  p_AccountManager = 0 OR (p_AccountManager = 1 AND find_in_set(tblAccount.Owner,p_userId)) )
    order by `tblAccount`.`AccountID` desc
	 LIMIT 10;
END