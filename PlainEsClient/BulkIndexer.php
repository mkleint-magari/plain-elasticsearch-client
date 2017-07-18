<?php
/**
 *
 * @author Markus Kleint <markus.kleint@gmail.com>
 * @since  21.11.16
 * PHP 5.5
 *
 */

namespace PlainEsClient;

class BulkIndexer
{

    /**
     * @var array
     */
    private $bulkCache = [];

    /**
     * @var int
     */
    private $maxBulkSize = 20000;

    /**
     * @var Client
     */
    private $client;

    /**
     * BulkIndexer constructor.
     *
     * @param int    $maxBulkSize
     * @param Client $client
     */
    public function __construct($maxBulkSize, Client $client)
    {
        $this->maxBulkSize = $maxBulkSize;
        $this->client = $client;
    }

    /**
     * @param string $index
     * @param string $type
     * @param array  $body
     * @param string $id
     */
    public function add($index, $type, array $body, $id = null)
    {
        if (0 === count($body)) {
            throw new \InvalidArgumentException('empty document body is not allowed');
        }

        $indexCommand = [
            'index' => [
                '_index' => $index,
                '_type' => $type,
            ],
        ];

        if (null !== $id) {

            $indexCommand['index']['_id'] = $id;
        }

        $encodedBody = json_encode($body);

        $this->bulkCache[] = json_encode($indexCommand);
        $this->bulkCache[] = $encodedBody;

        if (count($this->bulkCache) >= $this->maxBulkSize * 2) {

            $this->flush();
        }
    }

    public function flush()
    {
        if (count($this->bulkCache)) {

            // trailing linefeed is important for elasticsearch. last document in bulkcache is invalid otherwise
            $stringCache = implode(PHP_EOL, $this->bulkCache) . PHP_EOL;

            $this->client->bulk($stringCache);

            $this->resetBulkCache();
        }
    }

    private function resetBulkCache()
    {
        $this->bulkCache = [];
    }

}