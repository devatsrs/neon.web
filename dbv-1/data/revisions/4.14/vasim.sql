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
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='account' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_last_time + INTERVAL 1 DAY) AND tad.created_at>v_last_time;

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
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='account' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_last_time + INTERVAL 1 DAY) AND tad.created_at>v_last_time;

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
			df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_last_time + INTERVAL 1 DAY) AND tad.created_at>v_last_time;

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
			df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_last_time + INTERVAL 1 DAY) AND tad.created_at>v_last_time;

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