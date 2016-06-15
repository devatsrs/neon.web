CREATE TABLE `tblUsageDownloadFiles` (
	`UsageDownloadFilesID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL,
	`CompanyGatewayID` INT(11) NOT NULL DEFAULT '0',
	`FileName` VARCHAR(100) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`UpdatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Status` INT(11) NULL DEFAULT '1' COMMENT '1=pending;2=progress;3=completed;4=error;',
	`ProcessCount` INT(11) NULL DEFAULT '0',
	`process_at` DATETIME NULL DEFAULT NULL,
	`Message` TEXT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`UsageDownloadFilesID`),
	UNIQUE INDEX `IX_gateway_filename` ( `CompanyID`, `CompanyGatewayID`, `FileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci