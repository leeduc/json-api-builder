<?php
return [
  'id' => $data->id,
  'type' => 'user',
  'links' => [
    'self' => route('get_post', ['id' => $data->id])
  ]
];
