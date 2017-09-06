CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CheckDueTickets`(
	IN `p_CompanyID` int,
	IN `p_currentDateTime` DATETIME,
	IN `P_Group` VARCHAR(50),
	IN `P_Agent` VARCHAR(50)
)
BEGIN
	DECLARE V_Status varchar(100);
	DECLARE V_OverDue int(11);
	DECLARE V_DueToday int(11);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='';

	SELECT
		 group_concat(TFV.ValuesID separator ',') INTO V_Status FROM tblTicketfieldsValues TFV
	LEFT JOIN tblTicketfields TF
		ON TF.TicketFieldsID = TFV.FieldsID
	WHERE
		TF.FieldType = 'default_status' AND TFV.FieldValueAgent!='Closed' AND TFV.FieldValueAgent!='Resolved';

	 DROP TEMPORARY TABLE IF EXISTS tmp_tickets_sla_voilation_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tickets_sla_voilation_(
		TicketID int,
		TicketSlaID int,
		CreatedDate datetime,
		DueDate datetime,
		IsResolvedVoilation int
	);
		insert into tmp_tickets_sla_voilation_
		SELECT
			T.TicketID,
			T.TicketSlaID as TicketSlaID,
			T.created_at as CreatedDate,	  
		   T.DueDate,
		   T.ResolveSlaPolicyVoilationEmailStatus AS IsResolvedVoilation
		FROM
			tblTickets T
		LEFT JOIN tblTicketSlaTarget TST
			ON TST.TicketSlaID = T.TicketSlaID
		WHERE
			T.CompanyID = p_CompanyID
			AND TST.PriorityID = T.Priority
			AND (V_Status = '' OR find_in_set(T.`Status`,V_Status))
			AND (T.RespondSlaPolicyVoilationEmailStatus = 0 OR T.ResolveSlaPolicyVoilationEmailStatus = 0)
			AND T.TicketSlaID>0
			AND (P_Group = '' OR FIND_IN_SET(T.`Group`,P_Group))
			AND (P_Agent = '' OR FIND_IN_SET(T.`Agent`,P_Agent));


			UPDATE tmp_tickets_sla_voilation_ TSV SET
			TSV.IsResolvedVoilation  =
			CASE
				WHEN p_currentDateTime>=TSV.DueDate THEN 1 ELSE 0
			END;

			
			select count(*) as OverDue INTO V_OverDue from tmp_tickets_sla_voilation_ where IsResolvedVoilation >0;
			select count(*) as DueToday INTO V_DueToday from tmp_tickets_sla_voilation_ where IsResolvedVoilation >0 and DATE(p_currentDateTime) = DATE(DueDate);

			SELECT V_OverDue as OverDue,V_DueToday as DueToday;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END