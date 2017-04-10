USE `Ratemanagement3`;

INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('RecurringProfile.Add', '1');
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('RecurringProfile.Edit', '1');
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('RecurringProfile.Delete', '1');
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('RecurringProfile.View', '1');
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('RecurringProfile.All', '1');


UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.Add' limit 1) WHERE  `ResourceName`='RecurringInvoice.create';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.Add' limit 1) WHERE  `ResourceName`='RecurringInvoice.store';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.Edit' limit 1) WHERE  `ResourceName`='RecurringInvoice.edit';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.update' limit 1) WHERE  `ResourceName`='RecurringInvoice.update';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.Delete' limit 1) WHERE  `ResourceName`='RecurringInvoice.delete';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.index';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.recurringinvoicelog';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.startstop';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.getAccountInfo';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.getBillingClassInfo';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.generate';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.ajax_datagrid';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.ajax_recurringinvoicelog_datagrid';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='RecurringProfile.View' limit 1) WHERE  `ResourceName`='RecurringInvoice.calculate_total';

DROP PROCEDURE IF EXISTS `prc_GetCronJobHistory`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCronJobHistory`(
	IN `p_CronJobID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_SearchText` VARCHAR(50),
	IN `p_Status` VARCHAR(50),
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

    IF p_isExport = 0
    THEN
            SELECT
                tblCronJobCommand.Title,
                tblCronJobLog.CronJobStatus,
                tblCronJobLog.Message,
                tblCronJobLog.created_at
            FROM tblCronJob
            INNER JOIN tblCronJobLog
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
                AND tblCronJobLog.CronJobID = p_CronJobID
                AND (p_Status = '' OR tblCronJobLog.CronJobStatus = p_Status)
                AND tblCronJobLog.created_at between p_StartDate and p_EndDate
                AND (p_SearchText='' OR tblCronJobLog.Message like Concat('%',p_SearchText,'%'))
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblCronJobCommand.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblCronJobCommand.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusDESC') THEN tblCronJobLog.CronJobStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusASC') THEN tblCronJobLog.CronJobStatus
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageDESC') THEN tblCronJobLog.Message
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageASC') THEN tblCronJobLog.Message
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblCronJobLog.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblCronJobLog.created_at
                END ASC
           LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblCronJobLog.CronJobID) as totalcount
        FROM tblCronJob
            INNER JOIN tblCronJobLog
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
                AND tblCronJobLog.CronJobID = p_CronJobID
                AND (p_Status = '' OR tblCronJobLog.CronJobStatus = p_Status)
                AND tblCronJobLog.created_at between p_StartDate and p_EndDate
                AND (p_SearchText='' OR tblCronJobLog.Message like Concat('%',p_SearchText,'%'))
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            tblCronJobCommand.Title,
            tblCronJobLog.CronJobStatus,
            tblCronJobLog.Message,
            tblCronJobLog.created_at
        FROM tblCronJob
        INNER JOIN tblCronJobLog
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
                AND tblCronJobLog.CronJobID = p_CronJobID
                AND (p_Status = '' OR tblCronJobLog.CronJobStatus = p_Status)
                AND tblCronJobLog.created_at between p_StartDate and p_EndDate
                AND (p_SearchText='' OR tblCronJobLog.Message like Concat('%',p_SearchText,'%'))
        INNER JOIN tblCronJobCommand
            ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID;
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;