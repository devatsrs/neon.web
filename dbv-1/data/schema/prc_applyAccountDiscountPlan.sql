CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_applyAccountDiscountPlan`(IN `p_AccountID` INT, IN `p_tbltempusagedetail_name` VARCHAR(200), IN `p_processId` INT)
BEGIN
	
	DECLARE v_MinutesRemaining_ INT;
	DECLARE v_DiscountPlanID_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
	CREATE TEMPORARY TABLE tmp_codes_ (
		RateID INT,
		Code VARCHAR(50),
		DiscountID INT
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_discountsecons_;
	CREATE TEMPORARY TABLE tmp_discountsecons_ (
		TempUsageDetailID INT,
		TotalMinutes INT,
		DiscountID INT,
		MinutesRemaining INT,
		Discount INT
	);
	
	/* get discount plan id*/
	SELECT DiscountPlanID INTO  v_DiscountPlanID_ FROM tblAccountDiscountPlan WHERE AccountID = p_AccountID;
	
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
	INSERT INTO tmp_discountsecons_ (TempUsageDetailID,TotalMinutes,DiscountID)
	SELECT 
		d.TempUsageDetailID,
		@t := IF(@pre_DiscountID = d.DiscountID, @t + TotalMinutes,TotalMinutes) as TotalMinutes,
		@pre_DiscountID := d.DiscountID
	FROM
	(
		SELECT 
			CEIL(billed_duration/60) as TotalMinutes,
			TempUsageDetailID,
			area_prefix,
			DiscountID,
			AccountID
		FROM NeonCDRDev.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN tmp_codes_ c
			ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 0 
			AND ud.AccountID = ' , p_AccountID , '
			AND area_prefix =  c.Code 
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
	SET d.MinutesRemaining = (adc.Threshold - adc.MinutesUsed),d.Discount=adc.Discount;
	
	/* remove call which cross the threshold */
	DELETE FROM tmp_discountsecons_ WHERE TotalMinutes > MinutesRemaining;
	
	/* update call cost which are under threshold */
	SET @stm = CONCAT('
	UPDATE NeonCDRDev.' , p_tbltempusagedetail_name , ' ud  INNER JOIN
	tmp_discountsecons_ d ON d.TempUsageDetailID = ud.TempUsageDetailID 
	SET cost = (cost - d.Discount*cost/100);
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
			MAX(TotalMinutes) as MinutesUsed,
			DiscountID 
		FROM tmp_discountsecons_
		GROUP BY DiscountID
	)d 
	ON adc.DiscountID = d.DiscountID
	SET adc.MinutesUsed = adc.MinutesUsed+d.MinutesUsed
	WHERE adp.AccountID = p_AccountID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END