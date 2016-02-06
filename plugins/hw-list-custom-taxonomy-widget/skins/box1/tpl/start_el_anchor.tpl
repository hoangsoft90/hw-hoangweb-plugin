{{ args.before | raw }}
{{args.submenu_before}}
<a {{ attributes | raw }}>
{{args.link_before|raw}}
{{args.submenu_link_before}}
{{title}}
{{args.submenu_link_after}}
{{args.link_after | raw}}
</a>
{{args.submenu_after}}
{{ args.after | raw }}