<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 30/06/2015
 * Time: 09:03
 */
$theme_options[] = array(
    'name' => 'padding',
    'type' => 'text',
    'description' => 'Space inside fancyBox around content.',
    'value' => '15'
);
$theme_options[] = array(
    'name' => 'margin',
    'type' => 'text',
    'description' => 'Minimum space between viewport and fancyBox. Can be set as array - [top, right, bottom, left].',
    'value' => '20'
);
$theme_options[] = array(
    'name' => 'width',
    'type' => 'text',
    'description' => "Default width for 'iframe' and 'swf' content. Also for 'inline', 'ajax' and 'html' if 'autoSize' is set to 'false'. Can be numeric or 'auto'.	",
    'value' => '800'
);
$theme_options[] = array(
    'name' => 'height',
    'type' => 'text',
    'description' => "Default height for 'iframe' and 'swf' content. Also for 'inline', 'ajax' and 'html' if 'autoSize' is set to 'false'. Can be numeric or 'auto'	",
    'value' => '600'
);
$theme_options[] = array(
    'name' => 'minWidth',
    'type' => 'text',
    'description' => 'Minimum width fancyBox should be allowed to resize to	',
    'value' => '100'
);
$theme_options[] = array(
    'name' => 'minHeight',
    'type' => 'text',
    'description' => 'Minimum height fancyBox should be allowed to resize to	',
    'value' => '100'
);
$theme_options[] = array(
    'name' => 'maxWidth',
    'type' => 'text',
    'description' => 'Maximum width fancyBox should be allowed to resize to	',
    'value' => '9999'
);
$theme_options[] = array(
    'name' => 'maxHeight',
    'type' => 'text',
    'description' => 'Maximum height fancyBox should be allowed to resize to	',
    'value' => "9999"
);
$theme_options[] = array(
    'name' => 'autoSize',
    'type' => 'checkbox',
    'description' => 'If true, then sets both autoHeight and autoWidth to true	',

);
$theme_options[] = array(
    'name' => 'autoHeight',
    'type' => 'checkbox',
    'description' => "If set to true, for 'inline', 'ajax' and 'html' type content width is auto determined. If no dimensions set this may give unexpected results	"
);
$theme_options[] = array(
    'name' => 'autoWidth',
    'type' => 'checkbox',
    'description' => "If set to true, for 'inline', 'ajax' and 'html' type content height is auto determined. If no dimensions set this may give unexpected results	"
);
$theme_options[] = array(
    'name' => 'autoResize',
    'type' => 'checkbox',
    'description' => 'If set to true, the content will be resized after window resize event	'
);
$theme_options[] = array(
    'name' => 'autoCenter',
    'type' => 'checkbox',
    'description' => 'If set to true, the content will always be centered'
);
$theme_options[] = array(
    'name' => 'openEffect',
    'type' => 'select',
    'options' => 'none,fade,fade,elastic,elastic',
    'description' => 'Animation effect'
);
$theme_options[] = array(
    'name' => 'closeEffect',
    'type' => 'select',
    'options' => 'none,fade,fade,elastic,elastic',
    'description' => 'Animation effect'
);
$theme_options[] = array(
    'name' => 'nextEffect',
    'type' => 'select',
    'options' => 'none,fade,fade,elastic,elastic',
    'description' => 'Animation effect'
);
$theme_options[] = array(
    'name' => 'prevEffect',
    'type' => 'select',
    'options' => 'none,fade,fade,elastic,elastic',
    'description' => 'Animation effect'
);
$theme_options[] = array(
    'name' => 'hideOnContentClick',
    'type' => 'checkbox',
    'description' => 'You may want to set hideOnContentClick to false if you display iframed or inline content and it containts clickable elements (for example - play buttons for movies, links to other pages)'
);
$theme_options[] = array(
    'name' => 'overlayShow',
    'type' => 'checkbox',
    'description' => 'Toggle overlay'
);
$theme_options[] = array(
    'name' => 'overlayOpacity',
    'type' => 'text',
    'description' => 'Opacity of the overlay (from 0 to 1; default - 0.3)'
);

$theme_options[] = array(
    'name' => 'overlayColor',
    'type' => 'text',
    'description' => 'Color of the overlay'
);
$theme_options[] = array(
    'name' => 'titleShow',
    'type' => 'checkbox',
    'description' => 'Toggle title'
);
