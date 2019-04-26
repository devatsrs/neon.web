USE `speakintelligentRM`;

DROP PROCEDURE IF EXISTS `prc_FindApiInBoundPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_FindApiInBoundPrefix`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_cli` VARCHAR(200),
	IN `p_cld` VARCHAR(200),
	IN `p_City` VARCHAR(200),
	IN `p_Tariff` VARCHAR(50),
	IN `p_OriginType` VARCHAR(50),
	IN `p_OriginProvider` VARCHAR(50),
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Type` VARCHAR(50)
)
BEGIN

	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;
	DECLARE v_Count_ INT;
	DECLARE v_Count1_ INT;
	DECLARE v_Count2_ INT;

		SELECT
			CodeDeckId,
			RateTableId
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_codes(
		RateID INT,
		Code varchar(50)
	);
	
	INSERT INTO tmp_codes
	SELECT RateID,
		Code
	FROM tblRate
	WHERE CodeDeckId = v_codedeckid_;
	

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate_(
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		AccessType varchar(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_(
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		AccessType varchar(50)
	);
	
	INSERT INTO tmp_RateTableRate_
	SELECT 
		RateTableDIDRateID,
		OriginationRateID,
		RateID,
		'Other' as OriginationCode,
		'Other' as DestincationCode,
		IFNULL(City,'') as City,
		IFNULL(Tariff,'') as Tariff,
		IFNULL(AccessType,'') as AccessType
	FROM tblRateTableDIDRate
	WHERE RateTableId = p_RateTableID
		AND TimezonesID = p_TimezonesID
		AND EffectiveDate <= NOW()
		AND ApprovedStatus =1
		;
		
		UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.RateID=c.RateID
	 SET DestincationCode = c.Code; 	
	 UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.OriginationRateID=c.RateID
	 SET OriginationCode = c.Code;

	/** Both mathc cld-> destination code , cli -> origination code */
	
	IF (p_OriginType != '' OR p_OriginProvider != '')
	THEN		
		
	INSERT INTO tmp_RateTableRate2_
	SELECT * FROM tmp_RateTableRate_
	WHERE p_cli REGEXP "^[0-9]+$"
			AND (OriginationCode like  CONCAT("%",p_OriginType,"%") && OriginationCode like CONCAT("%",p_OriginProvider,"%"))			
			AND p_cld REGEXP "^[0-9]+$"
			-- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = p_City
			AND Tariff = p_Tariff
			AND AccessType = p_Type
			;
	
	END IF;		
			
	SELECT COUNT(*) into v_Count_ from tmp_RateTableRate2_;


	/** if not found record above , we only match on cld->destincation code */
	
	IF v_Count_ = 0
	THEN 
	
		INSERT INTO tmp_RateTableRate2_
		SELECT * FROM tmp_RateTableRate_
		WHERE OriginationCode ='Other'
			AND p_cld REGEXP "^[0-9]+$"
		 -- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = p_City
			AND Tariff = p_Tariff
			AND AccessType = p_Type
				;
				
		SELECT COUNT(*) into v_Count1_ from tmp_RateTableRate2_;
		
	ELSE
	
		SET v_Count1_=v_Count_;
		
	END IF;
	
	/*
	
	IF v_Count1_ = 0
	THEN
	
		INSERT INTO tmp_RateTableRate2_
		SELECT * FROM tmp_RateTableRate_
		WHERE OriginationCode ='Other'
			AND p_cld REGEXP "^[0-9]+$"
			-- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = ''
			AND Tariff = ''
			;
				
		SELECT COUNT(*) into v_Count2_ from tmp_RateTableRate2_;
		SET v_Count1_=v_Count2_;
	
	
	END IF;

	*/
	
	IF v_Count1_ > 0
	THEN
		SELECT * FROM tmp_RateTableRate2_ ORDER BY LENGTH(DestincationCode) DESC LIMIT 1; 
	END IF;	

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_FindApiOutBoundPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_FindApiOutBoundPrefix`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_cli` VARCHAR(200),
	IN `p_cld` VARCHAR(200)
)
BEGIN

	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;
	DECLARE v_Count_ INT;
	DECLARE v_Count1_ INT;

		SELECT
			CodeDeckId,
			RateTableId
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_codes(
		RateID int,
		Code varchar(50)
	);
	
	INSERT INTO tmp_codes
	SELECT RateID,
	Code
	FROM tblRate
	WHERE CodeDeckId = v_codedeckid_;
	

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate_(
		RateTableRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_(
		RateTableRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50)
	);
	
	INSERT INTO tmp_RateTableRate_
	SELECT 
		RateTableRateID,
		OriginationRateID,
		RateID,
		'Other' as OriginationCode,
		'Other' as DestincationCode
	FROM tblRateTableRate
	WHERE RateTableId = p_RateTableID
		AND TimezonesID = p_TimezonesID
		AND EffectiveDate <= NOW()
		AND ApprovedStatus=1
		;
		
	UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.RateID=c.RateID
	 SET DestincationCode = c.Code; 	
	 
	 UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.OriginationRateID=c.RateID
	 SET OriginationCode = c.Code;
		
	/** Both mathc cld-> destination code , cli -> origination code */
		
	INSERT INTO tmp_RateTableRate2_
	select * from tmp_RateTableRate_
	where p_cli REGEXP "^[0-9]+$"
			AND p_cli like  CONCAT(OriginationCode,"%")			
			AND p_cld REGEXP "^[0-9]+$"
			AND p_cld like  CONCAT(DestincationCode,"%");
			
	SELECT COUNT(*) into v_Count_ from tmp_RateTableRate2_;
	
	/** if not found record above , we only match on cld->destincation code */
		
	IF v_Count_ = 0
	THEN 
	
		INSERT INTO tmp_RateTableRate2_
		select * from tmp_RateTableRate_
		where OriginationCode ='Other'
				AND p_cld REGEXP "^[0-9]+$"
				AND p_cld like  CONCAT(DestincationCode,"%");
				
		SELECT COUNT(*) into v_Count1_ from tmp_RateTableRate2_;
		
	ELSE
	
		SET v_Count1_=v_Count_;
		
	END IF;

	IF v_Count1_ > 0
	THEN
		SELECT * FROM tmp_RateTableRate2_ order by length(DestincationCode) desc limit 1; 
	END IF;
	
END//
DELIMITER ;