USE `Ratemanagement3`;

CREATE TABLE IF NOT EXISTS `tblAccountDetails` (
	`AccountDetailID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NOT NULL,
	`CustomerPaymentAdd` INT(11) NULL DEFAULT '0',
	`customerpanelpassword` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`DisplayRates` INT(11) NULL DEFAULT '0',
	`ResellerOwner` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`AccountDetailID`),
	UNIQUE INDEX `UI_AccountID` (`AccountID`),
	INDEX `IX_AccountID` (`AccountID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
