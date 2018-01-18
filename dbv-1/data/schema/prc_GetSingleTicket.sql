CREATE DEFINER=`neon-user`@`%` PROCEDURE `prc_GetSingleTicket`(
	IN `p_TicketID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	select t.* , eal.EmailTo from tblTickets t
	left join AccountEmailLog eal on t.TicketID = eal.TicketID -- and eal.EmailParent = 0
	where t.TicketID =  p_TicketID order by eal.AccountEmailLogID asc limit 1;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;

END