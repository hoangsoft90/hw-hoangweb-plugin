<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/11/2015
 * Time: 15:47
 */
class HW_Module_Config_marquee extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('create_widget', array($this, '_create_widget'));
        //$this->enable_command_stats();
    }
    /**
     * load fields
     */
    function setUp() {
        //$this->support_fields('hw_html');
        $this->load_fields();

    }
    /**
     * module stats
     * @return mixed|void
     */
    public function view_stats(){

    }
    //enable sharing
    public function _create_widget() {
        $this->run_cli_cmd('init_widgets');
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
            __hw_installer.load_module_commands('marquee', '<?php echo $this->get_current_module()->option('module_name')?>',{
                create_widget : function (obj) {
                    __hw_installer.command('create_widget', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                //__hw_installer.get_commands('marquee').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('marquee').create_widget(this)" href="javascript:void(0)">add widget</a>

        </div>
    <?php
    }
}
HW_Module_Config_marquee::register_config_page();