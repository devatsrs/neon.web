<?php

Route::group(array('before' => 'auth'), function () {

    Route::any('customer/dashboard', array("as" => "dashboardCustomer", "uses" => "DashboardCustomerController@home"));
	Route::any('customer/monitor', array("as" => "monitorCustomer", "uses" => "DashboardCustomerController@monitor_dashboard"));
	Route::any('customer/analysis', "AnalysisController@customer_index");
	Route::any('customer/vendor_analysis', "AnalysisController@vendor_index");
    Route::any('customer/invoice_expense_chart', 'DashboardCustomerController@invoice_expense_chart');
    Route::any('customer/invoice_expense_total', 'DashboardCustomerController@invoice_expense_total');
	Route::any('customer/subscriptions', 'DashboardCustomerController@subscriptions');	
	Route::any('customer/subscription/ajax_datagrid', 'DashboardCustomerController@subscriptions_ajax_datagrid');	
    Route::any('customer/getoutstandingamount', 'ProfileController@get_outstanding_amount');
    //Invoice
    Route::any('customer/invoice', 'InvoicesCustomerController@index');
    Route::any('customer/invoice/ajax_datagrid/{type}', 'InvoicesCustomerController@ajax_datagrid');
    //Route::any('customer/invoice/{id}/print_preview', 'InvoicesCustomerController@print_preview'); Not in use.
    //Route::any('customer/invoice/{id}/print', 'InvoicesCustomerController@pdf_view');
    Route::any('customer/invoice/pay_now', 'InvoicesCustomerController@pay_now');
    Route::any('customer/invoice/download_invoice_file/{id}', 'InvoicesCustomerController@download_invoice_file');
	Route::any('customer/invoice/ajax_datagrid_total', 'InvoicesCustomerController@ajax_datagrid_total');
	Route::any('customer/invoice/getInvoiceDetail', 'InvoicesCustomerController@getInvoiceDetail');

    //payment
    Route::any('customer/payments', 'PaymentsCustomerController@index');
    Route::any('customer/payments/create', 'PaymentsCustomerController@create');
    Route::any('customer/payments/ajax_datagrid/{type}', 'PaymentsCustomerController@ajax_datagrid');
    Route::any('customer/payments/ajax_datagrid_total', 'PaymentsCustomerController@ajax_datagrid_total');


	//serverinfo
	Route::any('serverinfo', 'ServerController@index');	

    //Account Statement

    Route::any('customer/account_statement', 'AccountStatementCustomerController@index');
    Route::any('customer/account_statement/payment', 'AccountStatementCustomerController@getPayment');
    Route::any('customer/account_statement/ajax_datagrid', 'AccountStatementCustomerController@ajax_datagrid');
    Route::any('customer/account_statement/exports/{type}', 'AccountStatementCustomerController@exports');

    //credit card
    Route::any('customer/PaymentMethodProfiles/paynow', 'PaymentProfileCustomerController@paynow');
    Route::any('/customer/PaymentMethodProfiles', 'PaymentProfileCustomerController@index');
    Route::any('/customer/PaymentMethodProfiles/create', 'PaymentProfileCustomerController@create');
    Route::any('/customer/PaymentMethodProfiles/{id}/delete', 'PaymentProfileCustomerController@delete');
    Route::any('/customer/PaymentMethodProfiles/update', 'PaymentProfileCustomerController@update');
    Route::any('/customer/PaymentMethodProfiles/ajax_datagrid', 'PaymentProfileCustomerController@ajax_datagrid');
    Route::any('/customer/PaymentMethodProfiles/{id}/set_default', 'PaymentProfileCustomerController@set_default');
    Route::any('/customer/PaymentMethodProfiles/{id}/card_status/{active_deactive}', array('as' => 'payment_rules', 'uses' => 'PaymentProfileCustomerController@card_active_deactive'))->where('active_deactive', '(active|deactive)');

	//cdr

	Route::any('customer/cdr', 'CDRCustomerController@index');
	Route::any('customer/cdr/ajax_datagrid/{type}', 'CDRCustomerController@ajax_datagrid');
	Route::any('customer/cdr/ajax_datagrid_total', 'CDRCustomerController@ajax_datagrid_total');

	//commercial

	Route::any('customer/customers_rates', 'RateCustomerController@settings');
	Route::any('customer/customers_rates/{id}/search_ajax_datagrid/{type}', 'RateCustomerController@search_ajax_datagrid');
	Route::any('customer/customers_rates/rate', 'RateCustomerController@index');
	Route::any('customer/customers_rates/inboundrate', 'RateCustomerController@inboundrate');
	Route::any('customer/customers_rates/{id}/search_inbound_ajax_datagrid/{type}', 'RateCustomerController@search_inbound_ajax_datagrid');

    //Role
    Route::any('/roles', array("as" => "users", "uses" => "RoleController@index"));
    Route::any('/roles/storerole', "RoleController@storerole");
    Route::any('/roles/storepermission', "RoleController@storepermission");
    Route::any('/roles/create', "RoleController@store");
    Route::any('/roles/edit/{id}', array('as' => 'edit_role', 'uses' => 'RoleController@edit'));
    Route::any('/roles/update', array('as' => 'role_update', 'uses' => 'RoleController@update'));
    Route::any('/roles/{id}/delete/', array('as' => 'role_delete', 'uses' => 'RoleController@delete'));
    Route::any('/roles/ajax_datagrid', 'RoleController@ajax_datagrid');
    Route::any('/roles/ajax_user_list/{where}', array('as' => 'ajax_group_list', 'uses' => 'RoleController@ajax_user_list'))->where('where', '(user|role|resource)');
    Route::any('/roles/ajax_role_list/{where}', array('as' => 'ajax_user_list', 'uses' => 'RoleController@ajax_role_list'))->where('where', '(user|role|resource)');
    Route::any('/roles/ajax_resource_list/{where}', array('as' => 'ajax_actions_list', 'uses' => 'RoleController@ajax_resource_list'))->where('where', '(user|role|resource)');

    //Profile
    Route::any('customer/profile', array('as' => 'profile_show', 'uses' => 'ProfileController@show'));
    Route::any('customer/profile/edit', array('as' => 'profile_edit', 'uses' => 'ProfileController@edit'));
    Route::any('customer/profile/update', array('as' => 'profile_update', 'uses' => 'ProfileController@update'));
	//User
	Route::any('users', array("as" => "users", "uses" => "UsersController@index"));
	Route::any('/users/add', "UsersController@add");
	Route::any('/users/store', "UsersController@store");
	Route::any('users/edit/{id}', array('as' => 'edit_user', 'uses' => 'UsersController@edit'));
	Route::any('/users/update/{id}', array('as' => 'user_update', 'uses' => 'UsersController@update'));
	Route::any('/users/exports/{type}', 'UsersController@exports');
	Route::any('users/ajax_datagrid/{type}', 'UsersController@ajax_datagrid');
	Route::any('users/edit_profile/{id}', 'UsersController@edit_profile');
	Route::any('users/update_profile/{id}', 'UsersController@update_profile');


	//DashBoard
    Route::get('/process_redirect',"HomeController@process_redirect");
	Route::get('/dashboard', array("as" => "dashboard", "uses" => "DashboardController@home"));
	Route::any('/salesdashboard', array("as" => "salesdashboard", "uses" => "DashboardController@salesdashboard"));
    Route::any('/billingdashboard', "DashboardController@billingdashboard");
	Route::post('/dashboard/GetUsersTasks', "DashboardController@GetUsersTasks");	
	Route::post('/dashboard/getpiplelinepata', "DashboardController@GetPipleLineData");		
	Route::post('/dashboard/getSalesdata', "DashboardController@getSalesdata");		
	
	Route::post('/dashboard/getSalesdataManager', "DashboardController@GetSalesDataManager");		
	
	
	Route::post('/dashboard/GetForecastData', "DashboardController@GetForecastData");		
	
	
	
	
	Route::any('/monitor', "DashboardController@monitor_dashboard");
	Route::any('/crmdashboard', "DashboardController@CrmDashboard");
    Route::any('/dashboard/ajax_get_recent_due_sheets', "DashboardController@ajax_get_recent_due_sheets");
    Route::any('/dashboard/ajax_get_recent_leads', "DashboardController@ajax_get_recent_leads");
    Route::any('/dashboard/ajax_get_jobs', "DashboardController@ajax_get_jobs");
    Route::any('/dashboard/ajax_get_processed_files', "DashboardController@ajax_get_processed_files");
    Route::any('/dashboard/ajax_get_recent_accounts', "DashboardController@ajax_get_recent_accounts");
    Route::any('/dashboard/ajax_get_missing_accounts', "DashboardController@ajax_get_missing_accounts");
	Route::any('/crmdashboard/ajax_opportunity_grid', 'DashboardController@GetOpportunites');
	
	Route::any('/crmdashboard/ajax_task_grid', 'DashboardController@GetUsersTasks');

	//new Dashboards ajax
	Route::any('/getHourlyData', "ChartDashboardController@getHourlyData");
	Route::any('/getReportData', "ChartDashboardController@getReportData");


	//Trunk
	Route::any('trunk/edit/{id}', array('as' => 'edit_trunk', 'uses' => 'TrunkController@edit'));
	Route::any('trunks/update/{id}', array('as' => 'update_trunk', 'uses' => 'TrunkController@update'));
	Route::any('trunks/store', array('as' => 'store_trunk', 'uses' => 'TrunkController@store'));
	Route::any('trunks/create', array('as' => 'create_trunk', 'uses' => 'TrunkController@create'));
	Route::any('trunks/ajax_datagrid', 'TrunkController@ajax_datagrid');
	Route::any('trunks/exports/{type}', 'TrunkController@exports');
	Route::resource('trunks', 'TrunkController');
	Route::controller('trunks', 'TrunkController');


	//CodeDecks
	Route::any('/codedecks/store', array('as' => 'codedecks_store', 'uses' => 'CodeDecksController@store'));
	Route::any('/codedecks/update/{id}', array('as' => 'codedecks_update', 'uses' => 'CodeDecksController@update'));
	Route::any('codedecks/upload', array('as' => 'codedecks_upload', 'uses' => 'CodeDecksController@upload'));
	Route::any('codedecks/ajax_datagrid', 'CodeDecksController@ajax_datagrid');
	Route::any('codedecks/exports/{type}', 'CodeDecksController@exports');
	Route::any('codedecks/delete_all', 'CodeDecksController@delete_all');
	Route::any('codedecks/delete_selected', 'CodeDecksController@delete_selected');
	Route::any('codedecks/update_selected', 'CodeDecksController@update_selected');
	Route::any('codedecks/download_sample_excel_file', 'CodeDecksController@download_sample_excel_file');
	Route::any('codedecks/cretecodedeck', 'CodeDecksController@cretecodedeck');
	Route::any('codedecks/{id}/delete', 'CodeDecksController@delete');
	//base codedecks
	Route::any('codedecks/base_datagrid', 'CodeDecksController@base_datagrid');
	Route::any('codedecks/basecodedeck/{id}', 'CodeDecksController@basecodedeck');
	Route::any('codedecks/updatecodedeck/{id}', 'CodeDecksController@updatecodedeck');
	Route::any('codedecks/{id}/base_delete', 'CodeDecksController@base_delete');
	Route::any('codedecks/base_exports/{type}', 'CodeDecksController@base_exports');
	Route::resource('codedecks', 'CodeDecksController');
	Route::controller('codedecks', 'CodeDecksController');

	//Account
	Route::any('/accounts/store', array('as' => 'accounts_store', 'uses' => 'AccountsController@store'));
	Route::any('/accounts/update/{id}', array('as' => 'accounts_update', 'uses' => 'AccountsController@update'));	
	Route::any('/accounts/{id}/show', array('uses' => 'AccountsController@show'));
	Route::post('/accounts/{id}/GetTimeLineSrollData/{scroll}', array('as' => 'GetTimeLineSrollData', 'uses' => 'AccountsController@GetTimeLineSrollData'));
	Route::any('/task/create', 'TaskController@create');

	Route::post('/account/upload_file', 'AccountsController@uploadFile');
	Route::any('/account/delete_actvity_attachment_file', 'AccountsController@deleteUploadFile');

	Route::any('/task/GetTask', 'TaskController@GetTask');
	Route::any('/task/{id}/delete_task', 'TaskController@delete_task');
	Route::any('/account/upload_file', 'AccountsController@upload_file');
	Route::any('/accounts/get_note', 'AccountsController@get_note');
	Route::any('/account/note/update', 'AccountsController@update_note');	
	Route::any('/account/delete_actvity_attachment_file', 'AccountsController@delete_upload_file');
	Route::any('/accounts/delete_task_prent', 'AccountsController@Delete_task_parent');
	Route::any('/accounts/update_bulk_account_status', 'AccountsController@UpdateBulkAccountStatus');
	
	

	Route::any('/accounts/{id}/store_note', array('as' => 'accounts_storenote', 'uses' => 'AccountsController@store_note'));
	Route::any('/accounts/{id}/delete_note', array('as' => 'accounts_delete_note', 'uses' => 'AccountsController@delete_note'));
    Route::any('/accounts/{id}/createOpportunity', array('as' => 'opportunity_create', 'uses' => 'AccountsController@createOpportunity'));
	Route::any('accounts/upload/{id}', 'AccountsController@upload');
	Route::any('accounts/download_doc/{id}', 'AccountsController@download_doc');
	Route::any('accounts/download_doc_file/{id}', 'AccountsController@download_doc_file');
	Route::any('accounts/delete_doc/{id}', 'AccountsController@delete_doc');
	Route::any('accounts/ajax_datagrid/{type}', 'AccountsController@ajax_datagrid');
	Route::any('accounts/exports', 'AccountsController@exports');
	Route::any('accounts/due_ratesheet', 'AccountsController@due_ratesheet');
	Route::any('accounts/ajax_datagrid_sheet/{type}', 'AccountsController@ajax_datagrid_sheet');
    Route::any('accounts/{id}/ajax_datagrid_PaymentProfiles', 'AccountsController@ajax_datagrid_PaymentProfiles');
	Route::any('accounts/addbillingaccount', 'AccountsController@addbillingaccount');
	Route::any('accounts/{id}/change_verifiaction_status/{status}', 'AccountsController@change_verifiaction_status')->where('status', '(.[09]*)+');;
    Route::any('accounts/getoutstandingamount/{id}', 'AccountsController@get_outstanding_amount');
    Route::any('accounts/paynow/{id}', 'AccountsController@paynow');
    Route::any('accounts/{id}/ajax_template', 'AccountsController@ajax_template');
    Route::any('accounts/{id}/ajax_getEmailTemplate/{template_type}', 'AccountsController@ajax_getEmailTemplate')->where('template_type', '(.[09]*)+');
    Route::any('accounts/bulk_mail', 'AccountsController@bulk_mail');
    Route::any('accounts/validate_cli', 'AccountsController@validate_cli');
    Route::any('accounts/validate_ip', 'AccountsController@validate_ip');
	Route::any('/accounts/bulk_tags', 'AccountsController@bulk_tags');
	Route::any('accounts/authenticate/{id}', 'AuthenticationController@authenticate');
	Route::any('accounts/authenticate_store', 'AuthenticationController@authenticate_store');
	Route::any('account/get_credit/{id}', 'AccountsController@get_credit');
	Route::any('account/update_credit', 'AccountsController@update_credit');
	Route::any('account/ajax_datagrid_credit/{type}', 'AccountsController@ajax_datagrid_credit');
    Route::any('accounts/{id}/addips', 'AuthenticationController@addipclis');
    Route::any('accounts/{id}/deleteips', 'AuthenticationController@deleteips');
    Route::any('accounts/{id}/addclis', 'AuthenticationController@addipclis');
    Route::any('accounts/{id}/deleteclis', 'AuthenticationController@deleteclis');
	Route::any('accounts/activity/{id}', 'AccountsController@expense');
	Route::any('accounts/expense_chart', 'AccountsController@expense_chart');
	Route::any('accounts/expense_top_destination/{id}', 'AccountsController@expense_top_destination');
	Route::any('accounts/unbilledreport/{id}', 'AccountsController@unbilledreport');
	Route::any('accounts/activity_pdf_download/{id}', 'AccountsController@activity_pdf_download');

	//Account Subscription
	Route::any('accounts/{id}/subscription/ajax_datagrid', 'AccountSubscriptionController@ajax_datagrid');
	Route::any('accounts/{id}/subscription/store', 'AccountSubscriptionController@store');
	Route::any('accounts/{id}/subscription/{subscription_id}/update', 'AccountSubscriptionController@update')->where('subscription_id', '(.[09]*)+');
	Route::any('accounts/{id}/subscription/{subscription_id}/delete', 'AccountSubscriptionController@delete')->where('subscription_id', '(.[09]*)+');

    //Account One of charge
    Route::any('accounts/{id}/oneofcharge/ajax_datagrid', 'AccountOneOffChargeController@ajax_datagrid');
    Route::any('accounts/{id}/oneofcharge/store', 'AccountOneOffChargeController@store');
    Route::any('accounts/{id}/oneofcharge/{oneofcharge_id}/update', 'AccountOneOffChargeController@update')->where('oneofcharge_id', '(.[09]*)+');
    Route::any('accounts/{id}/oneofcharge/{oneofcharge_id}/delete', 'AccountOneOffChargeController@delete')->where('oneofcharge_id', '(.[09]*)+');
    Route::any('accounts/{id}/oneofcharge/{oneofcharge_product_id}/ajax_getproductinfo', 'AccountOneOffChargeController@ajax_getProductInfo')->where('oneofcharge_id', '(.[09]*)+');

    //Account Activity
    Route::any('accounts/{id}/activities/ajax_datagrid', 'AccountActivityController@ajax_datagrid');

    Route::any('accounts/{id}/activities/store', 'AccountActivityController@store');
    Route::any('accounts/{id}/activities/{activity_id}/update', 'AccountActivityController@update')->where('activity_id', '(.[09]*)+');
    Route::any('accounts/{id}/activities/{activity_id}/delete', 'AccountActivityController@delete')->where('activity_id', '(.[09]*)+');


    //Account email log
    Route::any('accounts/{id}/activities/ajax_datagrid_email_log', 'AccountActivityController@ajax_datagrid_email_log');
    Route::any('accounts/{id}/activities/sendemail', 'AccountActivityController@sendMail');
	Route::any('accounts/{id}/activities/sendemail/api', 'AccountActivityController@sendMailApi');
    Route::any('accounts/{id}/activities/{log_id}/view_email_log', 'AccountActivityController@view_email_log')->where('log_id', '(.[09]*)+');
    Route::any('accounts/{id}/activities/{log_id}/delete_email_log', 'AccountActivityController@delete_email_log')->where('activity_id', '(.[09]*)+');
    Route::any('emails/{id}/getattachment/{attachmentID}', 'AccountActivityController@getAttachment');


    Route::any('/accounts/{id}/convert', array('as' => 'accounts_convert', 'uses' => 'AccountsController@convert'));
	Route::any('/accounts/{id}/update_inbound_rate_table',  'AccountsController@update_inbound_rate_table');

	//import account
	Route::any('/import/account',  'ImportsController@index');
	Route::any('/import/account/check_upload',  'ImportsController@check_upload');
	Route::any('/import/account/ajaxfilegrid',  'ImportsController@ajaxfilegrid');
	Route::any('/import/account/storeTemplate',  'ImportsController@storeTemplate');
	Route::any('/import/account/getAccountInfoFromGateway/{id}/{gateway}',  'ImportsController@getAccountInfoFromGateway');
	Route::any('/import/account/ajax_get_missing_gatewayaccounts',  'ImportsController@ajax_get_missing_gatewayaccounts');
	Route::any('/import/account/download_sample_excel_file',  'ImportsController@download_sample_excel_file');
	Route::any('/import/account/add_missing_gatewayaccounts',  'ImportsController@add_missing_gatewayaccounts');

	//import leads
	Route::any('/import/leads',  'ImportsController@import_leads');
	Route::any('/import/leads/leads_check_upload',  'ImportsController@leads_check_upload');
	Route::any('/import/leads/leads_ajaxfilegrid',  'ImportsController@leads_ajaxfilegrid');
	Route::any('/import/leads/leads_storeTemplate',  'ImportsController@leads_storeTemplate');
	Route::any('/import/leads/leads_download_sample_excel_file',  'ImportsController@leads_download_sample_excel_file');

	Route::resource('accounts', 'AccountsController');
	Route::controller('accounts', 'AccountsController');


	//Account Statement

	Route::any('account_statement', 'AccountStatementController@index');
	Route::any('account_statement/payment', 'AccountStatementController@getPayment');
	Route::any('account_statement/ajax_datagrid', 'AccountStatementController@ajax_datagrid');
	Route::any('account_statement/exports/{type}', 'AccountStatementController@exports');

    //EmailTemplate

    Route::any('email_template', 'EmailTemplateController@index');
	Route::any('email_template/{id}/update', 'EmailTemplateController@update');
    Route::any('email_template/{id}/edit', 'EmailTemplateController@edit');
    Route::any('email_template/{id}/delete', 'EmailTemplateController@delete');
    Route::any('email_template/store', 'EmailTemplateController@store');
	Route::any('email_template/ajax_datagrid', 'EmailTemplateController@ajax_datagrid');
	Route::any('email_template/exports/{type}', 'EmailTemplateController@exports');

	//Leads
	//Leads
	Route::any('/leads/store', array('as' => 'leads_store', 'uses' => 'LeadsController@store'));
	Route::any('/leads/update/{id}', array('as' => 'leads_update', 'uses' => 'LeadsController@update'));
	Route::any('/leads/{id}/show', array('as' => 'accounts_show', 'uses' => 'LeadsController@show'));
	Route::any('/leads/{id}/store_note', array('as' => 'accounts_storenote', 'uses' => 'LeadsController@store_note'));
	Route::any('/leads/{id}/delete_note', array('as' => 'accounts_delete_note', 'uses' => 'LeadsController@delete_note'));
	Route::any('/leads/{id}/convert', array('as' => 'accounts_convert', 'uses' => 'LeadsController@convert'));
    Route::any('/leads/{id}/createOpportunity', array('as' => 'opportunity_create', 'uses' => 'LeadsController@createOpportunity'));
    Route::any('/leads/{id}/ajax_template', 'LeadsController@ajax_template');
    Route::any('/leads/bulk_mail', 'LeadsController@bulk_mail');
    Route::any('/leads/bulk_tags', 'LeadsController@bulk_tags');
    Route::any('/leads/{id}/clone', 'LeadsController@lead_clone');
	Route::any('/leads/ajax_datagrid', 'LeadsController@ajax_datagrid');
    Route::any('/leads/{id}/ajax_getEmailTemplate/{template_type}', 'LeadsController@ajax_getEmailTemplate')->where('template_type', '(.[09]*)+');
	Route::any('leads/exports/{type}', 'LeadsController@exports');
	Route::resource('leads', 'LeadsController');


	//Contacts
	Route::any('/contacts/create', array('as' => 'contacts_create', 'uses' => 'ContactsController@create'));
	Route::any('/contacts/store', array('as' => 'contacts_store', 'uses' => 'ContactsController@store'));
	Route::any('/contacts/store', array('as' => 'contacts_store', 'uses' => 'ContactsController@store'));
	Route::any('/contacts/update/{id}', array('as' => 'contacts_update', 'uses' => 'ContactsController@update'));
	Route::any('/contacts/{id}/show', array('as' => 'contacts_show', 'uses' => 'ContactsController@show'));
	Route::any('/contacts/{id}/store_note', array('as' => 'contacts_storenote', 'uses' => 'ContactsController@store_note'));
	Route::any('/contacts/{id}/delete_note', array('as' => 'contacts_delete_note', 'uses' => 'ContactsController@delete_note'));
	Route::any('/contacts/{id}/convert', array('as' => 'contacts_convert', 'uses' => 'ContactsController@convert'));
	Route::any('/contacts/{id}/delete', array('as' => 'contacts_delete', 'uses' => 'ContactsController@destroy'));
	Route::any('contacts/ajax_datagrid', 'ContactsController@ajax_datagrid');
	Route::any('contacts/exports/{type}', 'ContactsController@exports');
	Route::resource('contacts', 'ContactsController');

	//CustomersRates
	Route::any('/customers_rates/{id}', array('as' => 'customer_rates', 'uses' => 'CustomersRatesController@index'));
	Route::any('/customers_rates/{id}/search_ajax_datagrid/{type}', 'CustomersRatesController@search_ajax_datagrid');
	Route::any('/customers_rates/{id}/search_customer_grid', 'CustomersRatesController@search_customer_grid');
	Route::any('/customers_rates/{id}/download', array('as' => 'customer_rates_download', 'uses' => 'CustomersRatesController@download'));
	Route::any('/customers_rates/{id}/process_download', array('as' => 'customer_rates_process_download', 'uses' => 'CustomersRatesController@process_download'));
	Route::any('/customers_rates/update/{id}', array('as' => 'customer_rates_update', 'uses' => 'CustomersRatesController@update'));
	Route::any('/customers_rates/process_bulk_rate_update/{id}', array('as' => 'process_bulk_rate_update', 'uses' => 'CustomersRatesController@process_bulk_rate_update'));
	Route::any('/customers_rates/process_bulk_rate_clear/{id}', array('as' => 'process_bulk_rate_clear', 'uses' => 'CustomersRatesController@process_bulk_rate_clear'));
	Route::any('/customers_rates/settings/{id}', array('as' => 'customer_rates_settings', 'uses' => 'CustomersRatesController@settings'));
	Route::any('/customers_rates/update_trunks/{id}', array('as' => 'customer_rates_trunks_update', 'uses' => 'CustomersRatesController@update_trunks'));
	Route::any('/customers_rates/delete_customerrates/{id}', array('as' => 'customer_rates_delete_customerrates', 'uses' => 'CustomersRatesController@delete_customerrates'));
	Route::any('/customers_rates/bulk_update/{id}', array('as' => 'customer_rates_bulk_update', 'uses' => 'CustomersRatesController@bulk_update'));
	Route::any('/customers_rates/ajax_datagrid_search_customer_rate/{id}', 'CustomersRatesController@ajax_datagrid_search_customer_rate');
	Route::any('/customers_rates/clear_rate/{id}', array('as' => 'customer_clear_rate', 'uses' => 'CustomersRatesController@clear_rate'));
	Route::any('/customers_rates/bulk_clear_rate/{id}', array('as' => 'customer_bulk_clear_rate', 'uses' => 'CustomersRatesController@bulk_clear_rate'));
	Route::any('/customers_rates/{id}/history', array('as' => 'customer_rates_history', 'uses' => 'CustomersRatesController@history'));
	Route::any('/customers_rates/{id}/history_ajax_datagrid', 'CustomersRatesController@history_ajax_datagrid');
	Route::any('/customers_rates/{id}/history/{hid}/view', 'CustomersRatesController@show_history')->where('hid', '(.[09]*)+');
	Route::any('/customers_rates/{id}/exports', 'CustomersRatesController@exports');
	Route::any('/customers_rates/{id}/history_exports/{type}', 'CustomersRatesController@history_exports');
	Route::any('/customers_rates/{id}/download_excel_file/{JobID}', 'CustomersRatesController@download_excel_file')->where('JobID', '(.[09]*)+');
	Route::any('/customers_rates/{id}/customerdownloadtype/{type}', 'CustomersRatesController@customerdownloadtype');
	Route::any('/vendor_merge', 'CustomersRatesController@vendor_merge');


	Route::resource('customers_rates', 'CustomersRatesController');
	Route::controller('customers_rates', 'CustomersRatesController');

	//VendoerBlocking
	Route::any('/vendor_blocking/{id}', array('as' => 'vendor_blocking', 'uses' => 'VendorBlockingsController@index'));
	Route::any('/vendor_blocking/blockby_code/{id}',  'VendorBlockingsController@blockby_code');
	Route::any('/vendor_blocking/block/{id}', array('as' => 'vendor_blocking_block', 'uses' => 'VendorBlockingsController@block'));
	Route::any('/vendor_blocking/unblock/{id}', array('as' => 'vendor_blocking_unblock', 'uses' => 'VendorBlockingsController@unblock'));

	Route::any('/vendor_blocking/index_blockby_code/{id}',  'VendorBlockingsController@index_blockby_code');

	Route::any('/vendor_blocking/blockby_code/{id}', array('as' => 'vendor_blocking_block_blockby_code', 'uses' => 'VendorBlockingsController@blockby_code'));
	Route::any('/vendor_blocking/unblockby_code/{id}', array('as' => 'vendor_blocking_unblockby_code', 'uses' => 'VendorBlockingsController@unblockby_code'));

	Route::any('/vendor_blocking/{id}/ajax_datagrid_blockbycountry', 'VendorBlockingsController@ajax_datagrid_blockbycountry');
	Route::any('/vendor_blocking/{id}/blockbycountry_exports/{type}', 'VendorBlockingsController@blockbycountry_exports');
	Route::any('/vendor_blocking/{id}/ajax_datagrid_blockbycode', 'VendorBlockingsController@ajax_datagrid_blockbycode');
	Route::any('/vendor_blocking/{id}/blockbycode_exports/{type}', 'VendorBlockingsController@blockbycode_exports');
	Route::any('/vendor_blocking/blockbycountry/{id}',  'VendorBlockingsController@blockbycountry');
	Route::any('/vendor_blocking/blockbycode/{id}',  'VendorBlockingsController@blockbycode');

	Route::resource('vendor_blocking', 'VendorBlockingsController');
	Route::controller('vendor_blocking', 'VendorBlockingsController');

	//VendorRates
	Route::any('/vendor_rates/download_sample_excel_file', 'VendorRatesController@download_sample_excel_file');
	Route::any('/vendor_rates/{id}', array('as' => 'vendor_rates', 'uses' => 'VendorRatesController@index'));
	Route::any('/vendor_rates/{id}/upload', array('as' => 'vendor_rates_upload', 'uses' => 'VendorRatesController@upload'));
	Route::any('/vendor_rates/{id}/settings', array('as' => 'vendor_rates_settings', 'uses' => 'VendorRatesController@settings'));
	Route::any('/vendor_rates/{id}/process_upload', array('as' => 'vendor_rates_process_upload', 'uses' => 'VendorRatesController@process_upload'));
	Route::any('/vendor_rates/{id}/download', array('as' => 'vendor_rates_download', 'uses' => 'VendorRatesController@download'));
	Route::any('/vendor_rates/{id}/process_download', array('as' => 'vendor_rates_process_download', 'uses' => 'VendorRatesController@process_download'));
	Route::any('/vendor_rates/{id}/history', 'VendorRatesController@history');
	Route::any('/vendor_rates/{id}/history_ajax_datagrid', 'VendorRatesController@history_ajax_datagrid');
	Route::any('/vendor_rates/{id}/history/{hid}/view', 'VendorRatesController@show_history')->where('hid', '(.[09]*)+');
	Route::any('/vendor_rates/{id}/history_exports/{type}', 'VendorRatesController@history_exports');
	Route::any('/vendor_rates/{id}/search_ajax_datagrid', 'VendorRatesController@search_ajax_datagrid');
	Route::any('/vendor_rates/{id}/exports/{type}', 'VendorRatesController@exports');
	Route::any('/vendor_rates/{id}/delete_vendorrates', 'VendorRatesController@delete_vendorrates');
	Route::any('/vendor_rates/{id}/update_settings', 'VendorRatesController@update_settings');
	Route::any('/vendor_rates/{id}/download/download_excel_file/{JobID}', 'VendorRatesController@downloaded_excel_file_download')->where('JobID', '(.[09]*)+');
	Route::any('/vendor_rates/{id}/upload/download_excel_file/{JobID}', 'VendorRatesController@uploaded_excel_file_download')->where('JobID', '(.[09]*)+');
	Route::any('/vendor_rates/bulk_clear_rate/{id}', array('as' => 'vendor_bulk_clear_rate', 'uses' => 'VendorRatesController@bulk_clear_rate'));
	Route::any('/vendor_rates/clear_all_vendorrate/{id}', array('as' => 'clear_all_vendorrate', 'uses' => 'VendorRatesController@clear_all_vendorrate'));
	Route::any('/vendor_rates/bulk_update/{id}', array('as' => 'vendor_rates_bulk_update', 'uses' => 'VendorRatesController@bulk_update'));
	Route::any('/vendor_rates/bulk_update_new/{id}', array('as' => 'vendor_rates_bulk_update_new', 'uses' => 'VendorRatesController@bulk_update_new'));
	Route::any('/vendor_rates/vendor_preference/{id}', 'VendorRatesController@vendor_preference');
	Route::any('/vendor_rates/{id}/search_ajax_datagrid_preference/{type}', 'VendorRatesController@search_ajax_datagrid_preference');
	Route::any('/vendor_rates/bulk_update_preference/{id}', 'VendorRatesController@bulk_update_preference');
    Route::any('/vendor_rates/{id}/check_upload', 'VendorRatesController@check_upload');
    Route::any('/vendor_rates/{id}/ajaxfilegrid', 'VendorRatesController@ajaxfilegrid');
    Route::any('/vendor_rates/{id}/storeTemplate', 'VendorRatesController@storeTemplate');
    Route::any('/vendor_rates/{id}/search_vendor_grid', 'VendorRatesController@search_vendor_grid');
	Route::any('/vendor_rates/{id}/customerdownloadtype/{type}', 'VendorRatesController@vendordownloadtype');

	Route::resource('vendor_rates', 'VendorRatesController');
	Route::controller('vendor_rates', 'VendorRatesController');

	//Jobs
	Route::any('/jobs', array('as' => 'jobs', 'uses' => 'JobsController@index'));
	Route::any('/jobs/ajax_datagrid', array('as' => 'jobs_dg', 'uses' => 'JobsController@ajax_datagrid'));
	Route::any('/jobs/{id}/show', array('as' => 'jobs_view', 'uses' => 'JobsController@show'));
	Route::any('/jobs/exports/{type}', array('as' => 'jobs_exports', 'uses' => 'JobsController@exports'));
	Route::any('/jobs/{id}/download_excel', 'JobsController@download_rate_sheet_file');
	Route::any('/jobs/loadDashboardJobsDropDown', 'JobsController@loadDashboardJobsDropDown');
	Route::any('/jobs/reset', 'JobsController@resetJobsAlert');
	Route::any('/jobs/{id}/jobRead', 'JobsController@jobRead');
	Route::any('/jobs/{id}/downloaoutputfile', 'JobsController@downloaOutputFile');
    Route::any('/activejob', 'JobsController@activejob');// removed
    Route::any('/jobs/jobactive_ajax_datagrid', 'JobsController@jobactive_ajax_datagrid');
    Route::any('/jobs/activeprocessdelete/', 'JobsController@activeprocessdelete');


	Route::any('/jobs/{id}/restart', 'JobsController@restart');
	Route::any('/jobs/{id}/terminate', 'JobsController@terminate');
	Route::any('/jobs/{id}/cancel', 'JobsController@cancel');


	Route::resource('jobs', 'JobsController');
	Route::controller('jobs', 'JobsController');

	//RateGenerator
	Route::any('/rategenerators', array('as' => 'rategenerator_list', 'uses' => 'RateGeneratorsController@index'));
	Route::any('/rategenerators/ajax_datagrid', array('as' => 'rategenerator_ajax_datagrid', 'uses' => 'RateGeneratorsController@ajax_datagrid'));
	Route::any('/rategenerators/ajax_margin_datagrid', array('as' => 'rategenerator_ajax_margin_datagrid', 'uses' => 'RateGeneratorsController@ajax_margin_datagrid'));
	Route::any('/rategenerators/{id}/delete', array('as' => 'rategenerator_delete', 'uses' => 'RateGeneratorsController@delete'));
	Route::any('/rategenerators/create', array('as' => 'rategenerator_create', 'uses' => 'RateGeneratorsController@create'));
	Route::any('/rategenerators/store', array('as' => 'rategenerator_store', 'uses' => 'RateGeneratorsController@store'));
	Route::any('/rategenerators/{id}/update', array('as' => 'rategenerator_update', 'uses' => 'RateGeneratorsController@update'));
	Route::any('/rategenerators/{id}/edit', array('as' => 'rategenerator_edit', 'uses' => 'RateGeneratorsController@edit'));
	Route::any('/rategenerators/{id}/store_rule', array('as' => 'rategenerator_store_rule', 'uses' => 'RateGeneratorsController@store_rule'));
	Route::any('/rategenerators/rules/{id}', array('as' => 'rategenerator_rules', 'uses' => 'RateGeneratorsController@rules'));
	Route::any('/rategenerators/{id}/generate_rate_table/{create_update}', array('as' => 'rategenerator_rules', 'uses' => 'RateGeneratorsController@generate_rate_table'))->where('create_update', '(create|update)');
	Route::any('/rategenerators/rules/{id}/edit/{ruleID}', 'RateGeneratorsController@edit_rule')->where('ruleID', '(.[09]*)+');
	Route::any('/rategenerators/rules/{id}/delete/{rule_id}', 'RateGeneratorsController@delete_rule')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/rules/{id}/edit_source/{rule_id}', 'RateGeneratorsController@edit_rule_source')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/rules/{id}/edit_margin/{rule_id}', 'RateGeneratorsController@edit_rule_margin')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/rules/{id}/update_margin/{rule_id}', 'RateGeneratorsController@update_rule_margin')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/rules/{id}/add_margin/{rule_id}', 'RateGeneratorsController@add_rule_margin')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/rules/{rule_id}/delete_margin/{id}', 'RateGeneratorsController@delete_rule_margin')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/rules/{id}/update_source/{rule_id}', 'RateGeneratorsController@update_rule_source')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/rules/{id}/update/{rule_id}', 'RateGeneratorsController@update_rule')->where('rule_id', '(.[09]*)+');
	Route::any('/rategenerators/{id}/change_status/{status}', 'RateGeneratorsController@change_status')->where('status', '(.[09]*)+');
	Route::any('/rategenerators/exports/{type}', 'RateGeneratorsController@exports');
	Route::any('/rategenerators/ajax_load_rate_table_dropdown', 'RateGeneratorsController@ajax_load_rate_table_dropdown');
	Route::resource('rategenerators', 'RateGeneratorsController');
	Route::controller('rategenerators', 'RateGeneratorsController');

	//RateTables
    Route::any('/rate_tables', array('as' => 'customer_rates', 'uses' => 'RateTablesController@index'));
	Route::any('/rate_tables/{id}/search_ajax_datagrid', array('as' => 'customer_rates_search', 'uses' => 'RateTablesController@search_ajax_datagrid'));
	Route::any('/rate_tables/ajax_datagrid', 'RateTablesController@ajax_datagrid');
	Route::any('/rate_tables/{id}/edit_ajax_datagrid', 'RateTablesController@edit_ajax_datagrid');
	Route::any('/rate_tables/store', 'RateTablesController@store');
	Route::any('/rate_tables/{id}/delete', 'RateTablesController@delete');
	Route::any('/rate_tables/{id}/view', 'RateTablesController@view');
    Route::any('/rate_tables/{id}/add_newrate', 'RateTablesController@add_newrate');
	Route::any('/rate_tables/{id}/clear_rate', 'RateTablesController@clear_rate');
	Route::any('/rate_tables/{id}/update_rate_table_rate/{RateTableRateID}', 'RateTablesController@update_rate_table_rate')->where('RateTableRateID', '(.[09]*)+');
	Route::any('/rate_tables/{id}/bulk_update_rate_table_rate', 'RateTablesController@bulk_update_rate_table_rate');
	Route::any('/rate_tables/{id}/bulk_clear_rate_table_rate', 'RateTablesController@bulk_clear_rate_table_rate');
	Route::any('/rate_tables/{id}/change_status/{status}', 'RateTablesController@change_status')->where('status', '(.[09]*)+');
	Route::any('/rate_tables/exports/{type}', 'RateTablesController@exports');
	Route::any('/rate_tables/{id}/rate_exports/{type}', 'RateTablesController@rate_exports');
    Route::any('/rate_tables/download_sample_excel_file', 'RateTablesController@download_sample_excel_file');
    Route::any('/rate_tables/{id}/upload', array('as' => 'rates_upload', 'uses' => 'RateTablesController@upload'));
    Route::any('/rate_tables/{id}/check_upload', array('as' => 'check_upload', 'uses' => 'RateTablesController@check_upload'));
    Route::any('/rate_tables/{id}/ajaxfilegrid', 'RateTablesController@ajaxfilegrid');
    Route::any('/rate_tables/{id}/storeTemplate', 'RateTablesController@storeTemplate');
	Route::any('/rate_tables/getCodeByAjax', 'RateTablesController@getCodeByAjax');
    Route::resource('rate_tables', 'RateTablesController');
	Route::controller('rate_tables', 'RateTablesController');

	//LCR
	Route::any('/lcr', 'LCRController@index');
	Route::any('lcr/search_ajax_datagrid/{type}', 'LCRController@search_ajax_datagrid');
	Route::any('lcr/exports', 'LCRController@exports');
	Route::resource('lcr', 'LCRController');
	Route::resource('lcr', 'LCRController');

	//Pages
	Route::any('/about', 'PagesController@about');
	Route::resource('page', 'PagesController');
	Route::controller('page', 'PagesController');

	//Account Approval

	Route::any('/accountapproval/ajax_datagrid', 'AccountApprovalController@ajax_datagrid');
	Route::any('/accountapproval/create', 'AccountApprovalController@create');
	Route::any('/accountapproval/update/{id}', 'AccountApprovalController@update');
	Route::any('/accountapproval/delete/{id}', 'AccountApprovalController@delete');
	Route::any('/accountapproval', 'AccountApprovalController@index');

	//Gateway Management
	Route::any('/gateway/ajax_datagrid/{type}', 'GatewayController@ajax_datagrid');
	Route::any('/gateway/create', 'GatewayController@create');
	Route::any('/gateway/update/{id}', 'GatewayController@update');
	Route::any('/gateway/ajax_load_gateway_dropdown', 'GatewayController@ajax_load_gateway_dropdown');
	Route::any('/gateway/delete/{id}', 'GatewayController@delete');
	Route::any('/gateway/test_connetion/{id}', 'GatewayController@test_connetion');
	Route::any('/gateway', 'GatewayController@index');

	//summaryreport
	Route::any('/summaryreport', 'SummaryController@index');
	Route::any('/summaryreport/ajax_datagrid/{type}', 'SummaryController@ajax_datagrid');
	Route::any('/summaryreport/list_accounts', 'SummaryController@list_accounts');
	Route::any('/summaryreport/list_vendor', 'SummaryController@list_vendor');
	Route::any('/summaryreport/summrybycountry', 'SummaryController@summrybycountry');
	Route::any('/summaryreport/summrybycustomer', 'SummaryController@summrybycustomer');
	Route::any('/summaryreport/summrybypincode', 'SummaryController@summrybycustomer');
	Route::any('/summaryreport/temp_action', 'SummaryController@temp_action');
	Route::any('/summaryreport/daily_sales_report', 'SummaryController@daily_sales_report');
	Route::any('/summaryreport/daily_ajax_datagrid', 'SummaryController@daily_ajax_datagrid');

	//cronjobs
	Route::any('/cronjobs', 'CronJobController@index'); // replaced by cronjob monitor
	Route::any('/cronjobs/ajax_datagrid/{type}', 'CronJobController@ajax_datagrid'); // replaced by cronjob monitor

	Route::any('/cronjobs/create', 'CronJobController@create');
	Route::any('/cronjobs/update/{id}', 'CronJobController@update');
	Route::any('/cronjobs/delete/{id}', 'CronJobController@delete');
	Route::any('/cronjobs/ajax_load_cron_dropdown', 'CronJobController@ajax_load_cron_dropdown');
	Route::any('/cronjobs/history/{id}', 'CronJobController@history');
	Route::any('/cronjobs/history_ajax_datagrid/{id}/{type}', 'CronJobController@history_ajax_datagrid');
    Route::any('/activecronjob', 'CronJobController@activecronjob');
    Route::any('/cronjobs/activecronjob_ajax_datagrid', 'CronJobController@activecronjob_ajax_datagrid');
    Route::any('/cronjobs/activeprocessdelete/', 'CronJobController@activeprocessdelete');


	Route::any('/cronjob_monitor', 'CronJobController@cronjob_monitor');
	Route::any('/cronjob/{id}/trigger', 'CronJobController@trigger');
	Route::any('/cronjob/{id}/terminate', 'CronJobController@terminate');
	Route::any('/cronjob/{id}/change_status/{id2}', 'CronJobController@change_status');
	Route::any('/cronjob/change_crontab_status/{id}', 'CronJobController@change_crontab_status');

	//Company
	Route::any('/company', 'CompaniesController@edit');
	Route::any('/company/update', 'CompaniesController@update');
	Route::post('/company/validatesmtp', 'CompaniesController@ValidateSmtp');
	//Route::resource('Companies', 'CompaniesController');

	//payment
	Route::any('/payments', 'PaymentsController@index');
	Route::any('/payments/ajax_datagrid_total', 'PaymentsController@ajax_datagrid_total');	
    Route::any('/payments/{id}/upload', 'PaymentsController@upload');
    Route::any('/payments/check_upload', 'PaymentsController@check_upload');
    Route::any('/payments/ajaxfilegrid', 'PaymentsController@ajaxfilegrid');
    Route::any('/payments/download_sample_excel_file', 'PaymentsController@download_sample_excel_file');
    Route::any('/payments/upload', 'PaymentsController@upload');
	Route::any('/payments/create', 'PaymentsController@create');
	Route::any('/payments/{id}/update', 'PaymentsController@update');
	Route::any('/payments/{id}/recall', 'PaymentsController@recall');
	Route::any('/payments/download_doc/{id}', 'PaymentsController@download_doc');
	Route::any('/payments/ajax_datagrid/{type}', 'PaymentsController@ajax_datagrid');
	Route::any('/payments/get_currency_invoice_numbers/{id}', 'PaymentsController@get_currency_invoice_numbers');
	Route::any('/payments/{id}/payment_approve_reject/{approve_reject}', array('as' => 'payment_rules', 'uses' => 'PaymentsController@payment_approve_reject'))->where('approve_reject', '(approve|reject)');

	#Route::any('/payments/{id}/upload', 'PaymentsController@upload'); not in use
	Route::any('/payments/upload/validate_column_mapping', 'PaymentsController@validate_column_mapping');
	Route::any('/payments/upload/confirm_bulk_upload', 'PaymentsController@confirm_bulk_upload');
	Route::any('/payments/check_upload', 'PaymentsController@check_upload');
	Route::any('/payments/ajaxfilegrid', 'PaymentsController@ajaxfilegrid');
	Route::any('/payments/download_sample_excel_file', 'PaymentsController@download_sample_excel_file');

	//Currency
	Route::any('/currency/ajax_datagrid', 'CurrenciesController@ajax_datagrid');
	Route::any('/currency', 'CurrenciesController@index');
	Route::any('/currency/create', 'CurrenciesController@create');
	Route::any('/currency/update/{id}', 'CurrenciesController@update');
	Route::any('/currency/{id}/delete', 'CurrenciesController@delete');
	Route::any('/currency/exports/{type}', 'CurrenciesController@exports');

    //currency_conversion
    Route::any('/currency_conversion/ajax_datagrid', 'CurrencyConversionController@ajax_datagrid');
    Route::any('/currency_conversion/ajax_datagrid_history', 'CurrencyConversionController@ajax_datagrid_history');
    Route::any('/currency_conversion', 'CurrencyConversionController@index');
    Route::any('/currency_conversion/create', 'CurrencyConversionController@create');
    Route::any('/currency_conversion/update/{id}', 'CurrencyConversionController@update');
    Route::any('/currency_conversion/{id}/delete', 'CurrencyConversionController@delete');


	//TaxRate
	Route::any('/taxrate/ajax_datagrid', 'TaxRatesController@ajax_datagrid');
	Route::any('/taxrate', 'TaxRatesController@index');
	Route::any('/taxrate/create', 'TaxRatesController@create');
	Route::any('/taxrate/update/{id}', 'TaxRatesController@update');
	Route::any('/taxrate/{id}/delete', 'TaxRatesController@delete');

	//BilllingSubscription
	Route::any('/billing_subscription/ajax_datagrid/{type}', 'BillingSubscriptionController@ajax_datagrid');
	Route::any('/billing_subscription', 'BillingSubscriptionController@index');
	Route::any('/billing_subscription/create', 'BillingSubscriptionController@create');
	Route::any('/billing_subscription/update/{id}', 'BillingSubscriptionController@update');
	Route::any('/billing_subscription/{id}/delete', 'BillingSubscriptionController@delete');	
	Route::any('/billing_subscription/{id}/getSubscriptionData_ajax', 'BillingSubscriptionController@getSubscriptionData_ajax');
    Route::any('/billing_subscription/{id}/get/{FieldName}', 'BillingSubscriptionController@get')->where('FieldName', '(.[azAZ]*)+');

	//InvoiceTemplate
	Route::any('/invoice_template/ajax_datagrid/{type}', 'InvoiceTemplatesController@ajax_datagrid');
	Route::any('/invoice_template', 'InvoiceTemplatesController@index');
	Route::any('/invoice_template/create', 'InvoiceTemplatesController@create');
	Route::any('/invoice_template/{id}/delete', 'InvoiceTemplatesController@delete');
	Route::any('/invoice_template/{id}/view', 'InvoiceTemplatesController@view');
	Route::any('/invoice_template/{id}/update', 'InvoiceTemplatesController@update');
	Route::any('/invoice_template/{id}/print', 'InvoiceTemplatesController@print_preview');
	Route::any('/invoice_template/{id}/pdf_download', 'InvoiceTemplatesController@pdf_download');
    Route::any('/invoice_template/{id}/get_logo', 'InvoiceTemplatesController@get_logo');

	//CDR Upload
	Route::any('/cdr_upload', 'CDRController@index');
	Route::any('/cdr_recal/bulk_recal', 'CDRController@bulk_recal');
	Route::any('/cdr_recal', 'CDRController@cdr_recal');
	Route::any('/cdr_upload/upload', 'CDRController@upload');
	Route::any('/cdr_upload/bulk_upload', 'CDRController@bulk_upload');
	Route::any('/cdr_upload/download_sample_excel_file/{type}', 'CDRController@download_sample_excel_file');
	Route::any('/cdr_upload/get_accounts/{id}', 'CDRController@get_accounts');
	Route::any('/cdr_show', 'CDRController@show');
	Route::any('/cdr_upload/delete_customer_cdr', 'CDRController@delete_customer_cdr');
	Route::any('/cdr_upload/delete_vendor_cdr', 'CDRController@delete_vendor_cdr');
	//Route::any('/cdr_upload/delete', 'CDRController@delete'); // Temporary hidden
	//Route::any('/cdr_upload/delete_cdr', 'CDRController@delete_cdr');// Temporary hidden
	Route::any('/cdr_upload/ajax_datagrid/{type}', 'CDRController@ajax_datagrid');
	Route::any('/cdr_upload/ajax_datagrid_total/{type}', 'CDRController@ajax_datagrid_total');	
    Route::any('/cdr_upload/check_upload', 'CDRController@check_upload');
    Route::any('/cdr_upload/ajaxfilegrid', 'CDRController@ajaxfilegrid');
    Route::any('/cdr_upload/storeTemplate', 'CDRController@storeTemplate');
    Route::any('/cdr_upload/ajaxfilegrid', 'CDRController@ajaxfilegrid');
    Route::any('/rate_cdr', 'CDRController@rate_cdr');
	Route::any('/vendorcdr_show', 'CDRController@vendorcdr_show');
	Route::any('/cdr_upload/ajax_datagrid_vendorcdr/{type}', 'CDRController@ajax_datagrid_vendorcdr');
	Route::any('/cdr_upload/ajax_datagrid_vendorcdr_total/{type}', 'CDRController@ajax_datagrid_vendorcdr_total');	
	Route::any('/vendorcdr_upload', 'CDRController@vendorcdr_upload');
	Route::any('/cdr_upload/check_vendorupload', 'CDRController@check_vendorupload');
	Route::any('/cdr_upload/storeVendorTemplate', 'CDRController@storeVendorTemplate');


	/////////////////
	//Estimates
	Route::any('/estimates', 'EstimatesController@index');
	Route::any('/estimate/create', 'EstimatesController@create');
	Route::any('/estimate/store', 'EstimatesController@store');
	Route::any('/estimate/bulk_send_estimate_mail', 'EstimatesController@bulk_send_estimate_mail');
    Route::any('/estimate/estimate_regen', 'EstimatesController@estimate_regen');
	Route::any('/estimate/{id}/edit', 'EstimatesController@edit');
	Route::any('/estimate/{id}/delete', 'EstimatesController@delete');
	Route::any('/estimate/{id}/view', 'EstimatesController@view');
	Route::any('/estimate/{id}/update', 'EstimatesController@update');
	Route::any('/estimate/{id}/estimate_preview', 'EstimatesController@estimate_preview'); //Customer View
	Route::any('/estimate/{id}/send', 'EstimatesController@send');
	Route::any('/estimate/{id}/ajax_getEmailTemplate', 'EstimatesController@ajax_getEmailTemplate');
	Route::any('/estimate/{id}/estimate_email', 'EstimatesController@estimate_email');
	Route::any('/estimate/estimate_change_Status', 'EstimatesController@estimate_change_Status');	
	Route::any('/estimate/estimate_change_Status_Bulk', 'EstimatesController@estimate_change_Status_Bulk');	
	Route::any('/estimate/estimate_delete_bulk', 'EstimatesController@delete_bulk');	
	Route::any('/estimate/{id}/download_usage', 'EstimatesController@downloadUsageFile');
    Route::any('/estimates_log/{id}', 'TransactionLogController@log');
    Route::any('/estimates_log/ajax_datagrid/{id}', 'TransactionLogController@ajax_datagrid');
    Route::any('/estimates_log/ajax_estimate_datagrid/{id}', 'TransactionLogController@ajax_estimate_datagrid');
    Route::any('/estimate/generate', 'EstimatesController@generate');
	Route::any('/estimate/ajax_datagrid/{type}', 'EstimatesController@ajax_datagrid');
	Route::any('/estimate/ajax_datagrid_total', 'EstimatesController@ajax_datagrid_total');	
	Route::any('/estimate/calculate_total', 'EstimatesController@calculate_total');
	Route::any('/estimate/get_account_info', 'EstimatesController@getAccountInfo');
	Route::any('/estimate/bulk_estimate', 'EstimatesController@bulk_estimate');
	Route::any('/estimate/add_estimate_in', 'EstimatesController@add_estimate_in');
	Route::any('/estimate/update_estimate_in/{id}', 'EstimatesController@update_estimate_in');
	Route::any('/estimate/download_doc_file/{id}', 'EstimatesController@download_doc_file');
	Route::any('/estimate/sageExport', 'EstimatesController@sageExport');
	Route::any('/estimate/getEstimateDetail', 'EstimatesController@getEstimateDetail');
	///////////////////////////

	//Invoice
	Route::any('/invoice', 'InvoicesController@index');
	Route::any('/invoice/create', 'InvoicesController@create');
	Route::any('/invoice/store', 'InvoicesController@store');
	Route::any('/invoice/bulk_send_invoice_mail', 'InvoicesController@bulk_send_invoice_mail');
    Route::any('/invoice/invoice_regen', 'InvoicesController@invoice_regen');
	Route::any('/invoice/{id}/edit', 'InvoicesController@edit');
	Route::any('/invoice/{id}/delete', 'InvoicesController@delete');
	Route::any('/invoice/{id}/view', 'InvoicesController@view');
	Route::any('/invoice/{id}/update', 'InvoicesController@update');
	//Route::any('/invoice/{id}/print_preview', 'InvoicesController@print_preview'); Not in use
	Route::any('/invoice/{id}/invoice_preview', 'InvoicesController@invoice_preview'); //Customer View
	//Route::any('/invoice/{id}/print', 'InvoicesController@pdf_view');
	Route::any('/invoice/{id}/send', 'InvoicesController@send');
	Route::any('/invoice/{id}/ajax_getEmailTemplate', 'InvoicesController@ajax_getEmailTemplate');
	Route::any('/invoice/{id}/invoice_email', 'InvoicesController@invoice_email');
	Route::any('/invoice/invoice_change_Status', 'InvoicesController@invoice_change_Status');
	Route::any('/invoice/{id}/download_usage', 'InvoicesController@downloadUsageFile');
    Route::any('/invoice_log/{id}', 'TransactionLogController@log');
    Route::any('/invoice_log/ajax_datagrid/{id}/{type}', 'TransactionLogController@ajax_datagrid');
    Route::any('/invoice_log/ajax_invoice_datagrid/{id}/{type}', 'TransactionLogController@ajax_invoice_datagrid');
    Route::any('/invoice/generate', 'InvoicesController@generate');
	Route::any('/invoice/ajax_datagrid/{type}', 'InvoicesController@ajax_datagrid');
	Route::any('/invoice/ajax_datagrid_total', 'InvoicesController@ajax_datagrid_total');
	Route::any('/invoice/calculate_total', 'InvoicesController@calculate_total');
	Route::any('/invoice/get_account_info', 'InvoicesController@getAccountInfo');
	Route::any('/invoice/bulk_invoice', 'InvoicesController@bulk_invoice');
	Route::any('/invoice/add_invoice_in', 'InvoicesController@add_invoice_in');
	Route::any('/invoice/update_invoice_in/{id}', 'InvoicesController@update_invoice_in');
	Route::any('/invoice/download_doc_file/{id}', 'InvoicesController@download_doc_file');
	Route::any('/invoice/sageExport', 'InvoicesController@sageExport');
	Route::any('/invoice/getInvoiceDetail', 'InvoicesController@getInvoiceDetail');
	Route::any('/invoice/reconcile', 'InvoicesController@invoice_in_reconcile');

	//Themes
	Route::any('/themes', 'ThemesController@index');
	Route::any('/themes/create', 'ThemesController@create');
	Route::any('/themes/store', 'ThemesController@store');
	Route::any('/themes/bulk_send_estimate_mail', 'ThemesController@bulk_send_estimate_mail');
    Route::any('/themes/estimate_regen', 'ThemesController@estimate_regen');
	Route::any('/themes/{id}/edit', 'ThemesController@edit');
	Route::any('/themes/{id}/delete', 'ThemesController@delete');
	Route::any('/themes/{id}/view', 'ThemesController@view');
	Route::any('/themes/{id}/update', 'ThemesController@update');
    Route::any('/themes/ajax_datagrid', 'ThemesController@ajax_datagrid');
	Route::any('/themes/themes_change_Status', 'ThemesController@themes_change_Status');	
	Route::any('/themes/themes_delete_bulk', 'ThemesController@delete_bulk');	

    //Opportunity boards

    Route::any('/opportunityboards', 'OpportunityBoardController@index');
    Route::any('/opportunityboards/create', 'OpportunityBoardController@create');
    Route::any('/opportunityboards/{id}/configure', 'OpportunityBoardController@configure');
    Route::any('/opportunityboards/{id}/manage/', 'OpportunityBoardController@manage');
    Route::any('/opportunityboards/{id}/update', 'OpportunityBoardController@update');
    Route::any('/opportunityboards/{id}/delete', 'OpportunityBoardController@delete');
    Route::any('/opportunityboards/ajax_datagrid', 'OpportunityBoardController@ajax_datagrid');


    //opportunity boards column

    Route::any('/opportunityboardcolumn', 'OpportunityBoardColumnController@index');
    Route::any('/opportunityboardcolumn/create', 'OpportunityBoardColumnController@create');
    Route::any('/opportunityboardcolumn/{id}/update', 'OpportunityBoardColumnController@update');
    Route::any('/opportunityboardcolumn/{id}/delete', 'OpportunityBoardColumnController@delete');
    Route::any('/opportunityboardcolumn/{id}/ajax_datacolumn', 'OpportunityBoardColumnController@ajax_datacolumn');
    Route::any('/opportunityboardcolumn/{id}/updateColumnOrder', 'OpportunityBoardColumnController@updateColumnOrder');

    //Opportunity

    Route::any('/opportunity/create', 'OpportunityController@create');
    Route::any('/opportunity/{id}/saveattachment', 'OpportunityController@saveattachment');
    Route::any('/opportunity/{id}/getlead', 'OpportunityController@getLead');
    Route::any('/opportunity/{id}/getDropdownLeadAccount', 'OpportunityController@getDropdownLeadAccount');
    Route::any('/opportunity/{id}/getopportunity', 'OpportunityController@getopportunity');
    Route::any('/opportunity/{id}/ajax_opportunity_grid', 'OpportunityController@ajax_grid');
    Route::any('/opportunity/{id}/update', 'OpportunityController@update');
    Route::any('/opportunity/{id}/deleteattachment/{attachmentid}', 'OpportunityController@deleteAttachment');
    Route::any('/opportunity/{id}/updateColumnOrder', 'OpportunityController@updateColumnOrder');
    Route::any('/opportunity/{id}/ajax_opportunity', 'OpportunityController@ajax_opportunity');
    Route::any('/opportunity/{id}/ajax_getattachments', 'OpportunityController@ajax_getattachments');
    Route::any('/opportunity/{id}/updatetaggeduser', 'OpportunityController@updateTaggedUser');
    Route::any('/opportunity/{id}/getattachment/{attachmentID}', 'OpportunityController@getAttachment');

    //File Upload
    Route::any('/opportunity/uploadfile', 'OpportunityController@uploadFile');
    Route::any('/opportunity/deleteattachmentfile', 'OpportunityController@deleteUploadFile');

    //Opportunity Comments

    Route::any('/opportunitycomment/create', 'OpportunityCommentsController@create');
    Route::any('/opportunitycomments/{id}/ajax_opportunitycomments', 'OpportunityCommentsController@ajax_opportunityComments');
    Route::any('/opportunitycomment/{id}/getattachment/{attachmentID}', 'OpportunityCommentsController@getAttachment');

    //Task

    Route::any('/task', 'TaskController@manage');
    Route::any('/task/{id}/configure', 'OpportunityBoardController@configure');
    Route::any('/task/create', 'TaskController@create');
    Route::any('/task/{id}/saveattachment', 'TaskController@saveattachment');
    Route::any('/task/{id}/getlead', 'TaskController@getLead');
    Route::any('/task/{id}/getDropdownLeadAccount', 'TaskController@getDropdownLeadAccount');
    Route::any('/task/{id}/getopportunity', 'TaskController@getopportunity');
    Route::any('/task/{id}/update', 'TaskController@update');
    Route::any('/task/{id}/deleteattachment/{attachmentid}', 'TaskController@deleteAttachment');
    Route::any('/task/{id}/updateColumnOrder', 'TaskController@updateColumnOrder');
    Route::any('/task/{id}/ajax_task_board', 'TaskController@ajax_task_board');
    Route::any('/task/{id}/ajax_task_grid', 'TaskController@ajax_task_grid');
    Route::any('/task/{id}/ajax_getattachments', 'TaskController@ajax_getattachments');
    Route::any('/task/{id}/updatetaggeduser', 'TaskController@updateTaggedUser');
    Route::any('/task/{id}/getattachment/{attachmentID}', 'TaskController@getAttachment');

    //File Upload
    Route::any('/task/uploadfile', 'OpportunityController@uploadFile');
    Route::any('/task/deleteattachmentfile', 'OpportunityController@deleteUploadFile');
    Route::any('/taskcomment/{id}/getattachment/{attachmentID}', 'TaskCommentsController@getAttachment');

    //task boards column

    Route::any('/taskboardcolumn', 'OpportunityBoardColumnController@index');
    Route::any('/taskboardcolumn/create', 'OpportunityBoardColumnController@create');
    Route::any('/taskboardcolumn/{id}/update', 'OpportunityBoardColumnController@update');
    Route::any('/taskboardcolumn/{id}/delete', 'OpportunityBoardColumnController@delete');
    Route::any('/taskboardcolumn/{id}/ajax_datacolumn', 'OpportunityBoardColumnController@ajax_datacolumn');
    Route::any('/taskboardcolumn/{id}/updateColumnOrder', 'OpportunityBoardColumnController@updateColumnOrder');

    //Task Comments

    Route::any('/taskcomment/create', 'TaskCommentsController@create');
    Route::any('/taskcomments/{id}/ajax_taskcomments', 'TaskCommentsController@ajax_taskComments');

	//Product

	Route::any('/products', 'ProductsController@index');
	Route::any('/products/create', 'ProductsController@create');
	Route::any('/products/{id}/update', 'ProductsController@update');
	Route::any('/products/{id}/delete', 'ProductsController@delete');
	Route::any('/products/ajax_datagrid/{type}', 'ProductsController@ajax_datagrid');

	Route::any('/product/{id}/get/{FieldName}', 'ProductsController@get')->where('FieldName', '(.[azAZ]*)+');
    Route::any('/billing_subscription/{id}/get/{FieldName}', 'BillingSubscriptionController@get')->where('FieldName', '(.[azAZ]*)+');


    Route::any('/billing_dashboard/invoice_expense_chart', 'BillingDashboard@invoice_expense_chart');
    Route::any('/billing_dashboard/invoice_expense_total', 'BillingDashboard@invoice_expense_total');
	Route::any('/billing_dashboard/ajax_top_pincode', 'BillingDashboard@ajax_top_pincode');
	Route::any('/billing_dashboard/ajaxgrid_top_pincode/{type}', 'BillingDashboard@ajaxgrid_top_pincode');


    //AccountPaymentProfile
    Route::any('/paymentprofile/create', 'AccountsPaymentProfileController@create');
    Route::any('/paymentprofile/{id}', 'AccountsPaymentProfileController@index');
    Route::any('/paymentprofile/{id}/ajax_datagrid', 'AccountsPaymentProfileController@ajax_datagrid');

    //VendorFileUploadTemplate
    Route::any('/uploadtemplate','VendorFileUploadTemplateController@index');
    Route::any('/uploadtemplate/ajax_datagrid/{type}','VendorFileUploadTemplateController@ajax_datagrid');
    Route::any('/uploadtemplate/ajaxfilegrid','VendorFileUploadTemplateController@ajaxfilegrid');
    Route::any('/uploadtemplate/create','VendorFileUploadTemplateController@create');
    Route::any('/uploadtemplate/{id}/edit','VendorFileUploadTemplateController@edit');
    Route::any('/uploadtemplate/update','VendorFileUploadTemplateController@update');
    Route::any('/uploadtemplate/{id}/delete','VendorFileUploadTemplateController@delete');
    Route::any('/uploadtemplate/store','VendorFileUploadTemplateController@store');

    //VendorProfiling
    Route::any('/vendor_profiling','VendorProfilingController@index');
    Route::any('/active_deactivate_vendor','VendorProfilingController@active_deactivate_vendor');
    Route::any('/vendor_profiling/ajax_datagrid','VendorProfilingController@ajax_datagrid');
    Route::any('/vendor_profiling/{id}/ajax_vendor','VendorProfilingController@ajax_vendor');
    Route::any('/vendor_profiling/block_unblockcode','VendorProfilingController@block_unblockcode');

    //Wysihtml5Controller
    Route::any('/Wysihtml5/getfiles','Wysihtml5Controller@getfiles');
    Route::any('/Wysihtml5/file_upload','Wysihtml5Controller@file_upload');

	//Analysis
	Route::any('/analysis', "AnalysisController@index");
	Route::any('/analysis/ajax_datagrid/{type}', "AnalysisController@ajax_datagrid");
	Route::any('/analysis/getAnalysisData', "AnalysisController@getAnalysisData");
	Route::any('/analysis/getAnalysisBarData', "AnalysisController@getAnalysisBarData");

	//Vendor Analysis
	Route::any('/vendor_analysis', "VendorAnalysisController@index");
	Route::any('/vendor_analysis/ajax_datagrid/{type}', "VendorAnalysisController@ajax_datagrid");
	Route::any('/vendor_analysis/getAnalysisData', "VendorAnalysisController@getAnalysisData");
	Route::any('/vendor_analysis/getAnalysisBarData', "VendorAnalysisController@getAnalysisBarData");
	
	//Disputes
	Route::any('/disputes','DisputeController@index');
	Route::any('/disputes/reconcile', 'DisputeController@reconcile');
	Route::any('/disputes/ajax_datagrid/{type}','DisputeController@ajax_datagrid');
	Route::any('/disputes/{id}/delete','DisputeController@delete');
	Route::any('/disputes/{id}/update','DisputeController@update');
	Route::any('/disputes/create','DisputeController@create');
	Route::any('/disputes/change_status','DisputeController@change_status');
	Route::any('/disputes/{id}/download_attachment','DisputeController@download_attachment');
	Route::any('/disputes/{id}/view','DisputeController@view');

	//DailString
	Route::any('/dialstrings', "DialStringController@index");
	Route::any('/dialstrings/dialstring_datagrid', "DialStringController@dialstring_datagrid");
	Route::any('/dialstrings/exports/{type}', "DialStringController@exports");
	Route::any('/dialstrings/create_dialstring', "DialStringController@create_dialstring");
	Route::any('/dialstrings/update_dialstring/{id}', "DialStringController@update_dialstring");
	Route::any('/dialstrings/{id}/delete_dialstring', "DialStringController@delete_dialstring");

	Route::any('/dialstrings/dialstringcode/{id}', "DialStringController@dialstringcode");
	Route::any('/dialstrings/ajax_datagrid/{type}', "DialStringController@ajax_datagrid");
	Route::any('/dialstrings/store', "DialStringController@store");
	Route::any('/dialstrings/update/{id}', "DialStringController@update");
	Route::any('/dialstrings/{id}/deletecode', "DialStringController@deletecode");
	Route::any('/dialstrings/update_selected', "DialStringController@update_selected");
	Route::any('/dialstrings/delete_selected', "DialStringController@delete_selected");
	Route::any('/dialstrings/{id}/upload', "DialStringController@upload");
	Route::any('/dialstrings/{id}/check_upload', "DialStringController@check_upload");
	Route::any('/dialstrings/{id}/ajaxfilegrid', 'DialStringController@ajaxfilegrid');
	Route::any('/dialstrings/{id}/storeTemplate', 'DialStringController@storeTemplate');
	Route::any('/dialstrings/download_sample_excel_file', "DialStringController@download_sample_excel_file");

});

Route::group(array('before' => 'global_admin'), function () {

	Session::put('isGuest',true);
    //Global User
    Route::any('/global_user_select_company','GlobalAdminsController@select_company');
    Route::any('/sa_get_user_dropdown/{id}','UsersController@get_users_dropdown');
    Route::any('/do_login_for_super_admin','HomeController@do_login_for_super_admin');

});

Route::group(array('before' => 'guest'), function () {

	Session::put('isGuest',true);
    // Login
    Route::get('/', array("as" => "home", "uses" => "HomeController@home"));
    Route::get('/customer', array("as" => "home", "uses" => "HomeCustomerController@home"));
    Route::get('customer/login', array("as" => "customerhome", "uses" => "HomeCustomerController@home"));
    Route::any('customer/dologin', 'HomeCustomerController@dologin');
    Route::get('customer/logout', array("as" => "logoutCustomer", "uses" => "HomeCustomerController@dologout"));
    Route::get('/login', array("as" => "home", "uses" => "HomeController@home"));
    Route::any('dologin', 'HomeController@dologin');
    Route::get('/forgot_password', array("as" => "home", "uses" => "HomeController@forgot_password"));
    Route::post('doforgot_password', 'HomeController@doforgot_password');
    Route::post('doreset_password', 'HomeController@doreset_password');
    Route::any('/reset_password', "HomeController@reset_password");
    Route::get('/logout', array("as" => "logout", "uses" => "HomeController@dologout"));
    Route::any('/registration', "HomeController@registration");
    Route::any('/doRegistration', "HomeController@doRegistration");
    Route::get('/super_admin', "HomeController@home");
    Route::get('/l/{id}', function($id){
        $user = User::find($id);
        $redirect_to = URL::to('/process_redirect');
        if(!empty($user) ){
        create_site_configration_cache();
        Auth::login($user);
        if(NeonAPI::login_by_id($id)) {
            User::setUserPermission();
            Session::set("admin", 1);
            return Redirect::to($redirect_to);
        }else{			
            Session::flush();
            Auth::logout();
            echo json_encode(array("login_status" => "invalid"));
            return;
        }
    }
    exit;
    });
    Route::any('/invoice/{id}/cview', 'InvoicesController@cview'); //Customer View
    //Route::any('/invoice/{id}/cprint', 'InvoicesController@cpdf_view');
    Route::any('/invoice/{id}/cdownload_usage', 'InvoicesController@cdownloadUsageFile');
    Route::any('/invoice/display_invoice/{id}', 'InvoicesController@display_invoice');
    Route::any('/invoice/download_invoice/{id}', 'InvoicesController@download_invoice');
    Route::any('/invoice_payment/{id}', 'InvoicesController@invoice_payment'); //Customer payment View
    Route::any('/pay_invoice', 'InvoicesController@pay_invoice'); //Customer payment pay
    Route::any('/invoice_thanks/{id}', 'InvoicesController@invoice_thanks'); //Customer payment pay
	
	#estimate
	Route::any('/estimate/{id}/cview', 'EstimatesController@cview'); //Customer View
	Route::any('/estimate/display_estimate/{id}', 'EstimatesController@display_estimate');
	Route::any('/estimate/{id}/estimate_email', 'EstimatesController@estimate_email');
	
	Route::any('/estimate/{id}/convert_estimate', 'EstimatesController@convert_estimate');
	
	Route::any('/estimate/download_estimate/{id}', 'EstimatesController@download_estimate');

});

/*
 * save isGuest to skip routes/urls for user permission
 * */
