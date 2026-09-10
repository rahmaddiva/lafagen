<?php

namespace App\Models;

use App\Enums\Community;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['community', 'name'];

    protected function casts(): array
    {
        return ['community' => Community::class];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function scopeFor(Builder $query, Community $community): Builder
    {
        return $query->where('community', $community->value);
    }

    public static function optionsFor(Community $community): array
    {
        return static::for($community)->orderBy('name')->get(['id', 'name'])
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->all();
    }
}
