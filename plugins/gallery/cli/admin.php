<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Module_Config_gallery
 */
class HW_Module_Config_gallery extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('create_gallery', array($this, '_add_new_gallery'));
        $this->register_command('del_all_galleries', array($this, '_del_all'));
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
    //create a gallery
    public function _add_new_gallery() {
        $this->run_cli_cmd('create_gallery');
    }
    //del all galleries
    public function _del_all() {
        $this->run_cli_cmd('delete_all_galleries');
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
            __hw_installer.load_module_commands('gallery', '<?php echo $this->get_current_module()->option('module_name')?>',{
                create_gallery : function (obj) {
                    __hw_installer.command('create_gallery', this.module(), obj);
                },
                del_all : function (obj) {
                    __hw_installer.command('del_all_galleries', this.module(), obj);
                }

            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                __hw_installer.get_commands('gallery').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
            <a class="button" onclick="__hw_installer.get_commands('gallery').create_gallery(this)" href="javascript:void(0)">add gallery</a>
            <a class="button" onclick="__hw_installer.get_commands('gallery').del_all(this)" href="javascript:void(0)">del all</a>

        </div>
    <?php
    }
}
add_action('hw_module_register_config_page', 'HW_Module_Config_gallery::init');