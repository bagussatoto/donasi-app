<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use HasFactory;

    protected $primaryKey = 'entry_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'entry_id',
        'header_id',
        'account_code',
        'debit',
        'credit',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->entry_id)) {
                $model->entry_id = (string) Str::uuid();
            }
        });
    }

    public function header()
    {
        return $this->belongsTo(JournalHeader::class, 'header_id', 'header_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_code', 'account_code');
    }
}
