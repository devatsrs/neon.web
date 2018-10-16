use Ratemanagement3;

ALTER TABLE `tblTicketGroups`
	ADD COLUMN `GroupEmailPort` SMALLINT(4) NULL DEFAULT NULL AFTER `GroupEmailServer`;

ALTER TABLE `tblTicketGroups`
	ADD COLUMN `GroupEmailIsSSL` TINYINT(1) NULL DEFAULT '0' AFTER `GroupEmailPort`;

INSERT INTO `tblTicketImportRuleConditionType` (`TicketImportRuleConditionTypeID`, `Condition`, `ConditionText`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES (10, 'type', 'Type', NULL, NULL, NULL, NULL);


DROP PROCEDURE IF EXISTS `prc_getInvoiceUsage`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getInvoiceUsage`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_ShowZeroCall` INT

)
BEGIN

	DECLARE v_InvoiceCount_ INT;
	DECLARE v_BillingTime_ INT;
	DECLARE v_CDRType_ INT;
	DECLARE v_AvgRound_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;

	CALL fnServiceUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_ServiceID,p_StartDate,p_EndDate,v_BillingTime_);

	SELECT
		it.CDRType,b.RoundChargesAmount  INTO v_CDRType_, v_AvgRound_
	FROM NeonRMDev.tblAccountBilling ab
	INNER JOIN  NeonRMDev.tblBillingClass b
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
					FROM NeonRMDev.tblRate r
					INNER JOIN NeonRMDev.tblCountry c
					ON c.CountryID = r.CountryID
					WHERE  r.Code = ud.area_prefix limit 1)
					AS Country,
					(SELECT Description
					FROM NeonRMDev.tblRate r
					WHERE  r.Code = ud.area_prefix limit 1 )
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
			FROM NeonRMDev.tblRate r
			INNER JOIN NeonRMDev.tblCountry c
			ON c.CountryID = r.CountryID
			WHERE  r.Code = ud.area_prefix limit 1)
			AS Country,
			(SELECT
			Description
			FROM NeonRMDev.tblRate r
			INNER JOIN NeonRMDev.tblCountry c
			ON c.CountryID = r.CountryID
			WHERE  r.Code = ud.area_prefix limit 1)
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


ALTER TABLE `tblTickets`
	CHANGE COLUMN `RequesterCC` `RequesterCC` TEXT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `RequesterName`;

INSERT INTO `tblCronJobCommand` (`CronJobCommandID`, `CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`)
VALUES (534, 1, 4, 'Import Pbx Payments', 'importpbxpayments', '[[{"title":"Import Days Limit","type":"text","value":"2","name":"importdayslimit"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2016-06-09 19:33:05', NULL);

INSERT INTO `tblCronJobCommand` (`CronJobCommandID`, `CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`)
VALUES (535, 1, 4, 'Export Pbx Payments', 'exportpbxpayments', '[[{"title":"Export Days Limit","type":"text","value":"2","name":"exportdayslimit"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2016-06-09 19:33:05', NULL);

ALTER TABLE `AccountEmailLog`
	ADD COLUMN `CcMessageID` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `MessageID`;