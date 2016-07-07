<?php
return [
  'id' => $data->id,
  'type' => 'comment',
  'attributes' => [
    'post_id' => (int) $data->post_id,
    'user_id' => (int) $data->user_id,
    'content' => $data->content
  ],
  'relationships' => [
    'user' => [
      'partial' => 'auth.show',
      'links' => [
        'self' => route('get_comment', ['id' => $data->id]) . '/relationships/comments',
        'related' => route('get_comment', ['id' => $data->id]) . '/comments'
      ]
    ]
  ],
  'links' => [
    'self' => route('get_comment', ['id' => $data->id])
  ]
];
