<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 09/12/2015
 * Time: 17:33
 */
/**
 * HW_Timber class
 */
include_once ('hw-timber.php');
//note: this file should not rename to template.php it will conflict with system file in wp
/**
 * Interface HW_Twig_Template_Context_Interface
 */
interface HW_Twig_Template_Context_Interface {
    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function get_object();
}


/**
 * Class HW_Twig_Template_Context
 */
class HW_Twig_Template_Context implements HW_Twig_Template_Context_Interface{
    /**
     * reference object
     * @var
     */
    private $object;

    /**
     * @param $object
     */
    public function __construct($object) {
        $this->object = $object;
    }
    /**
     * @var
     */
    public static $instance;
    /**
     * create class instance
     * @param $arg
     * @return HWoo_Template
     */
    public static function get_instance($arg=null) {
        $class = get_called_class();
        if(!$class::$instance) $class::$instance = new $class($arg);
        return $class::$instance;
    }

    /**
     * @param $name
     */
    public static  function add_context($name) {
        HW_Timber::add_context($name, get_called_class());
    }

    /**
     * @param $name
     * @param $args
     */
    public function __call($name, $args) {
        $obj = $this->get_object();
        if(empty($obj) || !is_object($obj)) return; //valid
        if(property_exists($this, $name) || method_exists($this, $name)) return;

        if( isset($obj->$name) || property_exists($obj,$name)) $result= $obj->$name;    //access property
        elseif(!method_exists($this, $name) && method_exists($obj, $name)) {   //invoke method of object
            $result = call_user_func_array(array($obj,$name), $args);
        }

        if(isset($result)) {
            $class = get_called_class();#if($name=='cart')__print(WC()->cart->get_checkout_url());
            if(is_object($result)) return new $class($result);
            else return $result;
        }

    }
    public function get_object() {
        return $this->object;
    }
}
/**
 * Class HW_Twig_Hook_Template
 */
class HW_Twig_Hook_Template extends HW_Twig_Template_Context{
    /**
     * store instance of this class
     * @var
     */
    public static $instance;
    /**
     * @var array
     */
    private $templates = array();

    public function __construct() {
        //$this->add_template('add_to_cart', 'hwwoo_template_single_add_to_cart');

    }

    /**
     * @param $alias
     * @param $function
     * @param $data
     */
    function add_template($alias, $function, $data = array()) {
        if(!isset($this->templates[$alias]) && ((is_string($function) && function_exists($function)) || is_callable($function)) ) {
            $this->templates[$alias] = array('function' => $function, 'data' => $data);
        }
    }

    /**
     * load template
     * @param $alias
     */
    function load(/*$alias, $args = array()*/) {
        $args = func_get_args();
        $alias = current(array_splice($args, 0,1) );

        if($alias && isset($this->templates[$alias]) ) {
            if(count($args)) $args = $args[0];   //get real  arguments for calling function
            $args[] = $this->templates[$alias]['data']; //append with last argument
            return call_user_func_array($this->templates[$alias]['function'], $args);
        }
    }

    /**
     * alias of `load` method
     * @param $alias
     * @return mixed
     */
    public function partial() {
        $args = func_get_args();
        return call_user_func_array(array($this, 'load'), $args);
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function load_partial($name, $args) {
        $inst = self::get_instance();
        array_unshift($args, $name);
        return call_user_func_array(array($inst,'load'), $args);
    }
}

/**
 * Class HW_Twig_Template_Function
 */
class HW_Twig_Template_Function extends HW_Twig_Hook_Template{

    /**
     * to call any function of passing this magic method
     * @param $name
     * @param $args
     */
    function __call($name, $args) {
        return $this->load($name, $args);
    }

    /**
     * @param $hook
     * @param $func
     */
    /*function add_template_utility($hook , $func) {
        if(file_exists($hook)) $hook = trim(str_replace(realpath(get_stylesheet_directory()),'', realpath($hook) ), '\/');
        $this->add_template($hook, $func);
    }

*/
}
/**
 * Class HW_Twig_Template_Utilities
 */
class HW_Twig_Template_Utilities {
    /**
     * store class instance
     */
    //public static $instance;

    /**
     * @var array
     */
    private $functions = array();
    /**
     * @var array
     */
    private $direct_templates = array();
    /**
     * get singleton of this class
     * @var
     */
    public static $instance;

    /**
     * create class instance
     * @return HWoo_Template
     */
    public static function get_instance() {
        if(!self::$instance) self::$instance = new self;
        return self::$instance;
    }
    /**
     * get hook name by specific file path
     * @param $file
     */
    private static function get_hook($hook) {
        if(file_exists($hook)) {
            $hook = rtrim(trim(str_replace(realpath(get_stylesheet_directory().''),'', realpath($hook) ), '\/'),'.php' );
        }
        $hook = str_replace('\\', '/',$hook );  //valid hook name
        return $hook;
    }
    /**
     * @param $file
     * @param $function
     */
    function add_utility($hook, $function, $alias='', $data=array()) {
        $hook = $this->get_hook($hook);#__print($hook);

        if(!isset($this->functions[$hook]) ) {
            $this->functions[$hook] = new HW_Twig_Template_Function;
        }
        if(!$alias) $alias = $function;
        $this->functions[$hook]->add_template($alias, $function, $data);
    }
    /**
     * @param $hook
     * @return mixed
     */
    function utilities($hook) {
        $hook = $this->get_hook($hook) ;
        if(isset($this->functions[$hook])) return $this->functions[$hook];
    }
    /**
     * @param $current
     * @param $file
     * @param $data
     */
    function add_template_twig($current, $file, $data = array()) {
        //valid
        if(!is_array($file)) return;
        if(!is_array($data)) $data = array();
        $hook = $this->get_hook($current);
        foreach ($file as $name => $tpl) {
            $this->direct_templates[$hook.':'. $name] = array('template' => (array)$tpl ,'data'=> $data);
        }
    }

    /**
     * render template with data send to template file
     * @param $hook
     */
    function render_template($hook) {
        if(isset($this->direct_templates[$hook])) {
            $temp = $this->direct_templates[$hook];
            $context = Timber::get_context();
            if(!empty($temp['data'])) $context = array_merge($context, $temp['data']);
            Timber::render((array)($temp['template']), $context);
        }
    }

    /**
     * @param $hook
     */
    public static function load_utilities($hook) {
        $inst = self::get_instance();
        $inst->utilities($hook);
    }
}


//parser
/**
 * token parser for `hw` tag
 * Class HW_Twig_TokenParser
 */
class HW_Twig_TokenParser extends \Twig_TokenParser
{

    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();

        $stream = $this->parser->getStream();

        // recovers all inline parameters close to your tag name
        $params = array_merge(array (), $this->getInlineParams($token));

        $continue = true;
        while ($continue)
        {
            // create subtree until the decideMyTagFork() callback returns true
            $body = $this->parser->subparse(array ($this, 'decideMyTagFork'));

            // I like to put a switch here, in case you need to add middle tags, such
            // as: {% mytag %}, {% nextmytag %}, {% endmytag %}.
            $tag = $stream->next()->getValue();

            switch ($tag)
            {
                /*case 'endmytag':
                    $continue = false;
                    break;*/
                default:$continue= false; break;
                    throw new \Twig_Error_Syntax(sprintf('Unexpected end of template. Twig was looking for the following tags "endmytag" to close the "mytag" block started at line %d)', $lineno), -1);
            }

            // you want $body at the beginning of your arguments
            array_unshift($params, $body);

            // if your endmytag can also contains params, you can uncomment this line:
            // $params = array_merge($params, $this->getInlineParams($token));
            // and comment this one:
            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        }

        return new HW_TagNode(new \Twig_Node($params), $lineno, $this->getTag());
    }

    /**
     * Recovers all tag parameters until we find a BLOCK_END_TYPE ( %} )
     *
     * @param \Twig_Token $token
     * @return array
     */
    protected function getInlineParams(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $params = array ();
        while (!$stream->test(\Twig_Token::BLOCK_END_TYPE))
        {
            $params[] = $this->parser->getExpressionParser()->parseExpression();
        }
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        return $params;
    }

    /**
     * Callback called at each tag name when subparsing, must return
     * true when the expected end tag is reached.
     *
     * @param \Twig_Token $token
     * @return bool
     */
    public function decideMyTagFork(\Twig_Token $token)
    {
        return $token->test(array ('endmytag'));
    }

    /**
     * Your tag name: if the parsed tag match the one you put here, your parse()
     * method will be called.
     *
     * @return string
     */
    public function getTag()
    {
        return 'hw';
    }

}
//compiler
/**
 * Class HW_TagNode
 */
class HW_TagNode extends \Twig_Node
{

    public function __construct($params, $lineno = 0, $tag = null)
    {
        parent::__construct(array ('params' => $params), array (), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $count = count($this->getNode('params'));

        $compiler
            ->addDebugInfo($this);

        for ($i = 0; ($i < $count); $i++)
        {
            // argument is not an expression (such as, a \Twig_Node_Textbody)
            // we should trick with output buffering to get a valid argument to pass
            // to the functionToCall() function.
            if (!($this->getNode('params')->getNode($i) instanceof \Twig_Node_Expression))
            {
                $compiler
                    ->write('ob_start();')
                    ->raw(PHP_EOL);

                $compiler
                    ->subcompile($this->getNode('params')->getNode($i));

                $compiler
                    ->write(sprintf('$_mytag[%d][] = ob_get_clean();', $this->getAttribute('counter')))
                    ->raw(PHP_EOL);
            }
            else
            {
                $compiler
                    ->write('$_mytag[] = ')
                    ->subcompile($this->getNode('params')->getNode($i))
                    ->raw(';')
                    ->raw(PHP_EOL);
            }
        }

        $compiler
            ->write('call_user_func_array(')
            ->string('functionToCall')
            ->raw(', $_mytag);')
            ->raw(PHP_EOL);

        $compiler
            ->write('unset($_mytag);')
            ->raw(PHP_EOL);
    }

}
class MyTagNodevisitor implements \Twig_NodeVisitorInterface
{

    private $counter = 0;

    public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        if ($node instanceof HW_TagNode)
        {
            $node->setAttribute('counter', $this->counter++);
        }
        return $node;
    }

    public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        if ($node instanceof HW_TagNode)
        {
            $node->setAttribute('counter', $this->counter--);
        }
        return $node;
    }

    public function getPriority()
    {
        return 0;
    }

}
class MyTagExtension extends \Twig_Extension
{

    public function getTokenParsers()
    {
        return array (
            new HW_Twig_TokenParser(),
        );
    }

    public function getName()
    {
        return 'hw';
    }
    public function getNodeVisitors()
    {
        return array (
            new MyTagNodeVisitor(),
        );
    }
}
function functionToCall($t){__print($t);}