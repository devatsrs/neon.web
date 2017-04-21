CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_AssignSlaToTicket`(
	IN `p_CompanyID` INT



,
	IN `p_TicketID` INT
)
BEGIN


DECLARE v_HasCompanyFilter int; 

DECLARE v_HasGroupFilter int; 

DECLARE v_HasTypeFilter int; 


DECLARE v_Group int; 

DECLARE v_Type int; 

DECLARE v_AccountID int; 

DECLARE v_SlaPolicyID int; 

DECLARE v_TicketSlaID int; 

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


select `Group` into v_Group from tblTickets where TicketID = p_TicketID;

select `Type` into v_Type from tblTickets where TicketID = p_TicketID;

select `AccountID` into v_AccountID from tblTickets where TicketID = p_TicketID;

-- if there is only one sla 
IF ((select count(*) from tblTicketSla where CompanyID=p_CompanyID) = 1 ) THEN
	
	
	-- check for any match
	select sla.TicketSlaID  into v_TicketSlaID 
	from tblTicketSla sla
	inner join tblTicketSlaPolicyApplyTo pol on pol.TicketSlaID = sla.TicketSlaID
		where sla.CompanyID=p_CompanyID
			and 
			(
				(pol.CompanyFilter is null  OR (pol.CompanyFilter is not null and FIND_IN_SET(v_AccountID,pol.CompanyFilter) > 0 ))
				OR
				(pol.GroupFilter is null  OR (pol.GroupFilter is not null and FIND_IN_SET(v_Group,pol.GroupFilter) > 0) )
				OR
				(pol.TypeFilter is null OR (pol.TypeFilter is not null and FIND_IN_SET(v_Type,pol.TypeFilter) > 0) )
			);

	

ELSE -- if there is many slas


		DROP TEMPORARY TABLE IF EXISTS `tmp_tblTicketSla`; 
		CREATE TEMPORARY TABLE `tmp_tblTicketSla` (
			`TicketSlaID` INT NOT NULL,
			numMatches  INT NOT NULL
		);
			
		-- check max exact matches
		
		INSERT INTO tmp_tblTicketSla
		SELECT TicketSlaID , numMatches
	   FROM (SELECT (CASE WHEN (pol.CompanyFilter is not null and FIND_IN_SET(v_AccountID,CompanyFilter) > 0) THEN 1 ELSE 0 END +
		                CASE WHEN (pol.GroupFilter is not null and FIND_IN_SET(v_Group,pol.GroupFilter) > 0) THEN 1 ELSE 0 END +
							CASE WHEN (pol.TypeFilter is not null and FIND_IN_SET(v_Type,pol.TypeFilter) > 0) THEN 1 ELSE 0 END 
		               ) AS numMatches,
		               sla.TicketSlaID
			          from tblTicketSla sla
						inner join tblTicketSlaPolicyApplyTo pol on pol.TicketSlaID = sla.TicketSlaID
						where sla.CompanyID=p_CompanyID 
		       ) tmptable
		 WHERE numMatches > 0
		 ORDER BY numMatches DESC ;
		
		
		IF ( ( SELECT count(*) FROM tmp_tblTicketSla where  numMatches = 3 limit 1 ) > 0 ) THEN
			
			select TicketSlaID into v_TicketSlaID from tmp_tblTicketSla where  numMatches = 3 limit 1;
			
		ELSEIF ( ( SELECT count(*) FROM tmp_tblTicketSla where  numMatches = 2 limit 1 ) > 0 ) THEN
		
			select TicketSlaID into v_TicketSlaID from tmp_tblTicketSla where  numMatches = 2 limit 1;
			
		ELSEIF ( ( SELECT count(*) FROM tmp_tblTicketSla where  numMatches = 1 limit 1 ) > 0 ) THEN
	
			select TicketSlaID into v_TicketSlaID from tmp_tblTicketSla where  numMatches = 1 limit 1;
		
		END IF;
		
		
			-- if  no exact match found  
		IF ( v_TicketSlaID is null ) THEN
		
				-- check for any match
				
					select sla.TicketSlaID  into v_TicketSlaID from tblTicketSla sla
					inner join tblTicketSlaPolicyApplyTo pol on pol.TicketSlaID = sla.TicketSlaID
					where sla.CompanyID=p_CompanyID
					and 
					(
						(pol.CompanyFilter is not null  OR (pol.CompanyFilter is not null and FIND_IN_SET(v_AccountID,pol.CompanyFilter) > 0 ))
						OR
						(pol.GroupFilter is null  OR (pol.GroupFilter is not null and FIND_IN_SET(v_Group,pol.GroupFilter) > 0) )
						OR
						(pol.TypeFilter is null OR (pol.TypeFilter is not null and FIND_IN_SET(v_Type,pol.TypeFilter) > 0) )
					);
						
	  END IF;
	
	 
				
END IF; 

	select v_TicketSlaID;
	
	
	-- update v_TicketSlaID;

	/*UPDATE tblTickets 
	SET  TicketSlaID = v_TicketSlaID
	WHERE TicketID = p_TicketID;
	*/
	
	IF ( v_TicketSlaID > 0 ) THEN
	

			-- update v_TicketSlaID;
			
			UPDATE tblTickets t
			INNER join tblTicketSlaTarget tat on t.TicketSlaID  = tat.TicketSlaID AND tat.PritiryID = t.Priority
			SET  t.TicketSlaID = v_TicketSlaID,
			t.DueDate = (
						CASE WHEN (tat.ResolveType = 'Minute') THEN
							DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Minute)  
						WHEN ResolveType = 'Hour' THEN
							DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Hour) 			
						WHEN (tat.ResolveType = 'Day') THEN
							DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Day)  
						WHEN tat.ResolveType = 'Month' THEN
							DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Month)  
						END 	
			) 
			WHERE
			t.TicketID = p_TicketID
			and t.TicketSlaID = v_TicketSlaID ;
 			
			 
	
	END IF;

	
	
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END