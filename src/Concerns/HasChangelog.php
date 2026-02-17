<?php

namespace Sinmiloluwa\LaravelModelChangelog\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Sinmiloluwa\LaravelModelChangelog\Models\ChangelogEntry;
use Sinmiloluwa\LaravelModelChangelog\Observer\ChangelogObserver;

trait HasChangelog
{
    public static function bootHasChangelog(): void
    {
        static::observe(ChangelogObserver::class);
    }

    public function changelog(): MorphMany
    {
        return $this->morphMany(ChangelogEntry::class, 'trackable')
            ->latest();
    }

    public function changelogFields(): array
    {
        return [];
    }

    public function changelogExcluded(): array
    {
        return array_merge(
            config('model-changelog.global_excluded', []),
            $this->changelogHidden()
        );
    }

    public function changelogHidden(): array
    {
        return [];
    }

    public function getTrackableFields(): array
    {
        $explicit = $this->changelogFields();

        if (! empty($explicit)) {
            return $explicit;
        }

        $fillable = $this->getFillable();

        return ! empty($fillable) ? $fillable : array_keys($this->getAttributes());
    }

    public function computeChangelogDiff(array $old, array $new): array
    {
        $trackable = $this->getTrackableFields();
        $excluded  = $this->changelogExcluded();
        $hidden    = $this->changelogHidden();
        $mask      = config('model-changelog.mask_value', '********');

        $changes = [];

        foreach ($trackable as $field) {
            if (in_array($field, $excluded, true)) {
                continue;
            }

            $oldVal = $old[$field] ?? null;
            $newVal = $new[$field] ?? null;

            if ((string) $oldVal === (string) $newVal) {
                continue;
            }

            $changes[$field] = [
                'from' => in_array($field, $hidden, true) ? $mask : $oldVal,
                'to'   => in_array($field, $hidden, true) ? $mask : $newVal,
            ];
        }

        return $changes;
    }

    public function withoutChangelog(callable $callback): mixed
    {
        ChangelogObserver::pauseFor($this);

        try {
            return $callback();
        } finally {
            ChangelogObserver::resumeFor($this);
        }
    }

    public function isChangelogPaused(): bool
    {
        return ChangelogObserver::isPausedFor($this);
    }
}