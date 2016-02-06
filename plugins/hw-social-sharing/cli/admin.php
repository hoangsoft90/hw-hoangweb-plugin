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
class HW_Module_Config_sharing extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('enable_sharing', array($this, '_enable_sharing'));
        $this->register_command('disable_sharing', array($this, '_disable_sharing'));
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
     * module stats
     * @return mixed|void
     */
    public function view_stats(){

    }
    //enable sharing
    public function _enable_sharing() {
        $this->run_cli_cmd('enable_social_sharing');
    }
    //disable sharing
    public function _disable_sharing() {
        $this->run_cli_cmd('disable_social_sharing');
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
            __hw_installer.load_module_commands('sharing', '<?php echo $this->get_current_module()->option('module_name')?>',{
                enable_sharing : function (obj) {
                    __hw_installer.command('enable_sharing', this.module(), obj);
                },
                disable_sharing: function(obj) {
                    __hw_installer.command('disable_sharing', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                //__hw_installer.get_commands('sharing').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('sharing').enable_sharing(this)" href="javascript:void(0)">Enable</a>
            <a class="button" onclick="__hw_installer.get_commands('sharing').disable_sharing(this)" href="javascript:void(0)">Disable</a>
        </div>
    <?php
    }
}
HW_Module_Config_sharing::register_config_page();