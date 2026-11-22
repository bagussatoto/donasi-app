<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CampaignUpdate extends Model
{
    use HasFactory;

    protected $primaryKey = 'update_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'update_id',
        'campaign_id',
        'title',
        'content',
        'image_path',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->update_id)) {
                $model->update_id = (string) Str::uuid();
            }
        });
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'campaign_id');
    }
}
