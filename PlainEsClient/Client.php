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
        $url = $this->buildSocket() . '/_search/scroll';

        $query = <<<JSON
{
    "scroll": "{$scrollDuration}",
    "scroll_id": "{$scrollId}"
}
JSON;

        exec('curl --silent "' . $url . '" -d \'' . $query . '\'', $output, $returnVal);

echo 'executing request with data ' . $query . PHP_EOL;

        return implode(PHP_EOL, $output);
    }

    /**
     * @return string
     */
    private function buildSocket()
    {
        return $this->host . ':' . $this->port;
    }
}