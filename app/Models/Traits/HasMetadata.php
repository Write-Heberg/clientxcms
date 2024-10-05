<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Traits;

use App\Models\Metadata;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMetadata
{
    /**
     * Relation pour obtenir les métadonnées associées au modèle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function metadata(): MorphMany
    {
        return $this->morphMany(Metadata::class, 'model');
    }

    /**
     * Cache pour stocker les métadonnées du modèle.
     *
     * @var array|null
     */
    protected $metadataCache = null;

    /**
     * Fonction pour obtenir les métadonnées du modèle.
     *
     * @return array
     */
    public function getCachedMetadata(): array
    {
        if ($this->metadataCache === null) {
            $this->metadataCache = $this->metadata()->get()->pluck('value', 'key')->toArray();
        }
        return $this->metadataCache;
    }

    public static function getItemsByMetadata($key, $value)
    {
        return self::whereHas('metadata', function ($query) use ($key, $value) {
            $query->where('key', $key)->where('value', $value)->where('model_type', self::class);
        })->get();
    }

    /**
     * Fonction pour attacher une métadonnée au modèle.
     *
     * @param string $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function attachMetadata(string $key, $value)
    {
        if ($this->metadata()->where('key', $key)->exists()) {
            $this->updateMetadata($key, $value);
            $metadata = $this->metadata()->where('key', $key)->first();
        } else {
            $metadata = $this->metadata()->create([
                'key' => $key,
                'value' => $value,
            ]);
        }

        // Mettre à jour le cache
        $this->updateMetadataCache();

        return $metadata;
    }

    /**
     * Fonction pour obtenir une métadonnée spécifique du modèle.
     *
     * @param string $key
     * @return mixed
     */
    public function getMetadata(string $key)
    {
        $cachedMetadata = $this->getCachedMetadata();

        return $cachedMetadata[$key] ?? null;
    }

    /**
     * Fonction pour synchroniser les métadonnées avec le cache.
     *
     * @param array $metadata
     * @return void
     */
    public function syncMetadata(array $metadata)
    {
        $this->metadata()->delete();
        foreach ($metadata as $key => $value) {
            $this->attachMetadata($key, $value);
        }

        // Mettre à jour le cache
        $this->updateMetadataCache();
    }

    /**
     * Fonction pour mettre à jour le cache des métadonnées.
     *
     * @return void
     */
    protected function updateMetadataCache()
    {
        $this->metadataCache = $this->metadata()->get()->pluck('value', 'key')->toArray();
    }

    public function detachMetadata(string $key)
    {
        $this->metadata()->where('key', $key)->delete();
        $this->updateMetadataCache();
    }

    public function updateMetadata(string $key, $value)
    {
        $this->metadata()->where('key', $key)->update(['value' => $value]);
        $this->updateMetadataCache();
    }

    public function updateMetadataOrCreate(string $key, $value)
    {
        $metadata = $this->metadata()->where('key', $key)->first();
        if ($metadata === null) {
            $this->attachMetadata($key, $value);
        } else {
            $metadata->update(['value' => $value]);
        }
    }

    public function hasMetadata(string $key): bool
    {
        return $this->getCachedMetadata()[$key] ?? false;
    }

    public static function bootHasMetadata()
    {
        static::deleting(function ($model) {
            $model->metadata()->delete();
        });
    }
}
