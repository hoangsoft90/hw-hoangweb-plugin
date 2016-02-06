<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_WP_NOTICES
 * notices class
 */
class HW_WP_NOTICES extends HW_Core{
    /**
     * singleton
     * @var
     */
    public static $instance = null;
    /**
     * @var array
     */
    var $notices = array();
    /**
     * @var
     */
    var $notices_type;
	/**
     * contructor method
     */
	public function __contruct(){
		$this->notices_type = array('error'=>'error','updated'=>'updated','update-nag'=>'update-nag');
		//admin notices
		add_action('admin_notices',array($this, 'admin_notices'));
	}
	/**
     * admin notices
     * @hook admin_notices
     */
	public function admin_notices(){
		echo $this->show_msgs();
	}
	/**
     * valid your notice
     * @param $type
     */
	protected function valid_notice_type($type = 'error'){
		if(in_array($type,array_keys($this->notices_type))) return $type;
		return 'error';		//default notice message
	}
	/**
	 * PUT message
     * @param $text
     * @param $type
	 */
	public function put_msg($txt,$type='error'){
		$this->notices[] = array('msg' => $txt, 'type' =>$this->valid_notice_type($type));
	}

    /**
     * show messages
     * @return string
     */
    public function show_msgs(){
		if(is_array($this->notices)){
			$s='<div class="hw-messages-container">';
			foreach($this->notices as $msg) {
				$class = $this->notices_type[$msg['type']];
				$s .= '<div class="'.$class.'"><p>'.$msg['msg'].'</p></div>';
			}
			$s .= '</div>';
			return $s;
		}
	}
}
?>