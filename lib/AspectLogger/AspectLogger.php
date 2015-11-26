<?php
/**
 * This file defines classes to manage loggers.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 */

require_once dirname(__FILE__) . '/LogEvent.php';
require_once dirname(__FILE__) . '/LogPage.php';

require_once dirname(__FILE__) . '/LogEventHandler.php';
require_once dirname(__FILE__) . '/LogEventHandler/FunctionHandler.php';
require_once dirname(__FILE__) . '/LogEventHandler/SqlHandler.php';

require_once dirname(__FILE__) . '/LogEventWriter.php';
require_once dirname(__FILE__) . '/LogEventWriter/FileWriter.php';


/**
 * Top level class for managing logging
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @uses LogEventHandler
 * @uses LogEventWriter
  */
class AspectLogger
{
    /**
     * Event handler.
     * @var LogEventHandler
     */
    private $_handler;

    /**
     * Event writer.
     * @var LogEventWriter
     */
    private $_writer;

    /**
     * Current page request information.
     * @var LogPage
     */
    private $_page;

    /**
     * Initializes logger instance with specified configuration.
     * @param string|SimpleXMLElement $config  <p>Path to logger configuration file.</p>
     */
    public function init($config) {
        // TODO: Add configuration class instead of using SimpleXML object.
        $this->_page = new LogPage();

        $this->_writer = new LogEventWriterCollection();
        $this->_writer->init($config->writers[0]->children());
        $this->_writer->open();

        $this->_handler = new LogEventHandlerCollection();
        $this->_handler->init( $config->handlers[0]->children(), $this );
        $this->_handler->register();

        register_shutdown_function(array($this, 'stop'));
    }


    /**
     * Returns information about current page request.
     * @return LogPage
     */
    public function getPage() {
        return $this->_page;
    }


    /**
     * Writes an event to log writer.
     * @param LogEvent $event  <p>Event to log.</p>
     */
    public function write( LogEvent $event ) {
        $this->_writer->write($event);
    }


    /**
     * Stops logging, deregisters handler and closes writer.
     */
    public function stop() {
        /// TODO: Implement LogEventHandler::deregister() function
        try {
            $this->_handler->deregister();
        } catch (Exception $e) {}

        $this->_writer->close();
    }
}
