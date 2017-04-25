USE `Ratemanagement3`;

UPDATE tblEmailTemplate SET  TemplateBody = REPLACE(TemplateBody, '<!--[if !mso]> <!-->', '<!--[if !mso]><!-- ><![endif]-->') where TemplateBody like '%<!--[if !mso]> <!-->%';


ALTER TABLE `tblTickets`
	ADD COLUMN `TicketSlaID` INT NULL AFTER `EscalationEmail`;
	ADD COLUMN `DueDate` INT NULL AFTER `TicketSlaID`;
	ADD COLUMN `TicketSlaID` INT NULL AFTER `DueDate`;


CREATE PROCEDURE `prc_AssignSlaToTicket`;

CREATE PROCEDURE `prc_UpdateTicketSLADueDate`;