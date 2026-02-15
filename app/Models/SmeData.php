<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmeData extends Model
{
    use HasFactory;

    protected $table = 'sme_datas';

    protected $fillable = [
        'data_id',
        'network',
        'plan_type',
        'amount',
        'size',
        'validity',
        'status',
    ];

    /**
     * Calculate final price for a specific user role.
     */
    public function calculatePriceForRole($role)
    {
        $service = Service::where('name', 'SME Data')->first();
        if (!$service) return (float)$this->amount;

        $networkMap = [
            'MTN' => 'SME01',
            'AIRTEL' => 'SME02',
            'GLO' => 'SME03',
            '9MOBILE' => 'SME04'
        ];

        $fieldCode = $networkMap[strtoupper($this->network)] ?? null;
        $field = $service->fields()->where('field_code', $fieldCode)->first();
        
        $fee = $field ? (float)$field->base_price : 0;
        $markup = 0;

        if ($field) {
            $markup = (float)$field->prices()
                ->where('user_type', $role)
                ->value('price') ?? 0;
        }

        return (float)$this->amount + $fee + $markup;
    }
}
