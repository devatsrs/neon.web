use Ratemanagement3;

ALTER TABLE `tblAccount`
ADD COLUMN `LanguageID` INT(11) NULL DEFAULT '43' AFTER `CurrencyId`;

ALTER TABLE `tblTicketGroups`
ADD COLUMN `LanguageID` INT(11) NULL DEFAULT '43' AFTER `CompanyID`;

ALTER TABLE `tblEmailTemplate`
ADD COLUMN `LanguageID` INT(11) NULL DEFAULT '43' AFTER `CompanyID`;


CREATE TABLE IF NOT EXISTS `tblLanguage` (
	`LanguageID` int(11) NOT NULL AUTO_INCREMENT,
	`ISOCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
	`Language` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
	`flag` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
	`is_rtl` enum('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
	PRIMARY KEY (`LanguageID`),
	UNIQUE KEY `ISO_Code` (`ISOCode`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `tblLanguage` (`LanguageID`, `ISOCode`, `Language`, `flag`, `is_rtl`) VALUES
	(1, 'ab', 'Abkhazian', NULL, 'N'),
	(2, 'aa', 'Afar', NULL, 'N'),
	(3, 'af', 'Afrikaans', NULL, 'N'),
	(4, 'ak', 'Akan', NULL, 'N'),
	(5, 'sq', 'Albanian', NULL, 'N'),
	(6, 'am', 'Amharic', NULL, 'N'),
	(7, 'ar', 'Arabic', 'sa.png', 'N'),
	(8, 'an', 'Aragonese', NULL, 'N'),
	(9, 'hy', 'Armenian', NULL, 'N'),
	(10, 'as', 'Assamese', NULL, 'N'),
	(11, 'av', 'Avaric', NULL, 'N'),
	(12, 'ae', 'Avestan', NULL, 'N'),
	(13, 'ay', 'Aymara', NULL, 'N'),
	(14, 'az', 'Azerbaijani', NULL, 'N'),
	(15, 'bm', 'Bambara', NULL, 'N'),
	(16, 'ba', 'Bashkir', NULL, 'N'),
	(17, 'eu', 'Basque', NULL, 'N'),
	(18, 'be', 'Belarusian', NULL, 'N'),
	(19, 'bn', 'Bengali(Bangla)', NULL, 'N'),
	(20, 'bh', 'Bihari', NULL, 'N'),
	(21, 'bi', 'Bislama', NULL, 'N'),
	(22, 'bs', 'Bosnian', NULL, 'N'),
	(23, 'br', 'Breton', NULL, 'N'),
	(24, 'bg', 'Bulgarian', NULL, 'N'),
	(25, 'my', 'Burmese', NULL, 'N'),
	(26, 'ca', 'Catalan', NULL, 'N'),
	(27, 'ch', 'Chamorro', NULL, 'N'),
	(28, 'ce', 'Chechen', NULL, 'N'),
	(29, 'ny', 'Chichewa,Chewa,Nyanja', NULL, 'N'),
	(30, 'zh', 'Chinese', NULL, 'N'),
	(31, 'zh-Hans', 'Chinese(Simplified)', NULL, 'N'),
	(32, 'zh-Hant', 'Chinese(Traditional)', NULL, 'N'),
	(33, 'cv', 'Chuvash', NULL, 'N'),
	(34, 'kw', 'Cornish', NULL, 'N'),
	(35, 'co', 'Corsican', NULL, 'N'),
	(36, 'cr', 'Cree', NULL, 'N'),
	(37, 'hr', 'Croatian', NULL, 'N'),
	(38, 'cs', 'Czech', NULL, 'N'),
	(39, 'da', 'Danish', NULL, 'N'),
	(40, 'dv', 'Divehi,Dhivehi,Maldivian', NULL, 'N'),
	(41, 'nl', 'Dutch', NULL, 'N'),
	(42, 'dz', 'Dzongkha', NULL, 'N'),
	(43, 'en', 'English', 'gb.png', 'N'),
	(44, 'eo', 'Esperanto', NULL, 'N'),
	(45, 'et', 'Estonian', NULL, 'N'),
	(46, 'ee', 'Ewe', NULL, 'N'),
	(47, 'fo', 'Faroese', NULL, 'N'),
	(48, 'fj', 'Fijian', NULL, 'N'),
	(49, 'fi', 'Finnish', NULL, 'N'),
	(50, 'fr', 'French', NULL, 'N'),
	(51, 'ff', 'Fula,Fulah,Pulaar,Pular', NULL, 'N'),
	(52, 'gl', 'Galician', NULL, 'N'),
	(53, 'gd', 'Gaelic(Scottish)', NULL, 'N'),
	(54, 'gv', 'Gaelic(Manx)', NULL, 'N'),
	(55, 'ka', 'Georgian', NULL, 'N'),
	(56, 'de', 'German', NULL, 'N'),
	(57, 'el', 'Greek', NULL, 'N'),
	(59, 'gn', 'Guarani', NULL, 'N'),
	(60, 'gu', 'Gujarati', NULL, 'N'),
	(61, 'ht', 'HaitianCreole', NULL, 'N'),
	(62, 'ha', 'Hausa', NULL, 'N'),
	(63, 'he', 'Hebrew', 'il.png', 'Y'),
	(64, 'hz', 'Herero', NULL, 'N'),
	(65, 'hi', 'Hindi', NULL, 'N'),
	(66, 'ho', 'HiriMotu', NULL, 'N'),
	(67, 'hu', 'Hungarian', NULL, 'N'),
	(68, 'is', 'Icelandic', NULL, 'N'),
	(69, 'io', 'Ido', NULL, 'N'),
	(70, 'ig', 'Igbo', NULL, 'N'),
	(71, 'id', 'Indonesian', NULL, 'N'),
	(72, 'ia', 'Interlingua', NULL, 'N'),
	(73, 'ie', 'Interlingue', NULL, 'N'),
	(74, 'iu', 'Inuktitut', NULL, 'N'),
	(75, 'ik', 'Inupiak', NULL, 'N'),
	(76, 'ga', 'Irish', NULL, 'N'),
	(77, 'it', 'Italian', NULL, 'N'),
	(78, 'ja', 'Japanese', NULL, 'N'),
	(79, 'jv', 'Javanese', NULL, 'N'),
	(80, 'kl', 'Kalaallisut,Greenlandic', NULL, 'N'),
	(81, 'kn', 'Kannada', NULL, 'N'),
	(82, 'kr', 'Kanuri', NULL, 'N'),
	(83, 'ks', 'Kashmiri', NULL, 'N'),
	(84, 'kk', 'Kazakh', NULL, 'N'),
	(85, 'km', 'Khmer', NULL, 'N'),
	(86, 'ki', 'Kikuyu', NULL, 'N'),
	(87, 'rw', 'Kinyarwanda(Rwanda)', NULL, 'N'),
	(88, 'rn', 'Kirundi', NULL, 'N'),
	(89, 'ky', 'Kyrgyz', NULL, 'N'),
	(90, 'kv', 'Komi', NULL, 'N'),
	(91, 'kg', 'Kongo', NULL, 'N'),
	(92, 'ko', 'Korean', NULL, 'N'),
	(93, 'ku', 'Kurdish', NULL, 'N'),
	(94, 'kj', 'Kwanyama', NULL, 'N'),
	(95, 'lo', 'Lao', NULL, 'N'),
	(96, 'la', 'Latin', NULL, 'N'),
	(97, 'lv', 'Latvian(Lettish)', NULL, 'N'),
	(98, 'li', 'Limburgish(Limburger)', NULL, 'N'),
	(99, 'ln', 'Lingala', NULL, 'N'),
	(100, 'lt', 'Lithuanian', NULL, 'N'),
	(101, 'lu', 'Luga-Katanga', NULL, 'N'),
	(102, 'lg', 'Luganda,Ganda', NULL, 'N'),
	(103, 'lb', 'Luxembourgish', NULL, 'N'),
	(105, 'mk', 'Macedonian', NULL, 'N'),
	(106, 'mg', 'Malagasy', NULL, 'N'),
	(107, 'ms', 'Malay', NULL, 'N'),
	(108, 'ml', 'Malayalam', NULL, 'N'),
	(109, 'mt', 'Maltese', NULL, 'N'),
	(110, 'mi', 'Maori', NULL, 'N'),
	(111, 'mr', 'Marathi', NULL, 'N'),
	(112, 'mh', 'Marshallese', NULL, 'N'),
	(113, 'mo', 'Moldavian', NULL, 'N'),
	(114, 'mn', 'Mongolian', NULL, 'N'),
	(115, 'na', 'Nauru', NULL, 'N'),
	(116, 'nv', 'Navajo', NULL, 'N'),
	(117, 'ng', 'Ndonga', NULL, 'N'),
	(118, 'nd', 'NorthernNdebele', NULL, 'N'),
	(119, 'ne', 'Nepali', NULL, 'N'),
	(120, 'no', 'Norwegian', NULL, 'N'),
	(121, 'nb', 'Norwegianbokmål', NULL, 'N'),
	(122, 'nn', 'Norwegiannynorsk', NULL, 'N'),
	(124, 'oc', 'Occitan', NULL, 'N'),
	(125, 'oj', 'Ojibwe', NULL, 'N'),
	(126, 'cu', 'OldChurchSlavonic,OldBulgarian', NULL, 'N'),
	(127, 'or', 'Oriya', NULL, 'N'),
	(128, 'om', 'Oromo(AfaanOromo)', NULL, 'N'),
	(129, 'os', 'Ossetian', NULL, 'N'),
	(130, 'pi', 'Pāli', NULL, 'N'),
	(131, 'ps', 'Pashto,Pushto', NULL, 'N'),
	(132, 'fa', 'Persian(Farsi)', NULL, 'N'),
	(133, 'pl', 'Polish', NULL, 'N'),
	(134, 'pt', 'Portuguese', NULL, 'N'),
	(135, 'pa', 'Punjabi(Eastern)', NULL, 'N'),
	(136, 'qu', 'Quechua', NULL, 'N'),
	(137, 'rm', 'Romansh', NULL, 'N'),
	(138, 'ro', 'Romanian', NULL, 'N'),
	(139, 'ru', 'Russian', NULL, 'N'),
	(140, 'se', 'Sami', NULL, 'N'),
	(141, 'sm', 'Samoan', NULL, 'N'),
	(142, 'sg', 'Sango', NULL, 'N'),
	(143, 'sa', 'Sanskrit', NULL, 'N'),
	(144, 'sc', 'Sardinian', NULL, 'N'),
	(145, 'sr', 'Serbian', NULL, 'N'),
	(146, 'sh', 'Serbo-Croatian', NULL, 'N'),
	(147, 'st', 'Sesotho', NULL, 'N'),
	(148, 'tn', 'Setswana', NULL, 'N'),
	(149, 'sn', 'Shona', NULL, 'N'),
	(150, 'ii', 'Sichuan Yi, Nuosu', NULL, 'N'),
	(151, 'sd', 'Sindhi', NULL, 'N'),
	(152, 'si', 'Sinhalese', NULL, 'N'),
	(154, 'sk', 'Slovak', NULL, 'N'),
	(155, 'sl', 'Slovenian', NULL, 'N'),
	(156, 'so', 'Somali', NULL, 'N'),
	(157, 'nr', 'SouthernNdebele', NULL, 'N'),
	(158, 'es', 'Spanish', 'es.png', 'N'),
	(159, 'su', 'Sundanese', NULL, 'N'),
	(160, 'sw', 'Swahili(Kiswahili)', NULL, 'N'),
	(161, 'ss', 'Swati, Siswati', NULL, 'N'),
	(162, 'sv', 'Swedish', NULL, 'N'),
	(163, 'tl', 'Tagalog', NULL, 'N'),
	(164, 'ty', 'Tahitian', NULL, 'N'),
	(165, 'tg', 'Tajik', NULL, 'N'),
	(166, 'ta', 'Tamil', NULL, 'N'),
	(167, 'tt', 'Tatar', NULL, 'N'),
	(168, 'te', 'Telugu', NULL, 'N'),
	(169, 'th', 'Thai', NULL, 'N'),
	(170, 'bo', 'Tibetan', NULL, 'N'),
	(171, 'ti', 'Tigrinya', NULL, 'N'),
	(172, 'to', 'Tonga', NULL, 'N'),
	(173, 'ts', 'Tsonga', NULL, 'N'),
	(174, 'tr', 'Turkish', NULL, 'N'),
	(175, 'tk', 'Turkmen', NULL, 'N'),
	(176, 'tw', 'Twi', NULL, 'N'),
	(177, 'ug', 'Uyghur', NULL, 'N'),
	(178, 'uk', 'Ukrainian', NULL, 'N'),
	(179, 'ur', 'Urdu', 'pk.png', 'Y'),
	(180, 'uz', 'Uzbek', NULL, 'N'),
	(181, 've', 'Venda', NULL, 'N'),
	(182, 'vi', 'Vietnamese', NULL, 'N'),
	(183, 'vo', 'Volapük', NULL, 'N'),
	(184, 'wa', 'Wallon', NULL, 'N'),
	(185, 'cy', 'Welsh', NULL, 'N'),
	(186, 'wo', 'Wolof', NULL, 'N'),
	(187, 'fy', 'WesternFrisian', NULL, 'N'),
	(188, 'xh', 'Xhosa', NULL, 'N'),
	(189, 'yi', 'Yiddish', NULL, 'N'),
	(190, 'yo', 'Yoruba', NULL, 'N'),
	(191, 'za', 'Zhuang,Chuang', NULL, 'N'),
	(192, 'zu', 'Zulu', NULL, 'N');



CREATE TABLE `tblTranslation` (
	`TranslationID` INT(11) NOT NULL AUTO_INCREMENT,
	`LanguageID` INT(11) NULL DEFAULT NULL,
	`Language` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Translation` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`TranslationID`)
)
	COLLATE='utf8_unicode_ci'
	ENGINE=InnoDB
	AUTO_INCREMENT=1
;

-- All
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('AccountSubscription.All', 1, 3);
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.*';

-- View
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('AccountSubscription.View', 1, 3);
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.main';
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.GetAccountSubscriptions';
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.ajax_datagrid_page';
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.ajax_datagrid';

-- Delete
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('AccountSubscription.Delete', 1, 3);
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.delete';

-- Edit
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('AccountSubscription.Edit', 1, 3);
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.update';

-- Add
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('AccountSubscription.Add', 1, 3);
UPDATE `tblResource` SET `CategoryID`=(SELECT LAST_INSERT_ID()) WHERE  `ResourceName`='AccountSubscription.store';