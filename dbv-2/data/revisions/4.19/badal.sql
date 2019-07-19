Use `RMBilling3`;

DROP PROCEDURE IF EXISTS `prc_getInvoiceUsage`;
DELIMITER //
CREATE PROCEDURE `prc_getInvoiceUsage`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_ShowZeroCall` INT

)
test:BEGIN

	DECLARE v_InvoiceCount_ INT;
	DECLARE v_BillingTime_ INT;
	DECLARE v_CDRType_ INT;
	DECLARE v_AvgRound_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;

	CALL fnServiceUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_ServiceID,p_StartDate,p_EndDate,v_BillingTime_);

	SELECT
		it.CDRType,b.RoundChargesCDR  INTO v_CDRType_, v_AvgRound_
	FROM Ratemanagement3.tblAccountBilling ab
	INNER JOIN  Ratemanagement3.tblBillingClass b
		ON b.BillingClassID = ab.BillingClassID
	INNER JOIN tblInvoiceTemplate it
		ON it.InvoiceTemplateID = b.InvoiceTemplateID
	WHERE ab.AccountID = p_AccountID
		AND ab.ServiceID = p_ServiceID
	LIMIT 1;

	IF( v_CDRType_ = 2)
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblSummaryUsageDetails_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblSummaryUsageDetails_(
	AccountID int,
	area_prefix varchar(50),
	trunk varchar(50),
	UsageDetailID int,
	duration int,
	billed_duration int,
	cost decimal(18,6),
	ServiceID INT,
	AvgRate decimal(18,6)
);

INSERT INTO tmp_tblSummaryUsageDetails_
SELECT
	AccountID,
	area_prefix,
	trunk,
	UsageDetailID,
	duration,
	billed_duration,
	cost,
	ServiceID,
	ROUND((uh.cost/uh.billed_duration)*60.0,v_AvgRound_) as AvgRate
FROM tmp_tblUsageDetails_ uh;

SELECT
			area_prefix AS AreaPrefix,
			Trunk,
			(SELECT
			Country
			FROM Ratemanagement3.tblRate r
			INNER JOIN Ratemanagement3.tblCodeDeck cd
			ON r.CodeDeckID = cd.CodeDeckID
			INNER JOIN Ratemanagement3.tblCountry c
			ON c.CountryID = r.CountryID
			WHERE  r.CompanyID = p_CompanyID AND r.Code = ud.area_prefix  AND cd.DefaultCodeDeck=1 limit 1)
			AS Country,
			(SELECT Description
			FROM Ratemanagement3.tblRate r
			INNER JOIN Ratemanagement3.tblCodeDeck cd
			ON r.CodeDeckID = cd.CodeDeckID
			WHERE  r.CompanyID = p_CompanyID AND r.Code = ud.area_prefix  AND cd.DefaultCodeDeck=1 limit 1 )
			AS Description,
			COUNT(UsageDetailID) AS NoOfCalls,
			CONCAT( FLOOR(SUM(duration ) / 60), ':' , SUM(duration ) % 60) AS Duration,
			CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS BillDuration,
			SUM(cost) AS ChargedAmount,
			SUM(duration ) as DurationInSec,
			SUM(billed_duration ) as BillDurationInSec,
			ud.ServiceID,
			ud.AvgRate as AvgRatePerMin
		FROM tmp_tblSummaryUsageDetails_ ud
		GROUP BY ud.area_prefix,ud.Trunk,ud.AccountID,ud.ServiceID,ud.AvgRate;

	ELSE
		SELECT
			trunk AS Trunk,
			area_prefix AS Prefix,
			CONCAT("'",cli) AS CLI,
			CONCAT("'",cld) AS CLD,
			(SELECT
			Country
			FROM Ratemanagement3.tblRate r
			INNER JOIN Ratemanagement3.tblCountry c
			ON c.CountryID = r.CountryID
			INNER JOIN Ratemanagement3.tblCodeDeck cd
			ON r.CodeDeckID = cd.CodeDeckID
			WHERE r.CompanyID = p_CompanyID AND r.Code = ud.area_prefix  AND cd.DefaultCodeDeck=1 limit 1)
			AS Country,
			(SELECT
			Description
			FROM Ratemanagement3.tblRate r
			INNER JOIN Ratemanagement3.tblCountry c
			ON c.CountryID = r.CountryID
			INNER JOIN Ratemanagement3.tblCodeDeck cd
			ON r.CodeDeckID = cd.CodeDeckID
			WHERE r.CompanyID = p_CompanyID AND  r.Code = ud.area_prefix  AND cd.DefaultCodeDeck=1 limit 1)
			AS Description,
			CASE
				WHEN is_inbound=1 then 'Incoming'
				ELSE 'Outgoing'
			END
			as CallType,
			connect_time AS ConnectTime,
			disconnect_time AS DisconnectTime,
			billed_duration AS BillDuration,
			SEC_TO_TIME(billed_duration) AS BillDurationMinutes,
			cost AS ChargedAmount,
			ServiceID
		FROM tmp_tblUsageDetails_ ud
		WHERE ((p_ShowZeroCall =0 AND ud.cost >0 ) OR (p_ShowZeroCall =1 AND ud.cost >= 0))
		ORDER BY connect_time ASC;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
