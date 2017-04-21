CREATE DEFINER=`neon-user-dev`@`117.247.87.156` PROCEDURE `prc_UpdateTicketSLADueDate`(
	IN `p_TicketID` INT,
	IN `p_PrevFieldValue` INT,
	IN `p_NewFieldValue` INT


)
BEGIN

	DECLARE 	v_created_at_from DATETIME;
	DECLARE 	v_created_at_to DATETIME;
	DECLARE 	v_DueDate DATETIME;		
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	
	IF
	(
		(SELECT count(*) FROM tblTicketfieldsValues where FieldSlaTime = 0 and ValuesID=p_PrevFieldValue) > 0 AND
		(SELECT count(*) FROM tblTicketfieldsValues where FieldSlaTime = 1 and ValuesID=p_NewFieldValue) > 0
	) THEN
	
	
		SELECT DueDate into v_DueDate FROM tblTickets WHERE TicketID= p_TicketID;

          -- Previous entry - current entry                              		
		SELECT created_at into v_created_at_from  FROM tblTicketLog where TicketID = p_TicketID order by TicketLogID desc limit 1,1;
          SELECT created_at into v_created_at_to FROM tblTicketLog where TicketID = p_TicketID  order by TicketLogID desc limit 1;
		
		Select DATE_ADD(v_DueDate, INTERVAL TIMESTAMPDIFF(Minute , v_created_at_from,v_created_at_to) Minute);
		
		UPDATE tblTickets
		SET DueDate = DATE_ADD(v_DueDate, INTERVAL TIMESTAMPDIFF(Minute , v_created_at_from,v_created_at_to) Minute)
		Where TicketID = p_TicketID;

	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
	
END