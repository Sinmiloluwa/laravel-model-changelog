<?php

namespace Sinmiloluwa\LaravelModelChangelog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChangeLogEntry extends Model
{
    public $timestamps = false;

    protected $table = 'changelog_entries';

    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'event',
        'changes',
        'old_values',
        'new_values',
        'causer_type',
        'causer_id',
        'causer_name',
    ];

    protected $casts = [
        'changes'    => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $entry) {
            $entry->created_at = now();
        });
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeForField($query, string $field)
    {
        return $query->whereJsonContainsKey("changes->{$field}");
    }

    public function oldValue(string $field): mixed
    {
        return $this->changes[$field]['from'] ?? null;
    }

    public function newValue(string $field): mixed
    {
        return $this->changes[$field]['to'] ?? null;
    }

    public function changedField(string $field): bool
    {
        return array_key_exists($field, $this->changes ?? []);
    }
}