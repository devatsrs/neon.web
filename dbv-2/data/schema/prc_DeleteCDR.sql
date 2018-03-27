CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_DeleteCDR`(
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AccountID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_ResellerID` INT
)
BEGIN

	DECLARE v_BillingTime_ int;
	DECLARE v_raccountids TEXT;
	SET v_raccountids = '';
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM NeonRMDev.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM NeonRMDev.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;		
		
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetail_ AS (

		SELECT
		UsageDetailID
		FROM
		(
			SELECT
				uh.AccountID,
				a.AccountName,
				trunk,
				area_prefix,
				UsageDetailID,
				duration,
				billed_duration,
				cli,
				cld,
				cost,
				connect_time,
				disconnect_time
			FROM `NeonCDRDev`.tblUsageDetails  ud
			INNER JOIN `NeonCDRDev`.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN NeonRMDev.tblAccount a
				ON uh.AccountID = a.AccountID
			WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
				AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
				AND uh.CompanyID = p_CompanyID
				AND uh.AccountID is not null
				AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
				AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
				AND (p_CDRType = '' OR ud.userfield LIKE CONCAT('%',p_CDRType,'%') )
				AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
				AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
				AND (p_zerovaluecost = 0 OR ( p_zerovaluecost = 1 AND cost = 0) OR ( p_zerovaluecost = 2 AND cost > 0))
				AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
				AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
				AND (p_trunk = '' OR trunk = p_trunk )
				AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)

		) tbl
		WHERE

			((v_BillingTime_ =1 OR v_BillingTime_ =3)  and connect_time >= p_StartDate AND connect_time <= p_EndDate)
			OR
			(v_BillingTime_ =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
			AND billed_duration > 0
	);

	DELETE ud.*
	FROM `NeonCDRDev`.tblUsageDetails ud
	INNER JOIN tmp_tblUsageDetail_ uds
		ON ud.UsageDetailID = uds.UsageDetailID; 

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END