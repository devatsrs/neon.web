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
            tblAuditDetails AS tad ON tah.AuditHeaderID=tad.AuditHeaderID
            LEFT JOIN
                tblDynamicFieldsValue AS dfv ON dfv.ParentID=tah.ParentColumnID
            LEFT JOIN
                tblDynamicFields AS df ON df.DynamicFieldsID=dfv.DynamicFieldsID
        WHERE
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_last_time + INTERVAL 1 DAY) AND tad.created_at>v_last_time;

        IF ((v_start_time IS NOT NULL AND v_start_time != '') AND (v_end_time IS NOT NULL AND v_end_time != ''))
        THEN
            INSERT INTO tblAccountAuditExportLog (CompanyID,CompanyGatewayID,Type,start_time,end_time,created_at) VALUES (p_CompanyID, p_GatewayID, 'accountip', v_start_time, v_end_time, v_cur_time);
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
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=DATE(v_last_time) AND tah.Date<=DATE(v_last_time + INTERVAL 1 DAY) AND tad.created_at>v_last_time;

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
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=(v_cur_date - INTERVAL 1 DAY) AND tah.Date<=v_cur_date AND tad.created_at<=v_cur_time;

        IF ((v_start_time IS NOT NULL AND v_start_time != '') AND (v_end_time IS NOT NULL AND v_end_time != ''))
        THEN
            INSERT INTO tblAccountAuditExportLog (CompanyID,CompanyGatewayID,Type,start_time,end_time,created_at) VALUES (p_CompanyID, p_GatewayID, 'accountip', v_start_time, v_end_time, v_cur_time);
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
            df.FieldName='Gateway' AND find_in_set(p_GatewayID,dfv.FieldValue) > 0 AND tah.Type='accountip' AND tah.Date>=(v_cur_date - INTERVAL 1 DAY) AND tah.Date<=v_cur_date AND tad.created_at<=v_cur_time;
    END IF;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;