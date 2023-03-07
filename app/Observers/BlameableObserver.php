<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class BlameableObserver
{
    /**
     * Set created_by & updated_by with logged user ID on create.
     *
     * @param Model $model
     * @return void
     */
    public function creating(Model $model)
    {
        if (!$model->isDirty('created_by')) {
            $model->created_by = auth()->user()->id;
        }

        if (!$model->isDirty('updated_by')) {
            $model->updated_by = auth()->user()->id;
        }
    }

    /**
     * Set created_by & updated_by with logged user ID on update.
     *
     * @param Model $model
     * @return void
     */
    public function updating(Model $model)
    {
        if (!$model->isDirty('updated_by')) {
            $model->updated_by = auth()->user()->id;
        }
    }
}
