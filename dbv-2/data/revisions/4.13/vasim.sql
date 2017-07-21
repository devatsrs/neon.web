use RMBilling3;

CREATE TABLE IF NOT EXISTS `tblTempProduct` (
  `ProductID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) DEFAULT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `Amount` decimal(18,2) DEFAULT NULL,
  `Active` tinyint(3) unsigned DEFAULT '1',
  `Note` longtext COLLATE utf8_unicode_ci,
  `BarCode` longtext COLLATE utf8_unicode_ci,
  `Change` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ProductID`),
  KEY `IX_ProcessID` (`ProcessID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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


DROP PROCEDURE IF EXISTS `prc_WSProcessItemUpload`;
DELIMITER |
CREATE PROCEDURE `prc_WSProcessItemUpload`(
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
	DECLARE current_datetime DATETIME DEFAULT NOW();
	DECLARE updated_records INT DEFAULT 0;
		
	SET sql_mode = '';	    
   	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   	SET SESSION sql_mode='';
    
	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
   	CREATE TEMPORARY TABLE tmp_JobLog_  ( 
			Message longtext     
   	);
    
	-- starts delete duplicate Code record from temp table
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
	-- ends delete duplicate Code record from temp table
	
	-- starts disable products which has delete action in csv or excel file
	UPDATE tblProduct p
	LEFT JOIN tblTempProduct tp ON tp.Code=p.Code
	SET p.Active=0, p.updated_at=current_datetime, p.ModifiedBy='system'
	WHERE tp.Code=p.Code AND tp.Change='D' AND tp.ProcessID=p_processId;

	-- log updated status records
	SELECT COUNT(*) INTO updated_records
		FROM tblProduct p
		LEFT JOIN tblTempProduct tp ON p.Code=tp.Code
		WHERE tp.Code=p.Code AND tp.Change='D' AND tp.ProcessID=p_processId;
		
	IF updated_records > 0
	THEN
		INSERT INTO tmp_JobLog_ (Message) VALUES(CONCAT(updated_records, ' Item(s) has been deleted!'));
	END IF;
	-- ends disable products which has delete action in csv or excel file

	-- starts delete all duplicate records from temp table if dynamic column is unique
	-- check if there is any dynamic columns for product table
	SELECT count(*) INTO dynamic_columns_count FROM Ratemanagement3.tblDynamicFields WHERE Type = dynamic_column_type AND Status = 1;

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
						Ratemanagement3.tblDynamicFields AS f
					LEFT JOIN
						Ratemanagement3.tblDynamicFieldsDetail AS fd
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
								tblTempDynamicFieldsValue AS fv
							LEFT JOIN
								Ratemanagement3.tblDynamicFields AS f
							ON
								fv.DynamicFieldsID = f.DynamicFieldsID
							WHERE
								fv.DynamicFieldsID IN (
									SELECT
										f1.DynamicFieldsID
									FROM 
										Ratemanagement3.tblDynamicFields AS f1
									LEFT JOIN
										Ratemanagement3.tblDynamicFieldsDetail AS fd
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
				FROM tblTempDynamicFieldsValue fv1 
				INNER JOIN (
					SELECT MIN(DynamicFieldsValueID) AS minid, DynamicFieldsID, FieldValue FROM tblTempDynamicFieldsValue
					WHERE ProcessID = p_processId
			     	GROUP BY FieldValue,DynamicFieldsID
					HAVING COUNT(1) > 1
				) AS fv2
			   ON (fv2.FieldValue = fv1.FieldValue
			   AND fv1.DynamicFieldsID = fv2.DynamicFieldsID
			   AND fv2.minid <> fv1.DynamicFieldsValueID)
			   INNER JOIN
					tblTempProduct AS p
				ON
					fv1.ParentID = p.ProductID
				LEFT JOIN
					Ratemanagement3.tblDynamicFields AS f
				ON
					fv1.DynamicFieldsID = f.DynamicFieldsID
				WHERE
					fv1.DynamicFieldsID IN (
						SELECT
							f1.DynamicFieldsID
						FROM 
							Ratemanagement3.tblDynamicFields AS f1
						LEFT JOIN
							Ratemanagement3.tblDynamicFieldsDetail AS fd
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
	-- ends delete all duplicate records from temp table if dynamic column is unique

	-- starts check unique dynamic column and delete it if exist in tblDynamicFieldsValue
	-- check unique dynamic column (if exist in tblDynamicFieldsValue)
	SELECT 
		count(fv1.FieldValue) INTO duplicate_f_records
	FROM 
		tblTempDynamicFieldsValue fv1
	LEFT JOIN
		Ratemanagement3.tblDynamicFieldsValue fv2 
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
									Ratemanagement3.tblDynamicFields AS f1
								LEFT JOIN
									Ratemanagement3.tblDynamicFieldsDetail AS fd
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
							tblTempDynamicFieldsValue fv1
						LEFT JOIN
							Ratemanagement3.tblDynamicFieldsValue fv2
						ON 
							fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
							fv1.FieldValue = fv2.FieldValue
						LEFT JOIN
							Ratemanagement3.tblDynamicFields AS f
						ON
							fv1.DynamicFieldsID = f.DynamicFieldsID
						WHERE
							fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
							fv1.FieldValue = fv2.FieldValue AND
							fv1.DynamicFieldsID IN (
													SELECT
														f1.DynamicFieldsID
													FROM 
														Ratemanagement3.tblDynamicFields AS f1
													LEFT JOIN
														Ratemanagement3.tblDynamicFieldsDetail AS fd
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
		tblTempDynamicFieldsValue fv1
	LEFT JOIN
		Ratemanagement3.tblDynamicFieldsValue fv2
	ON 
		fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
		fv1.FieldValue = fv2.FieldValue
	LEFT JOIN
		Ratemanagement3.tblDynamicFields AS f
	ON
		fv1.DynamicFieldsID = f.DynamicFieldsID
	INNER JOIN
		tblTempProduct AS p
	WHERE
		fv1.ParentID = p.ProductID AND
		fv1.DynamicFieldsID = fv2.DynamicFieldsID AND
		fv1.FieldValue = fv2.FieldValue AND
		fv1.DynamicFieldsID IN (
								SELECT
									f1.DynamicFieldsID
								FROM 
									Ratemanagement3.tblDynamicFields AS f1
								LEFT JOIN
									Ratemanagement3.tblDynamicFieldsDetail AS fd
								ON
									f1.DynamicFieldsID = fd.DynamicFieldsID
								WHERE 
									f1.Type = 'product' AND 
									f1.Status = 1 AND
									fd.FieldType = 'is_unique' AND
									fd.Options = 1
							);
	-- ends check unique dynamic column and delete it if exist in tblDynamicFieldsValue

	-- start product update if already exist
	UPDATE 
		tblProduct p
	LEFT JOIN
		tblTempProduct tp ON tp.Code = p.Code
	SET 
		p.Name=tp.Name,p.Description=tp.Description,p.Amount=tp.Amount,p.Active=tp.Active,p.Note=tp.Note,p.ModifiedBy='system',p.updated_at=current_datetime
	WHERE 
		tp.Code = p.Code AND  tp.Change!='D' AND tp.ProcessID = p_processId;
	-- ends product update if already exist

	-- starts count and log updated records
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
			  CONCAT(record_to_update, ' Records updated!')
			  		FROM(
						SELECT 
							count(ttp3.Code) AS record_to_update
						FROM 
							tblTempProduct ttp3
						LEFT JOIN
							tblProduct ttp4 ON ttp3.Code = ttp4.Code
						WHERE
							ttp3.Code = ttp4.Code) AS tbl;
	END IF;
	-- ends count and log updated records

	-- start insert dynamic columns if not exist of item to be updated
	INSERT INTO
		Ratemanagement3.tblDynamicFieldsValue (`CompanyId`,`ParentID`,`DynamicFieldsID`,`FieldValue`,`created_at`,`created_by`)
	SELECT
		ttdfv.CompanyId,ttp4.ProductID,ttdfv.DynamicFieldsID,ttdfv.FieldValue,ttdfv.created_at,ttdfv.created_by
	FROM
		tblTempDynamicFieldsValue ttdfv
	LEFT JOIN
		tblTempProduct ttp3 ON ttp3.ProductID = ttdfv.ParentID
	LEFT JOIN
		tblProduct ttp4 ON ttp3.Code = ttp4.Code
	WHERE
		NOT EXISTS (
		    SELECT * FROM Ratemanagement3.tblDynamicFieldsValue WHERE ParentID = ttp4.ProductID
		) AND
		ttp3.ProductID = ttdfv.ParentID AND
		ttp3.ProcessID = ttdfv.ProcessID AND
		ttp3.Code = ttp4.Code AND
		ttdfv.ProcessID = p_processId;
	-- ends insert dynamic columns if not exist of item to be updated

	-- start update dynamic columns if exist of item to be updated
	DROP TEMPORARY TABLE IF EXISTS tmp_DynamicFieldsValue_;
	CREATE TEMPORARY TABLE tmp_DynamicFieldsValue_  ( 
		ProductID INT,
		DynamicFieldsID INT,
		FieldValue LONGTEXT,
		ProcessID  LONGTEXT
	);

	INSERT INTO tmp_DynamicFieldsValue_ (ProductID,DynamicFieldsID,FieldValue,ProcessID)
	SELECT
		ttp4.ProductID,ttdfv.DynamicFieldsID,ttdfv.FieldValue,ttdfv.ProcessID
	FROM
		tblTempDynamicFieldsValue ttdfv
	LEFT JOIN
		tblTempProduct ttp3 ON ttp3.ProductID=ttdfv.ParentID
	LEFT JOIN
		tblProduct ttp4 ON ttp3.Code=ttp4.Code
	WHERE
		EXISTS (
		    SELECT * FROM Ratemanagement3.tblDynamicFieldsValue WHERE ParentID=ttp4.ProductID AND DynamicFieldsID=ttdfv.DynamicFieldsID
		) AND
		ttp3.ProductID=ttdfv.ParentID AND
		ttp3.ProcessID=ttdfv.ProcessID AND
		ttp3.Code=ttp4.Code AND
		ttdfv.ProcessID=p_processId;

	UPDATE
		Ratemanagement3.tblDynamicFieldsValue fv
	LEFT JOIN
		tmp_DynamicFieldsValue_ tfv
	ON 
		tfv.ProductID=fv.ParentID AND tfv.DynamicFieldsID=fv.DynamicFieldsID
	SET
		fv.FieldValue=tfv.FieldValue,fv.updated_at=current_datetime,fv.updated_by='system'
	WHERE
		tfv.ProductID=fv.ParentID AND tfv.DynamicFieldsID=fv.DynamicFieldsID AND tfv.ProcessID=p_processId;
	-- ends update dynamic columns if exist of item to be updated


	-- starts dynamic column insert of products to be inserted
	INSERT INTO
		Ratemanagement3.tblDynamicFieldsValue (`CompanyId`,`ParentID`,`DynamicFieldsID`,`FieldValue`,`created_at`,`created_by`)
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
	-- ends dynamic column insert of products to be inserted

	-- start product insert
	INSERT INTO 
		tblProduct (`CompanyId`,`Name`,`Code`,`Description`,`Amount`,`Active`,`Note`,`created_at`,`CreatedBy`,`ModifiedBy`,`updated_at`)
	SELECT 
		tp3.CompanyId,tp3.Name,tp3.Code,tp3.Description,tp3.Amount,tp3.Active,tp3.Note,tp3.created_at,tp3.Created_By,tp3.Created_By,tp3.created_at
	FROM 
		tblTempProduct tp3
	LEFT JOIN 
		tblProduct tp2 ON tp3.Code = tp2.Code
	WHERE 
		tp2.Code IS NULL AND ProcessID = p_processId;
	-- ends product insert

	SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	INSERT INTO tmp_JobLog_ (Message)
	SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded!' );	
	
	-- starts dynamic column update ParentID of inserted products
	UPDATE 
		Ratemanagement3.tblDynamicFieldsValue tdfv
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
	-- ends dynamic column update ParentID of inserted products


		DELETE  FROM tblTempProduct WHERE ProcessID = p_processId;
		DELETE  FROM tblTempDynamicFieldsValue WHERE ProcessID = p_processId;
		
		SELECT * from tmp_JobLog_; 					
	      
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getProductByBarCode`;
DELIMITER |
CREATE PROCEDURE `prc_getProductByBarCode`(
	IN `p_fieldvalue` LONGTEXT,
	IN `p_dynamicfieldsid` INT
)
BEGIN
	
	SELECT 
		`P`.`ProductID`, `P`.`Name`, `P`.`Description`, `P`.`Amount` 
		FROM 
			`tblProduct` AS `P` 
		LEFT JOIN 
			`Ratemanagement3`.`tblDynamicFieldsValue` AS `B` 
		ON 
			`P`.`ProductID` = `B`.`ParentID` 
		WHERE 
			`B`.`FieldValue` = p_fieldvalue AND 
			`B`.`DynamicFieldsID` = p_dynamicfieldsid;
	
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getProducts`;
DELIMITER |
CREATE PROCEDURE `prc_getProducts`(
	IN `p_CompanyID` INT,
	IN `p_Name` VARCHAR(50),
	IN `p_Code` VARCHAR(50),
	IN `p_Active` VARCHAR(1),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT

)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
			tblProduct.ProductID,
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Amount,
			tblProduct.updated_at,
			tblProduct.Active,
			tblProduct.Description,
			tblProduct.Note
            from tblProduct
            where tblProduct.CompanyID = p_CompanyID
			AND(p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active))
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountDESC') THEN Amount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountASC') THEN Amount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN tblProduct.updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN tblProduct.updated_at
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveDESC') THEN Active
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveASC') THEN Active
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(tblProduct.ProductID) AS totalcount
            from tblProduct
            where tblProduct.CompanyID = p_CompanyID
			AND(p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active));

	ELSE

			SELECT
			tblProduct.ProductID,
			tblProduct.Name,
			tblProduct.Code,
			tblProduct.Amount,
			tblProduct.updated_at,
			tblProduct.Active,
			tblProduct.Description,
			tblProduct.Note
            from tblProduct
			where tblProduct.CompanyID = p_CompanyID
			AND(p_Name ='' OR tblProduct.Name like Concat('%',p_Name,'%'))
            AND((p_Code ='' OR tblProduct.Code like CONCAT(p_Code,'%')))
            AND((p_Active = '' OR tblProduct.Active = p_Active));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END|
DELIMITER ;