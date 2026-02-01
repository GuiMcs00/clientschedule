<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Customer;

/**
 * API resource collection for transforming paginated customer data.
 * 
 * This collection wraps multiple CustomerResource instances and includes pagination
 * metadata in the response. It provides comprehensive pagination information including
 * total records, current page, per-page count, and total pages.
 * 
 * The response structure includes:
 * - 'data': Array of CustomerResource instances
 * - 'meta': Pagination metadata (total, count, per_page, current_page, total_pages)
 */
class CustomerCollection extends ResourceCollection
{

    public $collects = CustomerResource::class;

    /**
     * Transform the resource collection into an array with pagination metadata.
     * 
     * Structures the response to include both the customer data and pagination information,
     * making it easy for clients to implement paginated navigation and display total counts.
     *
     * @param Request $request The HTTP request instance
     * @return array<int|string, mixed> The collection data with pagination metadata
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
            ],
        ];
    }
}
