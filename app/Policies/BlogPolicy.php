<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\Doktor;

class BlogPolicy
{
    /**
     * Determine whether the doctor can view the blog post.
     */
    public function view(Doktor $doktor, Blog $blog): bool
    {
        return $doktor->id === $blog->doktor_id;
    }

    /**
     * Determine whether the doctor can update the blog post.
     */
    public function update(Doktor $doktor, Blog $blog): bool
    {
        return $doktor->id === $blog->doktor_id;
    }

    /**
     * Determine whether the doctor can delete the blog post.
     */
    public function delete(Doktor $doktor, Blog $blog): bool
    {
        return $doktor->id === $blog->doktor_id;
    }
}
