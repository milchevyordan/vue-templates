<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\File;
use App\Models\Image;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

trait HasImages
{
    /**
     * Inverse of imageable relationship.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable', 'imageable_type', 'imageable_id', 'id')
            ->orderBy('order');
    }

    /**
     * Get grouped images by sections provided in param.
     *
     * @param  array        $sections
     * @return Collection[] an array where each key represents a section, and the corresponding
     *                      value is a Collection of images associated with that section
     */
    public function getGroupedImages(array $sections): array
    {
        $this->load(['images' => function ($query) {
            $query->orderBy('order');
        }]);

        $images = $this->images;
        $imageGroups = [];

        foreach ($sections as $section) {
            $imageGroups[$section] = $images->filter(function ($image) use ($section) {
                return $section == $image->section;
            })->sortBy('order')->values();
        }

        return $imageGroups;
    }

    /**
     * Get the model's first image.
     */
    public function firstImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable', 'imageable_type', 'imageable_id', 'id')->ofMany('order', 'min');
    }

    /**
     * Get the model's last image.
     */
    public function lastImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable', 'imageable_type', 'imageable_id', 'id')->ofMany('order', 'max');
    }

    /**
     * Get the model's most recent image.
     */
    public function latestImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable', 'imageable_type', 'imageable_id', 'id')->latestOfMany();
    }

    /**
     * Get the model's oldest image.
     */
    public function oldestImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable', 'imageable_type', 'imageable_id', 'id')->oldestOfMany();
    }

    /**
     * Saves the record with images. If there are no images, it only saves the record.
     *
     * @param  null|Collection<File> $uploadedImages
     * @param  ?string               $section        - Separates the images by section if needed. This param allows us to have different sections in one model.
     * @return self
     */
    public function saveWithImages(?Collection $uploadedImages, ?string $section = null): self
    {
        if (! $this->id) {
            $this->save();
        }

        if (! $uploadedImages || $uploadedImages->isEmpty()) {
            return $this;
        }

        $maxImageOrder = $this->images()->max('order');

        $images = new Collection();

        foreach ($uploadedImages as $image) {
            $image->imageable_id = $this->id;
            $image->imageable_type = $this->getMorphClass();
            $image->section = $section;
            $image->order = $maxImageOrder + 1;

            $images->push($image);

            $maxImageOrder++;
        }

        $this->images()->saveMany($images);

        $this->updateProfileImage();

        return $this;
    }

    /**
     * Update profile image (image_path) to be the first image of the section provided in model's profileImageSection variable.
     *
     * @return void
     */
    public function updateProfileImage(): void
    {
        if (! $this->profileImageSection) {
            return;
        }

        $images = $this->getGroupedImages([$this->profileImageSection]);
        $newPath = $images[$this->profileImageSection]->first()?->path ?? null;

        if ($this->image_path == $newPath) {
            return;
        }

        $this->image_path = $newPath;
        $this->save();
    }

    /**
     * Delete the all entity images.
     *
     * @return self
     */
    public function deleteEntityImages(): self
    {
        $this->images()->whereIn('id', $this->id)->delete();

        return $this;
    }

    /**
     * Delete images By array of paths.
     *
     * @param  array $paths
     * @return self
     */
    public function deleteImagesByPaths(array $paths): self
    {
        $this->images()->whereIn('path', $paths)->delete();

        return $this;
    }
}
