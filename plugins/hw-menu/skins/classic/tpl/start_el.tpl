{#
    filter 'raw' keep print extract quote character
#}
{{indent}}<li {{ id|raw }} class="{{ classes|raw }}">
    {{args.before|raw}} {{args.submenu_before|raw}}

        <a {{ attributes | raw }}>
{{image_img|raw}}   {#  menu item image #}
{{font_icon|raw}}  {# font icon #}
            {{args.link_before | raw}} {{args.submenu_link_before|raw}}
            {{(title | raw)}}
            {{args.submenu_link_after|raw}} {{args.link_after | raw}}
        </a>
    {{args.submenu_after|raw}} {{args.after|raw}}