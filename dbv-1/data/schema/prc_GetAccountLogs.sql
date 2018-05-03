CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_GetAccountLogs`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_AccountID` int ,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5)






)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	SELECT * FROM
	(
		(
			SELECT
				d.ColumnName,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgo.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=1) THEN
						'YES'
					ELSE
						d.OldValue
				END AS OldValue,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgn.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=1) THEN
						'YES'
					ELSE
						d.NewValue
				END AS NewValue,
				d.created_at,
				d.created_by
			FROM tblAuditDetails AS d
			LEFT JOIN tblAuditHeader AS h
				ON h.AuditHeaderID = d.AuditHeaderID
			LEFT JOIN tblAccount AS a
				ON h.ParentColumnID = a.AccountID
			LEFT JOIN tblCompanyGateway AS cgo
				ON FIND_IN_SET(cgo.CompanyGatewayID,d.OldValue)
			LEFT JOIN tblCompanyGateway AS cgn
				ON FIND_IN_SET(cgn.CompanyGatewayID,d.NewValue)
			WHERE   
				h.Type = 'account' AND 
				h.ParentColumnName = 'AccountID' AND 
				h.ParentColumnID = p_AccountID AND 
				a.AccountID = p_AccountID
			GROUP BY
				d.AuditDetailID,d.OldValue,d.NewValue
		)
		
		UNION
		
		(
			SELECT
				d.ColumnName,
				d.OldValue AS OldValue,
				d.NewValue AS NewValue,
				d.created_at,
				d.created_by
			FROM tblAuditDetails AS d
			LEFT JOIN tblAuditHeader AS h
				ON h.AuditHeaderID = d.AuditHeaderID
			LEFT JOIN tblAccountBilling AS ab
				ON h.ParentColumnID = ab.AccountBillingID	
			LEFT JOIN tblAccount AS a
				ON ab.AccountID = a.AccountID
			WHERE   
				h.Type = 'accountbilling' AND 
				h.ParentColumnName = 'AccountBillingID' AND 
				ab.AccountID = p_AccountID
			GROUP BY
				d.AuditDetailID,d.OldValue,d.NewValue
		)
		
		UNION
		
		(
			SELECT
				IF(d2.ColumnName="CustomerAuthValue","Customer IP","Vendor IP"),
				d2.OldValue AS OldValue,
				d2.NewValue AS NewValue,
				d2.created_at,
				d2.created_by
			FROM tblAuditDetails AS d2
			LEFT JOIN tblAuditHeader AS h2
				ON h2.AuditHeaderID = d2.AuditHeaderID
			LEFT JOIN tblAccountAuthenticate AS aa
				ON aa.AccountAuthenticateID=h2.ParentColumnID
			LEFT JOIN tblAccount AS a2
				ON aa.AccountID = a2.AccountID
			WHERE   
				h2.Type = 'accountip' AND 
				h2.ParentColumnName = 'AccountAuthenticateID' AND 
				a2.AccountID = p_AccountID
			GROUP BY
				d2.AuditDetailID,d2.OldValue,d2.NewValue
		)
	) mix

	ORDER BY
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ColumnNameDESC') THEN mix.ColumnName
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ColumnNameASC') THEN mix.ColumnName
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN mix.created_at
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN mix.created_at
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_byDESC') THEN mix.created_by
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_byASC') THEN mix.created_by
		END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;

	SELECT 
		COUNT(*) AS totalcount
	FROM
	(
		(
			SELECT
				d.ColumnName,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgo.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=1) THEN
						'YES'
					ELSE
						d.OldValue
				END AS OldValue,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgn.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=1) THEN
						'YES'
					ELSE
						d.NewValue
				END AS NewValue,
				d.created_at,
				d.created_by
			FROM tblAuditDetails AS d
			LEFT JOIN tblAuditHeader AS h
				ON h.AuditHeaderID = d.AuditHeaderID
			LEFT JOIN tblAccount AS a
				ON h.ParentColumnID = a.AccountID
			LEFT JOIN tblCompanyGateway AS cgo
				ON FIND_IN_SET(cgo.CompanyGatewayID,d.OldValue)
			LEFT JOIN tblCompanyGateway AS cgn
				ON FIND_IN_SET(cgn.CompanyGatewayID,d.NewValue)
			WHERE   
				h.Type = 'account' AND 
				h.ParentColumnName = 'AccountID' AND 
				h.ParentColumnID = p_AccountID AND 
				a.AccountID = p_AccountID
			GROUP BY
				d.AuditDetailID,d.OldValue,d.NewValue
		)
		
		UNION
		
		(
			SELECT
				d.ColumnName,
				d.OldValue AS OldValue,
				d.NewValue AS NewValue,
				d.created_at,
				d.created_by
			FROM tblAuditDetails AS d
			LEFT JOIN tblAuditHeader AS h
				ON h.AuditHeaderID = d.AuditHeaderID
			LEFT JOIN tblAccountBilling AS ab
				ON h.ParentColumnID = ab.AccountBillingID	
			LEFT JOIN tblAccount AS a
				ON ab.AccountID = a.AccountID
			WHERE   
				h.Type = 'accountbilling' AND 
				h.ParentColumnName = 'AccountBillingID' AND 
				ab.AccountID = p_AccountID
			GROUP BY
				d.AuditDetailID,d.OldValue,d.NewValue
		)
		
		UNION
		
		(
			SELECT
				d2.ColumnName,
				d2.OldValue AS OldValue,
				d2.NewValue AS NewValue,
				d2.created_at,
				d2.created_by
			FROM tblAuditDetails AS d2
			LEFT JOIN tblAuditHeader AS h2
				ON h2.AuditHeaderID = d2.AuditHeaderID
			LEFT JOIN tblAccountAuthenticate AS aa
				ON aa.AccountAuthenticateID=h2.ParentColumnID
			LEFT JOIN tblAccount AS a2
				ON aa.AccountID = a2.AccountID
			WHERE   
				h2.Type = 'accountip' AND 
				h2.ParentColumnName = 'AccountAuthenticateID' AND 
				a2.AccountID = p_AccountID
			GROUP BY
				d2.AuditDetailID,d2.OldValue,d2.NewValue
		)
	) totalcount;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END