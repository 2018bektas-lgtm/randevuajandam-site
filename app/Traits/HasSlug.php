<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Automatically generates unique slugs for Eloquent models.
 *
 * Usage:
 *   use HasSlug;
 *   protected function slugKaynak(): string { return 'baslik'; }       // Source field
 *   protected function slugKapsam(): array  { return ['doktor_id']; }  // Scope columns for uniqueness
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlugValue();
            }
        });

        static::updating(function ($model) {
            $kaynak = $model->slugKaynak();
            if ($model->isDirty($kaynak)) {
                $model->slug = $model->generateUniqueSlugValue();
            }
        });
    }

    /**
     * The attribute name used as the slug source (e.g. 'ad', 'baslik').
     */
    protected function slugKaynak(): string
    {
        return 'ad';
    }

    /**
     * Additional columns that scope the slug uniqueness (e.g. ['doktor_id']).
     * Return an empty array for globally unique slugs.
     */
    protected function slugKapsam(): array
    {
        return [];
    }

    /**
     * Generate a unique slug value within the configured scope.
     */
    public function generateUniqueSlugValue(): string
    {
        $baseSlug = Str::slug($this->{$this->slugKaynak()});
        $slug = $baseSlug;
        $counter = 1;

        $query = static::where('slug', $slug);

        foreach ($this->slugKapsam() as $column) {
            $query->where($column, $this->{$column});
        }

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        while ($query->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;

            $query = static::where('slug', $slug);
            foreach ($this->slugKapsam() as $column) {
                $query->where($column, $this->{$column});
            }
            if ($this->exists) {
                $query->where('id', '!=', $this->id);
            }
        }

        return $slug;
    }
}
