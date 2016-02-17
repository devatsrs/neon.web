CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorCDRReRateByAccount`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT, IN `p_processId` VARCHAR(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
    DECLARE v_CodeDeckId_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SELECT CodeDeckId into v_CodeDeckId_  from tblVendorTrunk where AccountID = p_AccountID and TrunkID = p_trunkID;
    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
	CREATE TEMPORARY TABLE tmp_VendorRate_ (
	    VendorRateID INT,
	    Code VARCHAR(50),
	    Description VARCHAR(200),
		ConnectionFee DECIMAL(18, 6),
	    Interval1 INT,
	    IntervalN INT,
	    Rate DECIMAL(18, 6),
	    EffectiveDate DATE,
	    updated_at DATETIME,
	    updated_by VARCHAR(50),
	    INDEX tmp_VendorRate_RateID (`RateID`)
	);

    INSERT INTO tmp_VendorRate_
	SELECT
				VendorRateID,
				Code,
				tblRate.Description,
				tblVendorRate.ConnectionFee,
				CASE WHEN tblVendorRate.Interval1 IS NOT NULL
				THEN tblVendorRate.Interval1
				ELSE tblRate.Interval1
				END AS Interval1,
				CASE WHEN tblVendorRate.IntervalN IS NOT NULL
				THEN tblVendorRate.IntervalN
				ELSE tblRate.IntervalN
				END AS IntervalN ,
				Rate,
				EffectiveDate,
				tblVendorRate.updated_at,
				tblVendorRate.updated_by
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			WHERE (tblRate.CompanyID = p_companyid)
			AND TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_
			AND EffectiveDate <= NOW();

	   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
	   AND  n1.Code = n2.Code;

    set @stm1 = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    SET selling_cost = CASE WHEN  billed_duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((billed_duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+ifnull(ConnectionFee,0)
        ElSE
        Rate+ifnull(ConnectionFee,0)
    END
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'"
    AND trunk = (select Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" limit 1)
    AND vr.rate is not null') ;

    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;


    set @stm2 = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    SET selling_cost = 0.0
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'"
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND vr.rate is null ');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;

    set @stm3 = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` SET selling_cost = 0.0
    WHERE processid = "',p_processId,'" AND accountid is null');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;


    set @stm4 = CONCAT('SELECT DISTINCT ud.cld as area_prefix,ud.trunk,ud.GatewayAccountID,a.AccountName
    FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    LEFT JOIN tblAccount a ON a.AccountID = ud.AccountID
    WHERE processid ="',p_processId,'"
    AND ud.accountid = "',p_AccountID ,'"
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND vr.rate is null');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END