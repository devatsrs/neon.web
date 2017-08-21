CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_CustomerRateForExport`(
	IN `p_CompanyID` INT,
	IN `p_CustomerID` INT ,
	IN `p_TrunkID` INT,
	IN `p_NameFormat` VARCHAR(50),
	IN `p_Account` VARCHAR(200),
	IN `p_Trunk` VARCHAR(200) ,
	IN `p_TrunkPrefix` VARCHAR(50),
	IN `p_Effective` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	CALL prc_GetCustomerRate(p_CompanyID,p_CustomerID,p_TrunkID,null,null,null,p_Effective,1,0,0,0,'','',-1);

	SELECT
		p_NameFormat AS AuthRule, 
		p_Account AS AccountName,
		p_Trunk AS Trunk,
		p_TrunkPrefix AS CustomerTrunkPrefix,
		Code,
		Description,
		Rate,
		EffectiveDate,
		ConnectionFee,
		Interval1,
		IntervalN,
		Prefix AS TrunkPrefix,
		RatePrefix AS TrunkRatePrefix,
		AreaPrefix AS TrunkAreaPrefix
	FROM tmp_customerrate_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END