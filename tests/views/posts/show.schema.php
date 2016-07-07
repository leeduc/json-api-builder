<?php
return [
  'id' => $data->id,
  'type' => 'post',
  'attributes' => [
    'title' => $data->title,
    'content' => $data->content
  ],
  'links' => [
    'self' => route('get_post', ['id' => $data->id])
  ]
];
