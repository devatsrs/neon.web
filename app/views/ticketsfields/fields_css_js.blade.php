    <link href="{{ URL::asset('assets/formbuilder/css/common.css')}}" media="screen" rel="stylesheet" type="text/css" />
	<link href="{{ URL::asset('assets/formbuilder/css/pattern.css')}}" media="screen, print, projection" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/formbuilder/css/plugins.css')}}" media="screen" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/formbuilder/css/sprites.css')}}" media="screen" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/formbuilder/css/app.css')}}" media="screen" rel="stylesheet" type="text/css" />

<script type="text/javascript">
	  /* TODO-RAILS3 need to cross check this area */
		/*PROFILE_BLANK_THUMB_PATH = 'https://assets1.freshdesk.com/assets/misc/profile_blank_thumb.jpg';
		PROFILE_BLANK_MEDIUM_PATH = 'https://assets9.freshdesk.com/assets/misc/profile_blank_medium.jpg';
		SPACER_IMAGE_PATH = 'https://assets2.freshdesk.com/assets/misc/spacer.gif';
		FILLER_IMAGES = {
			imageLoading : 'https://assets2.freshdesk.com/assets/animated/image_upload_placeholder.gif'
		};*/
		cloudfront_version = "1479315641";
		/*cloudfront_host_url = "https://assets9.freshdesk.com";*/
	</script>
    <script src="{{ URL::asset('assets/formbuilder/js/defaults.js')}}" type="text/javascript"></script>
    <script src="{{ URL::asset('assets/formbuilder/js/frameworks.js')}}" type="text/javascript"></script>
    <script src="{{ URL::asset('assets/formbuilder/js/workspace.js')}}" type="text/javascript"></script>
    <script src="{{ URL::asset('assets/formbuilder/js/pattern.js')}}" type="text/javascript"></script>
    
    <script type="text/javascript">
//<![CDATA[ 

    // Localizing ticket fields js elements
    customFields = [{"field_type":"default_requester","id":1000044227,"name":"requester","dom_type":"requester","label":"Search a requester","label_in_portal":"Requester","description":"Ticket requester","position":1,"active":true,"required":true,"required_for_closure":false,"visible_in_portal":true,"editable_in_portal":true,"required_in_portal":true,"choices":[],"levels":null,"level_three_present":null,"field_options":{"portalcc":false,"portalcc_to":"company"},"has_section":null},{"field_type":"default_subject","id":1000044228,"name":"subject","dom_type":"text","label":"Subject","label_in_portal":"Subject","description":"Ticket subject","position":2,"active":true,"required":true,"required_for_closure":false,"visible_in_portal":true,"editable_in_portal":true,"required_in_portal":true,"choices":[],"levels":null,"level_three_present":null,"field_options":{"section":false},"has_section":null},{"field_type":"default_ticket_type","id":1000044229,"name":"ticket_type","dom_type":"dropdown_blank","label":"Type","label_in_portal":"Type","description":"Ticket type","position":3,"active":true,"required":true,"required_for_closure":true,"visible_in_portal":true,"editable_in_portal":true,"required_in_portal":true,"choices":[["Call Quality","Call Quality",1001777432],["Call Capacity Change Request","Call Capacity Change Request",1001777433],["New Interconnection Support Request","New Interconnection Support Request",1001777434],["IP Address","IP Address",1001777435],["Payments","Payments",1001777436],["Portal/CDR","Portal/CDR",1001777437],["Other","Other",1001777438],["Rates","Rates",1002743606],["Connectivity","Connectivity",1002743607],["Testing/Routing","Testing/Routing",1002774703],["NEON","NEON",1002891555],["intuPBX","intuPBX",1002891556]],"levels":null,"level_three_present":null,"field_options":{},"has_section":null},{"field_type":"default_source","id":1000044230,"name":"source","dom_type":"hidden","label":"Source","label_in_portal":"Source","description":"Ticket source","position":4,"active":true,"required":false,"required_for_closure":false,"visible_in_portal":false,"editable_in_portal":false,"required_in_portal":false,"choices":[["Email",1],["Portal",2],["Phone",3],["Forum",4],["Twitter",5],["Facebook",6],["Chat",7],["MobiHelp",8],["Feedback Widget",9],["Outbound Email",10],["Ecommerce",11]],"levels":null,"level_three_present":null,"field_options":{"section":false},"has_section":null},{"field_type":"default_status","id":1000044231,"name":"status","dom_type":"dropdown","label":"Status","label_in_portal":"Status","description":"Ticket status","position":5,"active":true,"required":true,"required_for_closure":false,"visible_in_portal":true,"editable_in_portal":false,"required_in_portal":false,"choices":[{"status_id":2,"name":"Open","customer_display_name":"Being Processed","stop_sla_timer":false,"deleted":false},{"status_id":3,"name":"Pending","customer_display_name":"Awaiting your Reply","stop_sla_timer":true,"deleted":false},{"status_id":4,"name":"Resolved","customer_display_name":"This ticket has been Resolved","stop_sla_timer":true,"deleted":false},{"status_id":5,"name":"Closed","customer_display_name":"This ticket has been Closed","stop_sla_timer":true,"deleted":false},{"status_id":6,"name":"Waiting on Customer","customer_display_name":"Awaiting your Reply","stop_sla_timer":true,"deleted":false},{"status_id":7,"name":"Waiting on Third Party","customer_display_name":"Being Processed","stop_sla_timer":false,"deleted":false}],"levels":null,"level_three_present":null,"field_options":{"section":false},"has_section":null},{"field_type":"default_priority","id":1000044232,"name":"priority","dom_type":"dropdown","label":"Priority","label_in_portal":"Priority","description":"Ticket priority","position":6,"active":true,"required":true,"required_for_closure":false,"visible_in_portal":true,"editable_in_portal":true,"required_in_portal":true,"choices":[["Low",1],["Medium",2],["High",3],["Urgent",4]],"levels":null,"level_three_present":null,"field_options":{"section":false},"has_section":null},{"field_type":"default_group","id":1000044233,"name":"group","dom_type":"dropdown_blank","label":"Group","label_in_portal":"Group","description":"Ticket group","position":7,"active":true,"required":false,"required_for_closure":false,"visible_in_portal":false,"editable_in_portal":false,"required_in_portal":false,"choices":[["Business Supplier Support",1000197160],["intuPBX Support",1000191834],["NEON Support",1000196143],["PBX Support",1000187090],["Support",1000085319],["Wavetel Business Orders",1000197136]],"levels":null,"level_three_present":null,"field_options":{"section":false},"has_section":null},{"field_type":"default_agent","id":1000044234,"name":"agent","dom_type":"dropdown_blank","label":"Agent","label_in_portal":"Assigned to","description":"Agent","position":8,"active":true,"required":false,"required_for_closure":false,"visible_in_portal":true,"editable_in_portal":false,"required_in_portal":false,"choices":[["Abdul Samad Ali Shah",1010820579],["Fahad Shaikh",1017766697],["Khurram Saeed",1001328845],["Muhammad Atif",1002131632],["Muhammad Farooq",1001544570],["Muhammad Waqas",1001324770],["Noman Zahoor",1017936630],["Support",1000135497],["Zaid",1021825011]],"levels":null,"level_three_present":null,"field_options":{"section":false},"has_section":null},{"field_type":"default_product","id":1000044235,"name":"product","dom_type":"dropdown_blank","label":"Product","label_in_portal":"Product","description":"Select the product, the ticket belongs to.","position":9,"active":true,"required":false,"required_for_closure":false,"visible_in_portal":true,"editable_in_portal":true,"required_in_portal":false,"choices":[["intuPBX Support",1000005459],["NEON Support",1000006380],["Wavetel Business Orders",1000006611],["Wavetel Business Support",1000004358]],"levels":null,"level_three_present":null,"field_options":{"section":false},"has_section":null},{"field_type":"default_description","id":1000044236,"name":"description","dom_type":"html_paragraph","label":"Description","label_in_portal":"Description","description":"Ticket description","position":9,"active":true,"required":true,"required_for_closure":false,"visible_in_portal":true,"editable_in_portal":true,"required_in_portal":true,"choices":[],"levels":null,"level_three_present":null,"field_options":{},"has_section":null}];
    customSection = [];
    shared_ownership_enabled = false;
    sharedGroups = [];

    tf_lang = {
     untitled:                    "Untitled",
      firstChoice:                "First Choice",
      secondChoice:               "Second Choice", 
      customerLabelEmpty:         "Customer label cannot not be empty",
      noChoiceMessage:            "At least one valid choice has to be present",
      confirmDelete:              'Warning! You will lose the data pertaining to this field in all old tickets permanently. Are you sure you want to delete this field?',
      displayCCField:             'Display CC Field',
      ccCompanyContacts:          'Can CC only company contacts',
      ccAnyEmail:                 'Can CC any email address',
      sla_timer:                  'SLA timer',
      field_delete_disabled:      'Default fields cannot be deleted',
      section_type_change:        'Please save the ticket form to see the latest changes that you have made to type field.',
      dropdown_choice_disabled:   'Default choice cant be deleted',
      dropdown_items_edit:        'Dropdown items - Edit',
      dropdown_items_preview:     'Dropdown items - Preview',
      nestedfield_helptext_preview: 'This is the preview of sample dropdown items. Click \&quot;Edit\&quot; to change the values for each drop down.',
      nestedfield_helptext:       'Use the below textarea to add or edit items in your dropdown. Indent items by pressing the tab key once or twice. We will convert it to dropdown items based on the indentation. &lt;a target=\&#x27;_blank\&#x27; href=\&#x27;https://support.freshdesk.com/solution/categories/45957/folders/74594/articles/37599-using-dependent-fields\&#x27;&gt;Learn more&lt;\/a&gt; ',
      confirm_delete:             '<span class="translation_missing" title="translation missing: en.ticket_fields.formfield2_props.confirm_delete">Confirm Delete</span>',
      agent_mandatory_closure:    'Required when closing the ticket',
      remove_type:                'Section is associated with this type',
      new_section:                'New Section',
      confirm_text:               'Please Confirm...',
      would_you_like_to:          'Would you like to',
      move_keep_copy:             'Copy field to target section',
      delete_field_section:       'Delete field from this section',
      move_field_remove_section:  'Move field to target section',
      delete_permanent:           'Delete field from all sections',
      delete_from_section:        'Are you sure you want to delete this field from this section?',
      field_remove_all_section:   'Moving the field outside will remove the field from all sections',
      delete_section:             'Are you sure you want to delete this section?',
      section_has_fields:         'Section has fields',
      section_delete_disabled:    '<span class="translation_missing" title="translation missing: en.ticket_fields.section.section_delete_disabled">Section Delete Disabled</span>',
      field_available:            'The field already exists in target section.',
      delete_section_btn:         'Delete Section',
      ok_btn:                     'OK',
      confirm_btn:                'Confirm',
      oops_btn:                   'Oops!',
      delete_btn:                 'Delete',
      continue_btn:               'Continue',
      section_prop:               'Section Properties',
      sectino_label:              'Section Title',
      section_type_is:            'Show section when type is',  
      unique_section_name:        'Section name already used',  
      formTitle:                  'Properties',
      deleteField:                'Delete field',
      regExp:                     'Regular Expression',
      regExpExample:              'For example, To match a string that contains only alphabets & numbers: <b> /^[a-zA-Z0-9]*$/ <\/b>. To match a string that starts with word \"fresh\" <b> /^freshw/ <\/b>.',
      dropdownChoice:             'Dropdown Items',
      addNewChoice:               'Add Item',
      label:                      'Label',
      labelAgent:                 'Field label for agents',
      labelCustomer:              'Field label for customers',
      labelAgentLevel2:           'Level 2 label for agents',
      labelCustomerLevel2:        'Level 2 label for customers',
      behavior:                   'Behavior',
      forAgent:                   'For Agents',
      agentMandatory:             'Required when submitting the form',
      forCustomer:                'For Customers',
      customerVisible:            'Display to customer',
      customerEditable:           'Customer can edit',
      customerMandatory:          'Required when submitting the form',
      customerEditSignup:         'Customer can see this when they Sign up',
      validateRegex:              'Validate using Regular Expression',
      nested_tree_validation:     'You need atleast one category &amp; sub-category',
      nested_unique_names:        'Agent label should not be same as other two levels',
      nested_3rd_level:           'Label required for 3rd level items',
      mappedInternalGroup:        'Mapped Internal Groups',

      default:            'Default',
      number:             'Number',
      text:               'Single Line Text',
      empty_section_info: 'Drop fields here',
      paragraph:          'Multi Line Text',
      checkbox:           'Checkbox',
      dropdown:           'Dropdown',
      dropdown_blank:     'Dropdown',
      nested_field:       'Dependent field',
      phone_number:       'Phone Number',
      url:                'URL',
      date:               'Date',
      email:              'Email',
      decimal:            'Decimal',
      default_field_error:'Default fields cannot be dropped into section',
      learnMore:          'Learn more',
      delete:             'Delete',
      cancel:             'Cancel',
      done:               'Done',
      preview:            'Preview',
      edit:               'Edit',
      nestedFieldLabel:   'Dependent field labels',
      level:              'Level',
      maxItemsReached:    'Maximum items reached'
    };

    window['translate'] = {}; 
    translate.get = function(name){
      return tf_lang[name] || "";
    };

//]]>
</script> 

<script src="{{ URL::asset('assets/formbuilder/js/ticket_fields.js')}}" type="text/javascript"></script>