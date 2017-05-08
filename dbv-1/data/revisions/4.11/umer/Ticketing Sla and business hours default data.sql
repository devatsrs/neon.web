-- BusinessHours#################
INSERT INTO `tblTicketBusinessHours` (`ID`, `CompanyID`, `IsDefault`, `Name`, `Description`, `Timezone`, `HoursType`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES	(1, 1, 1, 'Default', 'Default Business Calendar', '0', 2, '', '', '', '');
INSERT INTO `tblTicketsWorkingDays` (`ID`, `BusinessHoursID`, `Day`, `Status`, `StartTime`, `EndTime`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
	(1, 1, 2, 1, '08:00:00', '17:00:00', '2017-04-26 11:29:58', NULL, '2017-04-26 11:29:58', NULL),
	(2, 1, 3, 1, '08:00:00', '17:00:00', '2017-04-26 11:29:58', NULL, '2017-04-26 11:29:58', NULL),
	(3, 1, 4, 1, '08:00:00', '17:00:00', '2017-04-26 11:29:58', NULL, '2017-04-26 11:29:58', NULL),
	(4, 1, 5, 1, '08:00:00', '17:00:00', '2017-04-26 11:29:58', NULL, '2017-04-26 11:29:58', NULL),
	(5, 1, 6, 1, '08:00:00', '17:00:00', '2017-04-26 11:29:58', NULL, '2017-04-26 11:29:58', NULL);

INSERT INTO `tblTicketBusinessHolidays` (`HolidayID`, `BusinessHoursID`, `HolidayMonth`, `HolidayDay`, `HolidayName`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES	(23,1 , 4, 26, 'Off Day', '2017-04-26 11:29:58', NULL, '2017-04-26 11:29:58', NULL);

-- #################################################################################

-- TicketSla
-- TicketSlaTarget
-- TicketSlaPolicyApplyTo #no data
-- TicketSlaPolicyViolation

INSERT INTO `tblTicketSla` (`TicketSlaID`, `CompanyID`, `\IsDefault`, `Name`, `Description`, `Status`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES	(1, 1, 1, 'Neon SLA', 'Neon SLA', 1, '', '', '', '');
	
INSERT INTO `tblTicketSlaTarget` (`SlaTargetID`, `TicketSlaID`, `PriorityID`, `RespondValue`, `RespondType`, `ResolveValue`, `ResolveType`, `OperationalHrs`, `EscalationEmail`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES
(1, 1, 4, 15, 'Minute', 1, 'Hour', '1', 1, '2017-05-08 10:44:16', NULL, '2017-05-08 10:44:16', NULL),
(2, 1, 3, 30, 'Minute', 2, 'Hour', '1', 1, '2017-05-08 10:44:16', NULL, '2017-05-08 10:44:16', NULL),
(3, 1, 2, 1, 'Hour', 2, 'Hour', '1', 1, '2017-05-08 10:44:16', NULL, '2017-05-08 10:44:16', NULL),
(4, 1, 1, 2, 'Hour', 1, 'Day', '1', 1, '2017-05-08 10:44:16', NULL, '2017-05-08 10:44:16', NULL);

INSERT INTO `tblTicketSlaPolicyViolation` (`ViolationID`, `TicketSlaID`, `Time`, `Value`, `VoilationType`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES
	(1, 1, 'immediately', '0', 0, '2017-05-08 10:44:18', NULL, '2017-05-08 10:44:18', NULL),
	(2, 1, 'immediately', '0', 1, '2017-05-08 10:44:22', NULL, '2017-05-08 10:44:22', NULL);
