CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_DeleteTicketGroup`(
	IN `p_CompanyID` INT,
	IN `p_GroupID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

-- 1
-- delete tblTicketLog

	DELETE tl FROM
		tblTicketLog tl
		inner join tblTickets t
		on tl.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;

-- 2
-- delete tblTicketDashboardTimeline

--	DELETE FROM tblTicketDashboardTimeline where GroupID = p_GroupID AND CompanyID = p_CompanyID;

-- 3
-- delete tblTicketsDetails

	DELETE td FROM
		tblTicketsDetails td
		inner join tblTickets t
		on td.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;


-- 4
-- delete tblTicketsDeletedLog

	DELETE FROM tblTicketsDeletedLog where tblTicketsDeletedLog.`Group` = p_GroupID AND CompanyID = p_CompanyID;

-- 5
-- delete AccountEmailLogDeletedLog

	DELETE ae FROM
		AccountEmailLogDeletedLog ae
		inner join tblTickets t
		on ae.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;


-- 6
-- delete tblTicketsDetails
	DELETE ae FROM
		AccountEmailLog ae
		inner join tblTickets t
		on ae.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;

-- 7
-- delete tblTickets

	DELETE FROM tblTickets where tblTickets.`Group` = p_GroupID AND CompanyID = p_CompanyID;

-- 8
-- delete tblTicketGroups

	DELETE FROM tblTicketGroups where GroupID = p_GroupID AND CompanyID = p_CompanyID;


	DELETE FROM tblTicketGroupAgents where GroupID = p_GroupID;



SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END