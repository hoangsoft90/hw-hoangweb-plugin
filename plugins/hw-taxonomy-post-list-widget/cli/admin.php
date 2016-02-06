<?php
/**
 * Class HW_Module_Config_tpl
 */
class HW_Module_Config_tpl extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('create_widgets', array($this, '_create_widgets'));
        $this->enable_command_stats();
    }
    /**
     * load fields
     */
    function setUp() {
        //$this->support_fields('hw_html');
        $this->load_fields();

    }

    /**
     * prepare tpl widgets
     */
    public function _create_widgets() {    //don't use function name '_setup'
        //$script = $this->get_module_setup_script();     #$script='ping google.com';
        $this->run_cli_cmd('prepare_widgets','load-widgets');
    }


    /**
     * module stats
     * @return mixed|void
     */
    public function view_stats(){
        echo '<strong>Stats:</strong><br/>';
        if(get_option('HW_Livechat_settings')) echo '<br/>Enabled Livechat !';
        else echo '<br/>Disabled Livechat.';
    }
    /**
     *
     * html ouput callback
     * @param $aField
     */
    public function content($aField) {
        $progressbar = $this->get_unique_id('_progressbar');
        ?>
        <div id="<?php echo $progressbar?>"></div>
        <script>
            __hw_installer.load_module_commands('tpl', '<?php echo $this->get_current_module()->option('module_name')?>',{
                init_widgets : function (obj) {
                    __hw_installer.command('create_widgets', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                //__hw_installer.get_commands('tpl').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('tpl').init_widgets(this)" href="javascript:void(0)">Create widgets</a>
        </div>
    <?php
    }
}
HW_Module_Config_tpl::register_config_page();