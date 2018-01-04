USE Ratemanagement3;

ALTER TABLE `tblInvoiceTemplate`
	ADD COLUMN `ManagementReport` TEXT NULL;

update tblCronJob set Status = 0 where CronJobCommandID IN (SELECT CronJobCommandID FROM tblCronJobCommand where Command = 'createvendorsummary');
update tblCronJobCommand set Status = 0 where Command = 'createvendorsummary';

update tblCronJobCommand set Title = 'Create Summary' where Command = 'createsummary';
update tblCronJob set JobTitle = 'Create Summary' where CronJobCommandID IN (SELECT CronJobCommandID FROM tblCronJobCommand where Command = 'createsummary');
update tblCronJobCommand set Settings = '[[{"title":"Start Date","type":"text","datepicker":"","value":"","name":"StartDate"},{"title":"End Date","type":"text","value":"","datepicker":"","name":"EndDate"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"}]]' where Command = 'createsummary';


DROP PROCEDURE IF EXISTS `prc_getVendorCodeRate`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorCodeRate`(
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
	IN `p_RateCDR` INT
)
BEGIN

	IF p_RateCDR = 0
	THEN 

		DROP TEMPORARY TABLE IF EXISTS tmp_vcodes_;
		CREATE TEMPORARY TABLE tmp_vcodes_ (
			RateID INT,
			Code VARCHAR(50),
			INDEX tmp_vcodes_RateID (`RateID`),
			INDEX tmp_vcodes_Code (`Code`)
		);

		INSERT INTO tmp_vcodes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code
		FROM tblRate
		INNER JOIN tblVendorRate
		ON tblVendorRate.RateID = tblRate.RateID
		WHERE 
			 tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkID = p_trunkID
		AND tblVendorRate.EffectiveDate <= NOW();

	END IF;

	IF p_RateCDR = 1
	THEN 

		DROP TEMPORARY TABLE IF EXISTS tmp_vcodes_;
		CREATE TEMPORARY TABLE tmp_vcodes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			INDEX tmp_vcodes_RateID (`RateID`),
			INDEX tmp_vcodes_Code (`Code`)
		);

		INSERT INTO tmp_vcodes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblVendorRate.Rate,
			tblVendorRate.ConnectionFee,
			tblVendorRate.Interval1,
			tblVendorRate.IntervalN
		FROM tblRate
		INNER JOIN tblVendorRate
		ON tblVendorRate.RateID = tblRate.RateID
		WHERE 
			 tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkID = p_trunkID
		AND tblVendorRate.EffectiveDate <= NOW()
		AND (tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate >= NOW()) ;

	END IF;
END//
DELIMITER ;
