<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 28/11/2015
 * Time: 11:36
 */
/**
 * Class HW_Module_Config_theme_options
 */
class HW_Module_Config_theme_options extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        #$this->register_command('config_settings', array($this, '_config_settings'));

        $this->enable_command_stats();
    }
    /**
     * module stats
     * @return mixed|void
     */
    public function view_stats(){

    }
    /**
     * load fields
     */
    function setUp() {
        //$this->support_fields('hw_html');
        $this->load_fields();

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
            __hw_installer.load_module_commands('theme_options', '<?php echo $this->get_current_module()->option('module_name')?>',{
                config_settings : function (obj) {
                    __hw_installer.command('config_settings', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                //__hw_installer.get_commands('theme_options').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('theme_options').config_settings(this)" href="javascript:void(0)">setting page</a>

        </div>
    <?php
    }
}
HW_Module_Config_theme_options::register_config_page();