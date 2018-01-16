CREATE DEFINER=`neon-user`@`%` PROCEDURE `prc_TicketCheckRepeatedEmails`(
	IN `p_CompanyID` INT,
	IN `p_Email` VARCHAR(100)


)
BEGIN


		DECLARE v_limit_records INT ;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


		SET @minutes = null;
		SET @minutes_limit = 5;

		SET v_limit_records  = 5;
		
		SET @isAlreadyBlocked = 0;
		SET @block = 0;
		
		select count(ir.TicketImportRuleID) into @isAlreadyBlocked
		from tblTicketImportRule  ir
		inner join tblTicketImportRuleCondition irc on irc.TicketImportRuleID = ir.TicketImportRuleID
		inner join tblTicketImportRuleConditionType irct on irc.TicketImportRuleConditionTypeID = irct.TicketImportRuleConditionTypeID
		where 
		ir.CompanyID = p_CompanyID AND
		irct.`Condition` = 'from_email'  AND
		irc.operand = 'is' AND
		irc.Value = p_Email;


		IF @isAlreadyBlocked > 0 THEN
			
			SET @isAlreadyBlocked = 1;
			
		END IF;
		
		select count(AccountEmailLogID) into @hasMoreThan5Emails from AccountEmailLog
		where
			CompanyID  = p_CompanyID AND
			Emailfrom = p_Email
		order by AccountEmailLogID desc
		limit v_limit_records;


		IF @isAlreadyBlocked = 0 AND @hasMoreThan5Emails >= v_limit_records THEN

			SELECT
				-- min(created_at) , max(created_at) , 
				TIMESTAMPDIFF( MINUTE, min(created_at), max(created_at) )  into @minutes
			FROM
				(
					select created_at from AccountEmailLog
					where
						CompanyID  = p_CompanyID AND
						Emailfrom = p_Email
					order by AccountEmailLogID desc
					limit v_limit_records
				) tmp;



		END IF;

		-- show minutes
		IF @minutes <=  @minutes_limit THEN

			SET @block = 1; -- block

		ELSE

			SET @block = 0; -- dont block

		END IF ;

		SELECT @block as block , @isAlreadyBlocked as isAlreadyBlocked; 
		

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


	END