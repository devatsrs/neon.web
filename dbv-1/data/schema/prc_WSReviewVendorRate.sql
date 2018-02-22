CREATE DEFINER=`neon-user`@`%` PROCEDURE `prc_WSReviewVendorRate`(
	IN `p_accountId` INT,
	IN `p_trunkId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT






















)
ThisSP:BEGIN


    -- @TODO: code cleanup
     DECLARE newstringcode INT(11) DEFAULT 0;
     DECLARE v_pointer_ INT;
     DECLARE v_rowCount_ INT;


	  DECLARE v_AccountCurrencyID_ INT;
	  DECLARE v_CompanyCurrencyID_ INT;


     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_;
    CREATE TEMPORARY TABLE tmp_split_VendorRate_ (
    		`TempVendorRateID` int,
			  `CodeDeckId` int ,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
			  `Change` varchar(100) ,
			  `ProcessId` varchar(200) ,
			  `Preference` varchar(100) ,
			  `ConnectionFee` decimal(18, 6),
			  `Interval1` int,
			  `IntervalN` int,
			  `Forbidden` varchar(100) ,
			  `DialStringPrefix` varchar(500) ,
			  INDEX tmp_EffectiveDate (`EffectiveDate`),
			  INDEX tmp_Code (`Code`),
        INDEX tmp_CC (`Code`,`Change`),
			  INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
    CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
    		`TempVendorRateID` int,
			  `CodeDeckId` int ,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
			  `Change` varchar(100) ,
			  `ProcessId` varchar(200) ,
			  `Preference` varchar(100) ,
			  `ConnectionFee` decimal(18, 6),
			  `Interval1` int,
			  `IntervalN` int,
			  `Forbidden` varchar(100) ,
			  `DialStringPrefix` varchar(500) ,
			  INDEX tmp_EffectiveDate (`EffectiveDate`),
			  INDEX tmp_Code (`Code`),
        INDEX tmp_CC (`Code`,`Change`),
			  INDEX tmp_Change (`Change`)
    );

    CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);


	-- archive vendor rate code	
--	CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId);


	ALTER TABLE `tmp_TempVendorRate_`	ADD Column `NewRate` decimal(18, 6) ;



    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;


	   SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
	   SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);
	
	
	-- update all rate on newrate with currency conversion.
	update tmp_TempVendorRate_
	SET
	NewRate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    Rate
                )
   WHERE ProcessID=p_processId;
   
	
		-- if no error
    IF newstringcode = 0
    THEN
			-- if rates is not in our database (new rates from file) than insert it into ChangeLog
			INSERT INTO tblVendorRateChangeLog(
				TempVendorRateID,
				VendorRateID,
		   	AccountId,
		   	TrunkID,
				RateId,
		   	Code,
		   	Description,
		   	Rate,
		   	EffectiveDate,
		   	EndDate,
		   	Interval1,
		   	IntervalN,
		   	ConnectionFee,
		   	`Action`,
		   	ProcessID,
		   	created_at
			) 
			SELECT
				tblTempVendorRate.TempVendorRateID,
				tblVendorRate.VendorRateID,
			   p_accountId AS AccountId,
			   p_trunkId AS TrunkID,
			   tblRate.RateId,
			   tblTempVendorRate.Code,
			   tblTempVendorRate.Description,
			   tblTempVendorRate.Rate,
			  	tblTempVendorRate.EffectiveDate,
				tblTempVendorRate.EndDate ,
			  	IFNULL(tblTempVendorRate.Interval1,tblRate.Interval1 ) as Interval1,		-- take interval from file and update in tblRate if not changed in service
			  	IFNULL(tblTempVendorRate.IntervalN , tblRate.IntervalN ) as IntervalN,
			   tblTempVendorRate.ConnectionFee,
			   'New' AS `Action`,
			   p_processId AS ProcessID,
			   now() AS created_at
			FROM tmp_TempVendorRate_ as tblTempVendorRate
			LEFT JOIN tblRate
			   ON tblTempVendorRate.Code = tblRate.Code AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblVendorRate
				ON tblRate.RateID = tblVendorRate.RateId AND tblVendorRate.AccountId = p_accountId   AND tblVendorRate.TrunkId = p_trunkId
				AND tblVendorRate.EffectiveDate  <= date(now()) 
		   WHERE tblTempVendorRate.ProcessID=p_processId AND tblVendorRate.VendorRateID IS NULL
              AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
				 -- AND tblTempVendorRate.EffectiveDate != '0000-00-00 00:00:00';


   		-- loop through effective date
      DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
			CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
				EffectiveDate  Date,
				RowID int,
				INDEX (RowID)
			);
      INSERT INTO tmp_EffectiveDates_
      SELECT distinct
        EffectiveDate,
        @row_num := @row_num+1 AS RowID
      FROM tmp_TempVendorRate_
        ,(SELECT @row_num := 0) x
      WHERE  ProcessID = p_processId
     -- AND EffectiveDate <> '0000-00-00 00:00:00'
      group by EffectiveDate
      order by EffectiveDate asc;


    SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

	         -- update  previous rate with all latest recent entriy of previous effective date

                       INSERT INTO tblVendorRateChangeLog(
                           TempVendorRateID,
                           VendorRateID,
                           AccountId,
                           TrunkID,
                           RateId,
                           Code,
                           Description,
                           Rate,
                           EffectiveDate,
                           EndDate,
                           Interval1,
                           IntervalN,
                           ConnectionFee,
                           `Action`,
                           ProcessID,
                           created_at
                       ) 
               			  SELECT
               			  distinct
                       tblTempVendorRate.TempVendorRateID,
                       VendorRate.VendorRateID,
                       p_accountId AS AccountId,
                       p_trunkId AS TrunkID,
                       VendorRate.RateId,
                       tblRate.Code,
                       tblRate.Description,
                       tblTempVendorRate.Rate,
                       tblTempVendorRate.EffectiveDate,
                       tblTempVendorRate.EndDate ,
                       tblTempVendorRate.Interval1,
                       tblTempVendorRate.IntervalN,
                       tblTempVendorRate.ConnectionFee,
                       IF(tblTempVendorRate.NewRate > VendorRate.Rate, 'Increased', IF(tblTempVendorRate.NewRate < VendorRate.Rate, 'Decreased','')) AS `Action`,
                       p_processid AS ProcessID,
                       now() AS created_at
                       FROM
                         (
                         -- get all rates RowID = 1 to remove old to old effective date

                         select distinct tmp.* ,
                         @row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                         @prev_RateId := tmp.RateID,
                         @prev_EffectiveDate := tmp.EffectiveDate
                         FROM
                         (
                         
                         
                         				select distinct vr1.*
	                         	     from tblVendorRate vr1
			                          LEFT outer join tblVendorRate vr2
												on vr1.AccountID = vr2.AccountID  
												and vr1.TrunkID = vr2.TrunkID 
												and vr1.RateID = vr2.RateID
												AND vr2.EffectiveDate  = @EffectiveDate
			                          where
			                          vr1.AccountID = p_accountId AND vr1.TrunkID = p_trunkId
			                          and vr1.EffectiveDate < COALESCE(vr2.EffectiveDate,@EffectiveDate)   
			                          order by vr1.RateID desc ,vr1.EffectiveDate desc
			                        
                         	
                         ) tmp , 	  
								 ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
								  order by RateID desc , EffectiveDate desc


                         ) VendorRate
                      JOIN tblRate
                         ON tblRate.CompanyID = p_companyId
                         AND tblRate.RateID = VendorRate.RateId
                      JOIN tmp_TempVendorRate_ tblTempVendorRate
                         ON tblTempVendorRate.Code = tblRate.Code 
								 	AND tblTempVendorRate.ProcessID=p_processId
                         --	AND  tblTempVendorRate.EffectiveDate <> '0000-00-00 00:00:00' 
								 AND  VendorRate.EffectiveDate < tblTempVendorRate.EffectiveDate
               				  AND tblTempVendorRate.EffectiveDate =  @EffectiveDate
               				 
               				   AND VendorRate.RowID = 1
               				  
                       WHERE
                         VendorRate.AccountId = p_accountId
                         AND VendorRate.TrunkId = p_trunkId
                         -- AND tblTempVendorRate.EffectiveDate <> '0000-00-00 00:00:00'
                         AND tblTempVendorRate.Code IS NOT NULL
                         AND tblTempVendorRate.ProcessID=p_processId
                         AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;


    		IF p_list_option = 1 -- p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
    		THEN

    			-- get rates which is not in file and insert it into ChangeLog
         	          INSERT INTO tblVendorRateChangeLog(
				VendorRateID,
			   	AccountId,
			   	TrunkID,
				RateId,
			   	Code,
			   	Description,
			   	Rate,
			   	EffectiveDate,
			   	EndDate,
			   	Interval1,
			   	IntervalN,
			   	ConnectionFee,
			   	`Action`,
			   	ProcessID,
			   	created_at
				)
				SELECT DISTINCT
                    tblVendorRate.VendorRateID,
                    p_accountId AS AccountId,
                    p_trunkId AS TrunkID,
                    tblVendorRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblVendorRate.Rate,
                    tblVendorRate.EffectiveDate,
                    tblVendorRate.EndDate ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    'Deleted' AS `Action`,
			   			p_processId AS ProcessID,
                    now() AS deleted_at
                    FROM tblVendorRate
                    JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
                    ON tblTempVendorRate.Code = tblRate.Code  
						  
						  AND tblTempVendorRate.ProcessID=p_processId
						  AND ( 
						  			-- normal condition
								  ( tblTempVendorRate.EndDate is null AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
							  	OR
							  		-- skip records just to avoid duplicate records in tblVendorRateChangeLog tabke - when EndDate is given with delete 
								  ( tblTempVendorRate.EndDate is not null AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') ) 
							  )
                    WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                    AND ( tblVendorRate.EndDate is null OR tblVendorRate.EndDate <= date(now()) )
                    AND tblTempVendorRate.Code IS NULL
                    ORDER BY VendorRateID ASC;

    		END IF;


            INSERT INTO tblVendorRateChangeLog(
				VendorRateID,
			   	AccountId,
			   	TrunkID,
				RateId,
			   	Code,
			   	Description,
			   	Rate,
			   	EffectiveDate,
			   	EndDate,
			   	Interval1,
			   	IntervalN,
			   	ConnectionFee,
			   	`Action`,
			   	ProcessID,
			   	created_at
				)
				SELECT DISTINCT
                    tblVendorRate.VendorRateID,
                    p_accountId AS AccountId,
                    p_trunkId AS TrunkID,
                    tblVendorRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblVendorRate.Rate,
                    tblVendorRate.EffectiveDate,
                    IFNULL(tblTempVendorRate.EndDate,tblVendorRate.EndDate) as  EndDate ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    'Deleted' AS `Action`,
			   			p_processId AS ProcessID,
                    now() AS deleted_at
                    FROM tblVendorRate
                    JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
	                    ON tblRate.Code = tblTempVendorRate.Code  
							  AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
							   AND tblTempVendorRate.ProcessID=p_processId
                    -- AND tblTempVendorRate.EndDate <= date(now())
         	           -- AND tblTempVendorRate.ProcessID=p_processId                  
                    WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
               	     -- AND tblVendorRate.EndDate <= date(now())
            	        AND tblTempVendorRate.Code IS NOT NULL 
                    ORDER BY VendorRateID ASC;

 

    END IF;

    SELECT * FROM tmp_JobLog_;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END