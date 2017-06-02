CREATE DEFINER=`neon-user`@`104.47.140.143` PROCEDURE `prc_getMissingAccounts`(
	IN `p_CompanyID` int,
	IN `p_CompanyGatewayID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	SELECT 
		cg.Title,
		CASE WHEN REPLACE(JSON_EXTRACT(cg.Settings, '$.NameFormat'),'"','') = 'NUB'
		THEN
			ga.AccountNumber
		ELSE
			CASE WHEN REPLACE(JSON_EXTRACT(cg.Settings, '$.NameFormat'),'"','') = 'IP'
			THEN
				ga.AccountIP
			ELSE
				CASE WHEN REPLACE(JSON_EXTRACT(cg.Settings, '$.NameFormat'),'"','') = 'CLI'
				THEN
					ga.AccountCLI
				ELSE 
					ga.AccountName
				END
			END
		END
		AS AccountName
	FROM tblGatewayAccount ga
	INNER JOIN NeonRMDev.tblCompanyGateway cg ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE ga.GatewayAccountID IS NOT NULL and ga.CompanyID =p_CompanyID AND ga.AccountID IS NULL AND cg.`Status` =1
	AND (p_CompanyGatewayID = 0 OR ga.CompanyGatewayID = p_CompanyGatewayID )
	ORDER BY ga.CompanyGatewayID,ga.AccountName;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END