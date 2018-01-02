use Ratemanagement3;





ALTER TABLE `tblTempAccountIP`
	ADD COLUMN `CompanyGatewayID` INT(11) NULL DEFAULT NULL AFTER `Type`,
	ADD COLUMN `i_account` INT(11) NULL DEFAULT NULL AFTER `CompanyGatewayID`,
	ADD COLUMN `i_vendor` INT(11) NULL DEFAULT NULL AFTER `i_account`,
	ADD INDEX `IX_i_account` (`i_account`),
	ADD INDEX `IX_i_vendor` (`i_vendor`),
	ADD INDEX `IX_AccountName` (`AccountName`);

	
CREATE TABLE IF NOT EXISTS `tblTempIPAccountSippy` (
	`TempIPAccountSippyID` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`i_account` int(11) NOT NULL DEFAULT '0',
	`username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`ProcessID` varchar(255) COLLATE utf8_unicode_ci,
	PRIMARY KEY (`TempIPAccountSippyID`),
	INDEX `IX_i_account` (`i_account`),
	INDEX `IX_username` (`username`),
	INDEX `IX_ProcessID` (`ProcessID`),
);

ALTER TABLE `tblRateSheet`
	ALTER `Level` DROP DEFAULT;
ALTER TABLE `tblRateSheet`
	CHANGE COLUMN `Level` `Level` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci' AFTER `FileName`;










DROP PROCEDURE IF EXISTS `prc_getMissingAccountsIPByGateway`;
DELIMITER //
CREATE PROCEDURE `prc_getMissingAccountsIPByGateway`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` VARCHAR(250),
	IN `p_Type` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT
)
BEGIN
	DECLARE v_OffSet_ INT;

	SET sql_mode = '';
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET SESSION sql_mode='';

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_Export = 0
	THEN

		SELECT DISTINCT
			tai.tblTempAccountIPID,
			tai.AccountName AS AccountName,
			tai.IP AS IP,
			IFNULL(a.AccountID,IFNULL(tas.AccountID,'')) AS SelectedAccountID
		FROM 
			tblTempAccountIP tai
		LEFT JOIN
			tblAccount a ON tai.AccountName=a.AccountName
		LEFT JOIN
			tblAccountSippy tas 
		ON 
			tai.AccountName=tas.username AND 
			(
				(p_Type=1 AND tas.i_account=tai.i_account) OR 
				(p_Type=2 AND tas.i_vendor=tai.i_vendor)
			)
		WHERE tai.CompanyID =p_CompanyID
			AND tai.CompanyGatewayID = p_CompanyGatewayID
			AND tai.ProcessID = p_ProcessID
			AND (
				(p_Type=0) OR 
				(p_Type=1 AND tai.Type='Customer') OR 
				(p_Type=2 AND tai.Type='Vendor')
			)
		ORDER BY
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tai.AccountName
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tai.AccountName
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPDESC') THEN tai.IP
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPASC') THEN tai.IP
		END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(tai.tblTempAccountIPID) AS totalcount
		FROM tblTempAccountIP tai
		WHERE tai.CompanyID =p_CompanyID
			AND tai.CompanyGatewayID = p_CompanyGatewayID
			AND tai.ProcessID = p_ProcessID
			AND (
				(p_Type=0) OR 
				(p_Type=1 AND tai.Type='Customer') OR 
				(p_Type=2 AND tai.Type='Vendor')
			);

	ELSE

		SELECT DISTINCT
			tai.AccountName AS AccountName,
			tai.IP AS IP
		FROM tblTempAccount tai
		WHERE tai.CompanyID =p_CompanyID
			AND tai.CompanyGatewayID = p_CompanyGatewayID
			AND tai.ProcessID = p_ProcessID
			AND (
				(p_Type=0) OR 
				(p_Type=1 AND tai.Type='Customer') OR 
				(p_Type=2 AND tai.Type='Vendor')
			);

	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


















DROP PROCEDURE IF EXISTS `prc_WSProcessImportAccountIP`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessImportAccountIP`(
	IN `p_processId` VARCHAR(200),
	IN `p_companyId` INT
)
BEGIN
    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_accounttype INT DEFAULT 0;
	DECLARE i INT;

	SET sql_mode = '';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET SESSION sql_mode='';

	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  (
        Message longtext
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_AccountAuthenticate_;
    CREATE TEMPORARY TABLE tmp_AccountAuthenticate_  (
        CompanyID INT,
        AccountID INT,
        IsCustomerOrVendor VARCHAR(20),
        IP TEXT	
    );
	
	SET i = 1;
	REPEAT
		INSERT INTO tmp_AccountAuthenticate_ (CompanyID, AccountID, IsCustomerOrVendor, IP)
		SELECT CompanyID, AccountID, 'Customer', FnStringSplit(CustomerAuthValue, ',', i)  FROM tblAccountAuthenticate
			WHERE CustomerAuthRule='IP' AND FnStringSplit(CustomerAuthValue, ',' , i) IS NOT NULL;
	SET i = i + 1;
	UNTIL ROW_COUNT() = 0
	END REPEAT;
	  
	SET i = 1;
	REPEAT
		INSERT INTO tmp_AccountAuthenticate_ (CompanyID, AccountID, IsCustomerOrVendor, IP)
		SELECT CompanyID, AccountID, 'Vendor', FnStringSplit(VendorAuthValue, ',', i)  FROM tblAccountAuthenticate
			WHERE VendorAuthRule='IP' AND FnStringSplit(VendorAuthValue, ',' , i) IS NOT NULL;
	SET i = i + 1;
	UNTIL ROW_COUNT() = 0
	END REPEAT;
    

	-- delete all ips which is duplicate (IP,AccountName,CompanyID,Type,ProcessID)
	DELETE FROM 
		tblTempAccountIP
	WHERE
		tblTempAccountIPID 
	IN(
		SELECT tblTempAccountIPID FROM (
			SELECT
				n1.tblTempAccountIPID
			FROM
				tblTempAccountIP n1, 
				tblTempAccountIP n2 
			WHERE 
				n1.tblTempAccountIPID > n2.tblTempAccountIPID AND
				n1.IP = n2.IP AND
				n1.CompanyID = n2.CompanyID AND 
				n1.AccountName = n2.AccountName AND 
				n1.`Type` = n2.`Type` AND
				n1.ProcessID = n2.ProcessID AND
				n1.ProcessID = p_processId AND
				n2.ProcessID = p_processId
			GROUP BY 
				n1.tblTempAccountIPID
			ORDER BY
				n1.IP
		) t
	);

	-- Log and delete all ips which is duplicate in different account (IP,AccountName!=,CompanyID,ProcessID)
	INSERT INTO tmp_JobLog_ (Message)
		SELECT
			CONCAT(n1.IP, ' Already Exist Against Account \n\r ' )
		FROM
			tblTempAccountIP n1,
			tblTempAccountIP n2
		WHERE 
			n1.tblTempAccountIPID > n2.tblTempAccountIPID AND
			n1.IP = n2.IP AND
			n1.CompanyID = n2.CompanyID AND 
			n1.AccountName != n2.AccountName AND 
			n1.ProcessID = n2.ProcessID AND
			n1.ProcessID = p_processId AND
			n2.ProcessID = p_processId
		GROUP BY 
			n1.tblTempAccountIPID
		ORDER BY
			n1.IP;
			
	DELETE FROM 
		tblTempAccountIP
	WHERE
		tblTempAccountIPID 
		IN(
			SELECT tblTempAccountIPID FROM (
				SELECT
					n1.tblTempAccountIPID
				FROM
					tblTempAccountIP n1,
					tblTempAccountIP n2
				WHERE 
					n1.tblTempAccountIPID > n2.tblTempAccountIPID AND
					n1.IP = n2.IP AND
					n1.CompanyID = n2.CompanyID AND 
					n1.AccountName != n2.AccountName AND
					n1.ProcessID = n2.ProcessID AND
					n1.ProcessID = p_processId AND
					n2.ProcessID = p_processId
				GROUP BY 
					n1.tblTempAccountIPID
				ORDER BY
					n1.IP
			) t
		);
		

		DELETE tblTempAccountIP
			FROM tblTempAccountIP
			INNER JOIN(
				SELECT 
					ta.tblTempAccountIPID
				FROM 
					tblTempAccountIP ta 
				LEFT JOIN 
					tblAccount a ON a.AccountName = ta.AccountName
				LEFT JOIN 
					tmp_AccountAuthenticate_ aa 
				ON
					(aa.IP=ta.IP AND a.AccountID != aa.AccountID) 
					OR
					(aa.IP=ta.IP AND ta.Type = aa.IsCustomerOrVendor) 
				WHERE ta.ProcessID = p_processId AND aa.CompanyID = p_companyId
			) aold ON aold.tblTempAccountIPID = tblTempAccountIP.tblTempAccountIPID;


 		DROP TEMPORARY TABLE IF EXISTS tmp_accountipimport;
		CREATE TEMPORARY TABLE tmp_accountipimport (
						  `CompanyID` INT,
						  `AccountID` INT,
						  `AccountName` VARCHAR(100),
						  `IP` LONGTEXT,
						  `Type` VARCHAR(50),
						  `ProcessID` VARCHAR(50),
						  `ServiceID` INT,
						  `created_at` DATETIME,
						  `created_by` VARCHAR(50)
		) ENGINE=InnoDB;

		INSERT INTO tmp_accountipimport(`CompanyID`,`AccountName`,`IP`,`Type`,`ProcessID`,`ServiceID`,`created_at`,`created_by`)
		select CompanyID,AccountName,IP,Type,ProcessID,ServiceID,created_at,created_by FROM tblTempAccountIP WHERE ProcessID = p_processId;
		
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		UPDATE tmp_accountipimport ta LEFT JOIN tblAccount a ON ta.AccountName=a.AccountName
				SET ta.AccountID = a.AccountID
		WHERE a.AccountID IS NOT NULL AND a.AccountType=1 AND a.CompanyId=p_companyId;

		DROP TEMPORARY TABLE IF EXISTS tmp_accountcustomerip;
			CREATE TEMPORARY TABLE tmp_accountcustomerip (
							  `CompanyID` INT,
							  `AccountID` INT,
							  `CustomerAuthRule` VARCHAR(50),
							  `CustomerAuthValue` VARCHAR(8000),
							  `ServiceID` INT
			) ENGINE=InnoDB;

		DROP TEMPORARY TABLE IF EXISTS tmp_accountvendorip;
			CREATE TEMPORARY TABLE tmp_accountvendorip (
							  `CompanyID` INT,
							  `AccountID` INT,
							  `VendorAuthRule` VARCHAR(50),
							  `VendorAuthValue` VARCHAR(500),
							  `ServiceID` INT
			) ENGINE=InnoDB;
		INSERT INTO tmp_accountcustomerip(CompanyID,AccountID,CustomerAuthRule,CustomerAuthValue,ServiceID)
		select CompanyID,AccountID,'IP' as CustomerAuthRule, GROUP_CONCAT(IP) as CustomerAuthValue,ServiceID from tmp_accountipimport where Type='Customer' GROUP BY AccountID,ServiceID;

		INSERT INTO tmp_accountvendorip(CompanyID,AccountID,VendorAuthRule,VendorAuthValue,ServiceID)
		select CompanyID,AccountID,'IP' as VendorAuthRule, GROUP_CONCAT(IP) as VendorAuthValue,ServiceID from tmp_accountipimport where Type='Vendor' GROUP BY AccountID,ServiceID;

		

		INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,CustomerAuthRule,CustomerAuthValue,ServiceID)
			SELECT ac.CompanyID,ac.AccountID,ac.CustomerAuthRule,'',ac.ServiceID
				FROM tmp_accountcustomerip ac LEFT JOIN tblAccountAuthenticate aa
					ON ac.AccountID=aa.AccountID AND ac.ServiceID=aa.ServiceID
			 WHERE aa.AccountID IS NULL;
			
			INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,VendorAuthRule,VendorAuthValue,ServiceID)
			SELECT av.CompanyID,av.AccountID,av.VendorAuthRule,'',av.ServiceID
				FROM tmp_accountvendorip av LEFT JOIN tblAccountAuthenticate aa
					ON av.AccountID=aa.AccountID AND av.ServiceID=aa.ServiceID
			WHERE aa.AccountID IS NULL;

		UPDATE tmp_accountcustomerip ac LEFT JOIN tblAccountAuthenticate aa ON ac.AccountID=aa.AccountID AND ac.ServiceID=aa.ServiceID
				SET	aa.CustomerAuthRule='IP',aa.CustomerAuthValue =
					CASE WHEN((aa.CustomerAuthValue IS NULL) OR (aa.CustomerAuthValue='') OR (aa.CustomerAuthRule!='IP'))
								THEN
									  ac.CustomerAuthValue
								ELSE
									  CONCAT(aa.CustomerAuthValue,',',ac.CustomerAuthValue)
								END
			WHERE ac.AccountID IS NOT NULL AND aa.AccountID IS NOT NULL;

			UPDATE tmp_accountvendorip av LEFT JOIN tblAccountAuthenticate aa ON av.AccountID=aa.AccountID AND av.ServiceID=aa.ServiceID
				SET aa.VendorAuthRule='IP',aa.VendorAuthValue =
					CASE WHEN (aa.VendorAuthValue IS NULL) OR (aa.VendorAuthValue='') OR (aa.VendorAuthRule!='IP')
								THEN
									 av.VendorAuthValue
								ELSE
									CONCAT(aa.VendorAuthValue,',',av.VendorAuthValue)
								END
			 WHERE av.AccountID IS NOT NULL AND aa.AccountID IS NOT NULL;


			INSERT INTO tmp_JobLog_ (Message)
			SELECT CONCAT(v_AffectedRecords_, ' IPs Uploaded \n\r ' );

			DELETE  FROM tblTempAccountIP WHERE ProcessID = p_processId;

		SELECT * from tmp_JobLog_;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER;















DROP PROCEDURE IF EXISTS `prc_WSGenerateRateSheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateSheet`(
	IN `p_CustomerID` INT,
	IN `p_Trunk` VARCHAR(100)
)
BEGIN
    DECLARE v_trunkDescription_ VARCHAR(50);
    DECLARE v_lastRateSheetID_ INT ;
    DECLARE v_IncludePrefix_ TINYINT;
    DECLARE v_Prefix_ VARCHAR(50);
    DECLARE v_codedeckid_  INT;
    DECLARE v_ratetableid_ INT;
    DECLARE v_RateTableAssignDate_  DATETIME;
    DECLARE v_NewA2ZAssign_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


       SELECT trunk INTO v_trunkDescription_
         FROM   tblTrunk
         WHERE  TrunkID = p_Trunk;

    DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate_;
    CREATE TEMPORARY TABLE tmp_RateSheetRate_(
        RateID        INT,
        Destination   VARCHAR(200),
        Codes         VARCHAR(50),
        Interval1     INT,
        IntervalN     INT,
        Rate          DECIMAL(18, 6),
        `level`         VARCHAR(50),
        `change`        VARCHAR(50),
        EffectiveDate  DATE,
			  INDEX tmp_RateSheetRate_RateID (`RateID`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        INDEX tmp_CustomerRates_RateId (`RateID`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        updated_at DATETIME,
        INDEX tmp_RateTableRate_RateId (`RateID`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail_;
    CREATE TEMPORARY TABLE tmp_RateSheetDetail_ (
        ratesheetdetailsid int,
        RateID int ,
        RateSheetID int,
        Destination varchar(200),
        Code varchar(50),
        Rate DECIMAL(18, 6),
        `change` varchar(50),
        EffectiveDate Date,
			  INDEX tmp_RateSheetDetail_RateId (`RateID`,`RateSheetID`)
    );
    SELECT RateSheetID INTO v_lastRateSheetID_
    FROM   tblRateSheet
    WHERE  CustomerID = p_CustomerID
        AND level = v_trunkDescription_
    ORDER  BY RateSheetID DESC LIMIT 1 ;

    SELECT includeprefix INTO v_IncludePrefix_
    FROM   tblCustomerTrunk
    WHERE  AccountID = p_CustomerID
      AND TrunkID = p_Trunk;

    SELECT prefix INTO v_Prefix_
    FROM   tblCustomerTrunk
    WHERE  AccountID = p_CustomerID
      AND TrunkID = p_Trunk;


    SELECT
        CodeDeckId,
        RateTableID,
        RateTableAssignDate
        INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
    FROM tblCustomerTrunk
    WHERE tblCustomerTrunk.TrunkID = p_Trunk
    AND tblCustomerTrunk.AccountID = p_CustomerID
    AND tblCustomerTrunk.Status = 1;

  INSERT INTO tmp_CustomerRates_
      SELECT  RateID,
              Interval1,
              IntervalN,
              tblCustomerRate.Rate,
              effectivedate,
              lastmodifieddate
      FROM   tblAccount
         JOIN tblCustomerRate
           ON tblAccount.AccountID = tblCustomerRate.CustomerID
      WHERE  tblAccount.AccountID = p_CustomerID

         AND tblCustomerRate.TrunkID = p_Trunk
         ORDER BY tblCustomerRate.CustomerID,tblCustomerRate.TrunkID,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;

	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates4_;
	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates2_;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);
   DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);

	INSERT INTO tmp_RateTableRate_
		SELECT
			tblRateTableRate.RateID,
         tblRateTableRate.Interval1,
         tblRateTableRate.IntervalN,
         tblRateTableRate.Rate,
         tblRateTableRate.EffectiveDate,
         tblRateTableRate.updated_at
      FROM tblAccount
      JOIN tblCustomerTrunk
           ON tblCustomerTrunk.AccountID = tblAccount.AccountID
      JOIN tblRateTable
           ON tblCustomerTrunk.ratetableid = tblRateTable.ratetableid
      JOIN tblRateTableRate
           ON tblRateTableRate.ratetableid = tblRateTable.ratetableid
      LEFT JOIN tmp_CustomerRates_ trc1
            ON trc1.RateID = tblRateTableRate.RateID
      WHERE  tblAccount.AccountID = p_CustomerID

         AND tblCustomerTrunk.TrunkID = p_Trunk
         AND (( tblRateTableRate.EffectiveDate <= Now()
             AND ( ( trc1.RateID IS NULL )
                OR ( trc1.RateID IS NOT NULL
                   AND tblRateTableRate.ratetablerateid IS NULL )
              ) )
          OR ( tblRateTableRate.EffectiveDate > Now()
             AND ( ( trc1.RateID IS NULL )
                OR ( trc1.RateID IS NOT NULL
                   AND tblRateTableRate.EffectiveDate < (SELECT
									   IFNULL(MIN(crr.EffectiveDate),
									   tblRateTableRate.EffectiveDate)
										  FROM   tmp_CustomerRates2_ crr
										  WHERE  crr.RateID =
					   tblRateTableRate.RateID
                      ) ) ) ) )
      ORDER BY tblRateTableRate.RateID,tblRateTableRate.EffectiveDate desc;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate4_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
   DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

    INSERt INTO tmp_RateSheetDetail_
      SELECT ratesheetdetailsid,
                     RateID,
                     RateSheetID,
                     Destination,
                     Code,
                     Rate,
                     `change`,
                     effectivedate
              FROM   tblRateSheetDetails
              WHERE  RateSheetID = v_lastRateSheetID_
          ORDER BY RateID,effectivedate desc;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail4_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetDetail4_ as (select * from tmp_RateSheetDetail_);
   DELETE n1 FROM tmp_RateSheetDetail_ n1, tmp_RateSheetDetail4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;


      DROP TABLE IF EXISTS tmp_CloneRateSheetDetail_ ;
      CREATE TEMPORARY TABLE tmp_CloneRateSheetDetail_ LIKE tmp_RateSheetDetail_;
      INSERT tmp_CloneRateSheetDetail_ SELECT * FROM tmp_RateSheetDetail_;


      INSERT INTO tmp_RateSheetRate_
      SELECT tbl.RateID,
             description,
             Code,
             tbl.Interval1,
             tbl.IntervalN,
             tbl.Rate,
             v_trunkDescription_,
             'NEW' as `change`,
             tbl.EffectiveDate
      FROM   (
			SELECT
				rt.RateID,
				rt.Interval1,
				rt.IntervalN,
				rt.Rate,
				rt.EffectiveDate
         FROM   tmp_RateTableRate_ rt
         LEFT JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
            	v_lastRateSheetID_
         LEFT JOIN tmp_RateSheetDetail_ as  rsd
         	ON rsd.RateID = rt.RateID AND rsd.RateSheetID = v_lastRateSheetID_
			WHERE  rsd.ratesheetdetailsid IS NULL

			UNION

			SELECT
				trc2.RateID,
            trc2.Interval1,
            trc2.IntervalN,
            trc2.Rate,
            trc2.EffectiveDate
         FROM   tmp_CustomerRates_ trc2
         LEFT JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
         		v_lastRateSheetID_
         LEFT JOIN tmp_CloneRateSheetDetail_ as  rsd2
         	ON rsd2.RateID = trc2.RateID AND rsd2.RateSheetID = v_lastRateSheetID_
			WHERE  rsd2.ratesheetdetailsid IS NULL

			) AS tbl
      INNER JOIN tblRate
      	ON tbl.RateID = tblRate.RateID;

      INSERT INTO tmp_RateSheetRate_
      SELECT tbl.RateID,
             description,
             Code,
             tbl.Interval1,
             tbl.IntervalN,
             tbl.Rate,
             v_trunkDescription_,
             tbl.`change`,
             tbl.EffectiveDate
      FROM   (SELECT rt.RateID,
                     description,
                     tblRate.Code,
                     rt.Interval1,
                     rt.IntervalN,
                     rt.Rate,
                     rsd5.Rate AS rate2,
                     rt.EffectiveDate,
                     CASE
                                WHEN rsd5.Rate > rt.Rate
                                     AND rsd5.Destination !=
                                         description THEN
                                'NAME CHANGE & DECREASE'
                                ELSE
                                  CASE
                                    WHEN rsd5.Rate > rt.Rate
                                         AND rt.EffectiveDate <= Now() THEN
                                    'DECREASE'
                                    ELSE
                                      CASE
                                        WHEN ( rsd5.Rate >
                                               rt.Rate
                                               AND rt.EffectiveDate > Now()
                                             )
                                      THEN
                                        'PENDING DECREASE'
                                        ELSE
                                          CASE
                                            WHEN ( rsd5.Rate <
                                                   rt.Rate
                                                   AND rt.EffectiveDate <=
                                                       Now() )
                                          THEN
                                            'INCREASE'
                                            ELSE
                                              CASE
                                                WHEN ( rsd5.Rate
                                                       <
                                                       rt.Rate
                                                       AND rt.EffectiveDate >
                                                           Now() )
                                              THEN
                                                'PENDING INCREASE'
                                                ELSE
                                                  CASE
                                                    WHEN
                                              rsd5.Destination !=
                                              description THEN
                                                    'NAME CHANGE'
                                                    ELSE 'NO CHANGE'
                                                  END
                                              END
                                          END
                                      END
                                  END
                              END as `Change`
              FROM   tblRate
                     INNER JOIN tmp_RateTableRate_ rt
                             ON rt.RateID = tblRate.RateID
                     INNER JOIN tblRateSheet
                             ON tblRateSheet.RateSheetID = v_lastRateSheetID_
                     INNER JOIN tmp_RateSheetDetail_ as  rsd5
                             ON rsd5.RateID = rt.RateID
                                AND rsd5.RateSheetID =
                                    v_lastRateSheetID_
              UNION
              SELECT trc4.RateID,
                     description,
                     tblRate.Code,
                     trc4.Interval1,
                     trc4.IntervalN,
                     trc4.Rate,
                     rsd6.Rate AS rate2,
                     trc4.EffectiveDate,
                     CASE
                                WHEN rsd6.Rate > trc4.Rate
                                     AND rsd6.Destination !=
                                         description THEN
                                'NAME CHANGE & DECREASE'
                                ELSE
                                  CASE
                                    WHEN rsd6.Rate > trc4.Rate
                                         AND trc4.EffectiveDate <= Now() THEN
                                    'DECREASE'
                                    ELSE
                                      CASE
                                        WHEN ( rsd6.Rate >
                                               trc4.Rate
                                               AND trc4.EffectiveDate > Now()
                                             )
                                      THEN
                                        'PENDING DECREASE'
                                        ELSE
                                          CASE
                                            WHEN ( rsd6.Rate <
                                                   trc4.Rate
                                                   AND trc4.EffectiveDate <=
                                                       Now() )
                                          THEN
                                            'INCREASE'
                                            ELSE
                                              CASE
                                                WHEN ( rsd6.Rate
                                                       <
                                                       trc4.Rate
                                                       AND trc4.EffectiveDate >
                                                           Now() )
                                              THEN
                                                'PENDING INCREASE'
                                                ELSE
                                                  CASE
                                                    WHEN
                                              rsd6.Destination !=
                                              description THEN
                                                    'NAME CHANGE'
                                                    ELSE 'NO CHANGE'
                                                  END
                                              END
                                          END
                                      END
                                  END
                              END as  `Change`
              FROM   tblRate
                     INNER JOIN tmp_CustomerRates_ trc4
                             ON trc4.RateID = tblRate.RateID
                     INNER JOIN tblRateSheet
                             ON tblRateSheet.RateSheetID = v_lastRateSheetID_
                     INNER JOIN tmp_CloneRateSheetDetail_ as rsd6
                             ON rsd6.RateID = trc4.RateID
                                AND rsd6.RateSheetID =
                                    v_lastRateSheetID_
				  ) AS tbl ;

      INSERT INTO tmp_RateSheetRate_
      SELECT tblRateSheetDetails.RateID,
             tblRateSheetDetails.Destination,
             tblRateSheetDetails.Code,
             tblRateSheetDetails.Interval1,
             tblRateSheetDetails.IntervalN,
             tblRateSheetDetails.Rate,
             v_trunkDescription_,
             'DELETE',
             tblRateSheetDetails.EffectiveDate
      FROM   tblRate
             INNER JOIN tblRateSheetDetails
                     ON tblRate.RateID = tblRateSheetDetails.RateID
                        AND tblRateSheetDetails.RateSheetID = v_lastRateSheetID_
             LEFT JOIN (SELECT DISTINCT RateID,
                                        effectivedate
                        FROM   tmp_RateTableRate_
                        UNION
                        SELECT DISTINCT RateID,
                                        effectivedate
                        FROM tmp_CustomerRates_) AS TBL
                    ON TBL.RateID = tblRateSheetDetails.RateID
      WHERE  `change` != 'DELETE'
             AND TBL.RateID IS NULL ;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate4_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetRate4_ as (select * from tmp_RateSheetRate_);
	DELETE n1 FROM tmp_RateSheetRate_ n1, tmp_RateSheetRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
	AND  n1.RateId = n2.RateId;

      IF v_IncludePrefix_ = 1
        THEN
            SELECT rsr.RateID AS rateid,
                 rsr.Interval1 AS interval1,
                 rsr.IntervalN AS intervaln,
                 rsr.Destination AS destination,
                 rsr.Codes AS codes,
                 v_Prefix_ AS `tech prefix`,
                 CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS `interval`,
                 FORMAT(rsr.Rate,6) AS `rate per minute (usd)`,
                 rsr.`level`,
                 rsr.`change`,
                 rsr.EffectiveDate AS `effective date`
            FROM   tmp_RateSheetRate_ rsr

            ORDER BY rsr.Destination,rsr.Codes desc;
      ELSE
            SELECT rsr.RateID AS rateid ,
                           rsr.Interval1 AS interval1,
                           rsr.IntervalN AS intervaln,
                           rsr.Destination AS destination,
                           rsr.Codes AS codes,
                           CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS  `interval`,
                           FORMAT(rsr.Rate, 6) AS `rate per minute (usd)`,
                           rsr.`level`,
                           rsr.`change`,
                           rsr.EffectiveDate AS `effective date`
            FROM   tmp_RateSheetRate_ rsr

          	ORDER BY rsr.Destination,rsr.Codes DESC;
        END IF;

        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_VendorBulkRateDelete`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_VendorBulkRateDelete`(
	IN `p_CompanyId` INT,
	IN `p_AccountId` INT,
	IN `p_TrunkId` INT ,
	IN `p_VendorRateIds` TEXT,
	IN `p_code` varchar(50),
	IN `p_description` varchar(200),
	IN `p_CountryId` INT,
	IN `p_effective` VARCHAR(50),
	IN `p_action` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

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
      EndDate DATETIME,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		deleted_at DATETIME,
        INDEX tmp_VendorRateDiscontinued_VendorRateID (`VendorRateID`)
	);

	 IF p_action = 1
	 THEN


	 INSERT INTO tmp_Delete_VendorRate(
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
		deleted_at
	 )
		SELECT
		  VendorRateID,
		  p_AccountId AS AccountId,
		  p_TrunkId AS TrunkID,
		  v.RateId,
		  r.Code,
		  r.Description,
		  Rate,
		  EffectiveDate,
		  EndDate,
		  v.Interval1,
		  v.IntervalN,
		  ConnectionFee,
		  now() AS deleted_at
		FROM tblVendorRate v
		  INNER JOIN tblRate r
		  		ON r.RateID = v.RateId
		  INNER JOIN tblVendorTrunk vt
		  		ON vt.trunkID = p_TrunkId
				   AND vt.AccountID = p_AccountId
					AND vt.CodeDeckId = r.CodeDeckId
		WHERE
			((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId))
			AND ((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%')))
			AND ((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%')))
		 	AND ((p_effective = 'Now' AND v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' AND v.EffectiveDate> NOW()) OR p_effective = 'All')
		 	AND v.AccountId = p_AccountId
			AND v.TrunkID = p_TrunkId;

	 END IF;

	IF p_action = 2
	THEN




		 INSERT INTO tmp_Delete_VendorRate(
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
			deleted_at
		 )
		SELECT
		  VendorRateID,
		  p_AccountId AS AccountId,
		  p_TrunkId AS TrunkID,
		  v.RateId,
		  r.Code,
		  r.Description,
		  Rate,
		  EffectiveDate,
		  EndDate,
		  v.Interval1,
		  v.IntervalN,
		  ConnectionFee,
		  now() AS deleted_at
		FROM tblVendorRate v
		  INNER JOIN tblRate r
		  		ON r.RateID = v.RateId
		WHERE v.AccountId = p_AccountId
				AND v.TrunkID = p_TrunkId
				AND ((p_VendorRateIds IS NULL) OR FIND_IN_SET(VendorRateID,p_VendorRateIds)!=0)
				;
	END IF;


	-- set end date will remove at bottom in archive proc
	UPDATE tblVendorRate
	JOIN tmp_Delete_VendorRate ON tblVendorRate.VendorRateID = tmp_Delete_VendorRate.VendorRateID
	SET tblVendorRate.EndDate = date(now())
	WHERE
	tblVendorRate.AccountId = p_accountId
	AND tblVendorRate.TrunkId = p_trunkId;


	-- archive rates which has EndDate <= today
	call prc_ArchiveOldVendorRate(p_accountId,p_trunkId);

	-- CALL prc_InsertDiscontinuedVendorRate(p_AccountId,p_TrunkId);


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;