CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessVendorRate`(
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
	IN `p_dialcodeSeparator` VARCHAR(50)
)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	 DECLARE     v_CodeDeckId_ INT ;
    DECLARE totaldialstringcode INT(11) DEFAULT 0;	 
    DECLARE newstringcode INT(11) DEFAULT 0;
	 DECLARE totalduplicatecode INT(11);	 
	 DECLARE errormessage longtext;
	 DECLARE errorheader longtext;
    
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
			`CodeDeckId` int ,
			`Code` varchar(50) ,
			`Description` varchar(200) ,
			`Rate` decimal(18, 6) ,
			`EffectiveDate` Datetime ,
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
    
    
    
    DROP TEMPORARY TABLE IF EXISTS tmp_Delete_VendorRate;
	CREATE TEMPORARY TABLE tmp_Delete_VendorRate (
     	VendorRateID INT,
      AccountId INT,
      TrunkID INT,
      RateId INT,
      Code VARCHAR(50),
      Description VARCHAR(200),
      Rate DECIMAL(18, 6),
      EffectiveDate DATETIME,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		deleted_at DATETIME,
        INDEX tmp_VendorRateDiscontinued_VendorRateID (`VendorRateID`)
	);

    
    		CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);
    		
    		SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_; 
	
	
    IF newstringcode = 0
    THEN
   		
	         IF  p_addNewCodesToCodeDeck = 1
            THEN

					
                INSERT INTO tblRate (CompanyID,
                Code,
                Description,
                CreatedBy,
                CountryID,
                CodeDeckId,
                Interval1,
                IntervalN)
                    SELECT DISTINCT
                        p_companyId,
                        vc.Code,
                        vc.Description,
                        'WindowsService',
                       
                       fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
                        CodeDeckId,
                        Interval1,
                        IntervalN
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description,
                            tblTempVendorRate.CodeDeckId,
                            tblTempVendorRate.Interval1,
                            tblTempVendorRate.IntervalN

                        FROM tmp_TempVendorRate_  as tblTempVendorRate
                   	     LEFT JOIN tblRate
						             ON tblRate.Code = tblTempVendorRate.Code
						             AND tblRate.CompanyID = p_companyId
						             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
	                        WHERE tblRate.RateID IS NULL
	                     	   AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) vc; 
                  						


               	SELECT GROUP_CONCAT(Code) into errormessage FROM(
                    SELECT DISTINCT
                        tblTempVendorRate.Code as Code, 1 as a
                    FROM tmp_TempVendorRate_  as tblTempVendorRate 
	                    INNER JOIN tblRate
					             ON tblRate.Code = tblTempVendorRate.Code
					             AND tblRate.CompanyID = p_companyId
					             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							  WHERE tblRate.CountryID IS NULL
	                 		   AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')) as tbl GROUP BY a;
                    
                  IF errormessage IS NOT NULL
                  THEN
                  
                    INSERT INTO tmp_JobLog_ (Message)
                    	 SELECT DISTINCT
                        CONCAT(tblTempVendorRate.Code , ' INVALID CODE - COUNTRY NOT FOUND')
                        FROM tmp_TempVendorRate_  as tblTempVendorRate 
                    INNER JOIN tblRate
				             ON tblRate.Code = tblTempVendorRate.Code
				             AND tblRate.CompanyID = p_companyId
				             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						  WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
                        
					 	END IF;		
					 
            ELSE
                
                SELECT GROUP_CONCAT(code) into errormessage FROM(
                    SELECT DISTINCT
                        c.Code as code, 1 as a
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description
                        FROM tmp_TempVendorRate_  as tblTempVendorRate 
                      	  LEFT JOIN tblRate
				                ON tblRate.Code = tblTempVendorRate.Code
				          	      AND tblRate.CompanyID = p_companyId
				         	       AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
									WHERE tblRate.RateID IS NULL
	                  	      AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c) as tbl GROUP BY a;
                        
                  IF errormessage IS NOT NULL
                  THEN
                    INSERT INTO tmp_JobLog_ (Message)
                    		SELECT DISTINCT
                        CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
                        FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description
                        FROM tmp_TempVendorRate_  as tblTempVendorRate 
                        LEFT JOIN tblRate
			                ON tblRate.Code = tblTempVendorRate.Code
			                AND tblRate.CompanyID = p_companyId
			                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
								WHERE tblRate.RateID IS NULL
                        AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) as tbl;
                        
					 	END IF;		


            END IF;

            IF  p_replaceAllRates = 1
            THEN


                DELETE FROM tblVendorRate
                WHERE AccountId = p_accountId
                    AND TrunkID = p_trunkId;

            END IF;
            				
				
       
		 
		          
            	INSERT INTO tmp_Delete_VendorRate(
			 	VendorRateID,
		      AccountId,
		      TrunkID,
		      RateId,
		      Code,
		      Description,
		      Rate,
		      EffectiveDate,
				Interval1,
				IntervalN,
				ConnectionFee,
				deleted_at
			 )
    		SELECT tblVendorRate.VendorRateID,
    			    p_accountId AS AccountId,
					 p_trunkId AS TrunkID,
					 tblVendorRate.RateId,
					 tblRate.Code,
					 tblRate.Description,
					 tblVendorRate.Rate,
					 tblVendorRate.EffectiveDate,
					 tblVendorRate.Interval1,
					 tblVendorRate.IntervalN,
					 tblVendorRate.ConnectionFee,
					 now() AS deleted_at  		
                FROM tblVendorRate
                JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId
                    AND tblRate.CompanyID = p_companyId
                    JOIN tmp_TempVendorRate_ as tblTempVendorRate
                        ON tblRate.Code = tblTempVendorRate.Code
            WHERE tblVendorRate.AccountId = p_accountId
                AND tblVendorRate.TrunkId = p_trunkId
                AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block');    
           
           
			  	CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId); 

            UPDATE tblVendorRate
					INNER JOIN tblRate
						ON tblVendorRate.RateId = tblRate.RateId
						AND tblVendorRate.AccountId = p_accountId
						AND tblVendorRate.TrunkId = p_trunkId
					INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON tblRate.Code = tblTempVendorRate.Code
						AND tblRate.CompanyID = p_companyId
						AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						AND tblVendorRate.RateId = tblRate.RateId
					SET tblVendorRate.ConnectionFee = tblTempVendorRate.ConnectionFee,
						tblVendorRate.Interval1 = tblTempVendorRate.Interval1,
							tblVendorRate.IntervalN = tblTempVendorRate.IntervalN
					WHERE tblVendorRate.AccountId = p_accountId
			            AND tblVendorRate.TrunkId = p_trunkId ;
			    
				 
            IF  p_forbidden = 1 OR p_dialstringid > 0
				THEN
					
					INSERT INTO tblVendorBlocking
					(
						 `AccountId`
						 ,`RateId`
						 ,`TrunkID`
						 ,`BlockedBy`
					)
					SELECT distinct
					   p_accountId as AccountId,
					   tblRate.RateID as RateId,						
						p_trunkId as TrunkID,
						'RMService' as BlockedBy
					 FROM tmp_TempVendorRate_ as tblTempVendorRate
					 INNER JOIN tblRate 
						ON tblRate.Code = tblTempVendorRate.Code
						AND tblRate.CompanyID = p_companyId
			         AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
			       LEFT JOIN tblVendorBlocking vb 			       		
					 	ON vb.AccountId=p_accountId
						 AND vb.RateId = tblRate.RateID
						 AND vb.TrunkID = p_trunkId   
					WHERE tblTempVendorRate.Forbidden IN('B')
					 AND vb.VendorBlockingId is null;
					 
					 DELETE tblVendorBlocking 
					 FROM tblVendorBlocking 
					INNER JOIN(
						select VendorBlockingId 
						FROM `tblVendorBlocking` tv
							INNER JOIN(
							 SELECT 
							 	tblRate.RateId as RateId
							 FROM tmp_TempVendorRate_ as tblTempVendorRate
							INNER JOIN tblRate 
								ON tblRate.Code = tblTempVendorRate.Code
								AND tblRate.CompanyID = p_companyId
					         AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							WHERE tblTempVendorRate.Forbidden IN('UB')
					     )tv1 on  tv.AccountId=p_accountId
						  	AND tv.TrunkID=p_trunkId
						  	AND tv.RateId = tv1.RateID
					 )vb2 on vb2.VendorBlockingId = tblVendorBlocking.VendorBlockingId;
	
				END IF;
				
				
				
				IF  p_preference = 1
				THEN
				
				INSERT INTO tblVendorPreference
					(
						 `AccountId`
						 ,`Preference`
						 ,`RateId`
						 ,`TrunkID`
						 ,`CreatedBy`
						 ,`created_at`
					)
				SELECT 
					   p_accountId AS AccountId,
					   tblTempVendorRate.Preference as Preference,
					   tblRate.RateID AS RateId,						
						p_trunkId AS TrunkID,
						'RMService' AS CreatedBy,
						NOW() AS created_at
					 FROM tmp_TempVendorRate_ as tblTempVendorRate
					INNER JOIN tblRate 
						ON tblRate.Code = tblTempVendorRate.Code
						AND tblRate.CompanyID = p_companyId
			         AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
					LEFT JOIN tblVendorPreference vp 
						ON vp.RateId=tblRate.RateID
						AND vp.AccountId = p_accountId 	
						AND vp.TrunkID = p_trunkId
					WHERE  tblTempVendorRate.Preference IS NOT NULL
					 AND  tblTempVendorRate.Preference > 0
					 AND  vp.VendorPreferenceID IS NULL;
					 
					 
					 UPDATE tblVendorPreference
					 	INNER JOIN tblRate 
					 		ON tblVendorPreference.RateId=tblRate.RateID
				      INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
							ON tblTempVendorRate.Code = tblRate.Code							
				         AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId   
				         AND tblRate.CompanyID = p_companyId
				      SET tblVendorPreference.Preference = tblTempVendorRate.Preference
						WHERE tblVendorPreference.AccountId = p_accountId  
							AND tblVendorPreference.TrunkID = p_trunkId
							AND  tblTempVendorRate.Preference IS NOT NULL
							AND  tblTempVendorRate.Preference > 0
							AND tblVendorPreference.VendorPreferenceID IS NOT NULL; 
							
						DELETE tblVendorPreference
							from	tblVendorPreference
					 	INNER JOIN tblRate 
					 		ON tblVendorPreference.RateId=tblRate.RateID
				      INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
							ON tblTempVendorRate.Code = tblRate.Code							
				         AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId   
				         AND tblRate.CompanyID = p_companyId
						WHERE tblVendorPreference.AccountId = p_accountId  
							AND tblVendorPreference.TrunkID = p_trunkId
							AND  tblTempVendorRate.Preference IS NOT NULL
							AND  tblTempVendorRate.Preference = '' 
							AND tblVendorPreference.VendorPreferenceID IS NOT NULL; 
					 
				END IF; 
				         

            DELETE tblTempVendorRate
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    JOIN tblVendorRate
                        ON tblVendorRate.RateId = tblRate.RateId
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        AND tblVendorRate.AccountId = p_accountId
                        AND tblVendorRate.TrunkId = p_trunkId
                        AND tblTempVendorRate.Rate = tblVendorRate.Rate
                        AND (
                        tblVendorRate.EffectiveDate = tblTempVendorRate.EffectiveDate 
                        OR  
                        (
                        DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempVendorRate.EffectiveDate > NOW() THEN 1 
                            ELSE 0
                        END)
                        )
            WHERE  tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block'); 

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

				
            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                JOIN tblVendorRate
                    ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				SET tblVendorRate.Rate = tblTempVendorRate.Rate
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
            AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
            AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');



            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

				
            INSERT INTO tblVendorRate (AccountId,
            TrunkID,
            RateId,
            Rate,
            EffectiveDate,
            ConnectionFee,
            Interval1,
            IntervalN
            )
                SELECT DISTINCT
                    p_accountId,
                    p_trunkId,
                    tblRate.RateID,
                    tblTempVendorRate.Rate,
                    tblTempVendorRate.EffectiveDate,
                    tblTempVendorRate.ConnectionFee,
                    tblTempVendorRate.Interval1,
                    tblTempVendorRate.IntervalN
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                LEFT JOIN tblVendorRate
					      ON tblRate.RateID = tblVendorRate.RateId
					      AND tblVendorRate.AccountId = p_accountId
					      AND tblVendorRate.trunkid = p_trunkId
					      AND tblTempVendorRate.EffectiveDate = tblVendorRate.EffectiveDate
					WHERE tblVendorRate.VendorRateID IS NULL
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS(); 

	 
 END IF;	 
	 
	 					 INSERT INTO tmp_JobLog_ (Message)
	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );
	 
 	 SELECT * FROM tmp_JobLog_;
    -- DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId; 
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END