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
     * @param string $query
     * @param string $index index- or aliasname
     * @param string $type (optional
     */
    public function search($query, $index, $type = '')
    {
        $url = 'localhost:9200';

        if (strlen($index)) {
            $url .= '/' .$index;
        }

        if (strlen($type)) {
            $url .= '/' .$type;
        }

        $url .= '/_search';



        exec('curl --silent "'.$url.'" -d "'.$query.'"', $output, $returnVal);

        return implode(PHP_EOL, $output);
    }
}