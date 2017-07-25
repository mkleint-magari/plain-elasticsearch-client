<?php
namespace PlainEsClient;

/**
 *
 * @author Markus Kleint <markus.kleint@gmail.com>
 * @since  16.07.17
 * PHP 5.5
 *
 */
class Client
{

    /**
     * @var string
     */
    private $host = 'localhost';

    /**
     * @var
     */
    private $port = 9200;

    /**
     * Client constructor.
     *
     * @param string $host
     * @param int    $port
     */
    public function __construct($host = '127.0.0.1', $port = 9200)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * returns elasticsearch response as plain string
     *
     * @param string $query
     * @param string $index index- or aliasname
     * @param string $type  (optional
     *
     * @return string
     */
    public function search($query, $index, $type = '')
    {
        $url = $this->buildSocket();

        if (strlen($index)) {
            $url .= '/' . $index;
        }

        if (strlen($type)) {
            $url .= '/' . $type;
        }

        $url .= '/_search';

        exec('curl --silent "' . $url . '" -d \'' . $query . '\'', $output, $returnVal);

        return implode(PHP_EOL, $output);
    }

    /**
     * returns elasticsearch response as plain string
     *
     * @param string $query
     * @param string $index index- or aliasname
     * @param string $type  (optional
     *
     * @return string
     */
    public function count($query, $index, $type = '')
    {
        $url = $this->buildSocket();

        if (strlen($index)) {
            $url .= '/' . $index;
        }

        if (strlen($type)) {
            $url .= '/' . $type;
        }

        $url .= '/_count';

        exec('curl --silent "' . $url . '" -d \'' . $query . '\'', $output, $returnVal);

        return implode(PHP_EOL, $output);
    }

    /**
     * returns elasticsearch response as plain string
     *
     * @param string $query
     * @param string $index index- or aliasname
     * @param string $scrollDuration
     * @param string $type  (optional
     *
     * @return string
     */
    public function initiateScan($query, $index, $scrollDuration = '1m', $type = '')
    {
        $url = $this->buildSocket();

        if (strlen($index)) {
            $url .= '/' . $index;
        }

        if (strlen($type)) {
            $url .= '/' . $type;
        }

        $url .= '/_search?scroll=' . $scrollDuration;

        exec('curl --silent "' . $url . '" -d \'' . $query . '\'', $output, $returnVal);

        return implode(PHP_EOL, $output);
    }

    /**
     * @param string $scrollId
     * @param string $scrollDuration
     *
     * @return string
     */
    public function scan($scrollId, $scrollDuration = '1m')
    {
        $url = $this->buildSocket() . '/_search/scroll?scroll=' . $scrollDuration;

        exec('curl --silent "' . $url . '" -d \'' . $scrollId . '\'', $output, $returnVal);

        return implode(PHP_EOL, $output);
    }

    /**
     * @param string $bulk
     *
     * @return string
     */
    public function bulk(&$bulk)
    {
        $url = $this->buildSocket() . '/_bulk';

        $ch = curl_init();

        curl_setopt_array(
            $ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $bulk,
            CURLOPT_RETURNTRANSFER => true,
        ]
        );

        return curl_exec($ch); //json data
    }

    /**
     * @param string $indexName
     *
     * @return string
     */
    public function refresh($indexName)
    {
        $url = $this->buildSocket() . '/' . $indexName . '/_refresh';

        exec('curl --silent -XPOST "' . $url . '"', $output, $returnVal);

        return implode(PHP_EOL, $output);
    }

    /**
     * use this to send a customized curl request to an elasticsearch endpoint of your choice.
     *
     * @param string $endpoint - the endpoint address relative to the socket
     * @param string $payload  - payload data that should be submitted as json string
     * @param string $method   - GET, POST, PUT or DELETE
     *
     * @return string
     */
    public function curl($endpoint, $payload, $method)
    {
        $url = $this->buildSocket() . $endpoint;

        $ch = curl_init();

        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_RETURNTRANSFER => true,
            ]
        );

        return curl_exec($ch); //json data
    }

    /**
     * @param string $indexName
     * @param string $typeName
     * @param string $query
     */
    public function deleteByQuery($indexName, $typeName, $query)
    {
        $command = sprintf(
            'curl --silent -XDELETE "http://%s/%s/%s/_query" -d \'%s\'', $this->buildSocket(), $indexName, $typeName, $query
        );

        exec(
            $command, $output, $returnVal
        );
    }

    /**
     * @return string
     */
    private function buildSocket()
    {
        return $this->host . ':' . $this->port;
    }

}