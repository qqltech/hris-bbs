<?php 
return [
   'supports_credentials' => false,
   'allowed_origins' => ['*'],
   'allowed_headers' => ['*'],
   'allowed_methods' => ['*'], // ex: ['GET', 'POST', 'PUT',  'DELETE']
   'exposed_headers' => ['*'],
   'max_age' => 0,
   'allowed_origins_patterns'=>[],
   'paths' => ['*'],
];