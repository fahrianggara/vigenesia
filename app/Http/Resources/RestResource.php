<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestResource extends JsonResource
{
    protected $status;
    protected $message;

    /**
     * ResResource constructor. (Response Resource)
     *
     * @param  mixed $resource
     * @param  mixed $status (true|false)
     * @param  mixed $message
     * @return void
     */
    public function __construct($resource, $message, $status = true)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->resource,
        ];
    }
}
