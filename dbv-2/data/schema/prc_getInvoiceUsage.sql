CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getInvoiceUsage`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_ShowZeroCall` INT
)
BEGIN

	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	DECLARE v_CDRType_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;

	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_,'','','',0); 

	SELECT b.CDRType  INTO v_CDRType_ FROM Ratemanagement3.tblAccountBilling ab INNER JOIN  Ratemanagement3.tblBillingClass b  ON b.BillingClassID = ab.BillingClassID WHERE ab.AccountID = p_AccountID;

	IF( v_CDRType_ = 2) -- Summery
	THEN

		SELECT
			area_prefix AS AreaPrefix,
			Trunk,
			(SELECT 
				Country
			FROM Ratemanagement3.tblRate r
			INNER JOIN Ratemanagement3.tblCountry c
				ON c.CountryID = r.CountryID
			WHERE  r.Code = ud.area_prefix LIMIT 1)
			AS Country,
			(SELECT Description
			FROM Ratemanagement3.tblRate r
			WHERE  r.Code = ud.area_prefix LIMIT 1 )
			AS Description,
			COUNT(UsageDetailID) AS NoOfCalls,
			CONCAT( FLOOR(SUM(duration ) / 60), ':' , SUM(duration ) % 60) AS Duration,
			CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS BillDuration,
			SUM(cost) AS TotalCharges,
			SUM(duration ) AS DurationInSec,
			SUM(billed_duration ) AS BillDurationInSec
		FROM tmp_tblUsageDetails_ ud
		GROUP BY ud.area_prefix,ud.Trunk,ud.AccountID;

	ELSE

		SELECT
			trunk,
			area_prefix,
			CONCAT("'",cli) AS cli,
			CONCAT("'",cld) AS cld,
			connect_time,
			disconnect_time,
			billed_duration,
			cost
		FROM tmp_tblUsageDetails_ ud
		WHERE
			((p_ShowZeroCall =0 AND ud.cost >0 ) OR (p_ShowZeroCall =1 AND ud.cost >= 0))
		ORDER BY connect_time ASC;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END