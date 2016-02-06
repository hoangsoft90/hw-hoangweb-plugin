<?php
  //include the main class file
  require_once("libs/admin-page-class/admin-page-class.php");
  
  
  /**
   * configure your admin page
   */
  $config = array(    
    'menu'           => 'settings',             //sub page to settings page
    'page_title'     => __('Public Localhost','apc'),       //The name of this page
    'capability'     => 'edit_themes',         // The capability needed to view the page 
    'option_group'   => 'hwngrok_options',       //the name of the option to create in the database
    'id'             => 'hw_admin_page',            // meta box id, unique per page
    'fields'         => array(),            // list of fields (can be added by field arrays)
    'local_images'   => false,          // Use local or hosted images (meta box images for add/remove)
    'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
  );  
  
  /**
   * instantiate your admin page
   */
  $options_panel = new BF_Admin_Page_Class($config);
  $options_panel->OpenTabs_container('');
  
  /**
   * define your admin page tabs listing
   */
  $options_panel->TabsListing(array(
    'links' => array(
      'options_1' =>  __('Simple Options','apc'),
      'options_2' =>  __('Fancy Options','apc'),
      /*'options_3' => __('Editor Options','apc'),
      'options_4' => __('WordPress Options','apc'),
      'options_5' =>  __('Advanced Options','apc'),
      'options_6' =>  __('Field Validation','apc'),
      'options_7' =>  __('Import Export','apc'),*/
    )
  ));
  
  /**
   * Open admin page first tab
   */
  $options_panel->OpenTab('options_1');

  /**
   * Add fields to your admin page first tab
   * 
   * Simple options:
   * input text, checbox, select, radio 
   * textarea
   */
  //title
  $options_panel->Title(__("For ngrok hoang plugin","apc"));
  //An optionl descrption paragraph
  $options_panel->addParagraph(__("This is a simple paragraph","apc"));
  //text field
  $options_panel->addText('text_baseurl_ngrok', array('name'=> __('Ngrok URL ','apc'), 'std'=> '', 'desc' => __('fill website base url with Ngrok. ie: http://22cbcbe4.ngrok.com/wordpress','apc')));

/*
  //textarea field
  $options_panel->addTextarea('textarea_field_id',array('name'=> __('My Textarea ','apc'), 'std'=> 'textarea', 'desc' => __('Simple textarea field description','apc')));
  //checkbox field
  $options_panel->addCheckbox('checkbox_field_id',array('name'=> __('My Checkbox ','apc'), 'std' => true, 'desc' => __('Simple checkbox field description','apc')));
  //select field
  $options_panel->addSelect('select_field_id',array('selectkey1'=>'Select Value1','selectkey2'=>'Select Value2'),array('name'=> __('My select ','apc'), 'std'=> array('selectkey2'), 'desc' => __('Simple select field description','apc')));
  //radio field
  $options_panel->addRadio('radio_field_id',array('radiokey1'=>'Radio Value1','radiokey2'=>'Radio Value2'),array('name'=> __('My Radio Filed','apc'), 'std'=> array('radiokey2'), 'desc' => __('Simple radio field description','apc')));
  */
  /**
   * Close first tab
   */   
  $options_panel->CloseTab();


  /**
   * Open admin page Second tab
   */
  $options_panel->OpenTab('options_2');
  /**
   * Add fields to your admin page 2nd tab
   * 
   * Fancy options:
   *  typography field
   *  image uploader
   *  Pluploader
   *  date picker
   *  time picker
   *  color picker
   */
  //title
  $options_panel->Title(__('Fancy Options','apc'));
  //Typography field
  $options_panel->addTypo('typography_field_id',array('name' => __("My Typography","apc"),'std' => array('size' => '14px', 'color' => '#000000', 'face' => 'arial', 'style' => 'normal'), 'desc' => __('Typography field description','apc')));
  //Image field
  $options_panel->addImage('image_field_id',array('name'=> __('My Image ','apc'),'preview_height' => '120px', 'preview_width' => '440px', 'desc' => __('Simple image field description','apc')));
  //PLupload field
  $options_panel->addPlupload('plupload_field_ID',array('name' => __('PlUpload Field','apc'), 'multiple' => true, 'desc' => __('Simple multiple image field description','apc')));  
  //date field
  $options_panel->addDate('date_field_id',array('name'=> __('My Date ','apc'), 'desc' => __('Simple date picker field description','apc')));
  //Time field
  $options_panel->addTime('time_field_id',array('name'=> __('My Time ','apc'), 'desc' => __('Simple time picker field description','apc')));
  //Color field
  $options_panel->addColor('color_field_id',array('name'=> __('My Color ','apc'), 'desc' => __('Simple color picker field description','apc')));
  
  /**
   * Close second tab
   */ 
  $options_panel->CloseTab();


  //Now Just for the fun I'll add Help tabs
  $options_panel->HelpTab(array(
    'id'      =>'tab_id',
    'title'   => __('My help tab title','apc'),
    'content' =>'<p>'.__('This is my Help Tab content','apc').'</p>'
  ));
  $options_panel->HelpTab(array(
    'id'       => 'tab_id2',
    'title'    => __('My 2nd help tab title','apc'),
    'callback' => 'help_tab_callback_demo'
  ));
  
  //help tab callback function
  function help_tab_callback_demo(){
    echo '<p>'.__('This is my 2nd Help Tab content from a callback function','apc').'</p>';
  }