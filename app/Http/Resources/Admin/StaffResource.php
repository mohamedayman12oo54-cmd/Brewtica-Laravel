<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'f_name'        => $this->f_name,
            'l_name'        => $this->l_name,
            'email'         => $this->email,
            'role'          => $this->role,
            'gender'        => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'staff_detail'  => $this->whenLoaded('staffDetail', function () {
                return $this->staffDetail ? [
                    'job_title'  => $this->staffDetail->job_title,
                    'salary'     => $this->staffDetail->salary,
                    'hire_date'  => $this->staffDetail->hire_date?->format('Y-m-d'),
                    'shift'      => $this->staffDetail->shift,
                    'department' => $this->staffDetail->department,
                ] : null;
            }),
            'created_at' => $this->created_at,
        ];
    }
}
