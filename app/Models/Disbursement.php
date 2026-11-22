<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Disbursement extends Model
{
    use HasFactory;

    protected $primaryKey = 'disbursement_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'disbursement_id',
        'campaign_id',
        'user_id',
        'amount',
        'bank_name',
        'account_number',
        'account_name',
        'purpose',
        'receipt_path',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->disbursement_id)) {
                $model->disbursement_id = (string) Str::uuid();
            }
        });
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
