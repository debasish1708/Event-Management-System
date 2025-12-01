<?php

namespace App\Http\Resources;

use App\Traits\ApiResourceTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EventCollection extends ResourceCollection
{
    use ApiResourceTrait;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public $collects = EventResource::class;
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $collection = [
            'data' => $this->collection
        ];

        if ($this->resource instanceof LengthAwarePaginator) {
            $paginated = $this->resource->toArray();
            $collection['links'] = $this->paginationLinks($paginated);
            $collection['meta'] = $this->meta($paginated);
        }

        return $collection;
    }
}
