<?php
/**
 * This file defines classes to manage event writers to PostgreSQL.
 *
 * @author Maksym Rykin <max.rykin@gmail.com>
 * @version 1.0
 */

/**
 * Manages PostgreSQL writer.
 */
class PgSQLWriter extends LogEventWriter
{
    /**
     * Connection resource.
     * @var resource
     */
    protected $_connection;

    /**
     * SQL query template to insert page info.
     * @var string
     */
    protected $_pages = <<<SQL
INSERT INTO "pages" ("uri", "query", "session", "time" )
  VALUES ( $1, $2, $3, $4 )
  RETURNING id;
SQL;

    /**
     * SQL query template to insert event info.
     * @var string
     */
    protected $_events = <<<SQL
INSERT INTO "events" ("pageId", "time", "class", "object", "action", "params" )
  VALUES ( $1, $2, $3, $4, $5, $6::hstore );
SQL;


    /**
     * {@inheritdoc}
     * @param mixed|SimpleXMLElement $config  Writer configuration.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function init($config)
    {
        parent::init($config);
        return true;
    }

    /**
     * {@inheritdoc}
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function open()
    {
        $this->_connection = @pg_connect( $this->config->connectionString );

        if ($this->_connection === false) {
            return false;
        }

        $this->_prepareAsynchronous( 'pages', $this->_pages );
        $this->_prepareAsynchronous( 'events', $this->_events );

        return true;
    }


    /**
     * Writes page info into postgres database.
     * @param LogPage $page  Page info to write.
     */
    private function _writePage(LogPage $page){
        $params = array(
            $page->uri,
            $this->_arrayToHstore( $page->query ),
            $this->_arrayToHstore( $page->session ),
            date( 'c', $page->time )
        );

        $result = pg_query_params( $this->_connection, $this->_pages, $params );
        $row = pg_fetch_assoc($result);
        $page->id = $row["id"];
    }


    /**
     * {@inheritdoc}
     * @param LogEvent $event  Handled event to format.
     * @return array
     */
    public function format(LogEvent $event) {
        $params = array(
            $event->page->id,
            date( 'Y-m-d H:i:s' , $event->time ) . "." . substr( $event->time - floor( $event->time ), 2 ),
            $event->class,
            serialize($event->object),
            $event->action,
            $this->_arrayToHstore( $event->params )
        );

        return $params;
    }


    /**
     * {@inheritdoc}
     * @param LogEvent $event  Event to write.
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function write(LogEvent $event)
    {
        if ( empty($event->page->id) ) {
            $this->_writePage($event->page);
        }

        $params = $this->format($event);

        while( true ) {
            $result = pg_get_result($this->_connection);

            if ( is_resource( $result ) == true ) {
                $error = pg_result_error( $result );
                if ( empty( $error ) == false ) {
                    error_log( $error );
                }
            }

            if ( !pg_connection_busy( $this->_connection ) && !$result ) {
                pg_send_execute($this->_connection, 'events', $params);
                $result = pg_get_result($this->_connection);
                return $result;
            }

            usleep(100);
        }

        return false;
    }


    /**
     * {@inheritdoc}
     * @return bool <code>True</code> if operation was succeeded, otherwise return <code>false</code>.
     */
    public function close()
    {
        pg_close( $this->_connection );
    }


    /**
     * Prepares asynchronous sql statement.
     * @param string $name  Statement name.
     * @param string $query Query template.
     */
    private function _prepareAsynchronous( $name, $query ) {
        while( true ) {
            if (!pg_connection_busy( $this->_connection )
                && !pg_get_result($this->_connection)) {
                pg_send_prepare($this->_connection, $name, $query);
                return;
            }

            usleep(100);
        }
    }


    /**
     * Converts php array to postgres hstore.
     * @param array $array  Array to convert.
     * @return string  String representation of postgres hstore type.
     */
    private function _arrayToHstore( $array ) {
        $list = array();

        if ( empty( $array ) || !is_array( $array ) ) {
            return "";
        }

        foreach( $array as $key => $value ) {
            if ( is_array( $value ) ) {
                foreach ($value as $arrayKey => $arrayValue) {
                    $hstoreKey   = str_replace( '"', '\"', "{$key}[{$arrayKey}]" );

                    if ( is_object($arrayValue) ) {
                        $arrayValue = serialize( $arrayValue );
                    }

                    $hstoreValue = str_replace('"', '\"', $arrayValue );

                    $list[] = "\"$hstoreKey\" => \"$hstoreValue\"";
                }
            }

            if ( is_object($value) ) {
                $value = serialize( $value );
            }

            $hstoreKey   = str_replace( '"', '\"', "$key" );
            $hstoreValue = str_replace('"', '\"', $value );

            $list[] = "\"$hstoreKey\" => \"$hstoreValue\"";
        }

        $result = implode( ',', $list);
        return $result;
    }
}
