<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RepaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'amount'       => $this->amount,
            'paid_amount'  => $this->paid_amount,
            'status'       => $this->status,
            'scheduled_at' => $this->scheduled_at,
            'paid_at'      => $this->paid_at,
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'created_at'   => $this->created_at,
        ];
    }
}
