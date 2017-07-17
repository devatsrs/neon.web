use NeonBillingDev;

-- Dumping structure for table LocalBillingDev.tblTempProduct
CREATE TABLE IF NOT EXISTS `tblTempProduct` (
  `ProductID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) DEFAULT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `Amount` decimal(18,2) DEFAULT NULL,
  `Active` tinyint(3) unsigned DEFAULT '1',
  `Note` longtext COLLATE utf8_unicode_ci,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ProductID`),
  KEY `IX_ProcessID` (`ProcessID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table LocalBillingDev.tblTempDynamicFieldsValue
CREATE TABLE IF NOT EXISTS `tblTempDynamicFieldsValue` (
  `DynamicFieldsValueID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ParentID` int(11) NOT NULL DEFAULT '0',
  `DynamicFieldsID` int(11) NOT NULL DEFAULT '0',
  `FieldValue` text COLLATE utf8_unicode_ci,
  `FieldOrder` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`DynamicFieldsValueID`),
  UNIQUE KEY `IXUnique_ParentID_DynamicFieldsID` (`ParentID`,`DynamicFieldsID`),
  KEY `IX_ParentID_DynamicFieldsID` (`DynamicFieldsID`,`ParentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for procedure LocalBillingDev.prc_WSProcessItemUpload
DROP PROCEDURE IF EXISTS `prc_WSProcessItemUpload`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_WSProcessItemUpload`(
	IN `p_processId` VARCHAR(50),
	IN `p_companyId` INT
	)
BEGIN
   	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE totalexistingcode INT(11) DEFAULT 0;
	DECLARE duplicate_c_records INT DEFAULT 0;
	DECLARE dynamic_columns_count INT DEFAULT 0;
	DECLARE dynamic_column_type VARCHAR(20) DEFAULT 'product';
	DECLARE duplicate_f_records INT DEFAULT 0;
		
	SET sql_mode = '';	    
   	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   	SET SESSION sql_mode='';
    
	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
   	CREATE TEMPORARY TABLE tmp_JobLog_  ( 
			Message longtext     
   	);
    
	-- delete duplicate Code record
	SELECT COUNT(*) INTO duplicate_c_records FROM (SELECT count(Code)
		FROM tblTempProduct 
		GROUP BY Code
		HAVING COUNT(*)>1) AS tbl;
		
	IF duplicate_c_records > 0
	THEN
		INSERT INTO tmp_JobLog_ (Message)
			  SELECT DISTINCT 
			  CONCAT( 'Duplicate Code in excel file - (',c_duplicate_count,' occurences) - ', Code)
			  		FROM(
						SELECT count(Code) AS c_duplicate_count, Code AS Code
						FROM tblTempProduct 
						GROUP BY Code
						HAVING COUNT(*)>1) AS tbl;
	END IF;
    
	DELETE n1,fv
		FROM tblTempProduct n1
		INNER JOIN (
			SELECT MIN(ProductID) as minid,Code FROM tblTempProduct WHERE ProcessID = p_processId
			GROUP BY Code
			HAVING COUNT(1)>1
		) n2 ON n2.Code = n1.Code AND minid <> n1.ProductID
		LEFT JOIN tblTempDynamicFieldsValue AS fv
		ON fv.ParentID = n1.ProductID
		WHERE n1.ProcessID = p_processId;

	-- check unique code
	SELECT 
		count(ttp1.Code) INTO totalexistingcode
	FROM 
		tblTempProduct ttp1
	LEFT JOIN
		tblProduct ttp2 ON ttp1.Code = ttp2.Code
	WHERE
		ttp1.Code = ttp2.Code;

	IF totalexistingcode > 0
	THEN
		INSERT INTO tmp_JobLog_ (Message)
			  SELECT DISTINCT 
			  CONCAT( 'Existing Code - ', Code)
			  		FROM(
						SELECT 
							ttp3.Code AS Code
						FROM 
							tblTempProduct ttp3
						LEFT JOIN
							tblProduct ttp4 ON ttp3.Code = ttp4.Code
						WHERE
							ttp3.Code = ttp4.Code) AS tbl;
	END IF;
	
	-- check if there is any dynamic columns for product table
	SELECT count(*) INTO dynamic_columns_count FROM NeonRMDev.tblDynamicFields WHERE Type = dynamic_column_type AND Status = 1;

	IF dynamic_columns_count > 0
	THEN
		SELECT COUNT(*) INTO duplicate_f_records FROM (SELECT count(FieldValue)
			FROM 
				tblTempDynamicFieldsValue 
			WHERE
				DynamicFieldsID IN (
					SELECT
						f.DynamicFieldsID
					FROM 
						NeonRMDev.tblDynamicFields AS f
					LEFT JOIN
						NeonRMDev.tblDynamicFieldsDetail AS fd
					ON
						f.DynamicFieldsID = fd.DynamicFieldsID
					WHERE 
						f.Type = dynamic_column_type AND 
						f.Status = 1 AND
						fd.FieldType = 'is_unique' AND
						fd.Options = 1
				)
			GROUP BY FieldValue,DynamicFieldsID
			HAVING COUNT(*)>1) AS tbl;
			
		IF duplicate_f_records > 0
		THEN
			INSERT INTO tmp_JobLog_ (Message)
			  	SELECT DISTINCT 
				  	CONCAT( 'Duplicate ',FieldName,' in excel file - (',f_duplicate_count,' occurences) - ', FieldValue)
				  		FROM(
							SELECT 
								count(fv.FieldValue) AS f_duplicate_count, fv.FieldValue AS FieldValue, f.FieldName AS FieldName
							FROM 
								NeonBillingDev.tblTempDynamicFieldsValue AS fv
							LEFT JOIN
								NeonRMDev.tblDynamicFields AS f
							ON
								fv.DynamicFieldsID = f.DynamicFieldsID
							WHERE
								fv.DynamicFieldsID IN (
									SELECT
										f1.DynamicFieldsID
									FROM 
										NeonRMDev.tblDynamicFields AS f1
									LEFT JOIN
										NeonRMDev.tblDynamicFieldsDetail AS fd
									ON
										f1.DynamicFieldsID = fd.DynamicFieldsID
									WHERE 
										f1.Type = dynamic_column_type AND 
										f1.Status = 1 AND
										fd.FieldType = 'is_unique' AND
										fd.Options = 1
								)
							GROUP BY fv.FieldValue,fv.DynamicFieldsID
							HAVING COUNT(*)>1) AS tbl;
			-- if dynamic column is unique than delete all duplicate records from temp table
			DELETE fv1, p
				FROM NeonBillingDev.tblTempDynamicFieldsValue fv1 
				INNER JOIN (
					SELECT MIN(DynamicFieldsValueID) AS minid, DynamicFieldsID, FieldValue FROM NeonBillingDev.tblTempDynamicFieldsValue
					WHERE ProcessID = p_processId
			     	GROUP BY FieldValue,DynamicFieldsID
					HAVING COUNT(1) > 1
				) AS fv2
			   ON (fv2.FieldValue = fv1.FieldValue
			   AND fv1.DynamicFieldsID = fv2.DynamicFieldsID
			   AND fv2.minid <> fv1.DynamicFieldsValueID)
			   INNER JOIN
					NeonBillingDev.tblTempProduct AS p
				ON
					fv1.ParentID = p.ProductID
				LEFT JOIN
					NeonRMDev.tblDynamicFields AS f
				ON
					fv1.DynamicFieldsID = f.DynamicFieldsID
				WHERE
					fv1.DynamicFieldsID IN (
						SELECT
							f1.DynamicFieldsID
						FROM 
							NeonRMDev.tblDynamicFields AS f1
						LEFT JOIN
							NeonRMDev.tblDynamicFieldsDetail AS fd
						ON
							f1.DynamicFieldsID = fd.DynamicFieldsID
						WHERE 
							f1.Type = dynamic_column_type AND 
							f1.Status = 1 AND
							fd.FieldType = 'is_unique' AND
							fd.Options = 1
					)
				AND
					fv1.ProcessID = p_processId;

		END IF;
	END IF;

	-- check unique dynamic column (if exist in tblDynamicFieldsValue)
	SELECT 
		count(fv1.FieldValue) INTO duplicate_f_records
	FROM 
		NeonBillingDev.tblTempDynamicFieldsValue fv1
	LEFT JOIN
		NeonRMDev.tblDynamicFieldsValue fv2 
	ON 
		fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
		fv1.FieldValue = fv2.FieldValue
	WHERE
		fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
		fv1.FieldValue = fv2.FieldValue AND
		fv1.DynamicFieldsID IN (
								SELECT
									f1.DynamicFieldsID
								FROM 
									NeonRMDev.tblDynamicFields AS f1
								LEFT JOIN
									NeonRMDev.tblDynamicFieldsDetail AS fd
								ON
									f1.DynamicFieldsID = fd.DynamicFieldsID
								WHERE 
									f1.Type = dynamic_column_type AND 
									f1.Status = 1 AND
									fd.FieldType = 'is_unique' AND
									fd.Options = 1
							);

	IF duplicate_f_records > 0
	THEN
		INSERT INTO tmp_JobLog_ (Message)
			  SELECT DISTINCT 
			  CONCAT( 'Existing ',FieldName,' - ', FieldValue)
			  		FROM(
						SELECT 
							fv1.FieldValue AS FieldValue, f.FieldName AS FieldName
						FROM 
							NeonBillingDev.tblTempDynamicFieldsValue fv1
						LEFT JOIN
							NeonRMDev.tblDynamicFieldsValue fv2
						ON 
							fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
							fv1.FieldValue = fv2.FieldValue
						LEFT JOIN
							NeonRMDev.tblDynamicFields AS f
						ON
							fv1.DynamicFieldsID = f.DynamicFieldsID
						WHERE
							fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
							fv1.FieldValue = fv2.FieldValue AND
							fv1.DynamicFieldsID IN (
													SELECT
														f1.DynamicFieldsID
													FROM 
														NeonRMDev.tblDynamicFields AS f1
													LEFT JOIN
														NeonRMDev.tblDynamicFieldsDetail AS fd
													ON
														f1.DynamicFieldsID = fd.DynamicFieldsID
													WHERE 
														f1.Type = dynamic_column_type AND 
														f1.Status = 1 AND
														fd.FieldType = 'is_unique' AND
														fd.Options = 1
												)
						) AS tbl;
	END IF;

	-- delete duplicate data from temp table which is already exist in main table (dynamic column which is unique)
	DELETE
		fv1, p
	FROM
		NeonBillingDev.tblTempDynamicFieldsValue fv1
	LEFT JOIN
		NeonRMDev.tblDynamicFieldsValue fv2
	ON 
		fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
		fv1.FieldValue = fv2.FieldValue
	LEFT JOIN
		NeonRMDev.tblDynamicFields AS f
	ON
		fv1.DynamicFieldsID = f.DynamicFieldsID
	INNER JOIN
		NeonBillingDev.tblTempProduct AS p
	WHERE
		fv1.ParentID = p.ProductID AND
		fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
		fv1.FieldValue = fv2.FieldValue AND
		fv1.DynamicFieldsID IN (
								SELECT
									f1.DynamicFieldsID
								FROM 
									NeonRMDev.tblDynamicFields AS f1
								LEFT JOIN
									NeonRMDev.tblDynamicFieldsDetail AS fd
								ON
									f1.DynamicFieldsID = fd.DynamicFieldsID
								WHERE 
									f1.Type = 'product' AND 
									f1.Status = 1 AND
									fd.FieldType = 'is_unique' AND
									fd.Options = 1
							);

	-- dynamic column insert
	INSERT INTO
		NeonRMDev.tblDynamicFieldsValue (`CompanyId`,`ParentID`,`DynamicFieldsID`,`FieldValue`,`created_at`,`created_by`)
	SELECT
		ttdfv.CompanyId,ttdfv.ParentID,ttdfv.DynamicFieldsID,ttdfv.FieldValue,ttdfv.created_at,ttdfv.created_by
	FROM
		tblTempDynamicFieldsValue ttdfv
	LEFT JOIN
		tblTempProduct ttp3 ON ttp3.ProductID = ttdfv.ParentID
	LEFT JOIN
		tblProduct ttp4 ON ttp3.Code = ttp4.Code
	WHERE
		ttp3.ProductID = ttdfv.ParentID AND
		ttp3.ProcessID = ttdfv.ProcessID AND
		ttp4.Code IS NULL AND
		ttdfv.ProcessID = p_processId;

	-- product insert
	INSERT INTO 
		tblProduct (`CompanyId`,`Name`,`Code`,`Description`,`Amount`,`Active`,`Note`,`created_at`,`CreatedBy`)
	SELECT 
		tp3.CompanyId,tp3.Name,tp3.Code,tp3.Description,tp3.Amount,tp3.Active,tp3.Note,tp3.created_at,tp3.Created_By 
	FROM 
		tblTempProduct tp3
	LEFT JOIN 
		tblProduct tp2 ON tp3.Code = tp2.Code
	WHERE 
		tp2.Code IS NULL AND ProcessID = p_processId;

		
	SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	UPDATE 
		NeonRMDev.tblDynamicFieldsValue tdfv
	LEFT JOIN
		tblTempProduct ttp ON tdfv.ParentID = ttp.ProductID
	LEFT JOIN
		tblProduct tp ON tp.Code = ttp.Code
	SET 
		ParentID = tp.ProductID
	WHERE
		tp.Name = ttp.Name AND 
		tp.Code = ttp.Code AND 
		tp.CompanyId = ttp.CompanyId AND 
		tp.Description = ttp.Description AND 
		tp.Amount = ttp.Amount AND
		tp.Note = ttp.Note AND
		tp.created_at = ttp.created_at AND
		tp.CreatedBy = ttp.created_by AND
		ttp.ProcessID = p_processId AND
		tdfv.ParentID = ttp.ProductID;
	

		INSERT INTO tmp_JobLog_ (Message)
		SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded!' );	
		
		DELETE  FROM tblTempProduct WHERE ProcessID = p_processId;
		DELETE  FROM tblTempDynamicFieldsValue WHERE ProcessID = p_processId;
		
		SELECT * from tmp_JobLog_; 					
	      
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END




-- Dumping structure for procedure NeonBillingDev.prc_getProductByBarCode
DROP PROCEDURE IF EXISTS `prc_getProductByBarCode`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getProductByBarCode`(
	IN `df_fieldvalue` LONGTEXT,
	IN `df_dynamicfieldsid` INT
)
BEGIN
	
	SELECT 
		`P`.`ProductID`, `P`.`Name`, `P`.`Description`, `P`.`Amount` 
		FROM 
			`NeonBillingDev`.`tblProduct` AS `P` 
		LEFT JOIN 
			`NeonRMDev`.`tblDynamicFieldsValue` AS `B` 
		ON 
			`P`.`ProductID` = `B`.`ParentID` 
		WHERE 
			`B`.`FieldValue` = df_fieldvalue AND 
			`B`.`DynamicFieldsID` = df_dynamicfieldsid;
	
END//
DELIMITER ;