<?php
$theme_options[] = array(
    'name' => 'topSpacing',
    'type' => 'text',
    'description' => "Pixels between the page top and the element's top.",
    'value' => '0'
);
$theme_options[] = array(
    'name' => 'bottomSpacing',
    'type' => 'text',
    'description' => "Pixels between the page bottom and the element's bottom.",
    'value' => ''
);
$theme_options[] = array(
    'name' => 'className',
    'type' => 'text',
    'description' => "CSS class added to the element's wrapper when 'sticked'.",
    'value' => ''
);
$theme_options[] = array(
    'name' => 'wrapperClassName',
    'type' => 'text',
    'description' => "CSS class added to the wrapper.",
    'value' => ''
);
$theme_options[] = array(
    'name' => 'getWidthFrom',
    'type' => 'text',
    'description' => 'Selector of element referenced to set fixed width of "sticky" element.',
    'value' => ''
);
$theme_options[] = array(
    'name' => 'responsiveWidth',
    'type' => 'checkbox',
    'description' => "boolean determining whether widths will be recalculated on window resize (using getWidthfrom).",

);