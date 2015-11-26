<?php
/**
 * This file defines classes to manage socket writers.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 */

/**
 * Class manages writing operation to socket.
 */
class SocketWriter extends LogEventWriter
{
    /**
     * Managed socket connection.
     * @var resource
     */
    private $_socket;


    /**
     * {@inheritdoc}
     * @param mixed|SimpleXMLElement $config  Writer configuration.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function init($config)
    {
        parent::init($config);

        $this->_socket = socket_create(
            constant( $config->domain ),
            constant( $config->socketType ),
            constant( $config->protocol )
        );

        if ( $this->_socket === false ) {
            error_log( "Could not initialize socket writer" );
            return false;
        }

        return true;
    }


    /**
     * {@inheritdoc}
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function open()
    {
        $result = socket_connect($this->_socket, $this->_config->address, 0 + $this->_config->port);

        if ( $result === false ) {
            error_log(
                socket_strerror(
                    socket_last_error( $this->_socket )
                )
            );
        }

        socket_set_nonblock($this->_socket);
        return $result;
    }


    /**
     * {@inheritdoc}
     * @param LogEvent $event  Handled event to format.
     * @return string
     */
    public function format(LogEvent $event) {
        $message = json_encode($event);
        return $message;
    }


    /**
     * {@inheritdoc}
     * @param LogEvent $event  Event to write.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function write(LogEvent $event)
    {
        $message = @$this->format($event);

        $result = socket_write($this->_socket, $message, strlen($message));
        if ( $result === false ) {
            error_log(
                "Could not write a message: {$message} to socket {$this->_config->address}:{$this->_config->port}"
            );
        }

        return $result;
    }


    /**
     * {@inheritdoc}
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function close()
    {
        socket_shutdown( $this->_socket);
        socket_close($this->_socket);
    }
}
