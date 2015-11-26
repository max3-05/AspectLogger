<?php
/**
 * This file defines classes to store requested page info.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 */

/**
 * Data structure to store requested page parameters.
 * @author Maksym Rykin <max.rykin@gmail.com>
 */
class LogPage
{
    /**
     * Page identifier.
     * @var int
     */
    public $id = 0;

    /**
     * Requested Page URI.
     * @var string
     */
    public $uri;

    /**
     * List of query string parameters.
     * @var array
     */
    public $query = array();

    /**
     * List of session variables.
     * @var array
     */
    public $session = array();

    /**
     * List of cookie variables.
     * @var array
     */
    public $cookie  = array();

    /**
     * Time when the page requested.
     * @var float|DateTime
     */
    public $time;

    /**
     * List of events handled during page request servicing.
     * @var array
     */
    public $events;


    /**
     * Initializes instance of the page.
     */
    public function __construct() {
        $this->uri     = $_SERVER['REQUEST_URI'];
        $this->time    = $_SERVER['REQUEST_TIME'];
        $this->query   = $_REQUEST;
        $this->cookies = $_COOKIE;

        if ( isset($_SESSION) == true ) {
            $this->session = $_SESSION;
        } else {
            aop_add_after( 'session_start()', array($this, '_update') );
        }
    }

    /**
     * AOP Advice for session_start function.
     * It is needed to update $session variable if session been initialized.
     * @param AopJoinPoint $joinPoint  Join point info.
     */
    protected function _update(AopJoinPoint $joinPoint) {
        $this->session = $_SESSION;
    }
}
