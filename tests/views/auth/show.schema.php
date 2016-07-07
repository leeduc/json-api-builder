<?php
return [
  'id' => $data->id,
  'type' => 'user',
  'attributes' => [
    'name' => $data->name,
    'email' => $data->email
  ],
  'relationships' => [
    'posts' => [
      'partial' => 'posts.show',
      'links' => [
        'self' => route('get_user', ['id' => $data->id]) . '/relationships/posts',
        'related' => route('get_user', ['id' => $data->id]) . '/posts'
      ]
    ],
    'comments' => [
      'partial' => 'comments.show',
      'links' => [
        'self' => route('get_user', ['id' => $data->id]) . '/relationships/comments',
        'related' => route('get_user', ['id' => $data->id]) . '/comments'
      ]
    ]
  ],
  'links' => [
    'self' => route('get_user', ['id' => $data->id])
  ]
];
