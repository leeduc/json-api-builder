<?php
namespace Leeduc\JsonApiBuilder\JsonApiBuilder;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

class JsonApiBuilder
{
    public $layout = 'JsonApiBuilder::default';

    private $data = [];
    private $source = [];

    private $view = false;

    public function __construct($source = [])
    {
        $this->source = $source;
    }

    public function __get($source)
    {
        return $this->data[$source];
    }

    public function setData($source)
    {
        if (!is_object($source) && !is_array($source)) {
            throw new \Exception("Resource must be array or object.", 400);
        }

        if (!$source instanceof \Illuminate\Pagination\LengthAwarePaginator && !is_array($source)) {
            $source = [$source];
            $this->view = true;
        }

        $this->source = $source;

        return $this;
    }

    public function getData()
    {
        return $this->source;
    }

    public function entity(array $fields, callable $callback = null)
    {
        $data = [];
        foreach ($this->source as $k => $element) {
            $type = strtolower(class_basename($element));
            $data[$k]['id'] = $element->id;
            $data[$k]['type'] = $type;
            foreach ($fields as $field) {
                if (isset($element->$field)) {
                    $data[$k]['attributes'][$field] = $element->$field;
                }
            }

            if (\Route::has($type)) {
                $data[$k]['links'] = [
                    'self' => route($type, ['id' => $data[$k]['id']])
                ];
            }
        }

        if (is_callable($callback)) {
            $return = $callback($data);
            if ($return) {
                $data = $return;
            }
        }

        $this->data['data'] = $data;
        return $this;
    }

    public function relationship(array $objects)
    {
        foreach ($this->source as $k => $element) {
            $origin_type = strtolower(class_basename($element));
            foreach ($objects as $relationship) {
                $type = strtolower($relationship);
                if (isset($element->$relationship) && (is_object($element->$relationship) || is_array($element->$relationship))) {
                    foreach ($element->$relationship as $key => $value) {
                        // $type = strtolower(class_basename($value));
                        $this->data['data'][$k]['relationships'][$relationship]['data'][$key] = [
                            'id' => $value->id,
                            'type' => $type
                        ];
                    }

                    if (\Route::has($origin_type)) {
                        $this->data['data'][$k]['relationships'][$relationship]['links'] = [
                        'self' => route($origin_type, ['id' => $this->data['data'][$k]['id']]) . '/relationships/' . $relationship,
                        'related' => route($origin_type, ['id' => $this->data['data'][$k]['id']]) . '/' . $relationship
                      ];
                    }
                }
            }
        }

        return $this;
    }

    public function included(array $objects, callable $callback = null)
    {
        $include_data = [];
        foreach ($this->source as $k => $element) {
            // $origin_type = strtolower(class_basename($element));
            foreach ($objects as $relationship => $fields) {
                if (is_numeric($relationship)) {
                    $relationship = $fields;
                }


                if (isset($element->$relationship) && (is_object($element->$relationship) || is_array($element->$relationship))) {
                    foreach ($element->$relationship as $key => $value) {
                        // $type = strtolower(class_basename($value));
                        $type = strtolower($relationship);
                        $data = [];
                        $data['type'] = $type;
                        $data['id'] = $value->id;
                        if (is_array($fields)) {
                            foreach ($fields as $field) {
                                if (isset($value->$field)) {
                                    $data['attributes'][$field] = $value->$field;
                                }
                            }
                        } else {
                            if (is_object($value) && method_exists($value, 'toArray')) {
                                $data['attributes'] = $value->toArray();
                            } else {
                                if (is_object($value) || is_array($value)) {
                                    foreach ($value as $k1 => $v1) {
                                        $data['attributes'][$k1] = $v1;
                                    }
                                }
                            }
                        }

                        if (\Route::has($type)) {
                            $data['links'] = [
                                'self' => route($type, ['id' => $this->data['data'][$k]['id']])
                            ];
                        }

                        if (!in_array($data, $include_data)) {
                            $include_data[] = $data;
                        }
                    }
                }
            }
        }

        if (is_callable($callback)) {
            $return = $callback($include_data);
            if ($return) {
                $include_data = $return;
            }
        }

        $this->data['included'] = $include_data;
        return $this;
    }

    public function parse()
    {
        if ($this->view && $this->data) {
            $data = $this->data;
            $data['data'] = array_shift($data['data']);
            return $data;
        }

        return $this->data;
    }
}
