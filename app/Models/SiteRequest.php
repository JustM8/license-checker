<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteRequest extends Model
{
    protected $fillable = [
        'site_id', 'domain', 'ip', 'user_agent', 'success', 'status_code',
    ];

    public function site()
    {
        return $this->belongsTo(License::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_code) {
            0 => '✅ Успішно',
            1 => '❌ Ліцензія не знайдена',
            2 => '❌ Невірний ключ',
            3 => '⚠️ Протермінована/деактивована',
            4 => '🚨 Втручання (видалено брендинг)',
            default => '⁉️ Невідомо',
        };
    }
}
