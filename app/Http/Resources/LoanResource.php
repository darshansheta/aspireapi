<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
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
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'term'             => $this->term,
            'amount'           => $this->amount,
            'status'           => $this->status,
            'payment_status'   => $this->payment_status,
            'remaining_amount' => $this->remaining_amount,
            'paid_amount'      => $this->paid_amount,
            'repayments'       => RepaymentResource::collection($this->whenLoaded('repayments')),
            'started_at'       => $this->started_at,
            'created_at'       => $this->created_at,
        ];
    }
}
