<?php
/**
 * This file defines classes to manage function handlers.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
  */

require_once  realpath( dirname( __FILE__ ) ) . "/AspectHandler.php";

/**
 * Function handler abstraction using AOP handler.
 */
class FunctionHandler extends AspectHandler
{
    /**
     * {@inheritdoc}
     * @param AopJoinPoint $info  Params for handled events.
     */
    public function handle($info)
    {
        $event = new LogEvent();
        // TODO: make info translation to message more flexible.
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
}
