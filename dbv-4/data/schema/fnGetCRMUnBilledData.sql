CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `fnGetCRMUnBilledData`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_CurrencyID` INT,
	IN `p_ListType` VARCHAR(50),
	IN `p_Start` DATETIME,
	IN `p_End` DATETIME
)
BEGIN
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
	CREATE TEMPORARY TABLE tmp_Account_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		LastInvoiceDate DATE
	);

	INSERT INTO tmp_Account_ (AccountID)
	SELECT DISTINCT tblHeader.AccountID  FROM tblHeader INNER JOIN NeonRMDev.tblAccount ON tblAccount.AccountID = tblHeader.AccountID WHERE tblHeader.CompanyID = p_CompanyID;

	UPDATE tmp_Account_ SET LastInvoiceDate = fngetLastInvoiceDate(AccountID);

	INSERT INTO NeonRMDev.tmp_Dashboard_invoices_
	SELECT 
			CONCAT(tu.FirstName,' ',tu.LastName) AS `AssignedUserText`,
			 tu.UserID AS  AssignedUserID,
			sum(h.TotalCharges) AS `Revenue`,
			YEAR(dd.date) AS Year,
			MONTH(dd.date) AS Month,
			WEEK(dd.date)	 AS `Week`
	FROM tmp_Account_ a
	INNER JOIN tblDimDate dd
		ON dd.date >= a.LastInvoiceDate
	INNER JOIN tblHeader h
		ON h.AccountID = a.AccountID
		AND h.DateID = dd.DateID
	INNER JOIN NeonRMDev.tblAccount ac 
			ON ac.AccountID = a.AccountID 
	INNER JOIN NeonRMDev.tblUser tu 
			ON tu.UserID = ac.Owner
	WHERE ac.`Status` = 1
	AND tu.`Status` = 1
	AND (p_CurrencyID = '' OR ( p_CurrencyID !=''  AND ac.CurrencyId = p_CurrencyID))
	AND ac.CompanyID = p_CompanyID
	AND (p_OwnerID = '' OR   FIND_IN_SET(tu.UserID,p_OwnerID))
	AND (dd.date BETWEEN p_Start AND p_End)
	  GROUP BY
			YEAR(dd.date) 
			,MONTH(dd.date)
			,WEEK(dd.date)
			,CONCAT(tu.FirstName,' ',tu.LastName)
			,tu.UserID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END