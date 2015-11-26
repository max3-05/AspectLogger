<?php
/**
 * This file defines classes to manage event handlers.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 */

require_once  realpath( dirname( __FILE__ ) ) . "/LogEvent.php";
require_once  realpath( dirname( __FILE__ ) ) . "/AspectLogger.php";

/**
 * Abstraction for triggered event handler.
 * @abstract
 * @used AspectLogger
 */
abstract class LogEventHandler
{
    /**
     * Executes handler before trigger.
     * @const int
     */
    const MODE_BEFORE = 1;


    /**
     * Executes handler after trigger.
     * @const int
     */
    const MODE_AFTER  = 2;

    /**
     * Executes handler around trigger.
     * @const int
     */
    const MODE_AROUND = 3;

    /**
     * Expression to match trigger functions.
     * @link http://www.croes.org/gerald/projects/aop/documentation_aop_extension_php.pdf
     *
     * @var string
     */
    protected $_match;

    /**
     * Logger instance that caught the trigger.
     * @var AspectLogger
     */
    protected $_logger;

    /**
     * Match mode.
     * May be one of {@see LogEventHandler::MODE_BEFORE},
     * {@see LogEventHandler::MODE_AFTER},
     * {@see LogEventHandler::MODE_AROUND}.
     * {@see LogEventHandler::MODE_BEFORE} will be used by default.
     *
     * @var int
     */
    protected $_mode = LogEventHandler::MODE_BEFORE;


    /**
     * Initializes instance with handler configuration and logger instance.
     * @param SimpleXMLElement|mixed $config  <p>Handler configuration.</p>
     * @param AspectLogger $logger <p>Parent logger instance.</p>
     */
    public function init( $config, $logger ) {
        $mode = $config->mode;

        $this->_match  = (string)$config->match;
        $this->_mode   = constant( "LogEventHandler::$mode" );
        $this->_logger = $logger;
    }


    /**
     * Registers handler.
     * @abstract
     * @return void
     */
    public abstract function register();


    /**
     * Handles info about trigger execution.
     * @param mixed $info <p>Info about trigger execution.</p>
     * @abstract
     * @return void
     */
    public abstract function handle( $info );


    /**
     * Deregisters handler in the logger.
     * @abstract
     * @return void
     */
    public abstract function deregister();
}

/**
 * Abstraction to manage handlers list.
 * @author Maksym Rykin <max.rykin@gmail.com>
 */
class LogEventHandlerCollection extends LogEventHandler {

    /**
     * List of managed handlers.
     * @var array
     */
    private $_handlers = array();


    /**
     * Proxies method call to all managed handler instances.
     * @param string $name <p>Method name to call.</p>
     * @param array $arguments <p>List of arguments to pass to the method.</p>
     * @throws Exception
     * @return void
     */
    private function callMethod( $name, $arguments = array() ) {
        if ( in_array( $name, get_class_methods('LogEventHandler')) == false ) {
            throw new Exception( 'Not Implemented' );
        }

        foreach( $this->_handlers as $handler ) {
            call_user_func_array( array($handler, $name), $arguments);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register() {
        $this->callMethod('register');
    }

    /**
     * {@inheritdoc}
     * @param mixed $info <p>Info about trigger execution.</p>
     */
    public function handle($info) {
        $this->callMethod('handle', array( $info ));
    }

    /**
     * {@inheritdoc}
     * @param mixed|Iterator $configs  <p>List of handler configurations.</p>
     * @param AspectLogger $logger <p>Parent logger instance.</p>
     */
    public function init( $configs, $logger ) {
        $this->_handlers = array();

        foreach ( $configs as $config ) {
            // TODO: Bad initialization practice.
            $className = $config->type . 'Handler';
            /** @var $handler LogEventHandler */
            $handler = new $className();
            $handler->init( $config, $logger );

            $this->_handlers[] = $handler;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deregister()
    {
        $this->callMethod('deregister');
    }
}
