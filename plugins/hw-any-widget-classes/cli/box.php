<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 02/11/2015
 * Time: 11:01
 */
/**
 * Class HW_Module_Config_sidebars
 */
class HW_Module_Config_sidebars extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    public function start() {
        $this->register_command('register_sidebar', array($this, '_register_sidebar'));
        $this->register_command('remove_sidebar', array($this, '_remove_sidebar'));
        $this->register_command('apply_skin', array($this, '_apply_skin'));
        $this->enable_command_stats();
    }

    /**
     * register new sidebar
     */
    public function _register_sidebar(){
        //$this->run_cli_cmd('add_sidebar');
        $this->run_cli_cmds('add_sidebar');
    }
    public function _remove_sidebar() {

    }

    /**
     * apply skin to sidebar
     */
    public function _apply_skin() {
        $this->run_cli_cmd('apply_sidebar_skin');
    }
    /**
     * load fields
     */
    public function setUp() {
        $this->load_fields();

    }
    public function content($aField) {
        $progressbar = $this->get_unique_id('_progressbar');
        ?>
        <div id="<?php echo $progressbar?>"></div>
        <script>
            __hw_installer.load_module_commands('sidebar', '<?php echo $this->get_current_module()->option('module_name')?>',{
                register_sidebar : function (obj) {
                    __hw_installer.command('register_sidebar', this.module(), obj);
                },
                apply_skin: function(obj) {
                    __hw_installer.command('apply_skin', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                __hw_installer.get_commands('sidebar').view_stats([]);
            });


        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('sidebar').register_sidebar(this)" href="javascript:void(0)">Register sidebars</a>
            <a class="button" onclick="__hw_installer.get_commands('sidebar').apply_skin(this)" href="javascript:void(0)">apply skin sidebar</a>
        </div>
    <?php
    }
}
add_action('hw_module_register_config_page', 'HW_Module_Config_sidebars::init');