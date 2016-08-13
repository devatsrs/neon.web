CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAccounts`(IN `p_CompanyID` int, IN `p_userID` int , IN `p_IsVendor` int , IN `p_isCustomer` int , IN `p_activeStatus` int, IN `p_VerificationStatus` int, IN `p_AccountNo` VARCHAR(100), IN `p_ContactName` VARCHAR(50), IN `p_AccountName` VARCHAR(50), IN `p_tags` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT )
BEGIN
   DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
		SELECT cs.Value INTO v_Round_ from tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
		
	IF p_isExport = 0
	THEN



		SELECT 
		 	tblAccount.AccountID,
			tblAccount.Number, 
			tblAccount.AccountName,
			CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) as Ownername,
			tblAccount.Phone, 
			
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(((Select ifnull(sum(GrandTotal),0)  from NeonBillingDev.tblInvoice where AccountID = tblAccount.AccountID and CompanyID = p_CompanyID AND InvoiceStatus != 'cancel' ) -(Select ifnull(sum(Amount),0)  from NeonBillingDev.tblPayment where tblPayment.AccountID = tblAccount.AccountID and tblPayment.CompanyID = p_CompanyID and Status = 'Approved' AND tblPayment.Recall = 0 )),v_Round_)) as OutStandingAmount,
			tblAccount.Email, 
			tblAccount.IsCustomer, 
			tblAccount.IsVendor,
			tblAccount.VerificationStatus,
			tblAccount.Address1,
			tblAccount.Address2,
			tblAccount.Address3,
			tblAccount.City,
			tblAccount.Country,
			tblAccount.Picture
		FROM tblAccount
	 	LEFT JOIN tblUser ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact ON tblContact.Owner=tblAccount.AccountID
		WHERE tblAccount.CompanyID = p_CompanyID
		AND tblAccount.AccountType = 1
		AND tblAccount.Status = p_activeStatus
		AND tblAccount.VerificationStatus = p_VerificationStatus
		AND(p_userID = 0 OR tblAccount.Owner = p_userID)
	   AND((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
	   AND((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
		AND((p_AccountNo = '' OR tblAccount.Number like p_AccountNo))
		AND((p_AccountName = '' OR tblAccount.AccountName like Concat('%',p_AccountName,'%')))
		AND((p_tags = '' OR tblAccount.tags like Concat(p_tags,'%')))
		AND((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) like Concat('%',p_ContactName,'%')))
		group by tblAccount.AccountID
			ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tblAccount.AccountName
             END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tblAccount.AccountName
                END DESC,                
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberDESC') THEN tblAccount.Number
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberASC') THEN tblAccount.Number
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameDESC') THEN tblUser.FirstName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameASC') THEN tblUser.FirstName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneDESC') THEN tblAccount.Phone
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneASC') THEN tblAccount.Phone
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailDESC') THEN tblAccount.Email
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailASC') THEN tblAccount.Email
	                END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;

	SELECT
   	COUNT(tblAccount.AccountID) AS totalcount
	FROM tblAccount
 	LEFT JOIN tblUser ON tblAccount.Owner = tblUser.UserID
	LEFT JOIN tblContact ON tblContact.Owner=tblAccount.AccountID
	WHERE tblAccount.CompanyID = p_CompanyID
	AND tblAccount.AccountType = 1
	AND tblAccount.Status = p_activeStatus
	AND tblAccount.VerificationStatus = p_VerificationStatus
	AND(p_userID = 0 OR tblAccount.Owner = p_userID)
   AND((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
   AND((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
	AND((p_AccountNo = '' OR tblAccount.Number like p_AccountNo))
	AND((p_AccountName = '' OR tblAccount.AccountName like Concat('%',p_AccountName,'%')))
	AND((p_tags = '' OR tblAccount.tags like Concat('%',p_tags,'%')))
	AND((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) like Concat('%',p_ContactName,'%')));
	
	END IF;
	IF p_isExport = 1
    THEN
        SELECT
            tblAccount.Number as NO, tblAccount.AccountName,CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) as Name,tblAccount.Phone, 
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,(Select ifnull(sum(GrandTotal),0)  from NeonBillingDev.tblInvoice  where AccountID = tblAccount.AccountID and CompanyID = p_CompanyID AND InvoiceStatus != 'cancel' ) -(Select ifnull(sum(Amount),0)  from NeonBillingDev.tblPayment  where tblPayment.AccountID = tblAccount.AccountID and tblPayment.CompanyID = p_CompanyID and Status = 'Approved' AND tblPayment.Recall = 0 )) as OS ,
			tblAccount.Email
			FROM tblAccount
			LEFT JOIN tblUser ON tblAccount.Owner = tblUser.UserID
			LEFT JOIN tblContact ON tblContact.Owner=tblAccount.AccountID
			WHERE tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND(p_userID = 0 OR tblAccount.Owner = p_userID)
			AND((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND((p_AccountNo = '' OR tblAccount.Number like p_AccountNo))
			AND((p_AccountName = '' OR tblAccount.AccountName like Concat('%',p_AccountName,'%')))
			AND((p_tags = '' OR tblAccount.tags like Concat('%',p_tags,'%')))
			AND((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) like Concat('%',p_ContactName,'%')))
			group by tblAccount.AccountID;
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END