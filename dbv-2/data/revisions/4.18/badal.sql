Use RMBilling3;

ALTER TABLE `tblInvoiceTemplate` ADD COLUMN `IgnoreCallCharge` TINYINT(1) NULL DEFAULT '0' AFTER `ManagementReport`;
ALTER TABLE `tblInvoiceTemplate` ADD COLUMN `ShowPaymentWidgetInvoice` TINYINT(1) NULL DEFAULT '0' AFTER `IgnoreCallCharge`;

