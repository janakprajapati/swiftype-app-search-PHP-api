# swiftype-app-search-php

A PHP client for [Swiftype App Search](https://swiftype.com/app-search) API.



## Example usage

```php
require 'swiftype.php';

$client = new \Swiftype\SwiftypeClient('api_key', 'api_end_point', 'api_base_path');

print_r($client->create_engine('library'));

print_r($client->create_document('library', array(
  array(
    'title' => 'The Art of Community',
    'author' => 'Jono Bacon'
  ),
  array(
    'title' => 'Harry Potter and the Cursed Child',
    'author' => 'J. K. Rowling'
  )
)));

print_r($client->documents('library'));
```

### Documentation

The library should conform to the documentation found [here](https://swiftype.com/documentation/app-search/getting-started).

#### > __construct([api_key String], [api_end_point String], [api_base_path String])
The constructor for the SwiftypeClient object. Set your authentication information here.

`$client = new \Swiftype\SwiftypeClient('api-oer628q5hdfswb7dfdf3wvzbj50i', 'https://host-xxxxxx.api.swiftype.com', '/api/as/v1/');`

#### > engines()
Returns all your engines

`$client->engines();`

#### > engine(engine_id String)
Returns a specific engine.

`$client->engine('library');`

#### > create_engine(engine_id String)
Creates a new engine

`$client->create_engine('library');`

#### > delete_engine(engine_id String)
Destroys an engine

`$client->delete_engine('library');`

#### > list_documents(engine_id String)
Returns all documents for a certain engine.

`$client->list_documents('library');`

#### > documents(engine_id String, document_ids Array)
Returns specific documents as per given document ids.

`$client->documents('library', array('1','2'));`

#### > create_documents(engine_id String, documents Array)
Creates documents. A document is an associative array containing key-value pairs where the key is the field name and the value is the content. If no "id" is provided, then one will be created by Swiftype.

```php
$client->create_documents('library', array(
      array(
        'title' => 'The Art of Community',
        'author' => 'Jono Bacon'
      ),
      array(
        'title' => 'Harry Potter and the Cursed Child',
        'author' => 'J. K. Rowling'
      )
    )
);
```

#### > create_or_update_documents(engine_id String, documents Array)
Same as `create_document`, except it updates an existing document if given document id exists.

```php
$client->create_or_update_documents('library', array(
      array(
        'title' => 'Harry Potter And The Philosophers Stone New Jacket',
        'author' => 'J. K. Rowling'
      )
    )
);
```

#### > update_documents(engine_id String, documents Array)
Updates existing documents based on the specified document id.

```php
$client->update_documents('library', array(
    array(
      'id'=>'1', 
      'author' => 'Jorbo Bacon'
    )
));
```

#### > delete_documents(engine_id String, document_ids Array)
Destroy documents in bulk. `document_ids` is a simple array containing the `id`s of the documents you wish to destroy.

`$client->destroy_documents('library', array('1', '2'));`

#### > search(engine_id String, query String, [options Array])
`search` searches through the specified engine to find documents that matches the query.

To see what options are available, [see the documentation](https://swiftype.com/documentation/app-search/api/search).

```php
$client->search('library', 'community', array(
    "page" => array("current"=>1,"size"=>3)
));
```


## Acknowledgments

* The code and documentation is based on the [swiftype-php](https://github.com/Nevon/swiftype-php) PHP client. The [swiftype-php](https://github.com/Nevon/swiftype-php) is a PHP client for the Swiftype [Site Search](https://swiftype.com/documentation/site-search/overview) API. However, the "App Search" API structure is bit different than the "Site Search" API. I have applied required edits in the [swiftype-php](https://github.com/Nevon/swiftype-php) code to support the "App Search" API format.