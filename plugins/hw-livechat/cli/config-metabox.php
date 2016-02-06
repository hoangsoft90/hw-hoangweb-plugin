<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 01/11/2015
 * Time: 22:03
 */
class HW_Module_Config_livechat extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('setup-livechat', array($this, '_enable_livechat'));
        $this->register_command('remove_livechat', array($this, '_remove_livechat'));
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
     * turn on livechat with default configuration
     */
    public function _enable_livechat() {    //don't use function name '_setup'
        //$script = $this->get_module_setup_script();     #$script='ping google.com';
        $this->run_cli_cmd('setup_livechat','default');
    }

    /**
     * disable livechat
     */
    public function _remove_livechat() {
        //echo 'turn off livechat.';
        $this->run_cli_cmd('disable_livechat');
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
            __hw_installer.load_module_commands('livechat', '<?php echo $this->get_current_module()->option('module_name')?>',{
                setup_livechat : function (obj) {
                    __hw_installer.command('setup-livechat', this.module(), obj);
                },
                disable_livechat : function(obj) {
                    __hw_installer.command('remove_livechat', this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                //__hw_installer.get_commands('livechat').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('livechat').setup_livechat(this)" href="javascript:void(0)">Turn on</a>
            <a class="button" onclick="__hw_installer.get_commands('livechat').disable_livechat(this)" href="javascript:void(0)">Disable livechat</a>
        </div>
<?php
    }
}
HW_Module_Config_livechat::register_config_page();