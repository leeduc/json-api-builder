<?php
namespace Leeduc\JsonApiBuilder\JsonApiBuilder;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use Symfony\Component\Yaml\Yaml;
use Leeduc\JsonApiBuilder\Exceptions\ViewException;

class Generate
{
    private $data = [];
    private $source = [];
    private $request;

    private $view = false;
    private $view_path;
    private $relationships = [];

    private $generate;
    private $finder;
    public function __construct(Request $request, Factory $factory)
    {
        $this->request = $request;
        $this->view_path = realpath(base_path('resources/views'));
        $this->factory = $factory;
        $this->finder = $factory->getFinder();
        $this->finder->addExtension('schema.yaml');
    }

    public function __get($source)
    {
        return $this->data[$source];
    }

    public function json($data = [])
    {
        if ($data) {
            $this->data = $data;
        }

        return new Parse($this->request, $this->factory, $this->data, $this->source);
    }

    public function setData($source)
    {
        if (!is_object($source) && !is_array($source)) {
            throw new \Exception("Resource must be array or object.", 400);
        }

        if (!$source instanceof \Illuminate\Pagination\LengthAwarePaginator &&
            !$source instanceof \Illuminate\Database\Eloquent\Collection &&
            !is_array($source)) {
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

    public function entity($fields = null, callable $callback = null, $raw = false)
    {
        $data = [];

        if ($fields && !is_array($fields)) {
            $fields = $this->getSchema($fields);
        }

        if (isset($fields['relationships']) && is_array($fields['relationships'])) {
            foreach ($fields['relationships'] as $key => $value) {
                if(is_array($value)) {
                    $this->relationships[$key] = $value;
                    continue;
                }

                $this->relationships[$key] = $this->parsePartial($value);
            }
        }

        foreach ($this->source as $k => $element) {
            $type = isset($fields['type']) ? $fields['type'] : strtolower(class_basename($element));
            $data[$k]['id'] = $element->$fields['id'];
            $data[$k]['type'] = $type;
            if (isset($fields['attributes']) && is_array($fields['attributes'])) {
                foreach ($fields['attributes'] as $key => $field) {
                    if (isset($element->$field)) {
                        $data[$k]['attributes'][$key] = $element->$field;
                    }
                }
            } else {
                $data[$k]['attributes'] = $element->getAttributes();
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

    public function relationships(array $objects = [], $ele = false)
    {
        $relationships = $this->relationships;

        foreach ($this->source as $k => $element) {
            $origin_type = strtolower(class_basename($element));
            foreach ($objects as $field) {
                if (!isset($relationships[$field])) {
                    throw new \Exception("Relationship [$field] does not exists.", 500);
                }

                $type = isset($relationships[$field]['type']) ? $relationships[$field]['type'] : strtolower($field);

                if ((isset($element->$field) || method_exists($element, $field)) && (is_object($element->$field) || is_array($element->$field))) {
                    if ($element->$field instanceof \Illuminate\Database\Eloquent\Collection ||
                        is_array($element->$field)) {
                        foreach ($element->$field as $key => $value) {
                            $id = isset($relationships[$field]['id']) ? $value->$relationships[$field]['id'] : $value->id;
                            $this->data['data'][$k]['relationships'][$field]['data'][$key] = [
                            'id' => $id,
                            'type' => $type
                        ];
                        }
                    } else {
                        $value = $element->$field;
                        $id = isset($relationships[$field]['id']) ? $value->$relationships[$field]['id'] : $value->id;
                        $this->data['data'][$k]['relationships'][$field]['data'][] = [
                            'id' => $id,
                            'type' => $type
                        ];
                    }

                    if (\Route::has($origin_type) && !$ele) {
                        $this->data['data'][$k]['relationships'][$field]['links'] = [
                        'self' => route($origin_type, ['id' => $this->data['data'][$k]['id']]) . '/relationships/' . $field,
                        'related' => route($origin_type, ['id' => $this->data['data'][$k]['id']]) . '/' . $field
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
        $relationships = $this->relationships;
        foreach ($this->source as $k => $element) {
            // $origin_type = strtolower(class_basename($element));
            foreach ($objects as $relationship => $opts) {
                if (is_numeric($relationship)) {
                    $relationship = $opts;
                }

                if (!isset($relationships[$relationship])) {
                    throw new \Exception("Included [$relationship] data does not exists.", 500);
                }

                $resource = $relationships[$relationship];

                if (isset($element->$relationship) && (is_object($element->$relationship) || is_array($element->$relationship))) {
                    foreach ($element->$relationship as $key => $value) {
                        // $type = strtolower(class_basename($value));
                        if (!$this->generate) {
                            $this->generate = new Generate($this->request, $this->factory);
                        }

                        $this->generate->setData($element->$relationship);
                        $this->generate->entity($resource);
                        $this->generate->view = false;
                        $list_rls = [];

                        if (isset($resource['relationships']) && $resource['relationships']) {
                            foreach ($resource['relationships'] as $k2 => $v2) {
                                $list_rls[] = $k2;
                            }
                        }

                        $this->generate->relationships($list_rls);

                        $data = $this->generate->parse();

                        foreach ($data['data'] as $key => $value) {
                            if (!in_array($value, $include_data)) {
                                $include_data[] = $value;
                            }
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

    /**
     * @codeCoverageIgnore
     */
    public function errors($errors, callable $callback = null)
    {
        if ($errors instanceof \Illuminate\Support\MessageBag ||
            (method_exists($errors, 'first') &&
            method_exists($errors, 'toArray'))) {
            $data = [
                'detail' => $errors->first(),
                'source' => $errors->toArray()
            ];
        } else {
            $data = $errors;
        }


        if (is_callable($callback)) {
            $return = $callback($data);
            if ($return) {
                $data = $return;
            }
        }

        $this->data['errors'] = $data;

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

    public function getSchema($name)
    {
        $path = $this->getPath($name);
        return $this->deYaml($path);
    }

    protected function getPath($name)
    {
        $path = $this->finder->find($name);

        if (!preg_match("/\b(\.schema.yaml)\b/", $path)) {
            throw new \InvalidArgumentException("View [$name] not found.", 404);
        }

        return $path;
    }

    protected function deYaml($path)
    {
        $yaml_content = file_get_contents($path);
        return Yaml::parse($yaml_content);
    }

    protected function parsePartial($partial)
    {
        if (is_string($partial) && strpos($partial, 'partial:') !== false) {
            return $this->getSchema(str_replace('partial:', '', $partial));
        }

        return [];
    }
}
