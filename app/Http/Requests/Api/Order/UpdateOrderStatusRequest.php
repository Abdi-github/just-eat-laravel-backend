<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('orders.update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,confirmed,preparing,picked_up,delivered,cancelled'],
        ];
    }
}
