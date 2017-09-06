CREATE TABLE `DBEventsData` (
  `EventDate` datetime DEFAULT NULL,
  `EventType` text COLLATE utf8_unicode_ci,
  `EventDDL` text COLLATE utf8_unicode_ci,
  `EventXML` text COLLATE utf8_unicode_ci,
  `DatabaseName` text COLLATE utf8_unicode_ci,
  `SchemaName` text COLLATE utf8_unicode_ci,
  `ObjectName` text COLLATE utf8_unicode_ci,
  `ObjectType` text COLLATE utf8_unicode_ci,
  `ObjectCreateDate` datetime DEFAULT NULL,
  `ObjectModifyDate` datetime DEFAULT NULL,
  `HostName` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IPAddress` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProgramName` text COLLATE utf8_unicode_ci,
  `LoginName` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci