<?php
/**
 * This file defines classes to manage event writers to text files.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 */

/**
 * Manages file writer.
 */
class FileWriter extends LogEventWriter
{
    /**
     * Managed file resource.
     * @var resource
     */
    private $_resource;


    /**
     * Initializes writer instance depending on config.
     * @param mixed|SimpleXMLElement $config  Writer configuration.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function init( $config ) {
        parent::init($config);
        return true;
    }


    /**
     * {@inheritdoc}
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function open()
    {
        try {
            $this->_resource = fopen( $this->config->path, 'a' );
        } catch(Exception $e) {
            $exception = new Exception('Failed to open log writer source', 0, $e);
            error_log( $exception->getMessage() );

            return false;
        }

        return true;
    }


    /**
     * {@inheritdoc}
     * @param LogEvent $event  Handled event to format.
     * @return string
     */
    public function format(LogEvent $event) {
        $message = "Action captured:"
            . "\n\t time:\t"  . date( 'Y-m-d H:i:s', $event->time )
            . "\n\t user:\t"  . $event->userId
            . "\n\t class\t" . $event->class
            . "\n\t object\t" . serialize( $event->object )
            . "\n\t action\t" . $event->action
            . "\n\t stack\t"  . serialize( $event->stackTrace )
            . "\n\t params\t" . serialize( $event->params )
            . "\n\n";


        return $message;
    }


    /**
     * {@inheritdoc}
     * @param LogEvent $event  Event to write.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function write(LogEvent $event)
    {
        $message = $this->format( $event );

        if ( is_resource( $this->_resource ) == false ) {
            $this->open();
        }

        $result = fwrite($this->_resource, $message);

        if ( $result === false ) {
            $exception = new Exception('Failed to write log');
            error_log( $exception->getMessage() );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ( is_resource( $this->_resource ) ) {
            fclose( $this->_resource );
        }
    }
}
