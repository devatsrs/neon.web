use Ratemanagement3;

CREATE TABLE IF NOT EXISTS `tblAccountAuditExportLog` (
  `AccountAuditExportLogID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `Status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`AccountAuditExportLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `tblAccountAuditExportLog`
  ADD COLUMN `Type` VARCHAR(50) NULL DEFAULT NULL AFTER `CompanyGatewayID`;

  
ALTER TABLE `tblUser`
	ADD COLUMN `LastLoginDate` DATETIME NULL AFTER `JobNotification`;

ALTER TABLE `tblTempVendorRate`
	ADD COLUMN `CountryCode` VARCHAR(500) NULL AFTER `CodeDeckId`;

ALTER TABLE `tblTempVendorRate`
	CHANGE COLUMN `Code` `Code` TEXT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `CountryCode`;
	
INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 3, 'Rate Export To Vos', 'rateexporttovos', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2017-09-07 12:57:02', 'RateManagementSystem');
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (3, 'SSH Host', 'sshhost', 1, '2017-09-11 11:10:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (3, 'SSH Username', 'sshusername', 1, '2017-09-11 11:10:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (3, 'SSH Password', 'sshpassword', 1, '2017-09-11 11:10:00', 'RateManagementSystem', NULL, NULL);

UPDATE `tblCronJobCommand` SET `GatewayID`=NULL WHERE Command='vendorratefileexport' AND `GatewayID`=10;
UPDATE `tblCronJobCommand` SET `GatewayID`=NULL WHERE Command='customerratefileexport' AND `GatewayID`=10;

UPDATE `tblGlobalAdmin` SET `EmailAddress`='globaladmin@neon-soft.com' , `password`='$2y$10$2sqYQFuptjEC5sD2eIi.l.YhByUmd8AATV/Qly8AofkaJpddBAojq' WHERE `GlobalAdminID`=1;


DROP PROCEDURE IF EXISTS `prc_getAccountAuditExportLog`;
DELIMITER |
CREATE PROCEDURE `prc_getAccountAuditExportLog`(
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT
)
BEGIN
    DECLARE v_last_time_exist INT DEFAULT 0;
    DECLARE v_last_time DATETIME;
    DECLARE v_start_time DATETIME;
    DECLARE v_end_time DATETIME;
    DECLARE v_cur_date DATE DEFAULT CURDATE();
    DECLARE v_cur_time DATETIME DEFAULT NOW();

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SELECT end_time INTO v_last_time FROM tblAccountAuditExportLog WHERE CompanyID=p_CompanyID AND CompanyGatewayID=p_GatewayID AND Type='account' AND Status=1 ORDER BY AccountAuditExportLogID DESC LIMIT 1;

    IF (v_last_time IS NOT NULL AND v_last_time != '')
    THEN
      -- SELECT end_time INTO v_last_time FROM tblAccountAuditExportLog WHERE CompanyID=p_CompanyID AND CompanyGatewayID=p_GatewayID AND Status=1 ORDER BY AccountAuditExportLogID DESC LIMIT 1;

        SELECT
             MIN(tad.created_at), MAX(tad.created_at) INTO v_start_time, v_end_time
        FROM
            tblAuditHeader AS tah
        LEFT JOIN
            tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
        LEFT JOIN
            tblDynamicFieldsValue AS dfv ON dfv.ParentID=tah.ParentColumnID
        LEFT JOIN
            tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
        WHERE
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='account' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_cur_date) AND tad.created_at>v_last_time;

        IF ((v_start_time IS NOT NULL AND v_start_time != '') AND (v_end_time IS NOT NULL AND v_end_time != ''))
        THEN
            INSERT INTO tblAccountAuditExportLog (CompanyID,CompanyGatewayID,Type,start_time,end_time,created_at) VALUES (p_CompanyID, p_GatewayID, 'account', v_start_time, v_end_time, v_cur_time);
        END IF;

        SELECT
            tah.ParentColumnID,tah.ParentColumnName,tad.ColumnName,tad.OldValue,tad.NewValue,v_start_time AS start_time,v_end_time AS end_time,v_cur_time AS created_at
        FROM
            tblAuditHeader AS tah
        LEFT JOIN
            tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
        LEFT JOIN
            tblDynamicFieldsValue AS dfv ON dfv.ParentID=tah.ParentColumnID
        LEFT JOIN
            tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
        WHERE
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='account' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_cur_date) AND tad.created_at>v_last_time;

    ELSE
        SELECT
            MIN(tad.created_at), MAX(tad.created_at) INTO v_start_time, v_end_time
        FROM
            tblAuditHeader AS tah
        LEFT JOIN
            tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
        LEFT JOIN
            tblDynamicFieldsValue AS dfv ON dfv.ParentID=tah.ParentColumnID
        LEFT JOIN
            tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
        WHERE
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='account' AND tah.Date>=(v_cur_date - INTERVAL 1 DAY) AND tah.Date<=v_cur_date AND tad.created_at<=v_cur_time;

        IF ((v_start_time IS NOT NULL AND v_start_time != '') AND (v_end_time IS NOT NULL AND v_end_time != ''))
        THEN
            INSERT INTO tblAccountAuditExportLog (CompanyID,CompanyGatewayID,Type,start_time,end_time,created_at) VALUES (p_CompanyID, p_GatewayID, 'account', v_start_time, v_end_time, v_cur_time);
        END IF;

        SELECT
            tah.ParentColumnID,tah.ParentColumnName,tad.ColumnName,tad.OldValue,tad.NewValue,v_start_time AS start_time,v_end_time AS end_time,v_cur_time AS created_at
        FROM
            tblAuditHeader AS tah
        LEFT JOIN
            tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
        LEFT JOIN
            tblDynamicFieldsValue AS dfv ON dfv.ParentID=tah.ParentColumnID
        LEFT JOIN
            tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
        WHERE
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='account' AND tah.Date>=(v_cur_date - INTERVAL 1 DAY) AND tah.Date<=v_cur_date AND tad.created_at<=v_cur_time;
    END IF;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;







DROP PROCEDURE IF EXISTS `prc_getAccountIPAuditExportLog`;
DELIMITER |
CREATE PROCEDURE `prc_getAccountIPAuditExportLog`(
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT
)
BEGIN
	DECLARE v_last_time_exist INT DEFAULT 0;
	DECLARE v_last_time DATETIME;
	DECLARE v_start_time DATETIME;
	DECLARE v_end_time DATETIME;
	DECLARE v_cur_date DATE DEFAULT CURDATE();
	DECLARE v_cur_time DATETIME DEFAULT NOW();

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT end_time INTO v_last_time FROM tblAccountAuditExportLog WHERE CompanyID=p_CompanyID AND CompanyGatewayID=p_GatewayID AND Type='accountip' AND Status=1 ORDER BY AccountAuditExportLogID DESC LIMIT 1;

	IF (v_last_time IS NOT NULL AND v_last_time != '')
	THEN
		SELECT
			 MIN(tad.created_at), MAX(tad.created_at) INTO v_start_time, v_end_time
		FROM
			tblAuditHeader AS tah
		LEFT JOIN
			tblAccountAuthenticate AS aa ON tah.ParentColumnID=aa.AccountAuthenticateID
		LEFT JOIN
			tblAccount AS a ON aa.AccountID=a.AccountID
		LEFT JOIN
			tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
		LEFT JOIN
			tblDynamicFieldsValue AS dfv ON dfv.ParentID=a.AccountID
		LEFT JOIN
			tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
		WHERE
			df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_cur_date) AND tad.created_at>v_last_time;

		IF ((v_start_time IS NOT NULL AND v_start_time != '') AND (v_end_time IS NOT NULL AND v_end_time != ''))
     	THEN
			INSERT INTO tblAccountAuditExportLog (CompanyID,CompanyGatewayID,Type,start_time,end_time,created_at) VALUES (p_CompanyID, p_GatewayID, 'accountip', v_start_time, v_end_time, v_cur_time);
		END IF;

		SELECT
			tah.ParentColumnID,tah.ParentColumnName,tad.ColumnName,tad.OldValue,tad.NewValue,s.ServiceName,v_start_time AS start_time,v_end_time AS end_time,v_cur_time AS created_at
		FROM
			tblAuditHeader AS tah
		LEFT JOIN
			tblAccountAuthenticate AS aa ON tah.ParentColumnID=aa.AccountAuthenticateID
		LEFT JOIN
			tblAccount AS a ON aa.AccountID=a.AccountID
		LEFT JOIN
			tblService AS s ON s.ServiceID=aa.ServiceID
		LEFT JOIN
			tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
		LEFT JOIN
			tblDynamicFieldsValue AS dfv ON dfv.ParentID=a.AccountID
		LEFT JOIN
			tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
		WHERE
			df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_cur_date) AND tad.created_at>v_last_time;

	ELSE
		SELECT
	   	MIN(tad.created_at), MAX(tad.created_at) INTO v_start_time, v_end_time
		FROM
			tblAuditHeader AS tah
		LEFT JOIN
			tblAccountAuthenticate AS aa ON tah.ParentColumnID=aa.AccountAuthenticateID
		LEFT JOIN
			tblAccount AS a ON aa.AccountID=a.AccountID
		LEFT JOIN
			tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
		LEFT JOIN
			tblDynamicFieldsValue AS dfv ON dfv.ParentID=a.AccountID
		LEFT JOIN
			tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
		WHERE
			df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=(v_cur_date - INTERVAL 1 DAY) AND tah.Date<=v_cur_date AND tad.created_at<=v_cur_time;

		IF ((v_start_time IS NOT NULL AND v_start_time != '') AND (v_end_time IS NOT NULL AND v_end_time != ''))
    	THEN
			INSERT INTO tblAccountAuditExportLog (CompanyID,CompanyGatewayID,Type,start_time,end_time,created_at) VALUES (p_CompanyID, p_GatewayID, 'accountip', v_start_time, v_end_time, v_cur_time);
		END IF;

		SELECT
	   		tah.ParentColumnID,tah.ParentColumnName,tad.ColumnName,tad.OldValue,tad.NewValue,s.ServiceName,v_start_time AS start_time,v_end_time AS end_time,v_cur_time AS created_at
		FROM
			tblAuditHeader AS tah
		LEFT JOIN
			tblAccountAuthenticate AS aa ON tah.ParentColumnID=aa.AccountAuthenticateID
		LEFT JOIN
			tblAccount AS a ON aa.AccountID=a.AccountID
		LEFT JOIN
			tblService AS s ON s.ServiceID=aa.ServiceID
		LEFT JOIN
			tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
		LEFT JOIN
			tblDynamicFieldsValue AS dfv ON dfv.ParentID=a.AccountID
		LEFT JOIN
			tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
		WHERE
			df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=(v_cur_date - INTERVAL 1 DAY) AND tah.Date<=v_cur_date AND tad.created_at<=v_cur_time;
   	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_GetRateTableRate`;
DELIMITER |
CREATE PROCEDURE `prc_GetRateTableRate`(
    IN `p_companyid` INT,
    IN `p_RateTableId` INT,
    IN `p_trunkID` INT,
    IN `p_contryID` INT,
    IN `p_code` VARCHAR(50),
    IN `p_description` VARCHAR(50),
    IN `p_effective` VARCHAR(50),
    IN `p_view` INT,
    IN `p_PageNumber` INT,
    IN `p_RowspPage` INT,
    IN `p_lSortCol` VARCHAR(50),
    IN `p_SortOrder` VARCHAR(5),
    IN `p_isExport` INT
)
BEGIN
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET sql_mode = '';
    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        ID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
          ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RateTableRateID INT,
        RateID INT,
        INDEX tmp_RateTableRate_RateID (`RateID`)
    );
    
    
    
    INSERT INTO tmp_RateTableRate_
    SELECT
        RateTableRateID AS ID,
        Code,
        Description,
        ifnull(tblRateTableRate.Interval1,1) as Interval1,
        ifnull(tblRateTableRate.IntervalN,1) as IntervalN,
          tblRateTableRate.ConnectionFee,
        IFNULL(tblRateTableRate.Rate, 0) as Rate,
        IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
        tblRateTableRate.updated_at,
        tblRateTableRate.ModifiedBy,
        RateTableRateID,
        tblRate.RateID
    FROM tblRate
    LEFT JOIN tblRateTableRate
        ON tblRateTableRate.RateID = tblRate.RateID
        AND tblRateTableRate.RateTableId = p_RateTableId
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
    WHERE       (tblRate.CompanyID = p_companyid)
        AND (p_contryID = '' OR CountryID = p_contryID)
        AND (p_code = '' OR Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description = '' OR Description LIKE REPLACE(p_description, '*', '%'))
        AND TrunkID = p_trunkID
        AND (           
            p_effective = 'All' 
        OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
        OR (p_effective = 'Future' AND EffectiveDate > NOW())
            );
     
      IF p_effective = 'Now'
        THEN
           CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);          
         DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
           AND  n1.RateID = n2.RateID;
        END IF;
     
    IF p_isExport = 0
    THEN

        IF p_view = 1 -- normal view
        THEN
        SELECT * FROM tmp_RateTableRate_
                    ORDER BY CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
                END ASC,                
                    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;
            
            SELECT
            COUNT(RateID) AS totalcount
            FROM tmp_RateTableRate_;
        
        ELSE  -- group by Description view
            SELECT group_concat(ID) AS ID, group_concat(Code) AS Code,Description,Interval1,Intervaln,ConnectionFee,Rate,EffectiveDate,MAX(updated_at) AS updated_at,MAX(ModifiedBy) AS ModifiedBy,group_concat(ID) AS RateTableRateID,group_concat(RateID) AS RateID FROM tmp_RateTableRate_
                    GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate
                    ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
                END ASC,                
                    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;
            
            SELECT COUNT(*) AS `totalcount`
            FROM (
                SELECT
                Description
                FROM tmp_RateTableRate_
                    GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate
            ) totalcount;
            
        -- END IF p_view = 1
        END IF;
    
    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            Code,
            Description,
            Interval1,
            IntervalN,
            ConnectionFee,
            Rate,
            EffectiveDate,
            updated_at,
            ModifiedBy

        FROM   tmp_RateTableRate_;
         

    END IF;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END|
DELIMITER ;






DROP PROCEDURE IF EXISTS `prc_AddAccountIPCLI`;
DELIMITER |
CREATE PROCEDURE `prc_AddAccountIPCLI`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_CustomerVendorCheck` INT,
	IN `p_IPCLIString` LONGTEXT,
	IN `p_IPCLICheck` LONGTEXT,
	IN `p_ServiceID` INT
)
BEGIN

	DECLARE i int;
	DECLARE v_COUNTER int;
	DECLARE v_IPCLI LONGTEXT;
	DECLARE v_IPCLICheck VARCHAR(10);
	DECLARE v_Check int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS `AccountIPCLI`; 
		CREATE TEMPORARY TABLE `AccountIPCLI` (
		  `AccountName` varchar(45) NOT NULL,
		  `IPCLI` LONGTEXT NOT NULL
		);

	DROP TEMPORARY TABLE IF EXISTS `AccountIPCLITable1`; 
		CREATE TEMPORARY TABLE `AccountIPCLITable1` (
		  `splitted_column` varchar(45) NOT NULL
		);
		
	DROP TEMPORARY TABLE IF EXISTS `AccountIPCLITable2`; 
		CREATE TEMPORARY TABLE `AccountIPCLITable2` (
		  `splitted_column` varchar(45) NOT NULL
		);
	
		
	INSERT INTO AccountIPCLI
	SELECT acc.AccountName,
	CONCAT(
	IFNULL(((CASE WHEN CustomerAuthRule = p_IPCLICheck THEN accauth.CustomerAuthValue ELSE '' END)),''),',',
	IFNULL(((CASE WHEN VendorAuthRule = p_IPCLICheck THEN accauth.VendorAuthValue ELSE '' END)),'')) as Authvalue
	FROM tblAccountAuthenticate accauth
	INNER JOIN tblAccount acc ON acc.AccountID = accauth.AccountID
	AND accauth.CompanyID = p_CompanyID
	AND ((CustomerAuthRule = p_IPCLICheck) OR (VendorAuthRule = p_IPCLICheck))
	WHERE 
		((SELECT fnFIND_IN_SET(CONCAT(IFNULL(accauth.CustomerAuthValue,''),',',IFNULL(accauth.VendorAuthValue,'')),p_IPCLIString) WHERE accauth.AccountID != p_AccountID) > 0)
		OR
		CASE(p_CustomerVendorCheck)
			WHEN 1 THEN
				(SELECT fnFIND_IN_SET(IFNULL(accauth.CustomerAuthValue,''),p_IPCLIString) WHERE accauth.AccountID = p_AccountID) > 0
			WHEN 2 THEN
				(SELECT fnFIND_IN_SET(IFNULL(accauth.VendorAuthValue,''),p_IPCLIString) WHERE accauth.AccountID = p_AccountID) > 0
		END
	;
	
	SELECT COUNT(AccountName) INTO v_COUNTER FROM AccountIPCLI;
	
	
	IF v_COUNTER > 0 THEN
		SELECT *  FROM AccountIPCLI;
		
		
		SET i = 1;
		REPEAT
			INSERT INTO AccountIPCLITable1
			SELECT FnStringSplit(p_IPCLIString, ',', i) WHERE FnStringSplit(p_IPCLIString, ',', i) IS NOT NULL LIMIT 1;
			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;
		
		
		INSERT INTO AccountIPCLITable2
		SELECT AccountIPCLITable1.splitted_column FROM AccountIPCLI,AccountIPCLITable1
		WHERE FIND_IN_SET(AccountIPCLITable1.splitted_column,AccountIPCLI.IPCLI)>0
		GROUP BY AccountIPCLITable1.splitted_column;
		
		
		DELETE t1 FROM AccountIPCLITable1 t1
		INNER JOIN AccountIPCLITable2 t2 ON t1.splitted_column = t2.splitted_column
		WHERE t1.splitted_column=t2.splitted_column;
		
		SELECT GROUP_CONCAT(t.splitted_column separator ',') INTO p_IPCLIString FROM AccountIPCLITable1 t;
		
		
		DELETE t1,t2 FROM AccountIPCLITable1 t1,AccountIPCLITable2 t2;
	END IF;
	
	
	SELECT 
	accauth.AccountAuthenticateID, 
	(CASE WHEN p_CustomerVendorCheck = 1 THEN accauth.CustomerAuthValue ELSE accauth.VendorAuthValue END) as AuthValue,
	(CASE WHEN p_CustomerVendorCheck = 1 THEN accauth.CustomerAuthRule ELSE accauth.VendorAuthRule END) as AuthRule INTO v_Check,v_IPCLI,v_IPCLICheck
	FROM tblAccountAuthenticate accauth 
	WHERE accauth.CompanyID =  p_CompanyID
	AND accauth.ServiceID = p_ServiceID
	AND accauth.AccountID = p_AccountID;
			
	IF v_Check > 0 && p_IPCLIString IS NOT NULL && p_IPCLIString!='' THEN
		IF v_IPCLICheck != p_IPCLICheck THEN
		
			IF p_CustomerVendorCheck = 1 THEN
				UPDATE tblAccountAuthenticate accauth SET accauth.CustomerAuthValue = ''
				WHERE accauth.CompanyID =  p_CompanyID
				AND accauth.ServiceID = p_ServiceID
				AND accauth.AccountID = p_AccountID;
			ELSEIF p_CustomerVendorCheck = 2 THEN
				UPDATE tblAccountAuthenticate accauth SET accauth.VendorAuthValue = ''
				WHERE accauth.CompanyID =  p_CompanyID
				AND accauth.ServiceID = p_ServiceID
				AND accauth.AccountID = p_AccountID;
			END IF;
			SET v_IPCLI = p_IPCLIString;
		ELSE
			
				SET i = 1;
				REPEAT
					INSERT INTO AccountIPCLITable1
					SELECT FnStringSplit(v_IPCLI, ',', i) WHERE FnStringSplit(v_IPCLI, ',', i) IS NOT NULL LIMIT 1;
					SET i = i + 1;
					UNTIL ROW_COUNT() = 0
				END REPEAT;
			
				SET i = 1;
				REPEAT
					INSERT INTO AccountIPCLITable2
					SELECT FnStringSplit(p_IPCLIString, ',', i) WHERE FnStringSplit(p_IPCLIString, ',', i) IS NOT NULL LIMIT 1;
					SET i = i + 1;
					UNTIL ROW_COUNT() = 0
				END REPEAT;
				
				
				SELECT GROUP_CONCAT(t.splitted_column separator ',') INTO v_IPCLI
				FROM
				(
				SELECT splitted_column FROM AccountIPCLITable1
				UNION
				SELECT splitted_column FROM AccountIPCLITable2
				GROUP BY splitted_column
				ORDER BY splitted_column
				) t;
		END IF;
			
			
		IF p_CustomerVendorCheck = 1 THEN
			UPDATE tblAccountAuthenticate accauth SET accauth.CustomerAuthValue = v_IPCLI, accauth.CustomerAuthRule = p_IPCLICheck
			WHERE accauth.CompanyID =  p_CompanyID
			AND accauth.ServiceID = p_ServiceID
			AND accauth.AccountID = p_AccountID;
		ELSEIF p_CustomerVendorCheck = 2 THEN
			UPDATE tblAccountAuthenticate accauth SET accauth.VendorAuthValue = v_IPCLI, accauth.VendorAuthRule = p_IPCLICheck
			WHERE accauth.CompanyID =  p_CompanyID
			AND accauth.ServiceID = p_ServiceID
			AND accauth.AccountID = p_AccountID;
		END IF;
	ELSEIF v_Check IS NULL && p_IPCLIString IS NOT NULL && p_IPCLIString!='' THEN
	
		IF p_CustomerVendorCheck = 1 THEN
			INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,CustomerAuthRule,CustomerAuthValue,ServiceID)
			SELECT p_CompanyID,p_AccountID,p_IPCLICheck,p_IPCLIString,p_ServiceID;
		ELSEIF p_CustomerVendorCheck = 2 THEN
			INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,VendorAuthRule,VendorAuthValue,ServiceID)
			SELECT p_CompanyID,p_AccountID,p_IPCLICheck,p_IPCLIString,p_ServiceID;
		END IF;
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
	
END|
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetAccountLogs`;
DELIMITER |
CREATE PROCEDURE `prc_GetAccountLogs`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_AccountID` int ,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5)
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	SELECT * FROM
	(
		(
			SELECT
				d.ColumnName,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgo.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=1) THEN
						'YES'
					ELSE
						d.OldValue
				END AS OldValue,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgn.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=1) THEN
						'YES'
					ELSE
						d.NewValue
				END AS NewValue,
				d.created_at,
				d.created_by
			FROM tblAuditDetails AS d
			LEFT JOIN tblAuditHeader AS h
				ON h.AuditHeaderID = d.AuditHeaderID
			LEFT JOIN tblAccount AS a
				ON h.ParentColumnID = a.AccountID
			LEFT JOIN tblCompanyGateway AS cgo
				ON FIND_IN_SET(cgo.CompanyGatewayID,d.OldValue)
			LEFT JOIN tblCompanyGateway AS cgn
				ON FIND_IN_SET(cgn.CompanyGatewayID,d.NewValue)
			WHERE   
				h.Type = 'account' AND 
				h.ParentColumnName = 'AccountID' AND 
				h.ParentColumnID = p_AccountID AND 
				a.AccountID = p_AccountID
			GROUP BY
				d.AuditDetailID,d.OldValue,d.NewValue
		)
		
		UNION
		
		(
			SELECT
				IF(d2.ColumnName="CustomerAuthValue","Customer IP","Vendor IP"),
				d2.OldValue AS OldValue,
				d2.NewValue AS NewValue,
				d2.created_at,
				d2.created_by
			FROM tblAuditDetails AS d2
			LEFT JOIN tblAuditHeader AS h2
				ON h2.AuditHeaderID = d2.AuditHeaderID
			LEFT JOIN tblAccountAuthenticate AS aa
				ON aa.AccountAuthenticateID=h2.ParentColumnID
			LEFT JOIN tblAccount AS a2
				ON aa.AccountID = a2.AccountID
			WHERE   
				h2.Type = 'accountip' AND 
				h2.ParentColumnName = 'AccountAuthenticateID' AND 
				a2.AccountID = p_AccountID
			GROUP BY
				d2.AuditDetailID,d2.OldValue,d2.NewValue
		)
	) mix

	ORDER BY
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ColumnNameDESC') THEN mix.ColumnName
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ColumnNameASC') THEN mix.ColumnName
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN mix.created_at
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN mix.created_at
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_byDESC') THEN mix.created_by
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_byASC') THEN mix.created_by
		END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;

	SELECT 
		COUNT(*) AS totalcount
	FROM
	(
		(
			SELECT
				d.ColumnName,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgo.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.OldValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.OldValue=1) THEN
						'YES'
					ELSE
						d.OldValue
				END AS OldValue,
				CASE
					WHEN (d.ColumnName='accountgateway') THEN
						GROUP_CONCAT(DISTINCT cgn.Title)
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsCustomer' AND d.NewValue=1) THEN
						'YES'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=0) THEN
						'NO'
					WHEN (d.ColumnName='IsVendor' AND d.NewValue=1) THEN
						'YES'
					ELSE
						d.NewValue
				END AS NewValue,
				d.created_at,
				d.created_by
			FROM tblAuditDetails AS d
			LEFT JOIN tblAuditHeader AS h
				ON h.AuditHeaderID = d.AuditHeaderID
			LEFT JOIN tblAccount AS a
				ON h.ParentColumnID = a.AccountID
			LEFT JOIN tblCompanyGateway AS cgo
				ON FIND_IN_SET(cgo.CompanyGatewayID,d.OldValue)
			LEFT JOIN tblCompanyGateway AS cgn
				ON FIND_IN_SET(cgn.CompanyGatewayID,d.NewValue)
			WHERE   
				h.Type = 'account' AND 
				h.ParentColumnName = 'AccountID' AND 
				h.ParentColumnID = p_AccountID AND 
				a.AccountID = p_AccountID
			GROUP BY
				d.AuditDetailID,d.OldValue,d.NewValue
		)
		
		UNION
		
		(
			SELECT
				d2.ColumnName,
				d2.OldValue AS OldValue,
				d2.NewValue AS NewValue,
				d2.created_at,
				d2.created_by
			FROM tblAuditDetails AS d2
			LEFT JOIN tblAuditHeader AS h2
				ON h2.AuditHeaderID = d2.AuditHeaderID
			LEFT JOIN tblAccountAuthenticate AS aa
				ON aa.AccountAuthenticateID=h2.ParentColumnID
			LEFT JOIN tblAccount AS a2
				ON aa.AccountID = a2.AccountID
			WHERE   
				h2.Type = 'accountip' AND 
				h2.ParentColumnName = 'AccountAuthenticateID' AND 
				a2.AccountID = p_AccountID
			GROUP BY
				d2.AuditDetailID,d2.OldValue,d2.NewValue
		)
	) totalcount;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_WSProcessImportAccountIP`;
DELIMITER |
CREATE PROCEDURE `prc_WSProcessImportAccountIP`(
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
    

    DELETE n1
		FROM tblTempAccountIP n1
		INNER JOIN (
			SELECT MAX(tblTempAccountIPID) as tblTempAccountIPID FROM tblTempAccountIP WHERE ProcessID = p_processId
			GROUP BY CompanyID,AccountName,IP,Type
			HAVING COUNT(*)>1
		) n2 ON n1.tblTempAccountIPID = n2.tblTempAccountIPID
		WHERE n1.ProcessID = p_processId;

		

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


			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

			INSERT INTO tmp_JobLog_ (Message)
			SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );

			DELETE  FROM tblTempAccountIP WHERE ProcessID = p_processId;

		SELECT * from tmp_JobLog_;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_SplitVendorRate`;
DELIMITER |
CREATE PROCEDURE `prc_SplitVendorRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_TempVendorRateID_ INT;
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);	
	DECLARE newcodecount INT(11) DEFAULT 0;
	
	IF p_dialcodeSeparator !='null'
	THEN
	
	
	
	
	
	DROP TEMPORARY TABLE IF EXISTS `my_splits`;
	CREATE TEMPORARY TABLE `my_splits` (
		`TempVendorRateID` INT(11) NULL DEFAULT NULL,
		`Code` Text NULL DEFAULT NULL,
		`CountryCode` Text NULL DEFAULT NULL
	);
    
  SET i = 1;
  REPEAT
    INSERT INTO my_splits (TempVendorRateID, Code, CountryCode)
      SELECT TempVendorRateID , FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempVendorRate
      WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
			 AND ProcessId = p_processId;
    SET i = i + 1;
    UNTIL ROW_COUNT() = 0
  END REPEAT;
  
  UPDATE my_splits SET Code = trim(Code);
  
	-- UPDATE my_splits SET Code = trim(SUBSTR(Code, LOCATE(' ', Code)));
  

  DROP TEMPORARY TABLE IF EXISTS tmp_newvendor_splite_;
	CREATE TEMPORARY TABLE tmp_newvendor_splite_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempVendorRateID INT(11) NULL DEFAULT NULL,
		Code VARCHAR(500) NULL DEFAULT NULL,
		CountryCode VARCHAR(500) NULL DEFAULT NULL
	);
	
	INSERT INTO tmp_newvendor_splite_(TempVendorRateID,Code,CountryCode)
	SELECT 
		TempVendorRateID,
		Code,
		CountryCode
	FROM my_splits
	WHERE Code like '%-%'
		AND TempVendorRateID IS NOT NULL;

  	
  
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newvendor_splite_);
	
	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_TempVendorRateID_ = (SELECT TempVendorRateID FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_); 
		SET v_Code_ = (SELECT Code FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		
		Call prc_SplitAndInsertVendorRate(v_TempVendorRateID_,v_Code_,v_CountryCode_);
		
	SET v_pointer_ = v_pointer_ + 1;
	END WHILE;
	
	
	DELETE FROM my_splits
		WHERE Code like '%-%'
			AND TempVendorRateID IS NOT NULL;

	DELETE FROM my_splits
		WHERE Code = '' OR Code IS NULL;
			
	 INSERT INTO tmp_split_VendorRate_
	SELECT DISTINCT
		   my_splits.TempVendorRateID as `TempVendorRateID`,
		   `CodeDeckId`,
		   CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
		   `Description`,
			`Rate`,
			`EffectiveDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Forbidden`,
			`DialStringPrefix`
		 FROM my_splits
		   INNER JOIN tblTempVendorRate 
				ON my_splits.TempVendorRateID = tblTempVendorRate.TempVendorRateID
		  WHERE	tblTempVendorRate.ProcessId = p_processId;	
		  
	END IF;
	
  	-- select * from tmp_split_VendorRate_;
	-- LEAVE ThisSP;
  
	IF p_dialcodeSeparator = 'null'
	THEN
	
		INSERT INTO tmp_split_VendorRate_
		SELECT DISTINCT
			  `TempVendorRateID`,
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
				`Forbidden`,
				`DialStringPrefix`
			 FROM tblTempVendorRate
			  WHERE ProcessId = p_processId;	
	
	END IF;	
		
END|
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_SplitAndInsertVendorRate`;
DELIMITER |
CREATE PROCEDURE `prc_SplitAndInsertVendorRate`(
	IN `TempVendorRateID` INT,
	IN `Code` VARCHAR(500),
	IN `p_countryCode` VARCHAR(50)
)
BEGIN

	DECLARE v_First_ INT;
	DECLARE v_Last_ INT;	
		
	SELECT  REPLACE(SUBSTRING(SUBSTRING_INDEX(Code, '-', 1)
                 , LENGTH(SUBSTRING_INDEX(Code, '-', 0)) + 1)
                 , '-'
                 , '') INTO v_First_;
                 
	 SELECT REPLACE(SUBSTRING(SUBSTRING_INDEX(Code, '-', 2)
                 , LENGTH(SUBSTRING_INDEX(Code, '-', 1)) + 1)
                 , '-'
                 , '') INTO v_Last_;                 
	
	
   WHILE v_Last_ >= v_First_
	DO
   	 INSERT my_splits (TempVendorRateID,Code,CountryCode) VALUES (TempVendorRateID,v_Last_,p_countryCode);
	    SET v_Last_ = v_Last_ - 1;
  END WHILE;
	
END|
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_checkDialstringAndDupliacteCode`;
DELIMITER |
CREATE PROCEDURE `prc_checkDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
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
						`Forbidden` varchar(100) ,
						`DialStringPrefix` varchar(500)
					);
		
		
		
		CALL prc_SplitVendorRate(p_processId,p_dialcodeSeparator);							
		
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
									`Forbidden`,
									`DialStringPrefix`
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
            
            	
            
                UPDATE tmp_TempVendorRate_
           	     SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
           		     WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

            END IF;
			
			
			SELECT count(*) INTO totalduplicatecode FROM(
				SELECT count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;
				
			
			IF  totalduplicatecode > 0
			THEN	
			
			
				SELECT GROUP_CONCAT(code) into errormessage FROM(
					SELECT DISTINCT code, 1 as a FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl) as tbl2 GROUP by a;				
				
				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT 
				  CONCAT(code , ' DUPLICATE CODE')
				  	FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;				
					
			END IF;
							
   IF	totalduplicatecode = 0
   THEN
						
	 
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
         
         SELECT  COUNT(*) as count INTO totaldialstringcode
			FROM tmp_TempVendorRate_ vr
				LEFT JOIN tmp_DialString_ ds
					ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  )) 
					-- ON vr.Code = ds.ChargeCode 
				WHERE vr.ProcessId = p_processId 
					AND ds.DialStringID IS NULL
					AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
		   
         IF totaldialstringcode > 0
         THEN
         
				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT CONCAT(Code ,' ', IFNULL(vr.DialStringPrefix,'') , ' No PREFIX FOUND')
				  	FROM tmp_TempVendorRate_ vr
						LEFT JOIN tmp_DialString_ ds
							-- ON vr.Code = ds.ChargeCode
							ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  )) 
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
							tblTempVendorRate.Forbidden as Forbidden ,
							tblTempVendorRate.DialStringPrefix as DialStringPrefix
					   FROM tmp_TempVendorRate_ as tblTempVendorRate
							INNER JOIN tmp_DialString_ ds
							  --	 ON tblTempVendorRate.Code = ds.ChargeCode
							  	ON ( (tblTempVendorRate.Code = ds.ChargeCode AND tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' AND tblTempVendorRate.DialStringPrefix =  ds.DialString AND tblTempVendorRate.Code = ds.ChargeCode  ))
							  -- on (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  )
						 WHERE tblTempVendorRate.ProcessId = p_processId
							AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');							
		
						
					DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_2;
					CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_2 as (SELECT * FROM tmp_VendorRateDialString_);
					
					DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_3;
					CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_3 as (					
					 SELECT vrs1.* from tmp_VendorRateDialString_2 vrs1
					 LEFT JOIN tmp_VendorRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId AND vrs1.Description=vrs2.Description AND vrs1.EffectiveDate=vrs2.EffectiveDate AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
					 WHERE ((vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL))						 
					);
					
					
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
							Forbidden,
							DialStringPrefix
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
						`Forbidden`,
						DialStringPrefix 
					 FROM tmp_VendorRateDialString_3;
					   
				  	UPDATE tmp_TempVendorRate_ as tblTempVendorRate
					JOIN tmp_DialString_ ds
					-- ON tblTempVendorRate.Code = ds.DialString
					  ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  )) 
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 1
					SET tblTempVendorRate.Forbidden = 'B';
					
					UPDATE tmp_TempVendorRate_ as  tblTempVendorRate
					JOIN tmp_DialString_ ds
						-- ON tblTempVendorRate.Code = ds.DialString
						ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 0
					SET tblTempVendorRate.Forbidden = 'UB';
					        
			END IF;	  

    END IF;	
   
END IF; 

 	

END|
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_WSProcessVendorRate`;
DELIMITER //
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
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_CurrencyID` INT
)
ThisSP:BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	  DECLARE v_CodeDeckId_ INT ;
    DECLARE totaldialstringcode INT(11) DEFAULT 0;
    DECLARE newstringcode INT(11) DEFAULT 0;
	  DECLARE totalduplicatecode INT(11);
	  DECLARE errormessage longtext;
	  DECLARE errorheader longtext;
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

	  -- LEAVE ThisSP;

    IF newstringcode = 0
    THEN
	          IF  p_addNewCodesToCodeDeck = 1
            THEN
                INSERT INTO tblRate (
                    CompanyID,
                    Code,
                    Description,
                    CreatedBy,
                    CountryID,
                    CodeDeckId,
                    Interval1,
                    IntervalN
                )
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
                    AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                ) vc;

               	SELECT GROUP_CONCAT(Code) into errormessage FROM(
                    SELECT DISTINCT
                        tblTempVendorRate.Code as Code, 1 as a
                    FROM tmp_TempVendorRate_  as tblTempVendorRate
                    INNER JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							      WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                ) as tbl GROUP BY a;

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
                          AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                    ) c
                ) as tbl GROUP BY a;

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
                              AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                        ) as tbl;
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
                    `AccountId`,
                    `RateId`,
                    `TrunkID`,
                    `BlockedBy`
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

            SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
            SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            JOIN tblVendorRate
                ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				    SET tblVendorRate.Rate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       tblTempVendorRate.Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    tblTempVendorRate.Rate
                )
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

            INSERT INTO tblVendorRate (
                AccountId,
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
                IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       tblTempVendorRate.Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    tblTempVendorRate.Rate
                ) ,
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
END//
DELIMITER ;