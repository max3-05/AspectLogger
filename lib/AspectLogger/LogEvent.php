<?php

/**
 * Data structure to store event parameters.
 * @author Maksym Rykin <max.rykin@gmail.com>
 */
class LogEvent
{
    /**
     * <p>Current page request information.</p>
     * @var LogPage
     */
    public $page;

    /**
     * <p>The time the event has occurred.</p>
     * @var float|DateTime
     */
    public $time;

    /**
     * <p>Class name of the object generated the event.</p>
     * @var string
     */
    public $class;

    /**
     * <p>Object generated event.</p>
     * @var mixed
     */
    public $object;

    /**
     * <p>Name of the function generated the event.</p>
     * @var string
     */
    public $action;

    /**
     * <p>List of parameters passed to the trigger function.</p>
     * @var array
     */
    public $params = array();

    /**
     * <p>Trigger function stack trace.</p>
     * @var array
     */
    public $stackTrace = array();

    /**
     * <p>Default instance constructor.</p>
     * @param LogPage $page [optional] <p>Current page request information.</p>
     * @param DateTime|float $time [optional] <p>The time the event has occurred.</p>
     * @param string $class [optional] <p>Class name of the object generated the event.</p>
     * @param mixed $object [optional] <p>Object generated event.</p>
     * @param string $action [optional] <p>Name of the function generated the event.</p>
     * @param array $params [optional] <p>List of parameters passed to the trigger function.</p>
     * @param array $stackTrace [optional] <p>Trigger function stack trace.</p>
     */
    public function __construct( $page = null,
                                 $time = null,
                                 $class  = null,
                                 $object = null,
                                 $action = null,
                                 $params = array(),
                                 $stackTrace = array()
    ) {
        $this->page       = $page;
        $this->time       = $time;
        $this->object     = $object;
        $this->action     = $action;
        $this->params     = $params;
        $this->stackTrace = $stackTrace;
    }
}
