<?php
/**
 * This file defines classes to manage event writers.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 */

include_once dirname(__FILE__) . '/LogEventWriter/FileWriter.php';
include_once dirname(__FILE__) . '/LogEventWriter/PgSQLWriter.php';
include_once dirname(__FILE__) . '/LogEventWriter/SocketWriter.php';

/**
 * Abstract class for event writer.
 * @abstract
 * @used AspectLogger
 */
abstract class LogEventWriter
{
    /**
     * Writer configuration.
     * @var mixed|SimpleXMLElement
     */
    protected $_config;

    /**
     * Initializes writer instance depending on config.
     * @param mixed|SimpleXMLElement $config  Writer configuration.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function init($config) {
        $this->_config = $config;
    }

    /**
     * Opens the writer target.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public abstract function open();

    /**
     * Formats handled event before writing.
     * @param LogEvent $event  Handled event to format.
     * @return mixed
     */
    public abstract function format(LogEvent $event);

    /**
     * Writes handled event to the writer target.
     * @param LogEvent $event  Event to write.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public abstract function write(LogEvent $event);

    /**
     * Closes the writer target.
     * @return void
     */
    public abstract function close();
}

/**
 * Abstraction class for event writers collection.
 */
class LogEventWriterCollection extends LogEventWriter {
    /**
     * List of managed writers.
     * @var array
     */
    private $_writers = array();


    #region Parent abstract methods implementations.
    /**
     * Proxies method call to all managed handler instances.
     * @param string $name <p>Method name to call.</p>
     * @param array $arguments <p>List of arguments to pass to the method.</p>
     * @throws Exception
     * @return void
     */
    private function callMethod( $name, $arguments ) {
        if ( in_array( $name, get_class_methods('LogEventWriter')) == false ) {
            throw new Exception( 'Not Implemented' );
        }

        foreach( $this->_writers as $writer ) {
            call_user_func_array( array($writer, $name), $arguments);
        }
    }

    /**
     * {@inheritdoc}
     * @param mixed|SimpleXMLElement $configs  Writer configuration list.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function init($configs) {
        foreach($configs as $config) {
            $className = ucfirst( $config->type ) . 'Writer';

            /** @var $writer LogEventWriter */
            $writer = new $className();

            $result = $writer->init($config);
            if ( $result == true ) {
                $this->add($writer);
            }
        }

        $result = count($this->_writers) > 0;
        return $result;
    }

    /**
     * {@inheritdoc}
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function open()
    {
        $brokenWriterIndexes = array();

        foreach( $this->_writers as $key => $writer ) {
            /** @var $writer LogEventWriter */

            $result = $writer->open();
            if ( $result === false ) {
                $brokenWriterIndexes[] = $key;
            }
        }

        foreach ( $brokenWriterIndexes as $index ) {
            unset( $this->_writers[$index] );
        }

        $result = count($this->_writers) > 0;
        return $result;
    }

    /**
     * {@inheritdoc}
     * @param LogEvent $event  Event to write.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function write(LogEvent $event)
    {
        $this->callMethod('write', array( $event ));
    }

    /**
     * {@inheritdoc}
     * @return void
     */
    public function close()
    {
        $this->callMethod('close', array());
    }
    #endregion


    /**
     * Calculates item hash.
     * @param LogEventWriter $item  Item to calculate the key.
     * @return string
     */
    protected function _key($item) {
        return md5(get_class($item) . $item->_config->__toString());
    }

    /**
     * Adds an item to collection.
     * @param LogEventWriter $writer  Instance to add.
     */
    public function add(LogEventWriter $writer) {
        $key = $this->_key($writer);
        $this->_writers[$key] = $writer;
    }

    /**
     * Removes an item from collection.
     * @param LogEventWriter $writer  Instance to remove.
     */
    public function remove(LogEventWriter $writer) {
        $key = $this->_key($writer);
        unset($this->_writers[$key]);
    }

    /**
     * Checks if the collection contains the specified item.
     * @param LogEventWriter $writer  Item to check.
     * @return bool
     */
    public function contains(LogEventWriter $writer) {
        $key = $this->_key($writer);
        return isset($key);
    }

    /**
     * Formats handled event before writing.
     * @param LogEvent $event  Handled event to format.
     * @throws Exception  Invalid operation on collections.
     * @return mixed|void
     */
    public function format(LogEvent $event)
    {
        throw new Exception('Invalid operation');
    }
}
