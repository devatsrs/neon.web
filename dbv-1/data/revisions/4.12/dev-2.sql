USE `Ratemanagement3`;

Delimiter ;;
DROP PROCEDURE IF EXISTS `prc_GetSingleTicket`;
CREATE PROCEDURE `prc_GetSingleTicket`(
	IN `p_TicketID` INT

)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	select t.* , eal.EmailTo from tblTickets t
	left join AccountEmailLog eal on t.TicketID = eal.TicketID and eal.EmailParent = 0
	where t.TicketID =  p_TicketID limit 1;



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;


END;;
Delimiter ;
