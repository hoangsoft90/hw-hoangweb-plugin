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
class HW_Module_Config_contactform7 extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('config_settings', array($this, '_config_settings'));
        $this->register_command('create_contactform', array($this, '_add_form'));
        $this->register_command('delete_all', array($this, '_delete_all_forms'));
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
     * setup module data
     * @return mixed|void
     */
    public function setup_user_data($xml, $namespaces) {
        $this->exporter()->export_wxr_data($xml);
        //$module = $this->get_current_module();
        #HW_Module::$instance;
    }
    /**
     * module stats
     * @return mixed|void
     */
    public function view_stats(){

    }
    //enable sharing
    public function _config_settings() {
        $this->run_cli_cmd('settings');
    }

    /**
     * create contact form
     */
    public function _add_form() {
        $this->run_cli_cmd('create_form');
    }
    public function _delete_all_forms() {
        $this->run_cli_cmd('delete_all_forms');
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
            __hw_installer.load_module_commands('hwcf7', '<?php echo $this->get_current_module()->option('module_name')?>',{
                config_settings : function (obj) {
                    __hw_installer.command('config_settings', this.module(), obj);
                },
                add_form : function (obj) {
                    __hw_installer.command('create_contactform', this.module(), obj);
                },
                del_all: function (obj) {
                    __hw_installer.command('delete_all', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                __hw_installer.get_commands('hwcf7').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('hwcf7').config_settings(this)" href="javascript:void(0)">setting page</a>
            <a class="button" onclick="__hw_installer.get_commands('hwcf7').add_form(this)" href="javascript:void(0)">Add</a>
            <a class="button" onclick="__hw_installer.get_commands('hwcf7').del_all(this)" href="javascript:void(0)">Del all</a>
        </div>
    <?php
    }
}
HW_Module_Config_contactform7::register_config_page();
#add_action('hw_module_register_config_page', 'HW_Module_Config_contactform7::init');