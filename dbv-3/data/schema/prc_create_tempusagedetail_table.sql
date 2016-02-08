CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_create_tempusagedetail_table`(IN `tbltempusagedetail_name` VARCHAR(250))
BEGIN

 

set @stm = CONCAT('
CREATE TABLE IF NOT EXISTS `' , @tbltempusagedetail_name , '` (
	`TempUsageDetailID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NULL DEFAULT NULL,
	`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
	`GatewayAccountID` VARCHAR(100) NULL DEFAULT NULL ,
	`AccountID` INT(11) NULL DEFAULT NULL,
	`connect_time` DATETIME NULL DEFAULT NULL,
	`disconnect_time` DATETIME NULL DEFAULT NULL,
	`billed_duration` INT(11) NULL DEFAULT NULL,
	`trunk` VARCHAR(50) NULL DEFAULT NULL ,
	`area_prefix` VARCHAR(50) NULL DEFAULT NULL ,
	`cli` VARCHAR(500) NULL DEFAULT NULL ,
	`cld` VARCHAR(500) NULL DEFAULT NULL ,
	`cost` DOUBLE NULL DEFAULT NULL,
	`ProcessID` VARCHAR(200) NULL DEFAULT NULL ,
	`ID` INT(11) NULL DEFAULT NULL,
	`remote_ip` VARCHAR(100) NULL DEFAULT NULL ,
	`duration` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`TempUsageDetailID`)
)
ENGINE=InnoDB ; ' );

PREPARE stmt FROM @stm;
    EXECUTE stmt;

DEALLOCATE PREPARE stmt;


END