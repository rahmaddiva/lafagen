<?php

namespace App\Models;

use App\Enums\Community;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    public const PER_PAGE = 15;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'community' => Community::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public static function filtered(Community $community, array $filters = []): Builder
    {
        $query = static::query()->where('community', $community->value);

        if (! empty($filters['year'])) {
            $query->whereYear('start_date', (int) $filters['year']);
        }
        if (! empty($filters['month'])) {
            $query->whereMonth('start_date', (int) $filters['month']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }
        if (! empty($filters['q'])) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['q']).'%';
            $query->where(function (Builder $sub) use ($like) {
                $sub->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('location', 'like', $like);
            });
        }

        return $query->latest('start_date')->latest('id');
    }
}
