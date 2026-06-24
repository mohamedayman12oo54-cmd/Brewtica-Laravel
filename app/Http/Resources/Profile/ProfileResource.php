<?php

namespace App\Http\Resources\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'f_name' => $this->f_name,
            'l_name' => $this->l_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('y-m-d'),
            'role' => $this->role,
            'loyalty_points' => $this->whenLoaded('customer', function() {
                return $this->customer?->loyalty_points ?? 0;
            }),
            'street' => $this->whenLoaded('customer', function() {
                return $this->customer?->street;
            }),
            'city' => $this->whenLoaded('customer', function() {
                return $this->customer?->city;
            }),
            'phones' => $this->whenLoaded('customer', function() {
                return $this->phones->map(fn($p) => [
                    'id' => $p->id,
                    'phone' => $p->phone,
                    'is_primary' => (bool) $p->is_primary,
                ]);
            }),
        ];
    }
}
