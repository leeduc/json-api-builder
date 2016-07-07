<?php
namespace Leeduc\JsonApiBuilder\JsonApiBuilder;

class JsonView
{
  public $schema;
  private $data;
  function __construct($path, $data)
  {
      $this->data = $data;
      $this->schema = include $path;
  }
}
