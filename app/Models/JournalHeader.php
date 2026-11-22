<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JournalHeader extends Model
{
    use HasFactory;

    protected $primaryKey = 'header_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'header_id',
        'voucher_number',
        'transaction_date',
        'source_type',
        'source_id',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->header_id)) {
                $model->header_id = (string) Str::uuid();
            }
        });
    }

    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'header_id', 'header_id');
    }

    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }
}
