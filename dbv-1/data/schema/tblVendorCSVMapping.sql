CREATE TABLE `tblVendorCSVMapping` (
  `VendorCSVMappingId` int(11) NOT NULL AUTO_INCREMENT,
  `VendorId` int(11) DEFAULT NULL,
  `ExcelColumn` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `MapTo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`VendorCSVMappingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci