<?php

namespace Sinmiloluwa\LaravelModelChangelog\Observer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Sinmiloluwa\LaravelModelChangelog\Concerns\HasChangelog;
use Sinmiloluwa\LaravelModelChangelog\Models\ChangelogEntry;

class ChangelogObserver
{
    protected static array $paused = [];

    public function created(Model $model): void
    {
        if (! $this->shouldTrack($model)) return;

        $this->record($model, 'created', [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldTrack($model)) return;

        $changes = $model->computeChangelogDiff(
            $model->getOriginal(),
            $model->getAttributes()
        );

        if (empty($changes)) return;

        $this->record($model, 'updated', $model->getOriginal(), $model->getAttributes(), $changes);
    }

    public function deleted(Model $model): void
    {
        if (! $this->shouldTrack($model)) return;

        $this->record($model, 'deleted', $model->getAttributes(), []);
    }

    public function restored(Model $model): void
    {
        if (! $this->shouldTrack($model)) return;

        $this->record($model, 'restored', [], $model->getAttributes());
    }

    protected function record(Model $model, string $event, array $old, array $new, array $changes = []): void
    {
        $causer = $this->resolveCauser();

        ChangelogEntry::create([
            'trackable_type' => get_class($model),
            'trackable_id'   => $model->getKey(),
            'event'          => $event,
            'changes'        => $changes,
            'old_values'     => $event === 'created' ? [] : $old,
            'new_values'     => $event === 'deleted' ? [] : $new,
            'causer_type'    => $causer ? get_class($causer) : null,
            'causer_id'      => $causer?->getKey(),
            'causer_name'    => $causer?->name ?? null,
        ]);
    }

    protected function shouldTrack(Model $model): bool
    {
        if (! in_array(HasChangelog::class, class_uses_recursive($model), true)) {
            return false;
        }

        return ! $model->isChangelogPaused();
    }

    protected function resolveCauser(): ?Model
    {
        return Auth::user();
    }

    public static function pauseFor(Model $model): void
    {
        static::$paused[spl_object_id($model)] = true;
    }

    public static function resumeFor(Model $model): void
    {
        unset(static::$paused[spl_object_id($model)]);
    }

    public static function isPausedFor(Model $model): bool
    {
        return isset(static::$paused[spl_object_id($model)]);
    }
}