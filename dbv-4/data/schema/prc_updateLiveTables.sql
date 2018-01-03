CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateLiveTables`(
	IN `p_CompanyID` INT,
	IN `p_UniqueID` VARCHAR(50)
)
BEGIN

	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @stmt = CONCAT('
	UPDATE tmp_tblUsageDetailsReport_',p_UniqueID,' uh
	INNER JOIN NeonBillingDev.tblGatewayAccount ga
		ON  uh.GatewayAccountPKID = ga.GatewayAccountPKID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ',p_CompanyID,';
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	UPDATE tmp_tblVendorUsageDetailsReport_',p_UniqueID,' uh
	INNER JOIN NeonBillingDev.tblGatewayAccount ga
		ON  uh.GatewayVAccountPKID = ga.GatewayAccountPKID
	SET uh.VAccountID = ga.AccountID
	WHERE uh.VAccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ',p_CompanyID,';
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END