Use Ratemanagement3;

insert into tblGatewayConfig (GatewayId, Title, Name, Status) VALUES ('7', 'Key', 'key', '1');
UPDATE `tblGatewayConfig` SET `Created_at`='2018-04-07 17:44:39' WHERE  `GatewayConfigID`=174;
UPDATE `tblGatewayConfig` SET `CreatedBy`='RateManagementSystem' WHERE  `GatewayConfigID`=174;

insert into tblGatewayConfig (GatewayId, Title, Name, Status) VALUES ('7', 'Key Phrase', 'keyphrase', '1');
UPDATE `tblGatewayConfig` SET `Created_at`='2018-04-10 17:44:39' WHERE  `GatewayConfigID`=174;
UPDATE `tblGatewayConfig` SET `CreatedBy`='RateManagementSystem' WHERE  `GatewayConfigID`=174;

ALTER TABLE `tblRateRule`
ADD COLUMN `Order` INT(11) NULL AFTER `ModifiedBy`;

UPDATE tblRateRule SET `Order` = RateRuleId;

DROP PROCEDURE IF EXISTS `prc_CloneRateRuleInRateGenerator`;
DELIMITER //
CREATE PROCEDURE `prc_CloneRateRuleInRateGenerator`(
	IN `p_RateRuleID` INT,
	IN `p_CreatedBy` VARCHAR(100)
)
BEGIN
		select max(`Order`) into @max_order from tblRateRule 		where RateGeneratorId  = (		select RateGeneratorId 		from tblRateRule 		where RateRuleID  = p_RateRuleID	limit 1) ;

		insert into tblRateRule
		(	RateGeneratorId,	Code,	Description, `Order`,	created_at,	CreatedBy)
		select
				RateGeneratorId,	Code,	Description, (@max_order+1) , 	now(),	p_CreatedBy
		from tblRateRule
		where RateRuleID  = p_RateRuleID;

		select LAST_INSERT_ID() into @NewRateRuleID;

		insert into tblRateRuleSource
		(	RateRuleId,AccountId,created_at,CreatedBy )
		select
				@NewRateRuleID,AccountId,	now(), p_CreatedBy
		from tblRateRuleSource
		where RateRuleID  = p_RateRuleID;

		insert into tblRateRuleMargin
		(	RateRuleId,MinRate,MaxRate,AddMargin,FixedValue,created_at,CreatedBy )
		select
				@NewRateRuleID,MinRate,MaxRate,AddMargin,FixedValue,now(),p_CreatedBy
		from tblRateRuleMargin
		where RateRuleID  = p_RateRuleID;

		select @NewRateRuleID as RateRuleID;
END//
DELIMITER ;
