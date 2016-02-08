CREATE TABLE `tblAccount` (
  `AccountID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountType` tinyint(3) unsigned DEFAULT NULL,
  `CompanyId` int(11) DEFAULT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `InvoiceTemplateID` int(11) DEFAULT NULL,
  `CurrencyId` int(11) DEFAULT NULL,
  `TaxRateId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Owner` int(11) DEFAULT NULL,
  `Number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NamePrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LeadStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Rating` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LeadSource` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Skype` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailOptOut` tinyint(1) DEFAULT NULL,
  `Twitter` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SecondaryEmail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IsVendor` tinyint(1) DEFAULT NULL,
  `IsCustomer` tinyint(1) DEFAULT NULL,
  `Ownership` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Website` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Mobile` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fax` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Employee` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `Address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PostCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RateEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingEmail` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TechnicalEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VatNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  `PaymentMethod` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDetail` longtext COLLATE utf8_unicode_ci,
  `Converted` tinyint(1) DEFAULT NULL,
  `ConvertedDate` datetime DEFAULT NULL,
  `ConvertedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TimeZone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VerificationStatus` tinyint(3) unsigned DEFAULT '0',
  `BillingType` tinyint(3) unsigned DEFAULT NULL,
  `BillingTimezone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SendInvoiceSetting` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDueInDays` int(11) DEFAULT NULL,
  `RoundChargesAmount` int(11) DEFAULT NULL,
  `BillingCycleType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingCycleValue` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subscription` tinyint(1) DEFAULT '0',
  `SubscriptionQty` int(11) DEFAULT NULL,
  `CDRType` int(11) DEFAULT NULL,
  `InvoiceUsage` int(11) DEFAULT NULL,
  `AccountIP` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingStartDate` datetime DEFAULT NULL,
  `LastInvoiceDate` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NextInvoiceDate` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` longtext COLLATE utf8_unicode_ci,
  `Picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AutorizeProfileID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tags` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Autopay` tinyint(3) unsigned DEFAULT NULL,
  `CustomerCLI` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NominalAnalysisNominalAccountNumber` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountID`),
  KEY `IX_tblAccount_AccountType_CompanyId_IsVendor_Status_Verificati10` (`AccountType`,`CompanyId`,`IsVendor`,`Status`,`VerificationStatus`,`AccountName`),
  KEY `CurrencyId` (`CurrencyId`),
  KEY `TaxRateId` (`TaxRateId`),
  KEY `InvoiceTemplateID` (`InvoiceTemplateID`),
  KEY `CodeDeckId` (`CodeDeckId`),
  KEY `IX_tblAccount_CompanyId_AccountName_AccountID_5E166` (`CompanyId`,`AccountName`),
  KEY `IX_tblAccount_AccountType_CompanyId_Status_738CD` (`AccountType`,`CompanyId`,`Status`,`AccountName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci