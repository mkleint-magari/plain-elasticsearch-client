# plain-elasticsearch-client

## installation
```bash

    composer require "mkleint-magari/plain-elasticsearch-client"
    
```

## usage for search

```php

    $client = new Client('127.0.0.1', 9200);
    $response = $client->search('{}', 'my-index', 'my-type');
    
```

## usage for bulk index


```php

    $testDocumentCount = 10000;
    $bulkIndexer = new BulkIndexer(5000, new Client('127.0.0.1', 9200));
    
    for ($i = 0; $i < $testDocumentCount; $i++) {
    
        $bulkIndexer->add('my-index', 'my-type', '{"my": "fancy document"}');
    }
    
    $bulkIndexer->flush();
    
```
