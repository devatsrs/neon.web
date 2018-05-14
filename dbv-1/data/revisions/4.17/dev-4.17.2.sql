Use Ratemanagement3;

SELECT GatewayID INTO @FTPGatewayID  FROM tblGateway WHERE `Name` = 'FTP';
-- delete existing and insert again.
DELETE FROM tblGatewayConfig WHERE `GatewayID` = @FTPGatewayID;
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Rate Format', 'RateFormat', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Authentication Rule', 'NameFormat', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'CDR ReRate', 'RateCDR', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Rerate Method', 'RateMethod', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Rerate Method Value', 'SpecifyRate', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'CLI Translation Rule', 'CLITranslationRule', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'FTP Host IP', 'host', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Protocol Type', 'protocol_type', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Port', 'port', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'SSL', 'ssl', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Passive Mode', 'passive_mode', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'User Name', 'username', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Password', 'password', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Key', 'key', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Key Phrase', 'keyphrase', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'FTP CDR Download Path', 'cdr_folder', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Billing Time', 'BillingTime', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` ( `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES ( @FTPGatewayID, 'File Name Rule', 'FileNameRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);


DROP PROCEDURE IF EXISTS `prc_WSGenerateSippySheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateSippySheet`(
	IN `p_CustomerID` INT ,
	IN `p_Trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE

)
	BEGIN

		-- get customer rates
		CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);

		SELECT
			CASE WHEN EndDate IS NOT NULL THEN
				'SA'
			ELSE
				'A'
			END AS `Action [A|D|U|S|SA`,
			'' as id,
			Concat(IFNULL(Prefix,''), Code) as Prefix,
			Description as COUNTRY ,
			Interval1 as `Interval 1`,
			IntervalN as `Interval N`,
			Rate as `Price 1`,
			Rate as `Price N`,
			0  as Forbidden,
			0 as `Grace Period`,

			-- DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' ) AS `Activation Date`,
			CASE WHEN EffectiveDate < NOW()  THEN
				'NOW'
			ELSE
				DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' )
			END AS `Activation Date`,
			DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`
		FROM
			tmp_customerrateall_
		ORDER BY
			Prefix;

	END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `vwVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `vwVendorSippySheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE


)
	BEGIN

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective,p_CustomDate);

		INSERT INTO tmp_VendorSippySheet_
			SELECT
				NULL AS RateID,
				CASE WHEN EndDate IS NOT NULL THEN
					'SA'
				ELSE
					'A'
				END AS `Action [A|D|U|S|SA`,
				'' AS id,
				Concat('' , tblTrunk.Prefix ,vendorRate.Code) AS Prefix,
				vendorRate.Description AS COUNTRY,
				IFNULL(tblVendorPreference.Preference,5) as Preference,
				vendorRate.Interval1 as `Interval 1`,
				vendorRate.IntervalN as `Interval N`,
				vendorRate.Rate AS `Price 1`,
				vendorRate.Rate AS `Price N`,
				10 AS `1xx Timeout`,
				60 AS `2xx Timeout`,
				0 AS Huntstop,
				CASE
				WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
							FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
							OR
							(blockCountry.VendorBlockingId IS NOT NULL AND
							 FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
							)
				) THEN 1
				ELSE 0
				END  AS Forbidden,
				CASE WHEN EffectiveDate < NOW()  THEN
					'NOW'
				ELSE
					DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' )
				END AS `Activation Date`,
				DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`,

				tblAccount.AccountID,
				tblTrunk.TrunkID
			FROM tmp_VendorCurrentRates_ AS vendorRate
				INNER JOIN tblAccount
					ON vendorRate.AccountId = tblAccount.AccountID
				LEFT OUTER JOIN tblVendorBlocking
					ON vendorRate.RateID = tblVendorBlocking.RateId
						 AND tblAccount.AccountID = tblVendorBlocking.AccountId
						 AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
				LEFT OUTER JOIN tblVendorBlocking AS blockCountry
					ON vendorRate.CountryID = blockCountry.CountryId
						 AND tblAccount.AccountID = blockCountry.AccountId
						 AND vendorRate.TrunkID = blockCountry.TrunkID
				LEFT JOIN tblVendorPreference
					ON tblVendorPreference.AccountId = vendorRate.AccountId
						 AND tblVendorPreference.TrunkID = vendorRate.TrunkID
						 AND tblVendorPreference.RateId = vendorRate.RateID
				INNER JOIN tblTrunk
					ON tblTrunk.TrunkID = vendorRate.TrunkID
			WHERE (vendorRate.Rate > 0);


	END//
DELIMITER ;

