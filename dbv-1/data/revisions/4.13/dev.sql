Use Ratemanagement3;


USE `Ratemanagement3`;


CREATE TABLE `tblAccountImportExportLog` (
	`AccountImportExportLogID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL DEFAULT '0',
	`AccountID` INT(11) NOT NULL DEFAULT '0',
	`GatewayID` INT(11) NULL DEFAULT NULL,
	`OLDAccountName` INT(11) NOT NULL DEFAULT '0',
	`AccountName` INT(11) NOT NULL DEFAULT '0',
	`Address1` INT(11) NOT NULL DEFAULT '0',
	`Address2` INT(11) NOT NULL DEFAULT '0',
	`Address3` INT(11) NOT NULL DEFAULT '0',
	`City` INT(11) NOT NULL DEFAULT '0',
	`PostCode` INT(11) NOT NULL DEFAULT '0',
	`Country` INT(11) NOT NULL DEFAULT '0',
	`IsCustomer` INT(11) NOT NULL DEFAULT '0',
	`IsVendor` INT(11) NOT NULL DEFAULT '0',
	`Processed` INT(11) NOT NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ModifiedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountImportExportLogID`),
	INDEX `CompanyID_Processed` (`CompanyID`, `Processed`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

Delimiter ;;
CREATE PROCEDURE `prc_AccountImportExportLogMarkProcessed`(
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT,
	IN `p_AccountImportExportLogIDs` TEXT

)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	UPDATE
	tblAccountImportExportLog
	SET Processed = 1
	WHERE
	FIND_IN_SET(AccountImportExportLogID,p_AccountImportExportLogIDs) > 0
	AND
	CompanyID = p_CompanyID
	AND
	GatewayID = p_GatewayID
	;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END;;
Delimiter ;


UPDATE tblCronJobCommand SET Settings='[[{"title":"File Generation Location (Path)","type":"text","value":"","name":"FileLocation"},{"title":"Effective","type":"select","value":{"Now":"Now","Future":"Future","All":"All"},"name":"Effective"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]' WHERE  `Command`='customerratefileexport' ;
UPDATE tblCronJobCommand SET Settings='[[{"title":"File Generation Location (Path)","type":"text","value":"","name":"FileLocation"},{"title":"Effective","type":"select","value":{"Now":"Now","Future":"Future","All":"All"},"name":"Effective"},{"title":"Add Discontinue Rates","type":"select","value":{"no":"No","yes":"Yes"},"name":"AddDiscontinueRates"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]' WHERE  `Command`='vendorratefileexport';

DELIMITER //
CREATE PROCEDURE `prc_getTrunkByMaxMatch`(
	IN `p_CompanyID` INT,
	IN `p_Trunk` VARCHAR(200)
)
BEGIN



	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	DROP TEMPORARY TABLE IF EXISTS tmp_all_trunk_;
   CREATE TEMPORARY TABLE tmp_all_trunk_ (
     RowTrunk  varchar(50),
     TrunkID INT,
     Trunk  varchar(50),
     RowNo int
   );



 insert into tmp_all_trunk_
  SELECT  distinct t.Trunk , t.TrunkID , RIGHT(t.Trunk, x.RowNo) as loopCode , RowNo
			 FROM (
		          SELECT @RowNo  := @RowNo + 1 as RowNo
		          FROM mysql.help_category
		          ,(SELECT @RowNo := 0 ) x
		          limit 20
          ) x
          INNER JOIN tblTrunk AS t
          ON t.CompanyId = p_CompanyID and x.RowNo   <= LENGTH(t.Trunk)
          and ( p_Trunk like concat('%' , t.Trunk ) )
          order by LENGTH(t.Trunk) desc , RowNo desc ;

	-- select TrunkID from tmp_all_trunk_ order by RowNo desc limit 1;

	select TrunkID , p_Trunk  as Trunk  from tmp_all_trunk_ where ( p_Trunk like concat('%' , Trunk ) ) order by  RowNo desc limit 1;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END//
DELIMITER ;
