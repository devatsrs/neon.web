use `Ratemanagement3`;


DROP PROCEDURE IF EXISTS `prc_getCustomerCodeRate`;
DELIMITER //
CREATE PROCEDURE `prc_getCustomerCodeRate`(
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_RateTableID` INT
)
BEGIN
	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;

	IF p_RateTableID > 0
	THEN

		SELECT
			CodeDeckId,
			RateTableId
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	ELSE

		SELECT
			CodeDeckId,
			RateTableID
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblCustomerTrunk
		WHERE tblCustomerTrunk.TrunkID = p_trunkID
		AND tblCustomerTrunk.AccountID = p_AccountID
		AND tblCustomerTrunk.Status = 1;

	END IF;



	IF p_RateCDR = 0
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
		CREATE TEMPORARY TABLE tmp_codes_ (
			RateID INT,
			Code VARCHAR(50),
			INDEX tmp_codes_RateID (`RateID`),
			INDEX tmp_codes_Code (`Code`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_codes2_;
		CREATE TEMPORARY TABLE tmp_codes2_ (
			RateID INT,
			Code VARCHAR(50),
			INDEX tmp_codes2_RateID (`RateID`),
			INDEX tmp_codes2_Code (`Code`)
		);

		INSERT INTO tmp_codes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code
		FROM tblRate
		INNER JOIN tblCustomerRate
		ON tblCustomerRate.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND CustomerID = p_AccountID
		AND tblCustomerRate.TrunkID = p_trunkID
		AND tblCustomerRate.EffectiveDate <= NOW();

		INSERT INTO tmp_codes2_
		SELECT * FROM tmp_codes_;

		INSERT INTO tmp_codes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		LEFT JOIN  tmp_codes2_ c ON c.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND RateTableID = v_ratetableid_
		AND c.RateID IS NULL
		AND tblRateTableRate.EffectiveDate <= NOW();

	END IF;

	IF p_RateCDR = 1
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
		CREATE TEMPORARY TABLE tmp_codes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_codes_RateID (`RateID`),
			INDEX tmp_codes_Code (`Code`),
			INDEX tmp_codes_TimezonesID (`TimezonesID`),
			INDEX tmp_codes_Code_TimezonesID (`Code`,`TimezonesID`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_codes2_;
		CREATE TEMPORARY TABLE tmp_codes2_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_codes2_RateID (`RateID`),
			INDEX tmp_codes2_Code (`Code`),
			INDEX tmp_codes2_TimezonesID (`TimezonesID`),
			INDEX tmp_codes2_Code_TimezonesID (`Code`,`TimezonesID`)
		);

		INSERT INTO tmp_codes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblCustomerRate.Rate,
			tblCustomerRate.RateN,
			tblCustomerRate.ConnectionFee,
			tblCustomerRate.Interval1,
			tblCustomerRate.IntervalN,
			tblCustomerRate.TimezonesID
		FROM tblRate
		INNER JOIN tblCustomerRate
		ON tblCustomerRate.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND CustomerID = p_AccountID
		AND tblCustomerRate.TrunkID = p_trunkID
		AND tblCustomerRate.EffectiveDate <= NOW();

		INSERT INTO tmp_codes2_
		SELECT * FROM tmp_codes_;

		INSERT INTO tmp_codes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblRateTableRate.Rate,
			tblRateTableRate.RateN,
			tblRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1,
			tblRateTableRate.IntervalN,
			tblRateTableRate.TimezonesID
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		LEFT JOIN  tmp_codes2_ c ON c.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND RateTableID = v_ratetableid_
		AND c.RateID IS NULL
		AND tblRateTableRate.EffectiveDate <= NOW();


		IF p_RateMethod = 'SpecifyRate'
		THEN


			IF (SELECT COUNT(*) FROM tmp_codes_) = 0
			THEN

				SET v_CompanyID_ = (SELECT CompanyId FROM tblAccount WHERE AccountID = p_AccountID);
				INSERT INTO tmp_codes_
				SELECT
					DISTINCT
					tblRate.RateID,
					tblRate.Code,
					p_SpecifyRate,
					p_SpecifyRate,
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1),
					NULL AS TimezonesID
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;

			END IF;

			UPDATE tmp_codes_ SET Rate=p_SpecifyRate, RateN=p_SpecifyRate;

		END IF;

	END IF;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getCustomerInboundRate`;
DELIMITER //
CREATE PROCEDURE `prc_getCustomerInboundRate`(
	IN `p_AccountID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_CLD` VARCHAR(500),
	IN `p_InboundTableID` INT
)
BEGIN

	DECLARE v_inboundratetableid_ INT;
	DECLARE v_CompanyID_ INT;

	IF p_CLD != ''
	THEN

		SELECT
			RateTableID INTO v_inboundratetableid_
		FROM tblCLIRateTable
		WHERE AccountID = p_AccountID AND CLI = p_CLD;

	ELSEIF p_InboundTableID > 0
	THEN

		SET v_inboundratetableid_ = p_InboundTableID;

	ELSE

		SELECT
			InboudRateTableID INTO v_inboundratetableid_
		FROM tblAccount
		WHERE AccountID = p_AccountID;

	END IF;

	IF p_RateCDR = 1
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_inboundcodes_;
		CREATE TEMPORARY TABLE tmp_inboundcodes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_inboundcodes_RateID (`RateID`),
			INDEX tmp_inboundcodes_Code (`Code`),
			INDEX tmp_inboundcodes_TimezonesID (`TimezonesID`),
			INDEX tmp_inboundcodes_Code_TimezonesID (`Code`,`TimezonesID`)
		);
		INSERT INTO tmp_inboundcodes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblRateTableRate.Rate,
			tblRateTableRate.RateN,
			tblRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1,
			tblRateTableRate.IntervalN,
			tblRateTableRate.TimezonesID
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		WHERE RateTableID = v_inboundratetableid_
		AND tblRateTableRate.EffectiveDate <= NOW();


		IF p_RateMethod = 'SpecifyRate'
		THEN

			IF (SELECT COUNT(*) FROM tmp_inboundcodes_) = 0
			THEN

				SET v_CompanyID_ = (SELECT CompanyId FROM tblAccount WHERE AccountID = p_AccountID);
				INSERT INTO tmp_inboundcodes_
				SELECT
					DISTINCT
					tblRate.RateID,
					tblRate.Code,
					p_SpecifyRate,
					p_SpecifyRate,
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1),
					NULL AS TimezonesID
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;

			END IF;

			UPDATE tmp_inboundcodes_ SET Rate=p_SpecifyRate, RateN=p_SpecifyRate;

		END IF;

	END IF;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getVendorCodeRate`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorCodeRate`(
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN

	DECLARE v_CompanyID_ INT;

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
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_vcodes_RateID (`RateID`),
			INDEX tmp_vcodes_Code (`Code`),
			INDEX tmp_vcodes_TimezonesID (`TimezonesID`),
			INDEX tmp_vcodes_Code_TimezonesID (`Code`,`TimezonesID`)
		);

		INSERT INTO tmp_vcodes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblVendorRate.Rate,
			tblVendorRate.RateN,
			tblVendorRate.ConnectionFee,
			tblVendorRate.Interval1,
			tblVendorRate.IntervalN,
			tblVendorRate.TimezonesID
		FROM tblRate
		INNER JOIN tblVendorRate
		ON tblVendorRate.RateID = tblRate.RateID
		WHERE
			 tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkID = p_trunkID
		AND tblVendorRate.EffectiveDate <= NOW()
		AND (tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate >= NOW()) ;

		IF p_RateMethod = 'SpecifyRate'
		THEN
			IF (SELECT COUNT(*) FROM tmp_vcodes_) = 0
			THEN

				SET v_CompanyID_ = (SELECT CompanyId FROM tblAccount WHERE AccountID = p_AccountID);
				INSERT INTO tmp_vcodes_
				SELECT
					DISTINCT
					tblRate.RateID,
					tblRate.Code,
					p_SpecifyRate,
					p_SpecifyRate,
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1),
					NULL AS TimezonesID
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;

			END IF;

			UPDATE tmp_vcodes_ SET Rate=p_SpecifyRate;

		END IF;

	END IF;
END//
DELIMITER ;