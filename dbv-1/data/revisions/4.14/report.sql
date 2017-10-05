USE Ratemanagement3;

INSERT INTO `tblResourceCategoriesGroups` (`CategoriesGroupID`, `GroupName`) VALUES (13, 'Reports');

INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('Report.Add', 1, 13);
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('Report.Edit', 1, 13);
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('Report.Delete', 1, 13);
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('Report.View', 1, 13);
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('Report.All', 1, 13);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.getdatalist', 'ReportController.getdatalist', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1308);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.getdatagrid', 'ReportController.getdatagrid', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1308);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.report_delete', 'ReportController.report_delete', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1309);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.report_update', 'ReportController.report_update', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1310);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.report_store', 'ReportController.report_store', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1311);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.edit', 'ReportController.edit', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1310);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.create', 'ReportController.create', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1311);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.ajax_datagrid', 'ReportController.ajax_datagrid', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1308);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.*', 'ReportController.*', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1307);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.index', 'ReportController.index', 1, 'Sumera Saeed', NULL, '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', 1308);