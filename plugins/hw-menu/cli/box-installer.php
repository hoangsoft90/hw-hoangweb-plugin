<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 04/11/2015
 * Time: 10:11
 */
class HW_Module_Config_menu extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('create_menu', array($this, '_create_nav_menu'));
        $this->register_command('delete_menu', array($this, '_del_nav_menu'));
        $this->enable_command_stats();
    }
    /**
     * load fields
     */
    function setUp() {
        //$this->support_fields('hw_html');
        $this->load_fields();

    }
    //create nav menu
    public function _create_nav_menu() {
        $this->run_cli_cmd('create_nav_menu');
        echo 'create menu';
    }
    //delete nav menu
    public function _del_nav_menu() {
        $this->run_cli_cmd('del_nav_menu');
    }
    /**
     * module stats
     * @return mixed|void
     */
    public function view_stats(){

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
            __hw_installer.load_module_commands('menu', '<?php echo $this->get_current_module()->option('module_name')?>',{
                create_menu : function (obj) {
                    __hw_installer.command('create_menu', this.module(), obj);
                },
                delete_menu : function(obj) {
                    __hw_installer.command('delete_menu', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                //__hw_installer.get_commands('menu').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('menu').create_menu(this)" href="javascript:void(0)">Create</a>
            <a class="button" onclick="__hw_installer.get_commands('menu').delete_menu(this)" href="javascript:void(0)">Remove</a>
        </div>
    <?php
    }
}
add_action('hw_module_register_config_page', 'HW_Module_Config_menu::init');