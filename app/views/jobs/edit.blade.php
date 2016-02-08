<?php
/*
Plugin 
*/
?><?php


add_filter('rwmb_meta_boxes', 'srs_register_meta_boxes');


function srs_register_meta_boxes($meta_boxes){
    // Better has an underscore as last sign
    $prefix = 'flight_';
    // 1st meta box
    $meta_boxes[] = array(
        // Meta box id, UNIQUE per meta box. Optional since 4.1.5
        'id' => 'standard',
        // Meta box title - Will appear at the drag and drop handle bar. Required.
        'title' => __('Flight Iternary', 'rwmb'),
        // Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
        'pages' => array('flight', 'pages'),
        // Where the meta box appear: normal (default), advanced, side. Optional.
        'context' => 'normal',

        // Order of meta box: high (default), low. Optional.

        'priority' => 'high',


        // Auto save: true, false (default). Optional.

        'autosave' => true,


        // List of meta fields

        'fields' => array(

            // HEADING

            array(

                'type' => 'heading',

                'name' => __('Primary Information ', 'rwmb'),

                'id' => 'fake_id', // Not used but needed for plugin

            ),

            array(

                // Field name - Will be used as label

                'name' => __('Featured Flight', 'rwmb'),

                // Field ID, i.e. the$meta key

                'id' => "{$prefix}featuredflight",

                // Field description (optional)

                'desc' => __('Do you want this flight in home page Main Slide Show', 'rwmb'),

                'type' => 'checkbox',

                // Default value (optional)

                'std' => __('', 'rwmb'),


                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),

            array(

                // Field name - Will be used as label

                'name' => __('Code', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}code",

                // Field description (optional)

                'desc' => __(' Flight Code', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),


                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            array(

                // Field name - Will be used as label

                'name' => __('Departure From', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}from",

                // Field description (optional)

                'desc' => __('Departure From Flight Code', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),

                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            // RADIO BUTTONS

            array(

                'name' => __('Direct', 'rwmb'),

                'id' => "{$prefix}direct",

                'type' => 'radio',

                // Array of 'value' => 'Label' pairs for radio options.

                // Note: the 'value' is stored in meta field, not the 'Label'

                'options' => array(

                    '1' => __('Yes', 'rwmb'),

                    '0' => __('No', 'rwmb'),


                ),

                'std' => 0,


            ),

            array(

                // Field name - Will be used as label

                'name' => __('Stop', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}stop",

                // Field description (optional)

                'desc' => __('Stop', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),

                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            // HEADING

            array(

                'type' => 'heading',

                'name' => __('Flight Schedule ', 'rwmb'),

                'id' => 'fake_id', // Not used but needed for plugin

            ),


            array(

                'name' => __('Departure Date/Time', 'rwmb'),

                'id' => "{$prefix}departuredate",

                //'type' => 'date',

                'type' => 'datetime',


                // jQuery date picker options. See here http://api.jqueryui.com/datepicker

                'js_options' => array(

                    'appendText' => __(' Format should be (YY-mm-dd)', 'rwmb'),

                    'dateFormat' => __('yy-mm-dd', 'rwmb'),

                    'changeMonth' => true,

                    'changeYear' => true,

                    'showButtonPanel' => true,

                    'stepMinute' => 15,

                    'showTimepicker' => true,

                ),

            ),


            // DATETIME

            array(

                'name' => __(' Arrival Date/Time', 'rwmb'),

                //'id'   => $prefix . 'datetime',

                'id' => "{$prefix}arrivaldate",

                'type' => 'datetime',


                // jQuery datetime picker options.

                // For date options, see here http://api.jqueryui.com/datepicker

                // For time options, see here http://trentrichardson.com/examples/timepicker/

                'js_options' => array(

                    'appendText' => __(' Format should be (YY-mm-dd)', 'rwmb'),

                    'dateFormat' => __('yy-mm-dd', 'rwmb'),

                    'changeMonth' => true,

                    'changeYear' => true,

                    'showButtonPanel' => true,

                    'stepMinute' => 15,

                    'showTimepicker' => true,

                ),

            ),


            // HEADING

            array(

                'type' => 'heading',

                'name' => __('Price ', 'rwmb'),

                'id' => 'fake_id', // Not used but needed for plugin

            ),


            /*array(

            // Field name - Will be used as label

            'name'  => __( 'Price', 'rwmb' ),

            // Field ID, i.e. the meta key

            'id'    => "{$prefix}price",

            // Field description (optional)

            'desc'  => __( 'Price', 'rwmb' ),

            'type'  => 'text',

            // Default value (optional)

            'std'   => __( '', 'rwmb' ),

            // CLONES: Add to make the field cloneable (i.e. have multiple value)

            'clone' => false,

        ),*/


            /*array(

                'name'    => __( 'Return', 'rwmb' ),

                'id'      => "{$prefix}return",

                'type'    => 'radio',

                // Array of 'value' => 'Label' pairs for radio options.

                // Note: the 'value' is stored in meta field, not the 'Label'

                'options' => array(

                    '1' => __( 'Yes', 'rwmb' ),

                    '0' => __( 'No', 'rwmb' ),



                ),

                'std'  => 0,

            ),

                */

            array(

                // Field name - Will be used as label

                'name' => __('Return Price', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}returnprice",

                // Field description (optional)

                'desc' => __('Return Price', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),

                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            array(

                'name' => __('is Special?', 'rwmb'),

                'id' => "{$prefix}isspecial",

                'type' => 'radio',

                // Array of 'value' => 'Label' pairs for radio options.

                // Note: the 'value' is stored in meta field, not the 'Label'

                'options' => array(

                    '1' => __('Yes', 'rwmb'),

                    '0' => __('No', 'rwmb'),

                ),

                'std' => 0,

            ),


            /*array(

            // Field name - Will be used as label

            'name'  => __( 'Special Price', 'rwmb' ),

            // Field ID, i.e. the$meta key

            'id'    => "{$prefix}specialprice",

            // Field description (optional)

            'desc'  => __( '', 'rwmb' ),

            'type'  => 'text',

            // Default value (optional)

            'std'   => __( '', 'rwmb' ),

            // CLONES: Add to make the field cloneable (i.e. have multiple value)

            'clone' => false,

        ),*/


            array(

                // Field name - Will be used as label

                'name' => __('Economy Class', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}economyclass",

                // Field description (optional)

                'desc' => __('Economy Class Price', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),

                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            array(

                // Field name - Will be used as label

                'name' => __('Premium Class', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}premiumclass",

                // Field description (optional)

                'desc' => __('Premium Class Price', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),

                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            array(

                // Field name - Will be used as label

                'name' => __('First Class', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}firstclass",

                // Field description (optional)

                'desc' => __('First Class Price', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),

                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            array(

                // Field name - Will be used as label

                'name' => __('Business Class', 'rwmb'),

                // Field ID, i.e. the meta key

                'id' => "{$prefix}businessclass",

                // Field description (optional)

                'desc' => __('Business Class Price', 'rwmb'),

                'type' => 'text',

                // Default value (optional)

                'std' => __('', 'rwmb'),

                // CLONES: Add to make the field cloneable (i.e. have multiple value)

                'clone' => false,

            ),


            // HEADING

            array(

                'type' => 'heading',

                'name' => __('Flight Thumbnail ', 'rwmb'),

                'id' => 'fake_id', // Not used but needed for plugin

            ),

            array(

                'name' => __('Thumbnail Image', 'rwmb'),

                'id' => "{$prefix}flightimage",

                'desc' => __('For Home Page and For Detail Page (Max Size: 270px X 228px)', 'rwmb'),


                'type' => 'image_advanced',

                'max_file_uploads' => 1,

            ),

            array(

                'name' => __('Detail Page Banner Images', 'rwmb'),

                'id' => "{$prefix}flightbannerimage",

                'desc' => __('For Detail Page Big Banner (Max Size: 1500px X 500px)', 'rwmb'),

                'type' => 'image_advanced',

                'max_file_uploads' => 1,

            ),


            array(

                'name' => __('Banner Images Short Description', 'rwmb'),

                'id' => "{$prefix}flightbannerimagedescription",

                'desc' => __('Short Description of Banner Image (1500px X 500px) ', 'rwmb'),

                'type' => 'textarea',

                'max_file_uploads' => 1,

            ),


            // TEXTAREA

            //array(

            //'name' => __( 'Textarea', 'rwmb' ),

            //'desc' => __( 'Textarea description', 'rwmb' ),

            //'id'   => "{$prefix}textarea",

            //'type' => 'textarea',

            //'cols' => 20,

            //'rows' => 3,

            //),

            //),

            //'validation' => array(

            //'rules' => array(

            //	"{$prefix}password" => array(

            //	'required'  => true,

            //'minlength' => 7,

            //),

            //),

            // optional override of default jquery.validate messages

            //'messages' => array(

            //	"{$prefix}password" => array(

            //	'required'  => __( 'Password is required', 'rwmb' ),

            //'minlength' => __( 'Password must be at least 7 characters', 'rwmb' ),

            //),

            //)

        ),

        'validation' => array(

            'rules' => array(

                "{$prefix}code" => array(

                    'required' => true,

                    'minlength' => 3,

                ),

                "{$prefix}to" => array(

                    'required' => true,

                    'minlength' => 3,

                ),

            ),

            // optional override of default jquery.validate messages

            /*'messages' => array(

                "{$prefix}password" => array(

                    'required'  => __( 'Password is required', 'rwmb' ),

                    'minlength' => __( 'Password must be at least 7 characters', 'rwmb' ),

                ),

            )*/

        ),

    );


    // 2nd meta box

    //$meta_boxes[] = array(

    //'title' => __( '', 'rwmb' ),


    //'fields' => array(

    // HEADING

    //array(

    //'type' => 'heading',

    //'name' => __( 'Heading', 'rwmb' ),

    //'id'   => 'fake_id', // Not used but needed for plugin

    //),

    // SLIDER

    //array(

    //'name' => __( 'Price Slider', 'rwmb' ),

    //'id'   => "{$prefix}slider",

    //	'type' => 'slider',


    // Text labels displayed before and after value

    //	'prefix' => __( '$', 'rwmb' ),

    //	'suffix' => __( ' USD', 'rwmb' ),


    // jQuery UI slider options. See here http://api.jqueryui.com/slider/

    //	'js_options' => array(

    //	'min'   => 10,

    //	'max'   => 1000,

    //	'step'  => 5,

    //),

    //),

    // NUMBER

    //array(

    //'name' => __( 'Number', 'rwmb' ),

    //'id'   => "{$prefix}number",

    //'type' => 'number',


    //'min'  => 0,

    //'step' => 5,

    //),

    // DATE


    // COLOR

    //	array(

    //	'name' => __( 'Color picker', 'rwmb' ),

    //	'id'   => "{$prefix}color",

    //'type' => 'color',

    //),

    // CHECKBOX LIST

    //array(

    //'name' => __( 'Checkbox list', 'rwmb' ),

    //'id'   => "{$prefix}checkbox_list",

    //'type' => 'checkbox_list',

    // Options of checkboxes, in format 'value' => 'Label'

    //	'options' => array(

    //	'value1' => __( 'Label1', 'rwmb' ),

    //	'value2' => __( 'Label2', 'rwmb' ),

    //),

    //),

    // EMAIL

    //array(

    //'name'  => __( 'Email', 'rwmb' ),

//				'id'    => "{$prefix}email",

//				'desc'  => __( 'Email description', 'rwmb' ),

//				'type'  => 'email',

//				'std'   => 'name@email.com',

//			),

//			// RANGE

    //array(

    //'name'  => __( 'Range', 'rwmb' ),

    //'id'    => "{$prefix}range",

    //'desc'  => __( 'Range description', 'rwmb' ),

    //'type'  => 'range',

    //'min'   => 0,

    //'max'   => 100,

    //'step'  => 5,

    //'std'   => 0,

    //),

    // URL

    //array(

    //	'name'  => __( 'URL', 'rwmb' ),

    //	'id'    => "{$prefix}url",

    //	'desc'  => __( 'URL description', 'rwmb' ),

    //	'type'  => 'url',

    //	'std'   => 'http://google.com',

    //	),

    // OEMBED

    //array(

    //	'name'  => __( 'oEmbed', 'rwmb' ),

    //	'id'    => "{$prefix}oembed",

    //'desc'  => __( 'oEmbed description', 'rwmb' ),

    //	'type'  => 'oembed',

    //),

    // SELECT ADVANCED BOX

    //array(

    //	'name'     => __( 'Select', 'rwmb' ),

    //	'id'       => "{$prefix}select_advanced",

    //	'type'     => 'select_advanced',

    // Array of 'value' => 'Label' pairs for select box

    //	'options'  => array(

    //'value1' => __( 'Label1', 'rwmb' ),


    //'value2' => __( 'Label2', 'rwmb' ),

    //),

    // Select multiple values, optional. Default is false.

    //	'multiple'    => false,

    // 'std'         => 'value2', // Default value, optional

    //	'placeholder' => __( 'Select an Item', 'rwmb' ),

    //	),

    // TAXONOMY

    //array(

//				'name'    => __( 'Taxonomy', 'rwmb' ),

//				'id'      => "{$prefix}taxonomy",

//				'type'    => 'taxonomy',

//				'options' => array(

    // Taxonomy name

    //	'taxonomy' => 'category',

    // How to show taxonomy: 'checkbox_list' (default) or 'checkbox_tree', 'select_tree', select_advanced or 'select'. Optional

    //'type' => 'checkbox_list',

    // Additional arguments for get_terms() function. Optional

    //	'args' => array()

    //	),

    //	),

    // POST

    //array(

    //'name'    => __( 'Posts (Pages)', 'rwmb' ),

    //'id'      => "{$prefix}pages",

    //'type'    => 'post',


    // Post type

    //'post_type' => 'page',

    // Field type, either 'select' or 'select_advanced' (default)

    //'field_type' => 'select_advanced',

    // Query arguments (optional). No settings means get all published posts

    //'query_args' => array(

    //	'post_status' => 'publish',

    //	'posts_per_page' => '-1',

    //)

    //),

    // WYSIWYG/RICH TEXT EDITOR

    //array(

    //'name' => __( 'WYSIWYG / Rich Text Editor', 'rwmb' ),

    //'id'   => "{$prefix}wysiwyg",

    //'type' => 'wysiwyg',

    // Set the 'raw' parameter to TRUE to prevent data being passed through wpautop() on save

    //	'raw'  => false,

    //	'std'  => __( 'WYSIWYG default value', 'rwmb' ),


    // Editor settings, see wp_editor() function: look4wp.com/wp_editor

    //	'options' => array(

    //	'textarea_rows' => 4,

    //	'teeny'         => true,

    //	'media_buttons' => false,

    //),

    //),

    // DIVIDER

    //array(

    //'type' => 'divider',

    //	'id'   => 'fake_divider_id', // Not used, but needed

    //),

    // FILE UPLOAD

    //	array(

    //	'name' => __( 'File Upload', 'rwmb' ),

    //	'id'   => "{$prefix}file",

    //	'type' => 'file',

    //),

    // FILE ADVANCED (WP 3.5+)

    //	array(

    //	'name' => __( 'File Advanced Upload', 'rwmb' ),

    //	'id'   => "{$prefix}file_advanced",

    //	'type' => 'file_advanced',

    //	'max_file_uploads' => 4,

    //	'mime_type' => 'application,audio,video', // Leave blank for all file types

    //),

    // IMAGE UPLOAD

    //array(

    //'name' => __( 'Image Upload', 'rwmb' ),

    //'id'   => "{$prefix}image",

    //'type' => 'image',

    //),

    // THICKBOX IMAGE UPLOAD (WP 3.3+)

    //	array(

    //	'name' => __( 'Thickbox Image Upload', 'rwmb' ),

    //	'id'   => "{$prefix}thickbox",

    //	'type' => 'thickbox_image',

    //),

    // PLUPLOAD IMAGE UPLOAD (WP 3.3+)

    //array(

    //	'name'             => __( 'Plupload Image Upload', 'rwmb' ),

    //	'id'               => "{$prefix}plupload",

    //	'type'             => 'plupload_image',

    //	'max_file_uploads' => 4,

    //),

    // IMAGE ADVANCED (WP 3.5+)

    //array(

    //	'name'             => __( 'Image Advanced Upload', 'rwmb' ),

    //'id'               => "{$prefix}imgadv",

    //'type'             => 'image_advanced',

    //	'max_file_uploads' => 4,

    //),

    // BUTTON

    //	array(

    //	'id'   => "{$prefix}button",

    //	'type' => 'button',

    //'name' => ' ', // Empty name will "align" the button to all field inputs

    //),


    //)

    //);


    return $meta_boxes;

}