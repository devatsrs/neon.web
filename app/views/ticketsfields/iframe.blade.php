<html>
<head>
<meta charset="utf-8">
<title>Ticket fields</title>
	<meta name="author" content="">
	<meta name="keywords" content="">
	<!-- Adding meta tag for CSRF token -->
	
	<!-- End meta tag for CSRF token -->
	<!-- Mobile viewport optimized: j.mp/bplateviewport -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{{ URL::asset('assets/formbuilder/css/common.css')}}" media="screen" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/formbuilder/css/pattern.css')}}" media="screen, print, projection" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/formbuilder/css/plugins.css')}}" media="screen" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/formbuilder/css/sprites.css')}}" media="screen" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/formbuilder/css/app.css')}}" media="screen" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/neon-forms.css')}}" media="screen" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/neon-theme.css')}}" media="screen" rel="stylesheet" type="text/css" />
<style>
.phpdebugbar{display:none;}
.antipattern{background:#f1f1f1 !important;}
</style>
<script type="text/javascript">
    var baseurl = "{{URL::to('/')}}";
    {{js_labels()}}
</script>
<script src="{{ URL::asset('assets/formbuilder/js/defaults.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset('assets/formbuilder/js/frameworks.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset('assets/formbuilder/js/workspace.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset('assets/formbuilder/js/pattern.js')}}" type="text/javascript"></script>
</head>

<body class="antipattern">
<div id="inner">
  <div class="leftcontent">
    <div id="Pagearea" class="Pagearea">
      <div class="ticket_fields">
        <div class="page-header-sticky custom-field-header" rel="sticky">
          <div class="sticky-header-wrapper">            
            <div class="pull-right mb20"> <a href="/admin/home" class="btn">Cancel</a>
              <input class="btn btn-primary save-custom-form" data-commit="Save" name="commit" value="Save" type="submit">
            </div>
          </div>
          <div class="field-picker">
            <div class="field-info-wrapper">
              <h3 class="title">Drag &amp; Drop Field</h3>
              <ul id="custom-fields" class="custom-fields">
                <li class="field tooltip" data-type="text" 
                data-field-type="custom_text" data-fresh="true" 
                data-drag-info="Single Line Text"> <span class="ficon-text dom-icon tooltip" title="Single Line Text"></span> </li>
                <li class="field tooltip" data-type="paragraph" 
                data-field-type="custom_paragraph" data-fresh="true" 
                data-drag-info="Multi Line Text"> <span class="ficon-default-format dom-icon tooltip" title="Multi Line Text"></span> </li>
                <li class="field tooltip" data-type="checkbox" 
                data-field-type="custom_checkbox" data-fresh="true" 
                data-drag-info="Checkbox"> <span class="ficon-checkbox dom-icon tooltip" title="Checkbox"></span> </li>
                <li class="field tooltip" data-type="number" 
                data-field-type="custom_number" data-fresh="true" 
                data-drag-info="Number"> <span class="ficon-number dom-icon tooltip" title="Number"></span> </li>
                <li class="field tooltip" data-type="dropdown_blank" 
                data-field-type="custom_dropdown" data-fresh="true" 
                data-drag-info="Dropdown"> <span class="ficon-dropdown_blank dom-icon tooltip" title="Dropdown"></span> </li>
                <li class="field tooltip" data-type="date" 
                data-field-type="custom_date" data-fresh="true" 
                data-drag-info="Date"> <span class="ficon-date dom-icon tooltip" title="Date"></span> </li>
                <li class="field tooltip" data-type="decimal" 
                data-field-type="custom_decimal" data-fresh="true" 
                data-drag-info="Decimal"> <span class="ficon-decimal dom-icon tooltip" title="Decimal"></span> </li>
              </ul>
            </div>
          </div>
        </div>
        <form accept-charset="UTF-8" action="{{ URL::to('ticketsfields/iframe/submit') }}" class="edit_helpdesk_ticket_field" id="Updateform" method="post">
          <div style="margin:0;padding:0;display:inline">
            <input name="utf8" type="hidden" value="&#x2713;" />
            <input name="_method" type="hidden" value="put" />
          </div>
          <div class="custom_form_outer">
            <input type="hidden" name="jsonData" id="field_values" value="" />
            <input type="hidden" name="jsonSectionData" id="section_field_values" value="" />
            <div>
              <ul id="custom-field-form" class="form-horizontal custom-field-form" data-item-class=".custom-field">
              </ul>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="CustomFieldsPropsDialog"></div>
<script type="text/javascript">
//<![CDATA[ 
customFields = {{$finaljson}};

    // Localizing ticket fields js elements
	console.log(customFields);
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
      nestedfield_helptext:       'Use the below textarea to add or edit items in your dropdown. Indent items by pressing the tab key once or twice. We will convert it to dropdown items based on the indentation. &lt;a target=\&#x27;_blank\&#x27;',
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

</body>
</html>