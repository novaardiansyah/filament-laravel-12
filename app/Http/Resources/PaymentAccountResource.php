<?php

namespace App\Http\Resources;

use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentAccountResource extends JsonResource
{
  /**
   * Transform the resource collection into an array.
   *
   * @return array<int|string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      ...parent::toArray($request),
      'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
      'f_deposit' => toIndonesianCurrency($this->deposit ?? 0),
    ];
  }
}
