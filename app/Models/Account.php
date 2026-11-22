<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $primaryKey = 'account_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_code',
        'name',
        'type',
        'description',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'account_code', 'account_code');
    }
}
