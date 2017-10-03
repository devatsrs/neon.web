USE RMCDR3;

CREATE TABLE IF NOT EXISTS `tblUCall` (
  `UID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `UUID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UID`),
  KEY `UUID` (`UUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP PROCEDURE IF EXISTS `prc_UniqueIDCallID`;
DELIMITER |
CREATE PROCEDURE `prc_UniqueIDCallID`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET @stm1 = CONCAT('
	INSERT INTO tblUCall (UUID)
	SELECT DISTINCT tud.UUID FROM  `' , p_tbltempusagedetail_name , '` tud
	LEFT JOIN tblUCall ON tud.UUID = tblUCall.UUID
	WHERE UID IS NULL
	AND  tblUCall.UUID IS NOT NULL
	AND  tud.CompanyID = "' , p_CompanyID , '"
	AND  tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
	AND  tud.ProcessID = "' , p_processId , '";
	');

	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET @stm2 = CONCAT('
	UPDATE `' , p_tbltempusagedetail_name , '` tud
	INNER JOIN tblUCall ON tud.UUID = tblUCall.UUID
	SET  tud.ID = tblUCall.UID
	WHERE tud.CompanyID = "' , p_CompanyID , '"
	AND  tblUCall.UUID IS NOT NULL
	AND  tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
	AND  tud.ProcessID = "' , p_processId , '";
	');

	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END|
DELIMITER ;