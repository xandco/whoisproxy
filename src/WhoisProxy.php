<?php

namespace btrsco\WhoisProxy;

class WhoisProxy
{
    /**
     * Proxy Host / IP
     * @var $_proxyHost
     */
    private $_proxyHost;

    /**
     * Proxy Port
     * @var $_proxyPort
     */
    private $_proxyPort;

    /**
     * Socket Connection Timeout
     * @var $_timeout
     */
    private $_timeout;

    /**
     * Default Authoritative Server
     * @var $_defaultServer
     */
    private $_defaultServer;

    /**
     * WhoisProxy constructor.
     * @param $options
     */
    public function __construct( $options )
    {
        $this->setProxyHost( $options['host'] ?? '127.0.0.1' );
        $this->setProxyPort( $options['port'] ?? 8080 );
        $this->setTimeout( $options['timeout'] ?? 10 );
        $this->setDefaultServer( 'whois.iana.org' );
    }

    /**
     * Get Proxy Host / IP
     * @return mixed
     */
    public function getProxyHost()
    {
        return $this->_proxyHost;
    }

    /**
     * Set Proxy Host / IP
     * @param mixed $proxyHost
     */
    public function setProxyHost( $proxyHost ): void
    {
        $this->_proxyHost = $proxyHost;
    }

    /**
     * Get Proxy Port
     * @return mixed
     */
    public function getProxyPort()
    {
        return $this->_proxyPort;
    }

    /**
     * Set Proxy Port
     * @param mixed $proxyPort
     */
    public function setProxyPort( $proxyPort ): void
    {
        $this->_proxyPort = $proxyPort;
    }

    /**
     * Get Socket Connection Timeout
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * Set Socket Connection Timeout
     * @param mixed $timeout
     */
    public function setTimeout($timeout): void
    {
        $this->_timeout = $timeout;
    }

    /**
     * Get Authoritative Server
     * @return mixed
     */
    public function getDefaultServer()
    {
        return $this->_defaultServer;
    }

    /**
     * Set Authoritative Server
     * @param mixed $defaultServer
     */
    public function setDefaultServer( $defaultServer ): void
    {
        $this->_defaultServer = $defaultServer;
    }

    /**
     * Initialize HTTP Proxy Connection
     * @return mixed
     */
    public function _initializeConnection()
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
     * @param $connection
     */
    public function _terminateConnection( $connection )
    {
        fclose( $connection );
    }

    /**
     * Parse Line of Raw Whois Result
     * @param $key
     * @param $haystack
     * @return string|null
     */
    public function parseValue( $key, $haystack )
    {
        preg_match_all( "/$key(.*)\n/mU", $haystack, $matches, PREG_SET_ORDER, 0 );
        return empty( $matches ) ? null : trim( $matches[0][1] );
    }

    /**
     * Query Whois Server for Information
     * @param $domain
     * @param null $server
     * @return string
     */
    public function queryWhois( $domain, $server = null )
    {
        $connection = $this->_initializeConnection();
        $whoisData  = "";

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
        return $whoisData;
    }

    /**
     * Get Appropriate Whois Server for Future Queries
     * @param $domain
     * @param null $server
     * @return string
     */
    public function getWhoisServer( $domain, $server = null )
    {
        $whois = $this->queryWhois( $domain, $server );
        return $this->parseValue( 'whois:', $whois ) ?? $this->parseValue( 'refer:', $whois );
    }
}
