<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Class HW_Module_Config_position
 */
class HW_Module_Config_position extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('config_settings', array($this, '_config_settings'));
        //$this->enable_command_stats();
    }
    /**
     * load fields
     */
    function setUp() {
        $this->load_fields();
    }
    //enable sharing
    public function _config_settings() {
        $this->run_cli_cmd('settings');
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
            __hw_installer.load_module_commands('position', '<?php echo $this->get_current_module()->option('module_name')?>',{
                config_settings : function (obj) {
                    __hw_installer.command('config_settings', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                //__hw_installer.get_commands('position').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('position').config_settings(this)" href="javascript:void(0)">setting page</a>

        </div>
    <?php
    }
}
HW_Module_Config_position::register_config_page();