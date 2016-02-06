<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/10/2015
 * Time: 14:54
 */
class HW_Module_Config_slider extends HW_Config_Module_Section{
    /**
     * @return mixed|void
     */
    function start() {
        $this->register_command('del_alls', array($this, 'del_all_sliders'));
        $this->register_command('add_slider', array($this, 'add_slider'));
        $this->enable_command_stats();
    }

    /**
     * load fields
     */
    function setUp() {
        //$this->support_fields('hw_html');
        $this->load_fields();
        //$this->addHTML(array($this, 'html_field_output_callback'));

    }

    /**
     * add new slider
     * example exec script from module config class
     * @hw_mcfg_command add_slider
     */
    public function add_slider() {
        //$script = $this->get_module_setup_script();
        $this->run_setup_script();
    }
    /**
     * delete all sliders
     * @hw_mcfg_command del_alls
     */
    public function del_all_sliders() {
        $this->run_cli_cmd('del_all_sliders');
    }

    /**
     * stats analystic
     * @hw_mcfg_command view_stats
     */
    public function view_stats() {
        $hwsliders = hwml_get_all_sliders();
        $mlslider = hw__metaslider::init();

        echo '<br/>HW Sliders: '. count($hwsliders) ;
        echo '<br/>Sliders count: '. count($mlslider->all_meta_sliders());
    }

    /**
     * html ouput callback
     * @param $aField
     */
    public function content($aField) {
        echo '<strong>Cấu hình slider.</strong>';
        $progressbar = $this->get_unique_id('_progressbar');
        ?>
        <style>
            .ui-progressbar {
                position: relative;
            }
            .progress-label {
                position: absolute;
                left: 50%;
                top: 4px;
                font-weight: bold;
                text-shadow: 1px 1px 0 #fff;
            }
        </style>
        <div id="<?php echo $progressbar?>"></div>
        <script>
            __hw_installer.load_module_commands('slider', '<?php echo $this->get_current_module()->option('module_name')?>',{
                create_slider : function (obj) {
                    __hw_installer.command('add_slider', this.module(), obj);
                },
                del_sliders : function(obj) {
                    __hw_installer.command('del_alls', this.module(), obj);
                },
                setup_total: function(obj) {
                    __hw_installer.command(null, this.module(), obj);
                }
            });

            jQuery(document).ready(function(){
                __hw_installer.get('<?php echo $this->get_current_module()->option('module_name')?>', '#<?php echo $progressbar?>');
                console.log('already slider config');
                //__hw_installer.get_commands('slider').view_stats([]);
            });

        </script>
        <br/>
        <div class="buttons-container">
        <a class="button" onclick="__hw_installer.get_commands('slider').setup_total(this)" href="javascript:void(0)">Auto</a>
        <a class="button" onclick="__hw_installer.get_commands('slider').create_slider(this)" href="javascript:void(0)">Add slider</a>
        <a class="button" onclick="__hw_installer.get_commands('slider').del_sliders(this)" href="javascript:void(0)">Delete all</a>
        <!-- <a class="button" onclick="__hw_installer.get_commands('slider').view_stats(this)" href="javascript:void(0)">stats</a> -->
        </div>
    <?php
    }
}
add_action('hw_module_register_config_page', 'HW_Module_Config_slider::init');