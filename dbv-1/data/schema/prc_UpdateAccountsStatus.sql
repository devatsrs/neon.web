CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_UpdateAccountsStatus`(IN `p_CompanyID` int, IN `p_userID` int , IN `p_IsVendor` int , IN `p_isCustomer` int , IN `p_VerificationStatus` int, IN `p_AccountNo` VARCHAR(100), IN `p_ContactName` VARCHAR(50), IN `p_AccountName` VARCHAR(50), IN `p_tags` VARCHAR(50), IN `P_status` INT)
BEGIN
		UPDATE   tblAccount ta
		LEFT JOIN tblContact tc ON tc.Owner=ta.AccountID
		 set  ta.Status = P_status
		WHERE    ta.CompanyID = p_CompanyID
		AND 	   ta.AccountType = 1
		AND 	   ta.VerificationStatus = p_VerificationStatus
		AND		(p_userID = 0 OR ta.Owner = p_userID)
	   AND		((p_IsVendor = 0 OR ta.IsVendor = 1))
	   AND		((p_isCustomer = 0 OR ta.IsCustomer = 1))
		AND		((p_AccountNo = '' OR ta.Number like p_AccountNo))
		AND		((p_AccountName = '' OR ta.AccountName like Concat('%',p_AccountName,'%')))
		AND		((p_tags = '' OR ta.tags like Concat(p_tags,'%')))
		AND		((p_ContactName = '' OR (CONCAT(IFNULL(tc.FirstName,'') ,' ', IFNULL(tc.LastName,''))) like Concat('%',p_ContactName,'%')));
END