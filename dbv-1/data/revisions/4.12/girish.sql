USE `Ratemanagement3`;

CREATE TABLE IF NOT EXISTS `tblNoticeBoardPost` (
  `NoticeBoardPostID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Detail` text COLLATE utf8_unicode_ci NOT NULL,
  `Type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`NoticeBoardPostID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (1, 'Prefix Translation Rule', 'PrefixTranslationRule', 0, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (2, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (3, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (4, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (5, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (6, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (7, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (8, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);


INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1305, 'NoticeBoardPost.Delete', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1304, 'NoticeBoardPost.Add', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1303, 'NoticeBoardPost.Edit', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1302, 'NoticeBoardPost.View', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1301, 'NoticeBoardPost.All', 1, 9);

INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2283, 'NoticeBoard.delete', 'NoticeBoardController.delete', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1305);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2282, 'NoticeBoard.store', 'NoticeBoardController.store', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1303);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2281, 'NoticeBoard.get_mor_updates', 'NoticeBoardController.get_mor_updates', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1302);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2280, 'NoticeBoard.*', 'NoticeBoardController.*', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1301);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2279, 'NoticeBoard.index', 'NoticeBoardController.index', 1, 'System', NULL, '2017-06-09 12:33:00.000', '2017-06-09 12:33:00.000', 1302);


DROP PROCEDURE IF EXISTS `prc_getCustomerCodeRate`;
DELIMITER |
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
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			INDEX tmp_codes_RateID (`RateID`),
			INDEX tmp_codes_Code (`Code`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_codes2_;
		CREATE TEMPORARY TABLE tmp_codes2_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			INDEX tmp_codes2_RateID (`RateID`),
			INDEX tmp_codes2_Code (`Code`)
		);
	
		INSERT INTO tmp_codes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblCustomerRate.Rate,
			tblCustomerRate.ConnectionFee,
			tblCustomerRate.Interval1,
			tblCustomerRate.IntervalN
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
			tblRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1,
			tblRateTableRate.IntervalN
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		LEFT JOIN  tmp_codes2_ c ON c.RateID = tblRate.RateID
		WHERE 
			 tblRate.CodeDeckId = v_codedeckid_
		AND RateTableID = v_ratetableid_
		AND c.RateID IS NULL
		AND tblRateTableRate.EffectiveDate <= NOW();

		/* if Specify Rate is set when cdr rerate */
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
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1)
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;
			
			END IF;
		
			UPDATE tmp_codes_ SET Rate=p_SpecifyRate;
			
		END IF;

	END IF;
END|
DELIMITER ;

-- Dumping structure for procedure Ratemanagement3.prc_getCustomerInboundRate
DROP PROCEDURE IF EXISTS `prc_getCustomerInboundRate`;
DELIMITER |
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
		
			IF (SELECT COUNT(*) FROM tmp_inboundcodes_) = 0
			THEN
			
				SET v_CompanyID_ = (SELECT CompanyId FROM tblAccount WHERE AccountID = p_AccountID);
				INSERT INTO tmp_inboundcodes_
				SELECT
					DISTINCT
					tblRate.RateID,
					tblRate.Code,
					p_SpecifyRate,
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1)
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;
			
			END IF;
		
			UPDATE tmp_inboundcodes_ SET Rate=p_SpecifyRate;
			
		END IF;

	END IF;
END|
DELIMITER ;