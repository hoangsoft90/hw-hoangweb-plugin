<?php
//NHP skin
$theme_options[] = array(
#    'id' => 'x',
    'type' => 'string',
    'desc' => 'Bạn cần kích hoạt dịch vụ "Google translate" ở tùy chọn dưới.'
);
$theme_options[] = array(
    'type' => 'text',
    'id' => 'google_translate_ID',
    'desc' => 'Tên ID cho thẻ div nơi hiển thị google translate.'
);
$theme_options[] = array(
    'id' => 'show_all_langs',
    'type' => 'checkbox',
    'desc' => 'Hiển thị mọi ngôn ngữ.'
);
$theme_options[] = array(
    'id' => 'specific_langs',
    'type' => 'multi_select',   //'select'
    'desc' => 'Cho phép dịch các ngôn ngữ này.',
    'options' => array(
        'af' => 'Afrikaans',
        'zh-CN' => 'Chinese',
        'en' => 'English',
        'fr' => 'French',
        'de' => 'German',
        'ko' => 'Korean',
        'vi' => 'Vietnamese'
    ),
    /*'attributes' => array(    //for type='select'
        'size' => 10,
        'multiple' => 'multiple'
    )*/

);
$theme_options[] = array(
    'id' => 'display_mode',
    'type' => 'select',
    'desc' => '',
    'options' => array(
        'inline-veritcal' => 'Inline Vertical',
        'inline-horizontal' => 'Inline Horizontal',
        'inline-dropdown-only' => 'Inline Dropdown only',
        'tabbed-lower-right' => 'Tabbed Lower Right',
        'tabbed-lower-left' => 'Tabbed Lower Left',
        'tabbed-upper-right' => 'Tabbed Upper Right',
        'tabbed-upper-left' => 'Tabbed Upper Left',
    )
);