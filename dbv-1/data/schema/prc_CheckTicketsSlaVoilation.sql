CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CheckTicketsSlaVoilation`(
	IN `p_CompanyID` int,
	IN `p_currentDateTime` DATETIME
)
BEGIN
	DECLARE P_Status varchar(100);
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='';
	
	SELECT 
		 group_concat(TFV.ValuesID separator ',') INTO P_Status FROM tblTicketfieldsValues TFV 
	LEFT JOIN tblTicketfields TF 
		ON TF.TicketFieldsID = TFV.FieldsID
	WHERE 
		TF.FieldType = 'default_status' AND TFV.FieldValueAgent!='Closed' AND TFV.FieldValueAgent!='Resolved';
				
	 DROP TEMPORARY TABLE IF EXISTS tmp_tickets_sla_voilation_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tickets_sla_voilation_(
		TicketID int,
		TicketSlaID int,
		CreatedDate datetime,
		RespondTime datetime,
		ResolveTime datetime,
		IsRespondedVoilation int,
		RespondEmailTime datetime,
		DueDate datetime,
		IsResolvedVoilation int,
		EscalationEmail int
	
	);
		insert into tmp_tickets_sla_voilation_
		SELECT 
			T.TicketID,				
			T.TicketSlaID as TicketSlaID,
			T.created_at as CreatedDate,
		   CASE WHEN (TST.RespondType = 'Minute') THEN
		       	DATE_ADD(T.created_at, INTERVAL TST.RespondValue Minute)  
	 	  		  WHEN RespondType = 'Hour' THEN
	   	 		DATE_ADD(T.created_at, INTERVAL TST.RespondValue Hour) 			
			 	  WHEN (TST.RespondType = 'Day') THEN
		      	 DATE_ADD(T.created_at, INTERVAL TST.RespondValue Day)  
	 	  		  WHEN RespondType = 'Month' THEN
	   	 		DATE_ADD(T.created_at, INTERVAL TST.RespondValue Month)  	   	 
	  END AS RespondTime,
	  	CASE WHEN (TST.ResolveType = 'Minute') THEN
		       DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Minute)  
	 	  	  WHEN ResolveType = 'Hour' THEN
		   	 DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Hour) 			
		 	  WHEN (TST.ResolveType = 'Day') THEN
		       DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Day)  
	 	  	  WHEN ResolveType = 'Month' THEN
	   		 DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Month)  	   	 
	  END AS ResolveTime,
	  T.RespondSlaPolicyVoilationEmailStatus AS IsRespondedVoilation,
	  '0000-00-00 00:00' as RespondEmailTime,
	  T.DueDate,
	  T.ResolveSlaPolicyVoilationEmailStatus AS IsResolvedVoilation,
	  TST.EscalationEmail as EscalationEmail
			 		
		FROM 
			tblTickets T			
		LEFT JOIN tblTicketSlaTarget TST
			ON TST.TicketSlaID = T.TicketSlaID											
		WHERE   
			T.CompanyID = p_CompanyID	
			AND TST.PriorityID = T.Priority		
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (T.RespondSlaPolicyVoilationEmailStatus = 0 OR T.ResolveSlaPolicyVoilationEmailStatus = 0)
			AND T.TicketSlaID>0;		
	
	    	
			
			UPDATE tmp_tickets_sla_voilation_ TSV SET
			TSV.IsRespondedVoilation = 
			CASE  
			  WHEN TSV.IsRespondedVoilation =1 THEN 0 
			  WHEN p_currentDateTime>=TSV.RespondTime THEN 1 ELSE 0			
			END,
			TSV.IsResolvedVoilation  =
			CASE  
				 WHEN TSV.IsResolvedVoilation =1 THEN 0 
				WHEN p_currentDateTime>=TSV.ResolveTime THEN 1 ELSE 0
			END;
		
			SELECT * FROM tmp_tickets_sla_voilation_;
		
			
			
			
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END