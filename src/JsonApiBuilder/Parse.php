<?php
namespace Leeduc\JsonApiBuilder\JsonApiBuilder;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use Symfony\Component\HttpFoundation\Response;

class Parse extends Response
{
    private $data;
    private $sources;
    private $request;

    private $finder;
    private $factory;
    public function __construct(Request $request, Factory $factory, array $data, $sources = [], $meta = [])
    {
        $this->request = $request;
        $this->data = $data;
        $this->sources = $sources;
        $this->factory = $factory;
        $this->finder = $factory->getFinder();

        if ($meta) {
            $this->data['jsonapi'] = array_filter($meta);
        }
    }

    public function response()
    {
        return response()->json($this->data);
    }

    public function pagination(array $fields = [])
    {
        $pagination = [];
        $source = $this->sources;

        if ($source && is_array($source)) {
            $source = $source[0];
        }

        if ($source &&
            $this->checkPaginationObject($source)) {
            $params = $this->request->except('page.number');
            array_set($params, 'page.size', $source->perPage());
            $source = $source->appends($params);

            $pagination = [
                'self'  => $this->request->fullUrl(),
                'first' => $source->url(1),
                'prev'  => $source->previousPageUrl(),
                'next'  => $source->nextPageUrl(),
                'last'  => $source->url($source->lastPage())
            ];
        }

        if ($fields) {
            $pagination = array_replace($pagination, $fields);
        }

        $this->data['links'] = array_filter($pagination);

        return $this;
    }

    public function meta(array $fields = [])
    {
        $meta = [];

        if ($fields) {
            $meta = $fields;
        }

        $this->data['meta'] = array_filter($meta);

        return $this;
    }

    public function checkPaginationObject($object)
    {
        return $object instanceof \Illuminate\Pagination\LengthAwarePaginator;
    }
}
