CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_GetFileUploadTemplates`(
	IN `p_CompanyID` INT,
	IN `p_Title` VARCHAR(255),
	IN `p_Type` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_IsExport` INT


)
BEGIN
	DECLARE v_OffSet_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	IF p_IsExport = 0
	THEN
		SELECT
			t.`Title` AS TemplateName,
			tt.`Title` AS TemplateType,
			t.`created_at`,
			t.`FileUploadTemplateID`
		FROM
			tblFileUploadTemplate t
		LEFT JOIN
			tblFileUploadTemplateType tt ON t.FileUploadTemplateTypeID=tt.FileUploadTemplateTypeID
		WHERE
			t.CompanyID = p_CompanyID AND
			(p_Title IS NULL OR t.Title LIKE REPLACE(p_Title, '*', '%')) AND
			(p_Type IS NULL OR t.FileUploadTemplateTypeID = p_Type)
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN t.Title
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN t.Title
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeASC') THEN tt.Title
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeDESC') THEN tt.Title
			END DESC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		-- total counts for datatable
		SELECT
			COUNT(t.Title) AS totalcount
		FROM
			tblFileUploadTemplate t
		LEFT JOIN
			tblFileUploadTemplateType tt ON t.FileUploadTemplateTypeID=tt.FileUploadTemplateTypeID
		WHERE
			t.CompanyID = p_CompanyID AND
			(p_Title IS NULL OR t.Title LIKE REPLACE(p_Title, '*', '%')) AND
			(p_Type IS NULL OR t.FileUploadTemplateTypeID = p_Type);
		
	END IF;
	
	IF p_IsExport = 1
	THEN
		SELECT
			t.`Title` AS TemplateName,
			tt.`Title` AS TemplateType,
			t.`created_at`
		FROM
			tblFileUploadTemplate t
		LEFT JOIN
			tblFileUploadTemplateType tt ON t.FileUploadTemplateTypeID=tt.FileUploadTemplateTypeID
		WHERE
			t.CompanyID = p_CompanyID;
	END IF;
	
END