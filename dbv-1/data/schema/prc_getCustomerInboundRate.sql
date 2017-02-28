CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getCustomerInboundRate`(
	IN `p_AccountID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_CLD` VARCHAR(500),
	IN `p_InboundTableID` INT
)
BEGIN

	DECLARE v_inboundratetableid_ INT;

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
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			INDEX tmp_inboundcodes_RateID (`RateID`),
			INDEX tmp_inboundcodes_Code (`Code`)
		);
		INSERT INTO tmp_inboundcodes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblRateTableRate.Rate,
			tblRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1,
			tblRateTableRate.IntervalN
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		WHERE RateTableID = v_inboundratetableid_
		AND tblRateTableRate.EffectiveDate <= NOW();
		
		/* if Specify Rate is set when cdr rerate */
		IF p_RateMethod = 'SpecifyRate'
		THEN
		
			UPDATE tmp_inboundcodes_ SET Rate=p_SpecifyRate;
			
		END IF;

	END IF;
END