<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Tags\Tag as TagsTag;

class Tag extends TagsTag
{
    public function products() : MorphToMany
    {
        return $this->morphedByMany(product::class, 'taggable');
    }
}
