Use Ratemanagement3;



/* For auto rate import table and procedure Start */



CREATE TABLE IF NOT EXISTS `tblAutoImport` (
  `AutoImportID` int(11) NOT NULL AUTO_INCREMENT,
  `JobID` int(11) NOT NULL DEFAULT '0',
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `AccountName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `To` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `From` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CC` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `Subject` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `MessageId` text COLLATE utf8_unicode_ci NOT NULL,
  `MailDateTime` datetime NOT NULL,
  `Attachment` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AutoImportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




CREATE TABLE IF NOT EXISTS `tblAutoImportInboxSetting` (
  `AutoImportInboxSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `port` int(11) DEFAULT NULL,
  `host` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IsSSL` tinyint(1) DEFAULT NULL,
  `username` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emailNotificationOnSuccess` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emailNotificationOnFail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SendCopyToAccount` enum('Y','N') COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AutoImportInboxSettingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




CREATE TABLE IF NOT EXISTS `tblAutoImportSetting` (
  `AutoImportSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `Type` tinyint(1) NOT NULL COMMENT '1=Vendor,2=RateTable',
  `TypePKID` int(11) DEFAULT NULL COMMENT 'Type:1 = AccountID ,Type:2 = VendorID',
  `TrunkID` int(11) DEFAULT NULL,
  `ImportFileTempleteID` int(11) DEFAULT NULL,
  `Subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FileName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SendorEmail` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AutoImportSettingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



DROP PROCEDURE IF EXISTS `prc_ImportSettingMatch`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_ImportSettingMatch`(
	IN `p_companyID` INT,
	IN `p_FromEmail` TEXT,
	IN `p_subject` TEXT,
	IN `p_filenames` TEXT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_matchRecord;
		CREATE TEMPORARY TABLE tmp_matchRecord(
			`AutoImportSettingID` INT(11),
			`CompanyID` INT(11) DEFAULT '0',
			`Type` TINYINT(1),
			`TypePKID` INT(11),
			`TrunkID` INT(11),
			`ImportFileTempleteID` INT(11),
			`Subject` VARCHAR(255),
			`FileName` VARCHAR(255),
			`SendorEmail` TEXT,
			`options` TEXT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_receivedFiles;
		CREATE TEMPORARY TABLE tmp_receivedFiles(
			`fileID` INT(11) AUTO_INCREMENT,
			`receivedFilename` text,
			PRIMARY KEY (`fileID`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_matchFileLangth;
		CREATE TEMPORARY TABLE tmp_matchFileLangth(
			`lognFileName` TEXT,
			`sortFileName` TEXT,
			`matchlength` INT(11)
		);

		  DROP TEMPORARY TABLE IF EXISTS tmp_finalMatch;
		CREATE TEMPORARY TABLE tmp_finalMatch(
			`AutoImportSettingID` INT(11),
			`CompanyID` INT(11) DEFAULT '0',
			`Type` TINYINT(1),
			`TypePKID` INT(11),
			`TrunkID` INT(11),
			`ImportFileTempleteID` INT(11),
			`Subject` VARCHAR(255),
			`FileName` VARCHAR(255),
			`SendorEmail` TEXT,
			`options` TEXT,
			`lognFileName` TEXT,
			`matchlength` INT(11)
		);

		SET @values = CONCAT('("', REPLACE(p_filenames, ',', '"), ("'), '")');
		SET @insert = CONCAT('INSERT INTO tmp_receivedFiles(receivedFilename) VALUES', @values);
		PREPARE stmt FROM @insert;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		insert tmp_matchRecord (AutoImportSettingID, CompanyID, Type, TypePKID, TrunkID, ImportFileTempleteID, Subject, FileName, SendorEmail, options)
					SELECT AutoImportSettingID, acimp.CompanyID, TYPE, TypePKID, TrunkID, acimp.ImportFileTempleteID, Subject, FileName, SendorEmail, TEMPLATE.Options
					FROM tblAutoImportSetting AS acimp
					INNER JOIN tblFileUploadTemplate AS TEMPLATE ON acimp.ImportFileTempleteID =TEMPLATE.FileUploadTemplateID
					WHERE
					(FIND_IN_SET(LOWER(p_FromEmail), LOWER(SendorEmail)) OR FIND_IN_SET(CONCAT('*@', SUBSTRING_INDEX(LOWER(p_FromEmail),'@',-1)), LOWER(SendorEmail)))
					and
					lower(p_subject) LIKE CONCAT('%', lower(Subject), '%')
					order by FileName desc;

		insert tmp_matchFileLangth
			select t1.receivedFilename as lognFileName, MAX(t2.FileName) as sortFileName, length(MAX(t2.FileName)) AS matchlength from tmp_receivedFiles t1
			right join tmp_matchRecord t2 ON t1.receivedFilename like concat('%',t2.FileName,'%')
			where t1.receivedFilename is not null
			group by t1.receivedFilename
			order by matchlength desc;


		  insert tmp_finalMatch
		select tmp_matchRecord.*, tmp_matchFileLangth.lognFileName, tmp_matchFileLangth.matchlength from tmp_matchRecord inner join tmp_matchFileLangth on sortFileName=FileName;

	  DROP TEMPORARY TABLE IF EXISTS tmp_finalMatch2;
		CREATE TEMPORARY TABLE tmp_finalMatch2 as ( select * from tmp_finalMatch);

		select * from tmp_finalMatch where FileName in( select FileName from tmp_finalMatch2 group by FileName having count(FileName)=1 );

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getAutoImportSetting_AccountAndRateTable`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getAutoImportSetting_AccountAndRateTable`(
	IN `p_SettingType` INT,
	IN `p_CompanyID` INT,
	IN `p_TrunkID` INT,
	IN `p_typePKID` INT,
	IN `p_search` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_OffSet_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;



	IF (p_SettingType = 1 ) THEN  -- Account Setting

					IF (p_isExport = 0) THEN


							select a.AccountName,t.Trunk , ut.Title,Subject,FileName,SendorEmail,a.AccountID,t.TrunkID,ut.FileUploadTemplateID,AutoImportSettingID from tblAutoImportSetting as s
							inner join tblTrunk as t on s.TrunkID=t.TrunkID
							inner join tblAccount as a on a.AccountID=s.TypePKID
							inner join tblFileUploadTemplate as ut on ut.FileUploadTemplateID=s.ImportFileTempleteID

							where
								  CASE
								  		WHEN (p_TrunkID > 0 AND p_typePKID > 0 AND p_search <> '') THEN
										    s.TrunkID=p_TrunkID AND  s.TypePKID=p_typePKID AND ( p_search = ''  OR s.Subject LIKE REPLACE(p_search,'*', '%') )  AND s.CompanyID = p_CompanyID AND s.`Type`= 1
								  		WHEN (p_TrunkID > 0 AND p_typePKID > 0) THEN
										    s.TrunkID=p_TrunkID AND  s.TypePKID=p_typePKID AND s.CompanyID = p_CompanyID AND s.`Type`= 1
										WHEN (p_TrunkID > 0) THEN
										    s.TrunkID=p_TrunkID AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
									   WHEN (p_typePKID > 0) THEN
										    s.TypePKID = p_typePKID AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
										WHEN (p_search <> '') THEN
										     (  s.Subject LIKE CONCAT('%',p_search,'%')  OR SendorEmail LIKE CONCAT('%',p_search,'%') OR ut.Title LIKE CONCAT('%',p_search,'%') ) AND  s.CompanyID = p_CompanyID AND s.`Type`= 1


										ELSE
											 s.CompanyID = p_CompanyID AND s.`Type`= 1

									END

								ORDER BY
								  CASE WHEN p_lSortCol = 'AccountName' AND p_SortOrder = 'desc' THEN a.AccountName END DESC,
								  CASE WHEN p_lSortCol = 'AccountName' THEN a.AccountName END,
								  CASE WHEN p_lSortCol = 'Trunk' AND p_SortOrder = 'desc' THEN t.Trunk END DESC,
								  CASE WHEN p_lSortCol = 'Trunk' THEN t.Trunk END,
								  CASE WHEN p_lSortCol = 'Import File Templete' AND p_SortOrder = 'desc' THEN ut.Title END DESC,
								  CASE WHEN p_lSortCol = 'Import File Templete' THEN ut.Title END,
								  CASE WHEN p_lSortCol = 'Subject Match' AND p_SortOrder = 'desc' THEN Subject END DESC,
								  CASE WHEN p_lSortCol = 'Subject Match' THEN Subject END,
								  CASE WHEN p_lSortCol = 'Filename Match' AND p_SortOrder = 'desc' THEN FileName END DESC,
								  CASE WHEN p_lSortCol = 'Filename Match' THEN FileName END,
								  CASE WHEN p_lSortCol = 'Sendor Match' AND p_SortOrder = 'desc' THEN SendorEmail END DESC,
								  CASE WHEN p_lSortCol = 'Sendor Match' THEN SendorEmail END


							LIMIT p_RowspPage OFFSET v_OffSet_;

					ELSE


							select a.AccountName,t.Trunk , ut.Title,Subject,SendorEmail,FileName from tblAutoImportSetting as s
							inner join tblTrunk as t on s.TrunkID=t.TrunkID
							inner join tblAccount as a on a.AccountID=s.TypePKID
							inner join tblFileUploadTemplate as ut on ut.FileUploadTemplateID=s.ImportFileTempleteID

							where
								  CASE
								  		WHEN (p_TrunkID > 0 AND p_typePKID > 0 AND p_search <> '') THEN
										    s.TrunkID=p_TrunkID AND  s.TypePKID=p_typePKID AND ( p_search = ''  OR s.Subject LIKE REPLACE(p_search,'*', '%') )  AND s.CompanyID = p_CompanyID AND s.`Type`= 1
								  		WHEN (p_TrunkID > 0 AND p_typePKID > 0) THEN
										    s.TrunkID=p_TrunkID AND  s.TypePKID=p_typePKID AND s.CompanyID = p_CompanyID AND s.`Type`= 1
										WHEN (p_TrunkID > 0) THEN
										    s.TrunkID=p_TrunkID AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
									   WHEN (p_typePKID > 0) THEN
										    s.TypePKID = p_typePKID AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
										WHEN (p_search <> '') THEN

										    (  s.Subject LIKE CONCAT('%',p_search,'%')  OR SendorEmail LIKE CONCAT('%',p_search,'%') OR ut.Title LIKE CONCAT('%',p_search,'%') ) AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
										ELSE
											 s.CompanyID = p_CompanyID AND s.`Type`= 1

									END
								ORDER BY
								  CASE WHEN p_lSortCol = 'AccountName' AND p_SortOrder = 'desc' THEN a.AccountName END DESC,
								  CASE WHEN p_lSortCol = 'AccountName' THEN a.AccountName END,
								  CASE WHEN p_lSortCol = 'Trunk' AND p_SortOrder = 'desc' THEN t.Trunk END DESC,
								  CASE WHEN p_lSortCol = 'Trunk' THEN t.Trunk END,
								  CASE WHEN p_lSortCol = 'Import File Templete' AND p_SortOrder = 'desc' THEN ut.Title END DESC,
								  CASE WHEN p_lSortCol = 'Import File Templete' THEN ut.Title END,
								  CASE WHEN p_lSortCol = 'Subject Match' AND p_SortOrder = 'desc' THEN Subject END DESC,
								  CASE WHEN p_lSortCol = 'Subject Match' THEN Subject END,
								  CASE WHEN p_lSortCol = 'Filename Match' AND p_SortOrder = 'desc' THEN FileName END DESC,
								  CASE WHEN p_lSortCol = 'Filename Match' THEN FileName END,
								  CASE WHEN p_lSortCol = 'Sendor Match' AND p_SortOrder = 'desc' THEN SendorEmail END DESC,
								  CASE WHEN p_lSortCol = 'Sendor Match' THEN SendorEmail END
							;


					END IF;


		select COUNT(*) AS `totalcount` from tblAutoImportSetting as s
		inner join tblTrunk as t on s.TrunkID=t.TrunkID
		inner join tblAccount as a on a.AccountID=s.TypePKID
		inner join tblFileUploadTemplate as ut on ut.FileUploadTemplateID=s.ImportFileTempleteID
		where
								  CASE
								  		WHEN (p_TrunkID > 0 AND p_typePKID > 0 AND p_search <> '') THEN
										    s.TrunkID=p_TrunkID AND  s.TypePKID=p_typePKID AND ( p_search = ''  OR s.Subject LIKE REPLACE(p_search,'*', '%') )  AND s.CompanyID = p_CompanyID AND s.`Type`= 1
								  		WHEN (p_TrunkID > 0 AND p_typePKID > 0) THEN
										    s.TrunkID=p_TrunkID AND  s.TypePKID=p_typePKID AND s.CompanyID = p_CompanyID AND s.`Type`= 1
										WHEN (p_TrunkID > 0) THEN
										    s.TrunkID=p_TrunkID AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
									   WHEN (p_typePKID > 0) THEN
										    s.TypePKID = p_typePKID AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
										WHEN (p_search <> '') THEN
										    (  s.Subject LIKE CONCAT('%',p_search,'%')  OR SendorEmail LIKE CONCAT('%',p_search,'%') OR ut.Title LIKE CONCAT('%',p_search,'%') ) AND  s.CompanyID = p_CompanyID AND s.`Type`= 1
										ELSE
											 s.CompanyID = p_CompanyID AND s.`Type`= 1

									END
							;

	END IF;



	IF (p_SettingType = 2 ) THEN  -- RateTable Setting

		IF (p_isExport = 0) THEN


				select r.RateTableName,ut.Title,Subject,FileName,SendorEmail,r.RateTableId,ut.FileUploadTemplateID,AutoImportSettingID from tblAutoImportSetting as s
				inner join tblRateTable as r on s.TypePKID = r.RateTableId
				inner join tblFileUploadTemplate as ut on ut.FileUploadTemplateID=s.ImportFileTempleteID

				where
					  CASE
					  		WHEN (p_typePKID > 0 AND p_search <> '') THEN
							     s.TypePKID=p_typePKID AND ( p_search = ''  OR s.Subject LIKE REPLACE(p_search,'*', '%') )  AND s.CompanyID = p_CompanyID AND s.`Type`= 2
					  		WHEN (p_typePKID > 0) THEN
							     s.TypePKID=p_typePKID AND s.CompanyID = p_CompanyID AND s.`Type`= 2
							WHEN (p_search <> '') THEN

							    (  s.Subject LIKE CONCAT('%',p_search,'%')  OR SendorEmail LIKE CONCAT('%',p_search,'%') OR ut.Title LIKE CONCAT('%',p_search,'%') ) AND  s.CompanyID = p_CompanyID AND s.`Type`= 2
							ELSE
								 s.CompanyID = p_CompanyID AND s.`Type`= 2

						END
					ORDER BY
					  CASE WHEN p_lSortCol = 'RateTable' AND p_SortOrder = 'desc' THEN r.RateTableName END DESC,
					  CASE WHEN p_lSortCol = 'RateTable' THEN r.RateTableName END,
					  CASE WHEN p_lSortCol = 'Import File Template' AND p_SortOrder = 'desc' THEN ut.Title END DESC,
					  CASE WHEN p_lSortCol = 'Import File Template' THEN ut.Title END,
					  CASE WHEN p_lSortCol = 'Subject Match' AND p_SortOrder = 'desc' THEN Subject END DESC,
					  CASE WHEN p_lSortCol = 'Subject Match' THEN Subject END,
					  CASE WHEN p_lSortCol = 'Filename Match' AND p_SortOrder = 'desc' THEN FileName END DESC,
					  CASE WHEN p_lSortCol = 'Filename Match' THEN FileName END,
					  CASE WHEN p_lSortCol = 'Sender Match' AND p_SortOrder = 'desc' THEN SendorEmail END DESC,
					  CASE WHEN p_lSortCol = 'Sender Match' THEN SendorEmail END

				LIMIT p_RowspPage OFFSET v_OffSet_;

		ELSE


				select r.RateTableName,ut.Title,Subject,SendorEmail,FileName from tblAutoImportSetting as s
				inner join tblRateTable as r on s.TypePKID = r.RateTableId
				inner join tblFileUploadTemplate as ut on ut.FileUploadTemplateID=s.ImportFileTempleteID

				where
					  CASE
					  		WHEN (p_typePKID > 0 AND p_search <> '') THEN
							     s.TypePKID=p_typePKID AND ( p_search = ''  OR s.Subject LIKE REPLACE(p_search,'*', '%') )  AND s.CompanyID = p_CompanyID AND s.`Type`= 2
					  		WHEN (p_typePKID > 0) THEN
							     s.TypePKID=p_typePKID AND s.CompanyID = p_CompanyID AND s.`Type`= 2
							WHEN (p_search <> '') THEN

							    (  s.Subject LIKE CONCAT('%',p_search,'%')  OR SendorEmail LIKE CONCAT('%',p_search,'%') OR ut.Title LIKE CONCAT('%',p_search,'%') ) AND  s.CompanyID = p_CompanyID AND s.`Type`= 2
							ELSE
								 s.CompanyID = p_CompanyID AND s.`Type`= 2

						END
					ORDER BY
					  CASE WHEN p_lSortCol = 'RateTable' AND p_SortOrder = 'desc' THEN r.RateTableName END DESC,
					  CASE WHEN p_lSortCol = 'RateTable' THEN r.RateTableName END,
					  CASE WHEN p_lSortCol = 'Import File Template' AND p_SortOrder = 'desc' THEN ut.Title END DESC,
					  CASE WHEN p_lSortCol = 'Import File Template' THEN ut.Title END,
					  CASE WHEN p_lSortCol = 'Subject Match' AND p_SortOrder = 'desc' THEN Subject END DESC,
					  CASE WHEN p_lSortCol = 'Subject Match' THEN Subject END,
					  CASE WHEN p_lSortCol = 'Filename Match' AND p_SortOrder = 'desc' THEN FileName END DESC,
					  CASE WHEN p_lSortCol = 'Filename Match' THEN FileName END,
					  CASE WHEN p_lSortCol = 'Sender Match' AND p_SortOrder = 'desc' THEN SendorEmail END DESC,
					  CASE WHEN p_lSortCol = 'Sender Match' THEN SendorEmail END
				;

		END IF;


		select COUNT(*) AS `totalcount` from tblAutoImportSetting as s
				inner join tblRateTable as r on s.TypePKID = r.RateTableId
				inner join tblFileUploadTemplate as ut on ut.FileUploadTemplateID=s.ImportFileTempleteID

				where
					  CASE
					  		WHEN (p_typePKID > 0 AND p_search <> '') THEN
							     s.TypePKID=p_typePKID AND ( p_search = ''  OR s.Subject LIKE REPLACE(p_search,'*', '%') )  AND s.CompanyID = p_CompanyID AND s.`Type`= 2
					  		WHEN (p_typePKID > 0) THEN
							     s.TypePKID=p_typePKID AND s.CompanyID = p_CompanyID AND s.`Type`= 2
							WHEN (p_search <> '') THEN
								(  s.Subject LIKE CONCAT('%',p_search,'%')  OR SendorEmail LIKE CONCAT('%',p_search,'%') OR ut.Title LIKE CONCAT('%',p_search,'%') ) AND  s.CompanyID = p_CompanyID AND s.`Type`= 2
							ELSE
								 s.CompanyID = p_CompanyID AND s.`Type`= 2

						END
				;




	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getAutoImportMail`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getAutoImportMail`(
	IN `p_jobType` INT,
	IN `p_jobStatus` INT,
	IN `p_CompanyID` INT,
	IN `p_search` TEXT,
	IN `p_AccountID` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_OffSet_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

					IF (p_isExport = 0) THEN


							select IFNULL(jt.Title, 'Not Match') as jobType,
							CONCAT(  a.Subject, '<br>', a.AccountName, '<br>', a.`From` )  AS header,
							a.created_at,a.JobID,IFNULL(js.Title, 'Not Match') as jobstatus,a.AutoImportID, a.AccountID, IFNULL(j.Title, '') as jobTitle from tblAutoImport a
							left join tblJob j on a.JobID=j.JobID
							left join tblJobType jt on j.JobTypeID = jt.JobTypeID
							left join tblJobStatus js on j.JobStatusID=js.JobStatusID
							where
								CASE
									WHEN (p_jobType > 0) THEN
										jt.JobTypeID = p_jobType AND a.CompanyID=p_CompanyID
									WHEN (p_jobStatus > 0) THEN
										js.JobStatusID = p_jobStatus AND a.CompanyID=p_CompanyID
									WHEN (p_search <> '') THEN
										    (  a.Subject LIKE CONCAT('%',p_search,'%')  OR a.JobID LIKE CONCAT('%',p_search,'%') )  AND a.CompanyID=p_CompanyID
								   ELSE
										a.CompanyID=p_CompanyID
								END
								AND ( p_AccountID = 0 OR j.AccountID=p_AccountID )
								ORDER BY
								  CASE WHEN p_lSortCol = 'Type' AND p_SortOrder = 'desc' THEN jobType END DESC,
								  CASE WHEN p_lSortCol = 'Type' THEN jobType END,
								  CASE WHEN p_lSortCol = 'Header' AND p_SortOrder = 'desc' THEN header END DESC,
								  CASE WHEN p_lSortCol = 'Header' THEN header END,
								  CASE WHEN p_lSortCol = 'Created' AND p_SortOrder = 'desc' THEN a.created_at END DESC,
								  CASE WHEN p_lSortCol = 'Created' THEN a.created_at END,
								  CASE WHEN p_lSortCol = 'Status' AND p_SortOrder = 'desc' THEN jobstatus END DESC,
								  CASE WHEN p_lSortCol = 'Status' THEN jobstatus END,
								  CASE WHEN p_lSortCol = 'JobId' AND p_SortOrder = 'desc' THEN a.JobID END DESC,
								  CASE WHEN p_lSortCol = 'JobId' THEN a.JobID END

							LIMIT p_RowspPage OFFSET v_OffSet_;

					ELSE


							select IFNULL(jt.Title, 'Not Match') as jobType,
							CONCAT(  a.Subject, '<br>', a.AccountName, '<br>', a.`From` )  AS header,
							a.created_at,a.JobID,IFNULL(js.Title, 'Not Match') as jobstatus,a.AutoImportID, a.AccountID, IFNULL(j.Title, '') as jobTitle from tblAutoImport a
							left join tblJob j on a.JobID=j.JobID
							left join tblJobType jt on j.JobTypeID = jt.JobTypeID
							left join tblJobStatus js on j.JobStatusID=js.JobStatusID
							where
								CASE
									WHEN (p_jobType > 0) THEN
										jt.JobTypeID = p_jobType AND a.CompanyID=p_CompanyID
									WHEN (p_jobStatus > 0) THEN
										js.JobStatusID = p_jobStatus AND a.CompanyID=p_CompanyID
									WHEN (p_search <> '') THEN
										    (  a.Subject LIKE CONCAT('%',p_search,'%')  OR a.JobID LIKE CONCAT('%',p_search,'%') )  AND a.CompanyID=p_CompanyID
								   ELSE
										a.CompanyID=p_CompanyID
								END
								AND ( p_AccountID = 0 OR j.AccountID=p_AccountID )
								ORDER BY
								  CASE WHEN p_lSortCol = 'Type' AND p_SortOrder = 'desc' THEN jobType END DESC,
								  CASE WHEN p_lSortCol = 'Type' THEN jobType END,
								  CASE WHEN p_lSortCol = 'Header' AND p_SortOrder = 'desc' THEN header END DESC,
								  CASE WHEN p_lSortCol = 'Header' THEN header END,
								  CASE WHEN p_lSortCol = 'Created' AND p_SortOrder = 'desc' THEN a.created_at END DESC,
								  CASE WHEN p_lSortCol = 'Created' THEN a.created_at END,
								  CASE WHEN p_lSortCol = 'Status' AND p_SortOrder = 'desc' THEN jobstatus END DESC,
								  CASE WHEN p_lSortCol = 'Status' THEN jobstatus END,
								  CASE WHEN p_lSortCol = 'JobId' AND p_SortOrder = 'desc' THEN a.JobID END DESC,
								  CASE WHEN p_lSortCol = 'JobId' THEN a.JobID END
							;


					END IF;



					select COUNT(*) AS `totalcount` from tblAutoImport a
							left join tblJob j on a.JobID=j.JobID
							left join tblJobType jt on j.JobTypeID = jt.JobTypeID
							left join tblJobStatus js on j.JobStatusID=js.JobStatusID
							where
								CASE
									WHEN (p_jobType > 0) THEN
										jt.JobTypeID = p_jobType AND a.CompanyID=p_CompanyID
									WHEN (p_jobStatus > 0) THEN
										js.JobStatusID = p_jobStatus AND a.CompanyID=p_CompanyID
									WHEN (p_search <> '') THEN
										    (  a.Subject LIKE CONCAT('%',p_search,'%')  OR a.JobID LIKE CONCAT('%',p_search,'%') )  AND a.CompanyID=p_CompanyID
								   ELSE
										a.CompanyID=p_CompanyID
								END
								AND ( p_AccountID = 0 OR j.AccountID=p_AccountID )
							LIMIT p_RowspPage OFFSET v_OffSet_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/* For auto rate import table and procedure END  */




















DROP PROCEDURE IF EXISTS `prc_GetLCR`;
DELIMITER //
CREATE  PROCEDURE `prc_GetLCR`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_isExport` INT)
BEGIN


		DECLARE v_OffSet_ int;

		DECLARE v_Code VARCHAR(50) ;
		DECLARE v_pointer_ int;
		DECLARE v_rowCount_ int;
		DECLARE v_p_code VARCHAR(50);
		DECLARE v_Codlen_ int;
		DECLARE v_position int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_has_null_position int ;
		DECLARE v_next_position1 VARCHAR(200) ;
		DECLARE v_CompanyCurrencyID_ INT;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_results='utf8';

		-- just for taking codes -

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50),
			FinalRankNumber int
		)
		;

		-- Loop codes

		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
		CREATE TEMPORARY TABLE tmp_search_code_ (
			Code  varchar(50),
			INDEX Index1 (Code)
		);

		-- searched codes.

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index2 (Code)
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
		CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			rankname INT,
			INDEX IX_Code (Code,rankname)
		)
		;

		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;

		-- Search code based on p_code

		insert into tmp_search_code_
			SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
																																SELECT @RowNo  := @RowNo + 1 as RowNo
																																FROM mysql.help_category
																																	,(SELECT @RowNo := 0 ) x
																																limit 15
																															) x
				INNER JOIN tblRate AS f
					ON f.CompanyID = p_companyid  AND f.CodeDeckId = p_codedeckID
						 AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR f.Code LIKE REPLACE(p_code,'*', '%') )
						 AND ( p_Description = ''  OR f.Description LIKE REPLACE(p_Description,'*', '%') )
						 AND x.RowNo   <= LENGTH(f.Code)
			order by loopCode   desc;

		-- distinct vendor rates

		INSERT INTO tmp_VendorCurrentRates1_
			Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT distinct tblVendorRate.AccountId,
						    CASE WHEN p_groupby = 'description' THEN IFNULL(blockCode.VendorBlockingId, 0) ELSE IFNULL(blockCode.VendorBlockingId, 0) END AS BlockingId,
						    IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
							 tblAccount.AccountName, tblRate.Code, tblRate.Description,
							 CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
								 THEN
									 tblVendorRate.Rate
							 WHEN  v_CompanyCurrencyID_ = p_CurrencyID
								 THEN
									 (
										 ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
									 )
							 ELSE
								 (
									 -- Convert to base currrncy and x by RateGenerator Exhange

									 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
									 * (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
								 )
							 END
																																		 as  Rate,
							 ConnectionFee,
							 DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference
						 FROM      tblVendorRate
							 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID

							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
							 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid     AND    tblVendorRate.RateId = tblRate.RateID   AND vt.CodeDeckId = tblRate.CodeDeckId
							 INNER JOIN tmp_search_code_  SplitCode   on tblRate.Code = SplitCode.Code
							 LEFT JOIN tblVendorPreference vp
								 ON vp.AccountId = tblVendorRate.AccountId
										AND vp.TrunkID = tblVendorRate.TrunkID
										AND vp.RateId = tblVendorRate.RateId
							 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																	 AND tblVendorRate.AccountId = blockCode.AccountId
																																	 AND tblVendorRate.TrunkID = blockCode.TrunkID
							 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																			 AND tblVendorRate.AccountId = blockCountry.AccountId
																																			 AND tblVendorRate.TrunkID = blockCountry.TrunkID
						 WHERE
							 ( EffectiveDate <= DATE(p_SelectedEffectiveDate) ) 
							 AND ( tblVendorRate.EndDate IS NULL OR  tblVendorRate.EndDate > Now() )   -- rate should not end Today
							 AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = p_trunkID
							  AND 
						        (
						           p_vendor_block = 1 OR 
						          ( 
						             p_vendor_block = 0 AND   (
						                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL  
						             )
						         )    
						       )
							 -- AND blockCode.RateId IS NULL
							 -- AND blockCountry.CountryId IS NULL

					 ) tbl
			order by Code asc;


		-- filter by Effective Dates

		IF p_groupby = 'description' THEN
		
		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,max(BlockingId),max(BlockingCountryId) ,max(AccountName),max(Code),Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
			FROM (
						 SELECT * ,
							 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := AccountID,
							 @prev_TrunkID := TrunkID,
							 @prev_Description := Description,
							 @prev_EffectiveDate := EffectiveDate
						 FROM tmp_VendorCurrentRates1_
							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
						 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
					 ) tbl
			WHERE RowID = 1
			group BY AccountId, TrunkID, Description 
			order by Description asc;
			
		ELSE

		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT * ,
							 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := AccountID,
							 @prev_TrunkID := TrunkID,
							 @prev_RateId := RateID,
							 @prev_EffectiveDate := EffectiveDate
						 FROM tmp_VendorCurrentRates1_
							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
						 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
					 ) tbl
			WHERE RowID = 1
			order by Code asc;
			
		END IF;
		-- Collect Codes pressent in vendor Rates from above query.
		/*
               9372     9372    1
               9372     937     2
               9372     93      3
               9372     9       4

    */


		insert into tmp_all_code_ (RowCode,Code,RowNo)
			select RowCode , loopCode,RowNo
			from (
						 select   RowCode , loopCode,
							 @RowNo := ( CASE WHEN ( @prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
													 ELSE 1
													 END

							 )      as RowNo,
							 @prev_Code := tbl1.RowCode

						 from (
										SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode FROM (
																																																SELECT @RowNo  := @RowNo + 1 as RowNo
																																																FROM mysql.help_category
																																																	,(SELECT @RowNo := 0 ) x
																																																limit 15
																																															) x
											INNER JOIN tmp_search_code_ AS f
												ON  x.RowNo   <= LENGTH(f.Code)
														AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
											INNER JOIN tblRate as tr on f.Code=tr.Code AND tr.CodeDeckId=p_codedeckID
										order by RowCode desc,  LENGTH(loopCode) DESC
									) tbl1
							 , ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;



		IF (p_isExport = 0)
		THEN

			insert into tmp_code_
				select * from tmp_all_code_
				order by RowCode	LIMIT p_RowspPage OFFSET v_OffSet_ ;

		ELSE

			insert into tmp_code_
				select * from tmp_all_code_
				order by RowCode	  ;

		END IF;


		IF p_Preference = 1 THEN

			-- Sort by Preference

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					preference_rank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								@preference_rank := CASE WHEN (@prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																		WHEN (@prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1
																		END AS preference_rank,
								@prev_Code := Code,
								@prev_Preference := IFNULL(Preference, 5),
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS preference,
								(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							ORDER BY preference.Code ASC, preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC

						 ) tbl
				WHERE preference_rank <= p_Position
				ORDER BY Code, preference_rank;

		ELSE

			-- Sort by Rate

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					RateRank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
								@rank := CASE WHEN (@prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END 
								ELSE
								@rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END 
								END	
									AS RateRank,
								@prev_Code := Code,
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS rank,
								(SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
							ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
				WHERE RateRank <= p_Position
				ORDER BY Code, RateRank;

		END IF;

		-- --------- Split Logic ----------
		/* DESC             MaxMatchRank 1  MaxMatchRank 2
    923 Pakistan :       *923 V1          92 V1
    923 Pakistan :       *92 V2            -

    now take only where  MaxMatchRank =  1
    */


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

		insert ignore into tmp_VendorRate_stage_1 (
			RowCode,
			AccountId ,
			BlockingId,
			BlockingCountryId,
			AccountName ,
			Code ,
			Rate ,
			ConnectionFee,
			EffectiveDate ,
			Description ,
			Preference
		)
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.BlockingId,
				v.BlockingCountryId,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				tr.Description,
				-- (select Description from tblRate where tblRate.Code =RowCode AND  tblRate.CodeDeckId=p_codedeckID ) as Description ,
				v.Preference
			FROM tmp_VendorRateByRank_ v
				left join  tmp_all_code_
									 SplitCode   on v.Code = SplitCode.Code
				inner join tblRate tr  on  RowCode = tr.Code AND  tr.CodeDeckId=p_codedeckID
			where  SplitCode.Code is not null and rankname <= p_Position
			order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;


		insert ignore into tmp_VendorRate_stage_
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.BlockingId,
				v.BlockingCountryId,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				@rank := ( CASE WHEN( @prev_AccountID = v.AccountId  and @prev_RowCode     = RowCode   )
					THEN  @rank + 1
									 ELSE 1
									 END
				) AS MaxMatchRank,
				@prev_RowCode := RowCode	 ,
				@prev_AccountID := v.AccountId
			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,RowCode desc ;



		IF p_groupby = 'description' THEN
		
			insert ignore into tmp_VendorRate_
			select
				distinct
				ANY_VALUE(AccountId) ,
				ANY_VALUE(BlockingId) ,
				ANY_VALUE(BlockingCountryId),
				ANY_VALUE(AccountName) ,
				ANY_VALUE(Code) ,
				ANY_VALUE(Rate) ,
				ANY_VALUE(ConnectionFee),
				ANY_VALUE(EffectiveDate) ,
				Description ,
				ANY_VALUE(Preference),
				ANY_VALUE(RowCode)
			from tmp_VendorRate_stage_
			where MaxMatchRank = 1
			group by Description
			order by Description asc;
			
		ELSE
		
			insert ignore into tmp_VendorRate_
				select
					distinct
					AccountId ,
					BlockingId ,
					BlockingCountryId,
					AccountName ,
					Code ,
					Rate ,
					ConnectionFee,
					EffectiveDate ,
					Description ,
					Preference,
					RowCode
				from tmp_VendorRate_stage_
				where MaxMatchRank = 1
				order by RowCode desc;
		END IF;				
			
			




		IF( p_Preference = 0 )
		THEN
			
			IF p_groupby = 'description' THEN
				/* group by description when preference off */

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,						
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,							
								@rank := CASE WHEN (@prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
											 WHEN (@prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
											 ELSE
												 1
											 END
								AS FinalRankNumber,
								@prev_Description  := Description,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_Description := '' , @prev_Rate := 0 ) x
							order by Description,Rate,AccountId ASC
	
						) tbl1
					where
						FinalRankNumber <= p_Position;
			ELSE
					/* group by code when preference off */

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,							
								@rank := CASE WHEN ( @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
										 WHEN ( @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank
										 ELSE
											 1
										 END
								AS FinalRankNumber,
								@prev_RowCode  := RowCode,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x
							order by RowCode,Rate,AccountId ASC
	
						) tbl1
					where
						FinalRankNumber <= p_Position;
						
			END IF;

		ELSE

			IF p_groupby = 'description' THEN
				/* group by description when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=AccountId AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_Description     = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_Description := Description,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC
	
						) tbl1
					where
						FinalRankNumber <= p_Position;
			ELSE
					/* group by code when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_Code := RowCode,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC
	
						) tbl1
					where
						FinalRankNumber <= p_Position;
			END IF;

		END IF;

		IF (p_Position = 5)
		THEN
			IF (p_isExport = 0)
			THEN
				IF p_groupby = 'description' THEN
					SELECT
						CONCAT(max(t.Description)) as Destination,						
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  t.Description
					ORDER BY t.Description ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;
	
				ELSE
				
					SELECT
						CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
						
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  RowCode
					ORDER BY RowCode ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;
					
				END IF;
				
				SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_
					WHERE  ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') ) ;
				
			END IF;

			IF p_isExport = 1
			THEN
				SELECT
					CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 5`
				FROM tmp_final_VendorRate_  t
				GROUP BY  RowCode
				ORDER BY RowCode ASC;
			END IF;
		END IF;

		IF (p_Position = 10)
		THEN
			IF (p_isExport = 0)
			THEN
				IF p_groupby = 'description' THEN				
					SELECT
						CONCAT(max(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 6`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 7`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 8`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 9`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 10`
					FROM tmp_final_VendorRate_  t
					GROUP BY  t.Description
					ORDER BY t.Description ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;
				
				ELSE
					
					SELECT
						CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 6`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 7`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 8`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 9`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 10`
					FROM tmp_final_VendorRate_  t
					GROUP BY  RowCode
					ORDER BY RowCode ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;
					
				END IF;	
				
				SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_
				WHERE  ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') ) ;
			END IF;

			IF p_isExport = 1
			THEN
				SELECT
					CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
				FROM tmp_final_VendorRate_  t
				GROUP BY  RowCode
				ORDER BY RowCode ASC;
			END IF;
		END IF;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;














DROP PROCEDURE IF EXISTS `prc_GetLCRwithPrefix`;
DELIMITER //
CREATE  PROCEDURE `prc_GetLCRwithPrefix`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_isExport` INT
)
BEGIN

		DECLARE v_OffSet_ int;
		DECLARE v_Code VARCHAR(50) ;
		DECLARE v_pointer_ int;
		DECLARE v_rowCount_ int;
		DECLARE v_p_code VARCHAR(50);
		DECLARE v_Codlen_ int;
		DECLARE v_position int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_has_null_position int ;
		DECLARE v_next_position1 VARCHAR(200) ;
		DECLARE v_CompanyCurrencyID_ INT;


		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_results='utf8';

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50),
			FinalRankNumber int
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
		CREATE TEMPORARY TABLE tmp_search_code_ (
			Code  varchar(50),
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50),
			Code  varchar(50),

			INDEX Index2 (Code)
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
		CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			rankname INT,
			INDEX IX_Code (Code,rankname)
		)
		;
		
		DROP TEMPORARY TABLE IF EXISTS tmp_block0;
		CREATE TEMPORARY TABLE tmp_block0(	
			AccountId INT,
			AccountName VARCHAR(200),
			des VARCHAR(200),
			RateId INT
		);
		
		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;
		
		
		
			INSERT INTO tmp_VendorCurrentRates1_
		
				Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (
					
						 SELECT distinct tblVendorRate.AccountId,
						 		CASE WHEN p_groupby = 'description' THEN IFNULL(blockCode.VendorBlockingId, 0) ELSE IFNULL(blockCode.VendorBlockingId, 0) END AS BlockingId,
						 		IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
								tblAccount.AccountName, tblRate.Code, tmpselectedcd.Description,
								CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
									THEN
										tblVendorRate.Rate
								WHEN  v_CompanyCurrencyID_ = p_CurrencyID
									THEN
										(
											( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
										)
								ELSE
									(
	
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
										* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
									)
								END
								as  Rate,
							 ConnectionFee,
																																				DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
							 tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference
						 FROM      tblVendorRate
							 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID

							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID
							 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid   AND    tblVendorRate.RateId = tblRate.RateID  AND vt.CodeDeckId = tblRate.CodeDeckId 


						    INNER JOIN 	(select Code,Description from tblRate where CodeDeckId=p_codedeckID ) tmpselectedcd on tmpselectedcd.Code=tblRate.Code

							 LEFT JOIN tblVendorPreference vp
								 ON vp.AccountId = tblVendorRate.AccountId
										AND vp.TrunkID = tblVendorRate.TrunkID
										AND vp.RateId = tblVendorRate.RateId
							 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																	 AND tblVendorRate.AccountId = blockCode.AccountId
																																	 AND tblVendorRate.TrunkID = blockCode.TrunkID
							 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																			 AND tblVendorRate.AccountId = blockCountry.AccountId
																																			 AND tblVendorRate.TrunkID = blockCountry.TrunkID
						 WHERE
							 ( CHAR_LENGTH(RTRIM(p_code)) = 0 OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )
							 AND (p_Description='' OR tblRate.Description LIKE REPLACE(p_Description,'*','%'))
							 AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
							-- AND EffectiveDate <= NOW()
							 AND EffectiveDate <= DATE(p_SelectedEffectiveDate)
							 AND (tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate > now() )    -- rate should not end Today
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = p_trunkID
							 AND 
						        (
						           p_vendor_block = 1 OR 
						          ( 
						             p_vendor_block = 0 AND   (
						                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL  
						             )
						         )    
						       )
							 -- AND blockCode.RateId IS NULL
							-- AND blockCountry.CountryId IS NULL
					 ) tbl
					order by Code asc;		
		
			

/* for grooup by description  			*/

			IF p_groupby = 'description' THEN
				
				INSERT INTO tmp_VendorCurrentRates_
				Select AccountId,max(BlockingId),max(BlockingCountryId) ,max(AccountName),max(Code),Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
				FROM (
							
							 SELECT * ,
								 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_Description := Description,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
								
							 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC 
						 ) tbl
				WHERE RowID = 1
				group BY AccountId, TrunkID, Description
				order by Description asc;

      
				
			Else

/* for grooup by code  */	

		INSERT INTO tmp_VendorCurrentRates_
				Select AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (
							 SELECT * ,
								 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_RateId := RateID,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
							 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
						 ) tbl
				WHERE RowID = 1
				order by Code asc;

      END IF;



		IF p_Preference = 1 THEN

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					preference_rank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								RateID,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								@preference_rank := CASE WHEN (@prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																		WHEN (@prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1
																		END AS preference_rank,
								@prev_Code := Code,
								@prev_Preference := IFNULL(Preference, 5),
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS preference,
								(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							ORDER BY preference.Code ASC, preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC

						 ) tbl
				WHERE preference_rank <= p_Position
				ORDER BY Code, preference_rank;

		ELSE

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					RateRank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								RateID,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
								@rank := CASE WHEN ( @prev_Rate < Rate) THEN @rank + 1
												 WHEN ( @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END 
								ELSE
								@rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END 
								END
									AS RateRank,
								@prev_Code := Code,
								@prev_Rate := Rate,
								@prev_Description := Description
							FROM tmp_VendorCurrentRates_ AS rank,
								(SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
							ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
				WHERE RateRank <= p_Position
				ORDER BY Code, RateRank;

		END IF;

		insert ignore into tmp_VendorRate_
			select
				distinct
				AccountId ,
				BlockingId ,
				BlockingCountryId,
				AccountName ,
				Code ,
				Rate ,
				RateID,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				Code as RowCode
			from tmp_VendorRateByRank_
			order by RowCode desc;

		IF( p_Preference = 0 )
		THEN
			
			 /* if group by description preference off */
			IF p_groupby = 'description' THEN
				
				
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId,
								-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where AccountId=tmp_VendorRate_.AccountId AND Description=tmp_VendorRate_.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
								(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=AccountId AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
								BlockingCountryId,
						      AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,								
								@rank := CASE WHEN (@prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
												 WHEN (@prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END							
								AS FinalRankNumber,
								@prev_Rate  := Rate,
								@prev_Description := Description,
								@prev_RateID  := RateID
								
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_Description := '' , @prev_Rate := 0 ) x
							order by tmp_VendorRate_.Description,Rate,AccountId ASC
	
						) tbl1
					where
						FinalRankNumber <= p_Position;
					
			ELSE
					/* if group by code start preference off */
					insert into tmp_final_VendorRate_ 
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,								
								@rank := CASE WHEN ( @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
												 WHEN ( @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END
								AS FinalRankNumber,
								@prev_RowCode  := RowCode,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x
							order by RowCode,Rate,AccountId ASC
	
						) tbl1
					where
						FinalRankNumber <= p_Position;
					/* if group by code end  preference off*/
			END IF;
			
		ELSE
			
			IF p_groupby = 'description' THEN
				/* group by descrion when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId = AccountId  AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,							
								@preference_rank := CASE WHEN (@prev_Description    = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
											WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
											WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
											ELSE 1 END
									AS FinalRankNumber,
								@prev_Preference := Preference,
								@prev_Description := Description,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Preference := 5, @prev_Description := '',  @prev_Rate := 0) x
							order by Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC
	
						) tbl1
					where
						FinalRankNumber <= p_Position;
				ELSE
					
						/* group by code when preference on start*/
						insert into tmp_final_VendorRate_
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								FinalRankNumber
							from
								(
									SELECT
										AccountId ,
										BlockingId ,
										BlockingCountryId,
										AccountName ,
										Code ,
										Rate ,
										RateID,
										ConnectionFee,
										EffectiveDate ,
										Description ,
										Preference,
										RowCode,										
										@preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1
													WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
													WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
													ELSE 1 END
										AS FinalRankNumber,
										@prev_Code := RowCode,
										@prev_Preference := Preference,
										@prev_Rate := Rate
									from tmp_VendorRate_
										,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
									order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC
			
								) tbl1
							where
								FinalRankNumber <= p_Position;
						/* group by code when preference on end */
					
				END IF;
		END IF;

	
		IF (p_Position = 5)
		THEN

			IF (p_isExport = 0)
			THEN
				
			   IF p_groupby = 'description' THEN
				
						SELECT				
						   CONCAT(max(t.Description)) as Destination,						
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
						FROM tmp_final_VendorRate_  t
						GROUP BY  t.Description
						ORDER BY t.Description ASC
						LIMIT p_RowspPage OFFSET v_OffSet_ ;
				
				ELSE 
						SELECT				
						   CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
							GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
						FROM tmp_final_VendorRate_  t
						GROUP BY  RowCode
						ORDER BY RowCode ASC
						LIMIT p_RowspPage OFFSET v_OffSet_ ;
				
				END IF;


				SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );

			ELSE
			
				IF p_groupby = 'description' THEN

					SELECT
						CONCAT(max(t.Description)) as Destination,						
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  t.Description
					ORDER BY t.Description ASC;
				
				ELSE
					SELECT
						CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  RowCode
					ORDER BY RowCode ASC;
					
				END IF;

			END IF;

		END IF;

		IF (p_Position = 10)
		THEN

			IF (p_isExport = 0)
			THEN
				
				IF p_groupby = 'description' THEN
				
					SELECT
						CONCAT(max(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 6`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 7`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 8`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 9`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 10`
					FROM tmp_final_VendorRate_  t
					GROUP BY  t.Description
					ORDER BY t.Description ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;
					
				ELSE
					
					SELECT
						CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 6`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 7`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 8`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 9`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 10`
					FROM tmp_final_VendorRate_  t
					GROUP BY  RowCode
					ORDER BY RowCode ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;
					
				END IF;
				
				SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where    ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );

			ELSE
			
				IF p_groupby = 'description' THEN
				
					SELECT
						CONCAT(max(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
					FROM tmp_final_VendorRate_  t
					GROUP BY  t.Description
					ORDER BY t.Description ASC;
				
				ELSE
					
				 SELECT
					CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
					FROM tmp_final_VendorRate_  t
					GROUP BY  RowCode
					ORDER BY RowCode ASC;
				
					
				END IF;
			END IF;

		END IF;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;













DROP PROCEDURE IF EXISTS `prc_lcrBlockUnblock`;
DELIMITER //
CREATE  PROCEDURE `prc_lcrBlockUnblock`(
  IN `p_companyId` INT,
	IN `p_groupby` VARCHAR(200),
	IN `p_blockId` INT,
	IN `p_preference` INT,
	IN `p_accountId` INT,
	IN `p_trunk` INT,
	IN `p_rowcode` VARCHAR(50),
	IN `p_codedeckId` INT,
	IN `p_description` VARCHAR(200),
	IN `p_username` VARCHAR(50),
	IN `p_action` VARCHAR(50),
	IN `p_countryBlockingID` INT
)
BEGIN

   DECLARE v_countryID INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_block0;
	CREATE TEMPORARY TABLE tmp_block0(	
		RateId INT(11)
	);	
	
		IF(p_action = '') THEN 
		
				IF p_groupby = 'description' THEN
						
						
						INSERT INTO tmp_block0
								select RateId
								FROM (
								select vr.RateId
									 from tblVendorRate vr
								 	 inner join tblRate r on vr.RateId=r.RateID 
								    where vr.AccountId = p_accountId AND vr.TrunkID=p_trunk AND r.Description = p_description) tbl;
						
									 	
							IF (p_blockId = 0) THEN
							
								/* insert into Vendor Blocking by description */								 
								 insert into tblVendorBlocking (AccountId,RateId,TrunkID,BlockedBy,BlockedDate)    
									    select p_accountId as AccountId, tmp0.RateID as RateId,p_trunk as TrunkID ,p_username as BlockedBy,NOW() as BlockedDate
										 from  tmp_block0 tmp0
										 	 left join tblVendorBlocking vb
											   on vb.RateId=tmp0.RateID 
											   AND vb.AccountId = p_accountId AND vb.TrunkID=p_trunk
									    where  vb.VendorBlockingId IS NULL;
							ELSE 
								select * from tmp_block0;
								/* Delete from Vendor Blocking by description  */
							
								DELETE vb
								FROM tblVendorBlocking vb
								INNER JOIN tmp_block0 t
								  ON vb.RateId = t.RateID
								WHERE vb.AccountId = p_accountId AND vb.TrunkID = p_trunk ; 
		
							END IF;	
							 
								    
								    
								    
				ELSE
					  				
						INSERT INTO tmp_block0
								select RateId
								FROM (						
								select vr.RateId
									 from tblVendorRate vr
								 	 inner join tblRate r on vr.RateId=r.RateID 
								    where vr.AccountId = p_accountId AND vr.TrunkID=p_trunk AND r.Code = p_rowcode) tbl;
									 
						
						IF	(select COUNT(*)
									 from tblVendorRate vr
									 	 inner join tblRate r
										   on vr.RateId=r.RateID 
										 inner join tblVendorBlocking vb
										   on r.RateID=vb.RateId
								    where vb.AccountId = p_accountId AND vr.TrunkID=p_trunk AND r.Code = p_rowcode)	= 0		
						THEN 
						
						
							 insert into tblVendorBlocking (AccountId,RateId,TrunkID,BlockedBy)    
							    select p_accountId as AccountId,tmp0.RateID as RateId,p_trunk as TrunkID ,p_username as BlockedBy
								 from  tmp_block0 tmp0
								 	 left join tblVendorBlocking vb
									   on vb.RateId=tmp0.RateID 
									   AND vb.AccountId = p_accountId AND vb.TrunkID=p_trunk
							    where  vb.VendorBlockingId IS NULL;
							    
							    
						ELSE
							    
							    DELETE FROM `tblVendorBlocking`
								   WHERE VendorBlockingId = p_blockId ;
								/* DELETE FROM `tblVendorBlocking`
								   WHERE AccountId = p_accountId
								    AND TrunkID = p_trunk AND RateId = (select RateId from tmp_block0);*/
								    
								    
						END IF;
						
				
				END IF;
	
		ELSE
		
			   /* Country Blocking Code Start */
			   if(p_action ='country_block')
				  THEN
		
							  select distinct CountryID into v_countryID from tblRate where Code=p_rowcode AND tblRate.CountryID is not null AND tblRate.CountryID!=0;
							  INSERT INTO tblVendorBlocking
							  (
									 `AccountId`
									 ,CountryId
									 ,`TrunkID`
									 ,`BlockedBy`
							  ) 
							  SELECT  
								p_accountId as AccountId
								,tblCountry.CountryID as CountryId
								,p_trunk as TrunkID
								,p_username as BlockedBy
								FROM    tblCountry
								LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunk AND AccountId = p_accountId
								WHERE  tblCountry.CountryID=v_countryID  AND tblVendorBlocking.VendorBlockingId is null;
											  
				  END IF;
				  
				  if(p_action ='country_unblock')
				  THEN
				  
				  	delete  from tblVendorBlocking 
					WHERE  AccountId = p_accountId AND TrunkID = p_trunk AND ( p_countryBlockingID ='' OR FIND_IN_SET(CountryId, p_countryBlockingID) );
							
				
				  END IF;
			   /* Country Blocking Code End */
	    END IF;
	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
















DROP PROCEDURE IF EXISTS `prc_editpreference`;
DELIMITER //
CREATE  PROCEDURE `prc_editpreference`(
	IN `p_groupby` VARCHAR(50),
	IN `p_preference` INT,
	IN `p_accountId` INT,
	IN `p_trunk` INT,
	IN `p_rowcode` INT,
	IN `p_codedeckId` INT,
	IN `p_description` VARCHAR(200),
	IN `p_username` VARCHAR(50)



)
BEGIN

	DECLARE v_description VARCHAR(200);
	DECLARE v_vendorPreferenceId int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	
	DROP TEMPORARY TABLE IF EXISTS tmp_pref0;
	CREATE TEMPORARY TABLE tmp_pref0(	
		RateId INT
	);	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_pref1;
	CREATE TEMPORARY TABLE tmp_pref1(	
		VendorPreferenceID INT,
		Preference INT
	);	
		
		IF p_groupby = 'description' THEN
		
				IF p_preference = 0 THEN
				
					INSERT INTO tmp_pref0
						select RateId
						FROM (
						select vr.RateId
							 from tblVendorRate vr
							 	 inner join tblRate r
								   on vr.RateId=r.RateID 
						    where vr.AccountId = p_accountId AND vr.TrunkID=p_trunk AND r.Description = p_description) tbl;
						    
					
						INSERT INTO tmp_pref1
						select VendorPreferenceID,Preference
						FROM (
						select vp.VendorPreferenceID,vp.Preference
							 from tblVendorPreference vp
							 	 inner join tmp_pref0 tmp0
								   on vp.RateId=tmp0.RateID 
						    where vp.AccountId = p_accountId AND vp.TrunkID=p_trunk) tbl1;
						
						select max(Preference) as Preference from tmp_pref1;				
						
				ELSE
				
				
					INSERT INTO tmp_pref0
						select RateId
						FROM (
						select vr.RateId
							 from tblVendorRate vr
							 	 inner join tblRate r
								   on vr.RateId=r.RateID 
						    where vr.AccountId = p_accountId AND vr.TrunkID=p_trunk AND r.Description = p_description) tbl;
						    
					/* Update preference */
					 UPDATE tblVendorPreference 
						INNER JOIN tmp_pref0 temp 
							ON tblVendorPreference.RateId = temp.RateID
						SET tblVendorPreference.Preference = p_preference,created_at=NOW(),CreatedBy=p_username
						WHERE tblVendorPreference.AccountId = p_accountId AND tblVendorPreference.TrunkID = p_trunk ;
						
					 /* insert preference if not in tblVendorPreference */		
					 insert into tblVendorPreference (AccountId,Preference,RateID,TrunkID,CreatedBy,created_at)    
						    select p_accountId as AccountId, p_preference as Preference,tmp0.RateID as RateId,p_trunk as TrunkID ,p_username as CreatedBy,NOW() as created_at
							 from  tmp_pref0 tmp0
							 	 left join tblVendorPreference vp
								   on vp.RateId=tmp0.RateID 
								   AND vp.AccountId = p_accountId AND vp.TrunkID=p_trunk
						    where  vp.VendorPreferenceID IS NULL;
		
								
				END IF;
		ELSE
				
				IF p_preference = 0 THEN
				
						select distinct vp.Preference
							 from tblVendorRate vr
							 	 inner join tblRate r
								   on vr.RateId=r.RateID 
								 inner join tblVendorPreference vp
								   on r.RateID=vp.RateId
						    where vp.AccountId = p_accountId AND vr.TrunkID=p_trunk AND r.Code = p_rowcode;									
						
				ELSE
					
					
					
						INSERT INTO tmp_pref0
						select RateId
						FROM (
						select vr.RateId
							 from tblVendorRate vr
							 	 inner join tblRate r
								   on vr.RateId=r.RateID 
						    where vr.AccountId = p_accountId AND vr.TrunkID=p_trunk AND r.Code = p_rowcode) tbl;
						    
					/* Update preference */
					 UPDATE tblVendorPreference 
						INNER JOIN tmp_pref0 temp 
							ON tblVendorPreference.RateId = temp.RateID
						SET tblVendorPreference.Preference = p_preference,created_at=NOW(),CreatedBy=p_username
						WHERE tblVendorPreference.AccountId = p_accountId AND tblVendorPreference.TrunkID = p_trunk ;
						
					 /* insert preference if not in tblVendorPreference */		
					 insert into tblVendorPreference (AccountId,Preference,RateID,TrunkID,CreatedBy,created_at)    
						    select p_accountId as AccountId, p_preference as Preference,tmp0.RateID as RateId,p_trunk as TrunkID ,p_username as CreatedBy,NOW() as created_at
							 from  tmp_pref0 tmp0
							 	 left join tblVendorPreference vp
								   on vp.RateId=tmp0.RateID 
								   AND vp.AccountId = p_accountId AND vp.TrunkID=p_trunk
						    where  vp.VendorPreferenceID IS NULL;
						    
					
								
				END IF;
		
		END IF;
		
			 

		
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;










/* === START === Apply RateTable for multiple Account Task Procedure 3 ( prc_getRateTableByAccount , prc_applyRateTableTomultipleAccByTrunk , prc_applyRateTableTomultipleAccByService ) */



DROP PROCEDURE IF EXISTS `prc_getRateTableByAccount`;
DELIMITER //
CREATE  PROCEDURE `prc_getRateTableByAccount`(
	IN `p_companyid` INT,
	IN `p_level` VARCHAR(1),
	IN `p_trunkid` INT,
	IN `p_currency` INT,
	IN `p_ratetableId` INT,
	IN `p_AccountIds` TEXT,
	IN `p_services` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
	)
BEGIN

			DECLARE v_OffSet_ int;

			SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

			SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
			
			DROP TEMPORARY TABLE IF EXISTS tmp_in;
					CREATE TEMPORARY TABLE tmp_in (
						CompanyID INT ,
						AccountID INT,
						ServiceID INT ,
						RateTableID INT,
						InBoundRateTableName VARCHAR(100),
						Type INT
					);
					
			DROP TEMPORARY TABLE IF EXISTS tmp_out;
					CREATE TEMPORARY TABLE tmp_out (
						CompanyID INT ,
						AccountID INT,
						ServiceID INT ,
						RateTableID INT,
						OutBoundRateTableName VARCHAR(100),
						Type INT
					);

			DROP TEMPORARY TABLE IF EXISTS tmp_acc2;
					CREATE TEMPORARY TABLE tmp_acc2 (
						CompanyID INT ,
						AccountID INT,
						ServiceID INT ,
						RateTableID INT,
						Type INT,
						InBountRateTableID INT,
						OutBountRateTableID INT
					);

			DROP TEMPORARY TABLE IF EXISTS tmp_AccountServiceRateTable;
					CREATE TEMPORARY TABLE tmp_AccountServiceRateTable (
						AccountID INT,
						AccountName VARCHAR(100) ,
						InBoundRateTableName VARCHAR(100),			
						OutBoundRateTableName VARCHAR(100),	
						ServiceName VARCHAR(200),				
						ServiceID INT ,
						CurrencyId INT,	
						InBountRateTableID INT,
						OutBountRateTableID INT
						
					);

	/*
		p_level = 'S'  -- Service 
		p_level = 'T'  -- Trunk 
		
			*/
			
			IF (p_level="S") 
			THEN
			 
			 	/*
				Get service wise rate table 
			*/
			
			/*
			
			1. Get account with service 
			2. update service Inbound
			3  update service outbound
			4  update rate table name 
			
			*/
			 	-- 1
				insert into tmp_AccountServiceRateTable (AccountID,AccountName,ServiceName,ServiceID,CurrencyId)
				select distinct a.AccountID,a.AccountName,s.ServiceName,ass.ServiceID,a.CurrencyId 
				from  tblAccount a 
				left join tblAccountService ass  on a.AccountID=ass.AccountID
				left join tblService s on ass.ServiceID=s.ServiceID
				where a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1 AND s.`Status`=1;
			
				 
				-- 2  (Type 2 = Inbound Update)
			 
				update tmp_AccountServiceRateTable ft 
				left join tblAccountTariff att on ft.AccountID = att.AccountID AND ft.ServiceID = att.ServiceID 
				set ft.InBountRateTableID=att.RateTableID
				where att.`Type`=2 AND att.AccountTariffID is not null  ;
				
				
				-- 3 (Type 1 = Outbound Update)
				update tmp_AccountServiceRateTable ft 
				left join tblAccountTariff att on ft.AccountID = att.AccountID AND ft.ServiceID = att.ServiceID 
				set ft.OutBountRateTableID=att.RateTableID
				where att.`Type`=1 AND att.AccountTariffID is not null  ;
				
				 
				-- 4  Update Rate TAble name
				update tmp_AccountServiceRateTable ft 
				inner join tblRateTable rt on ft.InBountRateTableID=rt.RateTableId
				set ft.InBoundRateTableName=rt.RateTableName;
		 	 
		 	 
				update tmp_AccountServiceRateTable ft 
				inner join tblRateTable rt on ft.OutBountRateTableID=rt.RateTableId
				set ft.OutBoundRateTableName=rt.RateTableName;
				
					IF p_isExport = 0
					THEN 
					
						select *  from tmp_AccountServiceRateTable ft
						where
								  CASE 
										WHEN (p_ratetableId > 0 AND p_services > 0) THEN
										    ft.ServiceID=p_services AND (ft.InBountRateTableID=p_ratetableId OR ft.OutBountRateTableID=p_ratetableId)
										    
										WHEN (p_services > 0) THEN
											 ft.ServiceID=p_services 
										 
									   WHEN (p_ratetableId > 0) THEN
										    (InBountRateTableID=p_ratetableId OR ft.OutBountRateTableID=p_ratetableId)
									   ELSE						
											 ft.AccountName IS NOT NULL 																
									END									 
								 AND  (p_AccountIds='' OR FIND_IN_SET(ft.AccountID,p_AccountIds) != 0 )	AND ft.CurrencyId=p_currency				 
						
						 ORDER BY
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
						 END DESC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameASC') THEN InBoundRateTableName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameDESC') THEN InBoundRateTableName
						 END DESC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutBoundRateTableNameASC') THEN OutBoundRateTableName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutBoundRateTableNameDESC') THEN OutBoundRateTableName
						 END DESC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameASC') THEN ServiceName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameDESC') THEN ServiceName
						 END DESC
						 
						 				 
						LIMIT p_RowspPage OFFSET v_OffSet_; 
					 
						
						
						SELECT COUNT(*) AS `totalcount` FROM tmp_AccountServiceRateTable ft
						where
								  CASE 
										WHEN (p_ratetableId > 0 AND p_services > 0) THEN
										    ft.ServiceID=p_services AND (ft.InBountRateTableID=p_ratetableId OR ft.OutBountRateTableID=p_ratetableId)
										    
										WHEN (p_services > 0) THEN
											 ft.ServiceID=p_services 
										 
									   WHEN (p_ratetableId > 0) THEN
										    (InBountRateTableID=p_ratetableId OR ft.OutBountRateTableID=p_ratetableId)
									   ELSE						
											 ft.AccountName IS NOT NULL 																
									END									 
								 AND  (p_AccountIds='' OR FIND_IN_SET(ft.AccountID,p_AccountIds) != 0 ) AND ft.CurrencyId=p_currency;
				
					 
				   ELSE 
					
						select AccountName,InBoundRateTableName,OutBoundRateTableName,ServiceName  from tmp_AccountServiceRateTable ft
						where
								  CASE 
										WHEN (p_ratetableId > 0 AND p_services > 0) THEN
										    ft.ServiceID=p_services AND (ft.InBountRateTableID=p_ratetableId OR ft.OutBountRateTableID=p_ratetableId)
										    
										WHEN (p_services > 0) THEN
											 ft.ServiceID=p_services 
										 
									   WHEN (p_ratetableId > 0) THEN
										    (InBountRateTableID=p_ratetableId OR ft.OutBountRateTableID=p_ratetableId)
									   ELSE						
											 ft.AccountName IS NOT NULL 																
									END									 
								 AND  (p_AccountIds='' OR FIND_IN_SET(ft.AccountID,p_AccountIds) != 0 )	AND ft.CurrencyId=p_currency				 
						
						 ORDER BY
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
						 END DESC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameASC') THEN InBoundRateTableName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameDESC') THEN InBoundRateTableName
						 END DESC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutBoundRateTableNameASC') THEN OutBoundRateTableName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutBoundRateTableNameDESC') THEN OutBoundRateTableName
						 END DESC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameASC') THEN ServiceName
						 END ASC,
						 CASE
								WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameDESC') THEN ServiceName
						 END DESC
						 ;
					
					END IF;
			
				
					
			ELSE
			
			/*
			Get trunk wise rate table 
			*/
					/*
					select a.AccountID,a.AccountName,t.CustomerTrunkID,t.TrunkID from 
					tblAccount a
					left join tblCustomerTrunk t on t.AccountID=a.AccountID
					where a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1; */

					IF p_isExport = 0
					THEN 
						

					
								select distinct a.AccountID,a.AccountName,r.RateTableName,t.Trunk,r.RateTableId,t.TrunkID,ct.`Status` from tblAccount a
								join tblTrunk t
								left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
								left join tblRateTable r on ct.RateTableID=r.RateTableId
								
								where 
								CASE 
									WHEN (p_ratetableId > 0 AND p_trunkid > 0) THEN
									    r.TrunkID=p_trunkid AND
										 r.RateTableId=p_ratetableId AND
									    a.CompanyId=p_companyid 
									    
									WHEN (p_trunkid > 0) THEN
										 t.TrunkID=p_trunkid AND
										 a.CompanyId=p_companyid 
									 
								   WHEN (p_ratetableId > 0) THEN
									    r.RateTableId=p_ratetableId AND
									    a.CompanyId=p_companyid 
								   ELSE						
										 a.CompanyId=p_companyid 					
									
								END
								AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
								AND a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
								AND a.CurrencyId=p_currency AND t.`Status`=1
								
								ORDER BY	
													
								CASE
									WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
								END ASC,
								CASE
											WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
								END DESC,
								CASE
									WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameASC') THEN r.RateTableName
								END ASC,
								CASE
											WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameDESC') THEN r.RateTableName
								END DESC,
								CASE
									WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutRateTableNameASC') THEN t.Trunk
								END ASC,
								CASE
											WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutRateTableNameDESC') THEN t.Trunk
								END DESC , r.RateTableName DESC , t.Trunk ASC
														 
								LIMIT p_RowspPage OFFSET v_OffSet_;
							
								
								
								
								select  COUNT(*) AS `totalcount` from  tblAccount a
								join tblTrunk t
								left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
								left join tblRateTable r on ct.RateTableID=r.RateTableId
								
								where 
								CASE 
									WHEN (p_ratetableId > 0 AND p_trunkid > 0) THEN
									    r.TrunkID=p_trunkid AND
										 r.RateTableId=p_ratetableId AND
									    a.CompanyId=p_companyid AND a.IsCustomer=1 
									    
									WHEN (p_trunkid > 0) THEN
										 t.TrunkID=p_trunkid AND
										 a.CompanyId=p_companyid AND a.IsCustomer=1 
									 
								   WHEN (p_ratetableId > 0) THEN
									    r.RateTableId=p_ratetableId AND
									    a.CompanyId=p_companyid AND a.IsCustomer=1
								   ELSE						
										 a.CompanyId=p_companyid AND a.IsCustomer=1 					
									
								END
								AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
								AND a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
								AND a.CurrencyId=p_currency  AND t.`Status`=1 ;
								 
								
							
								
								
					ELSE
					
								
								select distinct a.AccountName,r.RateTableName,t.Trunk, 
								CASE WHEN (ct.`Status`=1) THEN 'Active' ELSE 'Inactive' END As Statuss from tblAccount a
								join tblTrunk t
								left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
								left join tblRateTable r on ct.RateTableID=r.RateTableId
								where 
								CASE 
									WHEN (p_ratetableId > 0 AND p_trunkid > 0) THEN
									    r.TrunkID=p_trunkid AND
										 r.RateTableId=p_ratetableId AND
									    a.CompanyId=p_companyid  
									    
									WHEN (p_trunkid > 0) THEN
										 r.TrunkID=p_trunkid AND
										 a.CompanyId=p_companyid 
									 
								   WHEN (p_ratetableId > 0) THEN
									    r.RateTableId=p_ratetableId AND
									    a.CompanyId=p_companyid 
								   ELSE						
										 a.CompanyId=p_companyid				
									
								END
								AND (p_AccountIds='' OR FIND_IN_SET(a.AccountID,p_AccountIds) != 0 )
								AND a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
								AND a.CurrencyId=p_currency AND t.`Status`=1
								
								ORDER BY						
								CASE
									WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
								END ASC,
								CASE
											WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
								END DESC,
								CASE
									WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameASC') THEN r.RateTableName
								END ASC,
								CASE
											WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InBoundRateTableNameDESC') THEN r.RateTableName
								END DESC,
								CASE
									WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutBoundRateTableNameASC') THEN t.Trunk
								END ASC,
								CASE
											WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutBoundRateTableNameDESC') THEN t.Trunk 
								END DESC
								;
								
								
					END IF;				
						
											
					
			END IF;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;










DROP PROCEDURE IF EXISTS `prc_applyRateTableTomultipleAccByTrunk`;
DELIMITER //
CREATE  PROCEDURE `prc_applyRateTableTomultipleAccByTrunk`(
	IN `p_companyid` INT,
	IN `p_AccountIds` TEXT,
	IN `p_ratetableId` INT,
	IN `p_CreatedBy` VARCHAR(100),
	IN `p_checkedAll` VARCHAR(1),
	IN `p_select_trunkId` INT,
	IN `p_select_currency` INT,
	IN `p_select_ratetableid` INT,
	IN `p_select_accounts` TEXT
)
BEGIN

	DECLARE v_codeDeckId int;
	

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT CodeDeckId INTO v_codeDeckId FROM  tblRateTable WHERE RateTableId = p_ratetableId;

	DROP TEMPORARY TABLE IF EXISTS tmp;
		CREATE TEMPORARY TABLE tmp (
			CustomerTrunkID INT ,
			CompanyID INT ,
			AccountID INT,
			TrunkID INT ,
			RateTableID INT,
			CreatedBy VARCHAR(100)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_insert;
		CREATE TEMPORARY TABLE tmp_insert (
			CompanyID INT ,
			AccountID INT,
			TrunkID INT ,
			RateTableID INT,
			CreatedBy VARCHAR(100)
	);
		
			
         IF ( p_checkedAll = 'Y')  THEN
         
         	
				
					DROP TEMPORARY TABLE IF EXISTS tmp_allaccountByfilter;
						CREATE TEMPORARY TABLE tmp_allaccountByfilter (
							AccountID INT,
							RateTableID INT,
							TrunkID INT 
					);
         
					/* Get All Acount by filter (p_select_trunkId,p_select_currency,p_select_ratetableid,p_select_accounts)  */
				
						insert into tmp_allaccountByfilter (AccountID,RateTableID,TrunkID)					
						select distinct a.AccountID,r.RateTableId,t.TrunkID from tblAccount a
									join tblTrunk t
									left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
									left join tblRateTable r on ct.RateTableID=r.RateTableId
									where 
									CASE 
										WHEN (p_select_ratetableid > 0 AND p_select_trunkId > 0) THEN
										    r.TrunkID=p_select_trunkId AND r.RateTableId=p_select_ratetableid AND a.CompanyId=p_companyid  
										    
										WHEN (p_select_trunkId > 0) THEN
											 t.TrunkID=p_select_trunkId AND a.CompanyId=p_companyid 
										 
									   WHEN (p_select_ratetableid > 0) THEN
										    r.RateTableId=p_select_ratetableid AND a.CompanyId=p_companyid 
									   ELSE						
											 a.CompanyId=p_companyid				
										
									END
									AND (p_select_accounts='' OR FIND_IN_SET(a.AccountID,p_select_accounts) != 0 )
									AND a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
									AND a.CurrencyId=p_select_currency AND t.`Status`=1;								
								
					-- select * from tmp_allaccountByfilter;
					
					insert into tmp (CustomerTrunkID,AccountID,TrunkID,RateTableID)
					select distinct ct.CustomerTrunkID,a.AccountID,taf.TrunkID,ct.RateTableID
					from tblAccount a
					join tblTrunk t
					left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
					left join tblRateTable r on ct.RateTableID=r.RateTableId
					right join tmp_allaccountByfilter taf on a.AccountID=taf.AccountID
					where a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1 AND t.TrunkID=taf.TrunkID;
					
					select * from  tmp;
					 
					select * from  tmp where CustomerTrunkID IS NULL;
					select * from  tmp where CustomerTrunkID IS NOT NULL;
												
			
					
					
			ELSE
			
				
							/* Start the selected Account ratetable apply */
			
			
			
							DROP TEMPORARY TABLE IF EXISTS temp_acc_trunk;
								CREATE TEMPORARY TABLE temp_acc_trunk (
									Account_Trunk text,
									AccountID INT,
									TrunkID INT
							);
							DROP TEMPORARY TABLE IF EXISTS tmp_seprateAccountandTrunk;
								CREATE TEMPORARY TABLE tmp_seprateAccountandTrunk (
									Account_Trunk text,
									AccountID INT,
									TrunkID INT
							);
							
							/* Insert all comma seprate account with trunk in Account_Trunk field from Parameter "p_AccountIds" like 
							( Account_Trunk  AccountID   TrunkID
							  5009_7           NULL      NULL
							  5009_3           NULL      NULL
							  5009_5           NULL      NULL
							) */							
							set @sql = concat("insert into temp_acc_trunk (Account_Trunk) values ('", replace(( select distinct p_AccountIds as data), ",", "'),('"),"');");
							prepare stmt1 from @sql;
							execute stmt1;	
							
							
			
							/* Spit(_) Account with trunk field in AccountID and TrunkID like and insert into table (tmp_seprateAccountandTrunk)
								Account_Trunk  AccountID   TrunkID
							   5009_7           5009      7
							   5009_3           5009      3
							   5009_5           5009      5
							*/							
							insert into tmp_seprateAccountandTrunk (Account_Trunk,AccountID,TrunkID)
							SELECT   Account_Trunk , SUBSTRING_INDEX(Account_Trunk,'_',1) AS AccountID , SUBSTRING_INDEX(Account_Trunk,'_',-1) AS TrunkID FROM temp_acc_trunk;
							
							
							insert into tmp (CustomerTrunkID,AccountID,TrunkID,RateTableID)
							select distinct ct.CustomerTrunkID,a.AccountID,taf.TrunkID,ct.RateTableID
							from tblAccount a
							join tblTrunk t
							left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
							left join tblRateTable r on ct.RateTableID=r.RateTableId
							right join tmp_seprateAccountandTrunk taf on a.AccountID=taf.AccountID
							where a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1 AND t.TrunkID=taf.TrunkID;
							
															
				
							
			END IF;
			
			
			
			
			
			DELETE ct FROM tblCustomerRate ct
			inner join tmp on ct.TrunkID = tmp.TrunkID AND ct.CustomerID=tmp.AccountID;
			
		
			
			insert into tblCustomerTrunk (AccountID,RateTableID,CodeDeckId,CompanyID,TrunkID,IncludePrefix,RoutinePlanStatus,UseInBilling,Status,CreatedBy)
			select 
			sep_acc.AccountID,
			p_ratetableId as RateTableID,
			v_codeDeckId as CodeDeckId,
			p_companyid as CompanyID,
			sep_acc.TrunkID as TrunkID,
			0 as IncludePrefix,
			0 as RoutinePlanStatus,
			0 as UseInBilling,
			1 as Status,			
			p_CreatedBy as CreatedBy
			from tmp sep_acc
			left join tblCustomerTrunk ct on sep_acc.TrunkID = ct.TrunkID AND sep_acc.AccountID = ct.AccountID
			where sep_acc.CustomerTrunkID IS NULL;
			
			
			
			update tblCustomerTrunk 
			right join tmp sep_tmp on tblCustomerTrunk.AccountID=sep_tmp.AccountID AND tblCustomerTrunk.TrunkID=sep_tmp.TrunkID
			set tblCustomerTrunk.RateTableID=p_ratetableId,CodeDeckId=v_codeDeckId,tblCustomerTrunk.`Status`=1, ModifiedBy=p_CreatedBy
			where  tblCustomerTrunk.CustomerTrunkID IS NOT NULL;	
								
		  
		    

						

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;






DROP PROCEDURE IF EXISTS `prc_applyRateTableTomultipleAccByService`;
DELIMITER //
CREATE  PROCEDURE `prc_applyRateTableTomultipleAccByService`(
	IN `p_companyid` INT,
	IN `p_AccountIds` TEXT,
	IN `p_InboundRateTable` INT,
	IN `p_OutboundRateTable` INT,
	IN `p_CreatedBy` VARCHAR(100),
	IN `p_InBoundRateTableChecked` VARCHAR(5),
	IN `p_OutBoundRateTableChecked` VARCHAR(5),
	IN `p_checkedAll` VARCHAR(1),
	IN `p_select_serviceId` INT,
	IN `p_select_currency` INT,
	IN `p_select_ratetableid` INT,
	IN `p_select_accounts` TEXT
)
BEGIN

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
		DROP TEMPORARY TABLE IF EXISTS tmp_service_check;
				CREATE TEMPORARY TABLE tmp_service_check (
					ServiceID INT ,
					AccountID INT,
					CompanyID INT
				);
		
		
		DROP TEMPORARY TABLE IF EXISTS tmp_service;
				CREATE TEMPORARY TABLE tmp_service (
					ServiceID INT ,
					AccountID INT,
					CompanyID INT
				);
		
		DROP TEMPORARY TABLE IF EXISTS tmp_service_already;
				CREATE TEMPORARY TABLE tmp_service_already (
					CompanyID INT ,
					AccountID INT,
					ServiceID INT ,
					RateTableID INT,
					InRateTableName VARCHAR(100),
					Type INT
				);
		
		DROP TEMPORARY TABLE IF EXISTS tmp_tarrif;
				CREATE TEMPORARY TABLE tmp_tarrif (
					CompanyID INT ,
					AccountID INT,
					ServiceID INT ,
					RateTableID INT,
					InRateTableName VARCHAR(100),
					Type INT
				);
				

			IF (p_checkedAll = 'N') 
			THEN 
						
						/* Update As per selected acconut */
						
								
						DROP TEMPORARY TABLE IF EXISTS temp_acc_trunk;
							CREATE TEMPORARY TABLE temp_acc_trunk (
								Account_Service text,
								AccountID INT,
								ServiceID INT
						);
						DROP TEMPORARY TABLE IF EXISTS tmp_seprateAccountandService;
							CREATE TEMPORARY TABLE tmp_seprateAccountandService (
								Account_Service text,
								AccountID INT,
								ServiceID INT
						);
						
						/* Insert all comma seprate account with trunk in Account_Trunk field from Parameter "p_AccountIds" like 
						( Account_Trunk  AccountID   TrunkID
						  5009_7           NULL      NULL
						  5009_3           NULL      NULL
						  5009_5           NULL      NULL
						) */							
						set @sql = concat("insert into temp_acc_trunk (Account_Service) values ('", replace(( select distinct p_AccountIds as data), ",", "'),('"),"');");
						prepare stmt1 from @sql;
						execute stmt1;	
						
						
		
						/* Spit(_) Account with trunk field in AccountID and TrunkID like and insert into table (tmp_seprateAccountandTrunk)
							Account_Trunk  AccountID   TrunkID
						   5009_7           5009      7
						   5009_3           5009      3
						   5009_5           5009      5
						*/							
						insert into tmp_seprateAccountandService (Account_Service,AccountID,ServiceID)
						SELECT   Account_Service , SUBSTRING_INDEX(Account_Service,'_',1) AS AccountID , SUBSTRING_INDEX(Account_Service,'_',-1) AS ServiceID FROM temp_acc_trunk;
						
						
						
						
						insert into tmp_service_already (ServiceID,AccountID,CompanyID)  						                     
						select 
						ServiceID,
						AccountID,
						p_companyid as CompanyID
						from tmp_seprateAccountandService sep_acc ;
						
						
						
						
						
						 if(p_InBoundRateTableChecked = 'on') THEN
								
								/* Insert OR Update RateTable Inbound Ratetable */
								
							   update tblAccountTariff t
								inner join tmp_service_already act on t.CompanyID=act.CompanyID AND t.ServiceID=act.ServiceID AND t.AccountID=act.AccountID
								set t.RateTableID=p_InboundRateTable,t.updated_at=NOW()
								where t.`Type`=2 AND t.ServiceID=act.ServiceID; 
								
								               		
								insert into tmp_tarrif (CompanyID,AccountID,ServiceID,RateTableID,Type)
								select distinct
								p_companyid as CompanyID,
								t.AccountID as AccountID,
								t.ServiceID as ServiceID,
								p_InboundRateTable as RateTableID,
								2 as Type
								from tmp_service_already t 
								left join tblAccountTariff on t.AccountID = tblAccountTariff.AccountID  AND tblAccountTariff.ServiceID=t.ServiceID
								where tblAccountTariff.AccountTariffID is null ;
								
								
								insert into tblAccountTariff (CompanyID,AccountID,ServiceID,RateTableID,Type,created_at)
								select distinct
								p_companyid as CompanyID,
								AccountID as AccountID,
								ServiceID,
								p_InboundRateTable as RateTableID,
								2 as Type,
								NOW() as created_at
								from tmp_tarrif;
							
							
						 END IF;
		
		
						 if(p_OutBoundRateTableChecked = 'on') THEN
						
								/* Insert OR Update RateTable OutBound Ratetable */
							
								update tblAccountTariff t
								inner join tmp_service_already act on t.CompanyID=act.CompanyID AND t.ServiceID=act.ServiceID AND t.AccountID=act.AccountID
								set t.RateTableID=p_OutboundRateTable,t.updated_at=NOW()
								where t.`Type`=1 AND t.ServiceID=act.ServiceID;
								
						
									
								
								insert into tmp_tarrif (CompanyID,AccountID,ServiceID,RateTableID,Type)
								select distinct
								p_companyid as CompanyID,
								t.AccountID as AccountID,
								t.ServiceID as ServiceID,
								p_OutboundRateTable as RateTableID,
								1 as Type
								from tmp_service_already t 
								left join tblAccountTariff on t.AccountID = tblAccountTariff.AccountID  AND tblAccountTariff.ServiceID=t.ServiceID AND tblAccountTariff.`Type`=1
								where tblAccountTariff.AccountTariffID is null ;
								
								select * from tmp_tarrif;
								
								insert into tblAccountTariff (CompanyID,AccountID,ServiceID,RateTableID,Type,created_at)
								select distinct
								p_companyid as CompanyID,
								AccountID as AccountID,
								ServiceID,
								p_OutboundRateTable as RateTableID,
								1 as Type,
								NOW() as created_at
								from tmp_tarrif;
							
							
						 END IF; 
						
						
						
						
			ELSE
			
			
			
			
				
					/* Upload or insert All Accounts as per filter like selectall( All pages ) */
						
					DROP TEMPORARY TABLE IF EXISTS tmp_allaccountByfilter;
							CREATE TEMPORARY TABLE tmp_allaccountByfilter (
								AccountID INT,
								ServiceID INT
						);
						
					 insert into tmp_allaccountByfilter (AccountID,ServiceID)						
							select distinct a.AccountID as AccountID,s.ServiceID as ServiceID
							from  tblAccount a 
							left join tblAccountService ass  on a.AccountID=ass.AccountID
							left join tblService s on ass.ServiceID=s.ServiceID				
							left join tblAccountTariff att on a.AccountID = att.AccountID AND s.ServiceID = att.ServiceID 								
							where 
							CASE 
								  WHEN (p_select_serviceId > 0 AND p_select_ratetableid > 0) THEN				
							
										att.RateTableID=p_select_ratetableid AND ass.ServiceID=p_select_serviceId AND a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
							
								  WHEN (p_select_serviceId > 0) THEN				
										ass.ServiceID=p_select_serviceId AND a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
										
								  WHEN (p_select_ratetableid > 0) THEN
								      a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
								  		
							     ELSE
								   	a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1	
							END
							AND  (p_select_accounts='' OR FIND_IN_SET(a.AccountID,p_select_accounts) != 0 ) AND  a.CurrencyId=p_select_currency	;
						
					select * from tmp_allaccountByfilter;
									
						
						
						insert into tmp_service_already (ServiceID,AccountID,CompanyID)  						                     
						select 
						ServiceID,
						AccountID,
						p_companyid as CompanyID
						from tmp_allaccountByfilter sep_acc ;
						
						
								
						
						
						if(p_InBoundRateTableChecked =  'on') THEN
							
								
							   update tblAccountTariff t
								inner join tmp_service_already act on t.CompanyID=act.CompanyID AND t.ServiceID=act.ServiceID AND t.AccountID=act.AccountID
								set t.RateTableID=p_InboundRateTable,t.updated_at=NOW()
								where t.`Type`=2 AND t.ServiceID=act.ServiceID; 
								
								               		
								insert into tmp_tarrif (CompanyID,AccountID,ServiceID,RateTableID,Type)
								select distinct
								p_companyid as CompanyID,
								t.AccountID as AccountID,
								t.ServiceID as ServiceID,
								p_InboundRateTable as RateTableID,
								2 as Type
								from tmp_service_already t 
								left join tblAccountTariff on t.AccountID = tblAccountTariff.AccountID  AND tblAccountTariff.ServiceID=t.ServiceID
								inner join tmp_allaccountByfilter af on t.AccountID= af.AccountID
								where tblAccountTariff.AccountTariffID is null ;
								
								
								insert into tblAccountTariff (CompanyID,AccountID,ServiceID,RateTableID,Type,created_at)
								select distinct
								p_companyid as CompanyID,
								AccountID as AccountID,
								ServiceID,
								p_InboundRateTable as RateTableID,
								2 as Type,
								NOW() as created_at
								from tmp_tarrif;
							
							
						END IF;
		
		
						if(p_OutBoundRateTableChecked = 'on') THEN
							
								update tblAccountTariff t
								inner join tmp_service_already act on t.CompanyID=act.CompanyID AND t.ServiceID=act.ServiceID AND t.AccountID=act.AccountID
								set t.RateTableID=p_OutboundRateTable,t.updated_at=NOW()
								where t.`Type`=1 AND t.ServiceID=act.ServiceID;
								
						
									
								
								insert into tmp_tarrif (CompanyID,AccountID,ServiceID,RateTableID,Type)
								select distinct
								p_companyid as CompanyID,
								t.AccountID as AccountID,
								t.ServiceID as ServiceID,
								p_OutboundRateTable as RateTableID,
								1 as Type
								from tmp_service_already t 
								left join tblAccountTariff on t.AccountID = tblAccountTariff.AccountID  AND tblAccountTariff.ServiceID=t.ServiceID AND tblAccountTariff.`Type`=1
								inner join tmp_allaccountByfilter af on t.AccountID= af.AccountID
								where tblAccountTariff.AccountTariffID is null ;
								
								select * from tmp_tarrif;
								
								insert into tblAccountTariff (CompanyID,AccountID,ServiceID,RateTableID,Type,created_at)
								select distinct
								p_companyid as CompanyID,
								AccountID as AccountID,
								ServiceID,
								p_OutboundRateTable as RateTableID,
								1 as Type,
								NOW() as created_at
								from tmp_tarrif;
							
							
						 END IF; 
					
					 
					
					
					
					
					
					
					
			END IF;
		
		
                        
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


-- vishal start

INSERT INTO `tblCronJobCommand` ( `CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES ( 1, NULL, 'Read Email for AutoRateImport', 'reademailsautoimport', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, NULL, NULL);



/* === END === Apply RateTable for multiple Account Task Procedure 3 ( prc_getRateTableByAccount , prc_applyRateTableTomultipleAccByTrunk , prc_applyRateTableTomultipleAccByService ) */