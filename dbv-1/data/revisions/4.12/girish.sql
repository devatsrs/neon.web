USE `Ratemanagement3`;

CREATE TABLE IF NOT EXISTS `tblNoticeBoardPost` (
  `NoticeBoardPostID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Detail` text COLLATE utf8_unicode_ci NOT NULL,
  `Type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`NoticeBoardPostID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (1, 'Prefix Translation Rule', 'PrefixTranslationRule', 0, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (2, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (3, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (4, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (5, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (6, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (7, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (8, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);


INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1305, 'NoticeBoardPost.Delete', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1304, 'NoticeBoardPost.Add', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1303, 'NoticeBoardPost.Edit', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1302, 'NoticeBoardPost.View', 1, 9);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1301, 'NoticeBoardPost.All', 1, 9);

INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2283, 'NoticeBoard.delete', 'NoticeBoardController.delete', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1305);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2282, 'NoticeBoard.store', 'NoticeBoardController.store', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1303);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2281, 'NoticeBoard.get_mor_updates', 'NoticeBoardController.get_mor_updates', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1302);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2280, 'NoticeBoard.*', 'NoticeBoardController.*', 1, 'System', NULL, '2017-06-09 12:33:01.000', '2017-06-09 12:33:01.000', 1301);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2279, 'NoticeBoard.index', 'NoticeBoardController.index', 1, 'System', NULL, '2017-06-09 12:33:00.000', '2017-06-09 12:33:00.000', 1302);
