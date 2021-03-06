<?php

namespace WhoisProxy;

use WhoisProxy\Exceptions\WhoisProxyException;

class WhoisProxy
{
    /**
     * Proxy Host / IP
     *
     * @var string
     */
    protected $_proxyHost;

    /**
     * Proxy Port
     *
     * @var int
     */
    protected $_proxyPort;

    /**
     * Socket Connection Timeout
     *
     * @var int
     */
    protected $_timeout;

    /**
     * Default Authoritative Server
     *
     * @var string
     */
    protected $_defaultServer;

    /**
     * Max Loop Count
     *
     * @var int
     */
    protected $_maxLoop;

    /**
     * WhoisProxy constructor.
     *
     * @param $options
     */
    public function __construct( $options = [] )
    {
        $this->setProxyHost( $options['host'] ?? config('whoisproxy.proxy.host') );
        $this->setProxyPort( $options['port'] ?? config('whoisproxy.proxy.port') );
        $this->setTimeout( $options['timeout'] ?? config('whoisproxy.connection.timeout') );
        $this->setDefaultServer( $options['server'] ?? config('whoisproxy.connection.default') );
        $this->setMaxLoop( $options['max_loops'] ?? config('whoisproxy.connection.max-loops') );
    }

    /**
     * Get Proxy Host / IP
     *
     * @return mixed
     */
    protected function getProxyHost()
    {
        return $this->_proxyHost;
    }

    /**
     * Set Proxy Host / IP
     *
     * @param mixed $proxyHost
     */
    protected function setProxyHost( $proxyHost ): void
    {
        $this->_proxyHost = $proxyHost;
    }

    /**
     * Get Proxy Port
     *
     * @return mixed
     */
    protected function getProxyPort()
    {
        return $this->_proxyPort;
    }

    /**
     * Set Proxy Port
     *
     * @param mixed $proxyPort
     */
    protected function setProxyPort( $proxyPort ): void
    {
        $this->_proxyPort = $proxyPort;
    }

    /**
     * Get Socket Connection Timeout
     *
     * @return mixed
     */
    protected function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * Set Socket Connection Timeout
     *
     * @param mixed $timeout
     */
    protected function setTimeout( $timeout ): void
    {
        $this->_timeout = $timeout;
    }

    /**
     * Get Authoritative Server
     *
     * @return mixed
     */
    protected function getDefaultServer()
    {
        return $this->_defaultServer;
    }

    /**
     * Set Authoritative Server
     *
     * @param mixed $defaultServer
     */
    protected function setDefaultServer( $defaultServer ): void
    {
        $this->_defaultServer = $defaultServer;
    }

    /**
     * Get Max Loop Count
     *
     * @return mixed
     */
    protected function getMaxLoop()
    {
        return $this->_maxLoop;
    }

    /**
     * Set Max Loop Count
     *
     * @param mixed $maxLoop
     */
    protected function setMaxLoop( $maxLoop ): void
    {
        $this->_maxLoop = $maxLoop;
    }

    /**
     * Return Http Status Code from String
     *
     * @param $httpStatus
     * @return string|null
     */
    protected function _parseHttpStatus( $httpStatus )
    {
        preg_match_all( "/HTTP\/1.1 (\d+) .*/m", $httpStatus, $matches, PREG_SET_ORDER, 0 );
        return empty( $matches ) ? null : $matches[0][1];
    }

    /**
     * Initialize HTTP Proxy Connection
     *
     * @return mixed
     */
    protected function initializeConnection()
    {
        return fsockopen(
            $this->getProxyHost(),
            $this->getProxyPort(),
            $errno,
            $error,
            $this->getTimeout()
        );
    }

    /**
     * Terminate HTTP Proxy Connection
     *
     * @param $connection
     */
    protected function terminateConnection( $connection )
    {
        fclose( $connection );
    }

    /**
     * Parse Line of Raw Whois Result
     *
     * @param $key
     * @param $haystack
     * @return string|null
     */
    protected function parseValue( $key, $haystack )
    {
        preg_match_all( "/$key(.*)\n/mU", strtolower( $haystack ), $matches, PREG_SET_ORDER, 0 );
        return empty( $matches ) ? null : trim( $matches[0][1] );
    }

    /**
     * Get Appropriate Whois Server for Future Queries
     *
     * @param $domain
     * @param null $server
     * @return string|null
     * @throws WhoisProxyException
     */
    public function getWhoisServer( $domain, $server = null )
    {
        $whoisData = $this->query( $domain, $server );

        foreach ( config('whoisproxy.patterns.whois') as $pattern ) {
            $match = $this->parseValue( $pattern, $whoisData );
            if ( !is_null( $match ) ) return $match;
        }

        return null;
    }

    /**
     * Query Whois Server for Information
     *
     * @param $domain
     * @param null $server
     * @return string
     * @throws WhoisProxyException
     */
    public function query( $domain, $server = null )
    {
        $connection = $this->initializeConnection();
        $whoisData  = "";
        $loopCount  = 0;

        $domain = strtoupper( trim( $domain ) ) . "\r\n";
        $server = strtoupper( $server ? $server : $this->getDefaultServer() ) . ":43\r\n";

        fputs( $connection, "CONNECT $server" );
        fputs( $connection, "$domain" );

        while ( !feof( $connection ) ) {
            $loopCount++;
            $whoisData .= trim( fgets( $connection, 1024 ) ) . PHP_EOL;
            if ( $loopCount > $this->getMaxLoop() ) break;
        }

        $this->terminateConnection( $connection );

        $finalOutput = explode( "\n", $whoisData );
        $httpStatus  = (int)$this->_parseHttpStatus( $finalOutput[0] );
        $finalOutput = array_slice( $finalOutput, 2 );
        $finalOutput = implode( "\n", $finalOutput );

        if ( $httpStatus !== 200 || is_null( $httpStatus ) ) throw new WhoisProxyException( "Encountered an error while trying to access whois server.", $httpStatus );

        return $finalOutput;
    }

    /**
     * Query Registrar Whois Server for Information
     *
     * @param $domain
     * @return string
     * @throws WhoisProxyException
     */
    public function deepQuery( $domain )
    {
        $registry  = $this->getWhoisServer( $domain );
        $registrar = $this->getWhoisServer( $domain, $registry );

        return $this->query( $domain, $registrar ?? $registry );
    }
}
