<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantCustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        $addresses = [];
        if ($this->relationLoaded('addresses')) {
            $addresses = $this->addresses
                ->where('status', 1)
                ->values()
                ->map(fn ($address) => [
                    'id' => (int) $address->id,
                    'name' => $address->name,
                    'address' => $address->address,
                    'status' => (int) $address->status,
                ])
                ->all();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'image' => $this->image,
            'address' => $addresses,
            'rate_status' => $this->rate_status,
            'opening_balance' => $this->opening_balance,
            'creation_type' => $this->getCreationType(),
            'status' => (int) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Determine creation type based on mobile number pattern
     * Returns 1 if mobile number matches Bangladeshi patterns, 2 otherwise
     *
     * Bangladeshi mobile number prefixes:
     * - 017, 013: Grameenphone
     * - 018, 016: Robi
     * - 019, 014: Banglalink
     * - 015: Teletalk
     *
     * @return int 1 for Bangladeshi numbers, 2 for international numbers
     */
    private function getCreationType(): int
    {
        $mobile = $this->mobile ?? '';

        // Bangladeshi mobile number patterns (all major operators)
        $bdPatterns = [
            '017', // Grameenphone
            '013', // Grameenphone
            '018', // Robi
            '016', // Robi
            '019', // Banglalink
            '014', // Banglalink
            '015', // Teletalk
        ];

        // Check if mobile number starts with any BD pattern
        foreach ($bdPatterns as $pattern) {
            if (str_starts_with($mobile, $pattern)) {
                return 1; // Bangladeshi number
            }
        }

        return 2; // Non-Bangladeshi number
    }
}
