<?php
namespace Leeduc\JsonApiBuilder\JsonApiBuilder;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Parse extends Response
{
    private $data;
    private $sources;
    private $request;

    public function __construct(Request $request, array $data, $sources = [])
    {
        $this->request = $request;
        $this->data = $data;
        $this->sources = $sources;
    }

    public function response()
    {
        return response()->json($this->data);
    }

    public function pagination(array $fields = [])
    {
        $pagination = [];

        if ($this->sources &&
            is_array($this->sources) &&
            $this->checkPaginationObject($this->sources[0])) {
            $pagination = [
              'prev' => $this->sources[0]->previousPageUrl(),
              'next' => $this->sources[0]->nextPageUrl()
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
