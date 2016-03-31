CREATE ALGORITHM=UNDEFINED DEFINER=`neon-user`@`104.47.140.143` SQL SECURITY DEFINER VIEW `vwOldRMBillingRevenueByAccountManager` AS select concat(max(`u`.`FirstName`),' ',max(`u`.`LastName`)) AS `AccountManager`,`a`.`AccountName` AS `AccountName`,sum(`inv`.`GrandTotal`) AS `TotalRevenue`,week(`inv`.`IssueDate`,0) AS `Week`,year(`inv`.`IssueDate`) AS `Year` from ((`RateManagement4`.`tblAccount` `a` left join `RMBilling3`.`tblInvoice` `inv` on(((`inv`.`AccountID` = `a`.`AccountID`) and (`inv`.`InvoiceType` = 1) and (`inv`.`IssueDate` >= date_format((now() + interval -(12) week),'%Y%m%d'))))) join `RateManagement4`.`tblUser` `u` on((`a`.`Owner` = `u`.`UserID`))) where ((`a`.`AccountType` = 1) and (`a`.`IsCustomer` = 1) and (`a`.`CompanyId` = 1) and (`a`.`Status` = 1)) group by `a`.`Owner`,`a`.`AccountName`,week(`inv`.`IssueDate`,0),year(`inv`.`IssueDate`)