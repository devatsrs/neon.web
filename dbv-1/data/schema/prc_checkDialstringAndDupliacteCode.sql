CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkDialstringAndDupliacteCode`(IN `p_companyId` INT, IN `p_processId` VARCHAR(200) , IN `p_dialStringId` INT, IN `p_effectiveImmediately` INT)
BEGIN
	
    DECLARE totaldialstringcode INT(11) DEFAULT 0;	 
    DECLARE     v_CodeDeckId_ INT ;
  	 DECLARE totalduplicatecode INT(11);	 
  	 DECLARE errormessage longtext;
	 DECLARE errorheader longtext;


DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_ ;			
	 CREATE TEMPORARY TABLE `tmp_VendorRateDialString_` (						
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
						`Forbidden` varchar(100) 
					);
		
		-- vendor code split with ';' and insert codes in tmp_split_VendorRate_
					
		CALL prc_SplitVendorRate(p_processId);					

		
		DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_VendorRate_2 as (SELECT * FROM tmp_split_VendorRate_);
                                             
	     DELETE n1 FROM tmp_split_VendorRate_ n1, tmp_split_VendorRate_2 n2 
     WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId AND n2.ProcessId = p_processId;

		  INSERT INTO tmp_TempVendorRate_
	        SELECT DISTINCT `CodeDeckId`,
			  						`Code`,
									`Description`,
									`Rate`,
									`EffectiveDate`,
									`Change`,
									`ProcessId`,
									`Preference`,
									`ConnectionFee`,
									`Interval1`,
									`IntervalN`,
									`Forbidden`
						 FROM tmp_split_VendorRate_
						 	 WHERE tmp_split_VendorRate_.ProcessId = p_processId;
			
		
	 	     SELECT CodeDeckId INTO v_CodeDeckId_ 
					FROM tmp_TempVendorRate_
						 WHERE ProcessId = p_processId  LIMIT 1;

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            LEFT JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                AND tblRate.CodeDeckId =  v_CodeDeckId_
            SET  
                tblTempVendorRate.Interval1 = CASE WHEN tblTempVendorRate.Interval1 is not null  and tblTempVendorRate.Interval1 > 0
                                            THEN    
                                                tblTempVendorRate.Interval1
                                            ELSE
                                            CASE WHEN tblRate.Interval1 is not null  
                                            THEN    
                                                tblRate.Interval1
                                            ELSE CASE WHEN tblTempVendorRate.Interval1 is null and (tblTempVendorRate.Description LIKE '%gambia%' OR tblTempVendorRate.Description LIKE '%mexico%')
                                                 THEN 
                                                    60
                                            ELSE CASE WHEN tblTempVendorRate.Description LIKE '%USA%' 
                                                 THEN 
                                                    6
                                                 ELSE 
                                                    1
                                                END
                                            END

                                            END
                                            END,
                tblTempVendorRate.IntervalN = CASE WHEN tblTempVendorRate.IntervalN is not null  and tblTempVendorRate.IntervalN > 0
                                            THEN    
                                                tblTempVendorRate.IntervalN
                                            ELSE
                                                CASE WHEN tblRate.IntervalN is not null 
                                          THEN
                                            tblRate.IntervalN
                                          ElSE
                                            CASE
                                                WHEN tblTempVendorRate.Description LIKE '%mexico%' THEN 60
                                            ELSE CASE
                                                WHEN tblTempVendorRate.Description LIKE '%USA%' THEN 6
                                                
                                            ELSE 
                                            1
                                            END
                                            END
                                          END
                                          END;

 			
			IF  p_effectiveImmediately = 1
            THEN
            
            	-- DELETE n1 FROM tmp_TempVendorRate_ n1, tmp_TempVendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate AND n1.Code = n2.Code;
            
                UPDATE tmp_TempVendorRate_
           	     SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
           		     WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

            END IF;
			
			-- check duplicate code record	
			SELECT count(*) INTO totalduplicatecode FROM(
				SELECT count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate HAVING c>1) AS tbl;
				
			-- for duplicate code record	
			IF  totalduplicatecode > 0
			THEN	
			
			
				SELECT GROUP_CONCAT(code) into errormessage FROM(
					SELECT DISTINCT code, 1 as a FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate HAVING c>1) AS tbl) as tbl2 GROUP by a;				
				
				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT 
				  CONCAT(code , ' DUPLICATE CODE')
				  	FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate HAVING c>1) AS tbl;				
					
			END IF;
							
   IF	totalduplicatecode = 0
   THEN
						
	 -- IF dial string mapping 
    IF p_dialstringid >0
    THEN
    		
    		DROP TEMPORARY TABLE IF EXISTS tmp_DialString_;
    		CREATE TEMPORARY TABLE tmp_DialString_ (
				`DialStringID` INT,
				`DialString` VARCHAR(250),
				`ChargeCode` VARCHAR(250),
				`Description` VARCHAR(250),
				`Forbidden` VARCHAR(50),
				INDEX tmp_DialStringID (`DialStringID`),			
	         INDEX tmp_DialStringID_ChargeCode (`DialStringID`,`ChargeCode`)
         );

         INSERT INTO tmp_DialString_ 
			SELECT DISTINCT
				`DialStringID`,
				`DialString`,
				`ChargeCode`,
				`Description`,
				`Forbidden`
			FROM tblDialStringCode
				WHERE DialStringID = p_dialstringid;
				
         -- mapping code with dialstring charcode
         SELECT  COUNT(*) as count INTO totaldialstringcode
			FROM tmp_TempVendorRate_ vr
				LEFT JOIN tmp_DialString_ ds
					ON vr.Code = ds.ChargeCode 
				WHERE vr.ProcessId = p_processId 
					AND ds.DialStringID IS NULL
					AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
		   
         IF totaldialstringcode > 0
         THEN
         
				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT CONCAT(Code , ' No PREFIX FOUND')
				  	FROM tmp_TempVendorRate_ vr
						LEFT JOIN tmp_DialString_ ds
							ON vr.Code = ds.ChargeCode
						WHERE vr.ProcessId = p_processId
							AND ds.DialStringID IS NULL				
							AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
         
         END IF;

         IF totaldialstringcode = 0
         THEN
				 
				 	INSERT INTO tmp_VendorRateDialString_ 	
					 SELECT DISTINCT
							`CodeDeckId`,
							`DialString`,
							CASE WHEN ds.Description IS NULL OR ds.Description = ''
								THEN    
									tblTempVendorRate.Description									
								ELSE
									ds.Description
							END
								AS Description,
							`Rate`,
							`EffectiveDate`,
							`Change`,
							`ProcessId`,
							`Preference`,
							`ConnectionFee`,
							`Interval1`,
							`IntervalN`,
							tblTempVendorRate.Forbidden as Forbidden 
					   FROM tmp_TempVendorRate_ as tblTempVendorRate
							INNER JOIN tmp_DialString_ ds
							  	ON tblTempVendorRate.Code = ds.ChargeCode
						 WHERE tblTempVendorRate.ProcessId = p_processId
							AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');							
											
					
					DELETE  FROM tmp_TempVendorRate_ WHERE  ProcessId = p_processId; 
					
					INSERT INTO tmp_TempVendorRate_(
							CodeDeckId,
							Code,
							Description,
							Rate,
							EffectiveDate,
							`Change`,
							ProcessId,
							Preference,
							ConnectionFee,
							Interval1,
							IntervalN,
							Forbidden
						)
					SELECT DISTINCT 
						`CodeDeckId`,
						`Code`,
						`Description`,
						`Rate`,
						`EffectiveDate`,
						`Change`,
						`ProcessId`,
						`Preference`,
						`ConnectionFee`,
						`Interval1`,
						`IntervalN`,
						`Forbidden` 
					 FROM tmp_VendorRateDialString_;
					   
				  	UPDATE tmp_TempVendorRate_ as tblTempVendorRate
					JOIN tmp_DialString_ ds
						ON tblTempVendorRate.Code = ds.DialString
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 1
					SET tblTempVendorRate.Forbidden = 'B';
					
					UPDATE tmp_TempVendorRate_ as  tblTempVendorRate
					JOIN tmp_DialString_ ds
						ON tblTempVendorRate.Code = ds.DialString
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 0
					SET tblTempVendorRate.Forbidden = 'UB';
					
			END IF;	  
        
    END IF;	-- dialstring over		
   
END IF; -- duplicate code over 
 	

END