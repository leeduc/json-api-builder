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
    private $relationships = [];

    private $generate;
    private $finder;
    public function __construct(Request $request, Factory $factory)
    {
        $this->request = $request;
        $this->view_path = realpath(base_path('resources/views'));
        $this->factory = $factory;
        $this->finder = $factory->getFinder();
        $this->finder->addExtension('schema.php');
    }

    public function __get($source)
    {
        return $this->data[$source];
    }

    public function json($meta = [])
    {
        return new Parse($this->request, $this->factory, $this->data, $this->source, $meta);
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

    public function entity($schema = null, callable $callback = null, $raw = false)
    {
        $data = [];
        $fields = $schema;
        foreach ($this->source as $k => $element) {
            if ($schema && !is_array($schema) && !is_callable($schema)) {
                $fields = $this->getSchema($schema, $element)->schema;
            } elseif (is_callable($schema)) {
                $sch = $schema($element);
                if ($sch) {
                    $fields = $sch;
                }
            }

            if (isset($fields['relationships']) && is_array($fields['relationships'])) {
                foreach ($fields['relationships'] as $key => $value) {
                    $this->relationships[$element->id][$key] = $value;
                }
            }

            $type = isset($fields['type']) ? $fields['type'] : strtolower(class_basename($element));
            $data[$k]['id'] = $fields['id'];
            $data[$k]['type'] = $type;

            if (isset($fields['attributes']) && is_array($fields['attributes'])) {
                foreach ($fields['attributes'] as $key => $field) {
                    $data[$k]['attributes'][$key] = $field;
                }
            } else {
                $data[$k]['attributes'] = $element->getAttributes();
            }

            if (isset($fields['links'])) {
                $data[$k]['links'] = [
                    'self' => $fields['links']['self']
                ];
            }

            if (is_callable($callback)) {
                $return = $callback($data[$k]);
                if ($return) {
                    $data[$k] = $return;
                }
            }
        }

        $this->data['data'] = $data;
        return $this;
    }

    public function relationships(array $objects = [], $ele = false)
    {
        $relationships = $this->relationships;
        foreach ($this->source as $k => $element) {
            foreach ($objects as $field) {

                if (!isset($relationships[$element->id][$field])) {
                    throw new \Exception("Relationship [$field] does not exists.", 500);
                }

                $relat = $relationships[$element->id][$field];
                $partial = $relat['partial'];


                if ((isset($element->$field) || method_exists($element, $field)) && (is_object($element->$field) || is_array($element->$field))) {
                    if ($element->$field instanceof \Illuminate\Database\Eloquent\Collection ||
                        is_array($element->$field)) {
                        foreach ($element->$field as $key => $value) {
                            if (is_string($relat['partial'])) {
                                $partial = $this->getSchema($relat['partial'], $value)->schema;
                            }

                            $id = isset($partial['id']) ? $partial['id'] : $value->id;
                            $type = isset($partial['type']) ? $partial['type'] : strtolower($field);

                            $this->data['data'][$k]['relationships'][$field]['data'][$key] = [
                                'id' => $id,
                                'type' => $type
                            ];
                        }
                    } else {
                        $value = $element->$field;

                        if (is_string($relat['partial'])) {
                            $partial = $this->getSchema($relat['partial'], $value)->schema;
                        }

                        $id = $partial['id'];
                        $type = isset($partial['type']) ? $partial['type'] : strtolower($field);

                        $this->data['data'][$k]['relationships'][$field]['data'][] = [
                            'id' => $id,
                            'type' => $type
                        ];
                    }

                    if (isset($relat['links'])) {
                        $links = $relat['links'];
                        if (isset($links['self'])) {
                            $this->data['data'][$k]['relationships'][$field]['links']['self'] = $links['self'];
                        }

                        if (isset($links['related']) && !$ele) {
                            $this->data['data'][$k]['relationships'][$field]['links']['related'] = $links['related'];
                        }
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
            foreach ($objects as $relationship => $opts) {
                if (is_numeric($relationship)) {
                    $relationship = $opts;
                }

                if (!isset($relationships[$element->id][$relationship])) {
                    throw new \Exception("Included [$relationship] data does not exists.", 500);
                }

                $relat = $relationships[$element->id][$relationship];
                $resource = $relat['partial'];

                if (isset($element->$relationship) && (is_object($element->$relationship) || is_array($element->$relationship))) {
                    foreach ($element->$relationship as $key => $value) {
                        if (is_string($relat['partial'])) {
                            $resource = $this->getSchema($relat['partial'], $value)->schema;
                        }

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

                        $this->generate->relationships($list_rls, true);

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

    public function getSchema($name, $data = null)
    {
        $path = $this->finder->find($name);
        return new JsonView($path, $data);
    }
}
