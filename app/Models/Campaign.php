<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory;

    protected $primaryKey = 'campaign_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'slug',
        'title',
        'description',
        'category_id',
        'target_amount',
        'collected_amount',
        'start_date',
        'end_date',
        'image_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Boot method untuk model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set UUID dan slug otomatis saat create
        static::creating(function ($model) {
            if (empty($model->campaign_id)) {
                $model->campaign_id = (string) Str::uuid();
            }

            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(4);
            }
        });

        // Update slug jika title diubah
        static::updating(function ($model) {
            if ($model->isDirty('title')) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(4);
            }
        });
    }

    /**
     * Relasi Many-to-One: Campaigns ke Category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    /**
     * URL gambar kampanye; memakai placeholder bila gambar belum diunggah.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : asset('image/placeholder.svg');
    }

    /**
     * Relasi One-to-Many: Campaigns ke Disbursements (Pencairan Dana).
     */
    public function disbursements()
    {
        return $this->hasMany(Disbursement::class, 'campaign_id', 'campaign_id');
    }

    /**
     * Hitung total dana yang sudah dicairkan (status completed).
     */
    public function getTotalDisbursedAttribute(): int
    {
        return (int) $this->disbursements()->where('status', 'completed')->sum('amount');
    }

    /**
     * Hitung sisa saldo yang masih tersedia untuk dicairkan.
     */
    public function getAvailableBalanceAttribute(): int
    {
        return max(0, (int) $this->collected_amount - $this->total_disbursed);
    }

    /**
     * Relasi One-to-Many: Campaigns ke Donations.
     */
    public function donations()
    {
        return $this->hasMany(Donasi::class, 'campaign_id', 'campaign_id');
    }

    /**
     * Scope: tambahkan atribut total_donations (jumlah donasi berstatus completed)
     * dalam satu query agar tidak terjadi N+1.
     */
    public function scopeWithTotalDonations($query)
    {
        return $query->withCount([
            'donations as total_donations' => fn ($q) => $q->where('status', 'completed'),
        ]);
    }

    /**
     * Isi atribut tampilan (sisa hari & progres) pada campaign.
     */
    public function decorate(): static
    {
        $this->days_remaining = max(0, (int) now()->startOfDay()->diffInDays($this->end_date, false));
        $this->progress = $this->target_amount > 0
            ? round(($this->collected_amount / $this->target_amount) * 100, 2)
            : 0;

        return $this;
    }
}
