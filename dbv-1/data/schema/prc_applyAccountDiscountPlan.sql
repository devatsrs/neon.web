CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_applyAccountDiscountPlan`(IN `p_AccountID` INT, IN `p_tbltempusagedetail_name` VARCHAR(200), IN `p_processId` INT, IN `p_inbound` INT)
BEGIN
	
	DECLARE v_DiscountPlanID_ INT;
	DECLARE v_StartDate DATE;
	DECLARE v_EndDate DATE;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
	CREATE TEMPORARY TABLE tmp_codes_ (
		RateID INT,
		Code VARCHAR(50),
		DiscountID INT
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_discountsecons_;
	CREATE TEMPORARY TABLE tmp_discountsecons_ (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempUsageDetailID INT,
		TotalSecond INT,
		DiscountID INT,
		RemainingSecond INT,
		Discount INT,
		ThresholdReached INT DEFAULT 0,
		Unlimited INT
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_discountsecons2_;
	CREATE TEMPORARY TABLE tmp_discountsecons2_ (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempUsageDetailID INT,
		TotalSecond INT,
		DiscountID INT,
		RemainingSecond INT,
		Discount INT,
		ThresholdReached INT DEFAULT 0,
		Unlimited INT
	);
	
	/* get discount plan id*/
	SELECT DiscountPlanID,StartDate,EndDate INTO  v_DiscountPlanID_,v_StartDate,v_EndDate FROM tblAccountDiscountPlan WHERE AccountID = p_AccountID AND 
	( (p_inbound = 0 AND Type = 1) OR  (p_inbound = 1 AND Type = 2 ) );
	
	/* get codes from discount destination group*/
	INSERT INTO tmp_codes_
	SELECT 
		r.RateID,
		r.Code,
		d.DiscountID
	FROM tblDiscountPlan dp
	INNER JOIN tblDiscount d ON d.DiscountPlanID = dp.DiscountPlanID
	INNER JOIN tblDestinationGroupCode dgc ON dgc.DestinationGroupID = d.DestinationGroupID
	INNER JOIN tblRate r ON r.RateID = dgc.RateID
	WHERE dp.DiscountPlanID = v_DiscountPlanID_;
	
	/* get minutes total in cdr table by disconnect time*/
	SET @stm = CONCAT('
	INSERT INTO tmp_discountsecons_ (TempUsageDetailID,TotalSecond,DiscountID)
	SELECT 
		d.TempUsageDetailID,
		@t := IF(@pre_DiscountID = d.DiscountID, @t + TotalSecond,TotalSecond) as TotalSecond,
		@pre_DiscountID := d.DiscountID
	FROM
	(
		SELECT 
			billed_duration as TotalSecond,
			TempUsageDetailID,
			area_prefix,
			DiscountID,
			AccountID
		FROM NeonCDRDev.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN tmp_codes_ c
			ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = ',p_inbound,' 
			AND ud.AccountID = ' , p_AccountID , '
			AND area_prefix =  c.Code
			AND DATE(ud.disconnect_time) >= "', v_StartDate ,'"
			AND DATE(ud.disconnect_time) < "',v_EndDate, '"
		ORDER BY c.DiscountID asc , disconnect_time asc
	) d
	CROSS JOIN (SELECT @t := 0) i
	CROSS JOIN (SELECT @pre_DiscountID := 0) j
	');
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	/* update remaining minutes and discount */
	UPDATE tmp_discountsecons_ d
	INNER JOIN tblAccountDiscountPlan adp 
 		ON adp.AccountID = p_AccountID
	INNER JOIN tblAccountDiscountScheme adc 
		ON adc.AccountDiscountPlanID =  adp.AccountDiscountPlanID 
		AND adc.DiscountID = d.DiscountID
	SET d.RemainingSecond = (adc.Threshold - adc.SecondsUsed),d.Discount=adc.Discount,d.Unlimited = adc.Unlimited;
	
	/* remove call which cross the threshold */
	UPDATE  tmp_discountsecons_ SET ThresholdReached=1   WHERE Unlimited = 0 AND TotalSecond > RemainingSecond;
	
	INSERT INTO tmp_discountsecons2_
	SELECT * FROM tmp_discountsecons_;
	
	/* update call cost which are under threshold */
	SET @stm = CONCAT('
	UPDATE NeonCDRDev.' , p_tbltempusagedetail_name , ' ud  INNER JOIN
	tmp_discountsecons_ d ON d.TempUsageDetailID = ud.TempUsageDetailID 
	SET cost = (cost - d.Discount*cost/100)
	WHERE ThresholdReached = 0;
	');
	
	PREPARE stmt FROM @stm;
 	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	/* update remaining minutes in account discount */
	UPDATE tblAccountDiscountPlan adp 
	INNER JOIN tblAccountDiscountScheme adc 
		ON adc.AccountDiscountPlanID =  adp.AccountDiscountPlanID 
	INNER JOIN(
		SELECT 
			MAX(TotalSecond) as SecondsUsed,
			DiscountID 
		FROM tmp_discountsecons_
		WHERE ThresholdReached = 0
		GROUP BY DiscountID
	)d 
	ON adc.DiscountID = d.DiscountID
	SET adc.SecondsUsed = adc.SecondsUsed+d.SecondsUsed
	WHERE adp.AccountID = p_AccountID;
	

	/* update call cost which reach threshold and update seconds also*/
	SET @stm =CONCAT('
	UPDATE tmp_discountsecons_ d
	INNER JOIN( 
		SELECT MIN(RowID) as RowID  FROM tmp_discountsecons2_ WHERE ThresholdReached = 1
	GROUP BY DiscountID
	) tbl ON tbl.RowID = d.RowID
	INNER JOIN NeonCDRDev.' , p_tbltempusagedetail_name , ' ud
		ON ud.TempUsageDetailID = d.TempUsageDetailID
	INNER JOIN tblAccountDiscountPlan adp 
	 		ON adp.AccountID = ',p_AccountID,'
	INNER JOIN tblAccountDiscountScheme adc 
			ON adc.AccountDiscountPlanID =  adp.AccountDiscountPlanID 
			AND adc.DiscountID = d.DiscountID
	SET ud.cost = cost*(TotalSecond - RemainingSecond)/billed_duration,adc.SecondsUsed = adc.SecondsUsed + billed_duration - (TotalSecond - RemainingSecond);
	');
	
	PREPARE stmt FROM @stm;
 	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END