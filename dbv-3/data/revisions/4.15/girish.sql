USE RMCDR3;


DROP PROCEDURE IF EXISTS `prc_updatVendorSellingCost`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updatVendorSellingCost`(
	IN `p_ProcessID` INT,
	IN `p_customertable` VARCHAR(50),
	IN `p_vendortable` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;

	SET @stmt = CONCAT('
	UPDATE `' , p_vendortable , '` vd 
	INNER JOIN  `' , p_customertable , '` cd ON cd.ID = vd.ID 
		SET selling_cost = cost
	WHERE cd.ProcessID =  "' , p_ProcessID , '"
		AND vd.ProcessID =  "' , p_ProcessID , '"
		AND buying_cost <> 0;
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;