CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getFilesTobeDownload`(IN `p_CompanyGatewayID` INT, IN `p_filenames` LONGTEXT)
BEGIN

	
select  FileName from tblUsageDownloadFiles where CompanyGatewayID = p_CompanyGatewayID and ( FIND_IN_SET( FileName ,p_filenames) = 0 ); 

END