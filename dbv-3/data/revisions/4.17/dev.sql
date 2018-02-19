Use RMCDR3;

DROP PROCEDURE IF EXISTS `prc_updateSippyCustomerSetupTime`;
DELIMITER //
CREATE PROCEDURE `prc_updateSippyCustomerSetupTime`(
	IN `p_ProcessID` INT,
	IN `p_customertable` VARCHAR(50),
	IN `p_vendortable` VARCHAR(50)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;

	-- for sippy update connect_time from vendor (setup time)  cdr to customer cdr

	SET @stmt = CONCAT('
		UPDATE `'' , p_customertable , ''` cd
	INNER JOIN  `'' , p_vendortable , ''` vd ON cd.ID = vd.ID
		SET cd.connect_time = vd.connect_time
	WHERE cd.ProcessID =  "'' , p_ProcessID , ''"
		AND vd.ProcessID =  "'' , p_ProcessID , ''";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	-- return no of rows updated
	select FOUND_ROWS() as rows_updated;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
