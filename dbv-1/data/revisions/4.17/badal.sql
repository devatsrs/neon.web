Use Ratemanagement3;

ALTER TABLE `tblRateRule`
ADD COLUMN `Order` INT(11) NULL AFTER `ModifiedBy`;


DROP PROCEDURE IF EXISTS `prc_CloneRateRuleInRateGenerator`;
DELIMITER //
CREATE PROCEDURE `prc_CloneRateRuleInRateGenerator`(
	IN `p_RateRuleID` INT
,
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

