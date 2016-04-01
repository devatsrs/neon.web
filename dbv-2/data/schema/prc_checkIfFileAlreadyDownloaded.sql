CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkIfFileAlreadyDownloaded`(IN `p_CompanyGatewayID` INT, IN `p_filenames` LONGTEXT)
BEGIN

 select  filename from tblUsageDownloadFiles where CompanyGatewayID = p_CompanyGatewayID and FIND_IN_SET( filename ,p_filenames) > 0 ; 

END