<?php
/**
 * This file defines classes to manage aspect event handlers created with AOP PECL extension.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 * @link https://github.com/AOP-PHP/AOP
 */
require_once  realpath( dirname( __FILE__ ) ) . "/../LogEventHandler.php";

/**
 * Event handler abstraction using AOP extension.
 */
class AspectHandler extends LogEventHandler
{
    /**
     * Gets method name to register handler.
     * @return string
     */
    private function _getMethodName() {
        switch ( $this->_mode ) {
            case LogEventHandler::MODE_BEFORE :
                return 'aop_add_before';
            case LogEventHandler::MODE_AFTER :
                return 'aop_add_after';
            case LogEventHandler::MODE_AROUND :
                return 'aop_add_around';
         }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $method = $this->_getMethodName();
        $method( $this->_match, array($this, 'handle') );
    }

    /**
     * {@inheritdoc}
     * @param AopJoinPoint $info  Params for handled events.
     */
    public function handle($info)
    {
        $event = new LogEvent();
        $event->class  = $info->getClassName();
        $event->object = $info->getObject();
        $event->params = $info->getArguments();

        $event->action = ( empty($event->class) == true )
            ? $info->getFunctionName()
            : $info->getMethodName();
        $event->time   = microtime(true);

        $event->page = $this->_logger->getPage();

        $event->stackTrace = debug_backtrace();
        array_shift( $event->stackTrace );
        array_shift( $event->stackTrace );

        $this->_logger->write($event);
    }

    /**
     * {@inheritdoc}
     * @throws Exception  Not implemented.
     */
    public function deregister()
    {
        throw new Exception('Not Implemented');
    }
}
