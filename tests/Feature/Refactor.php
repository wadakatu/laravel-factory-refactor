<?php

declare(strict_types=1);

namespace Tests\Feature;

class Refactor
{
    public function beforeRefactor()
    {
        $model = null;
        $count = null;

        factory(User::class)->make();
        factory(App\Models\User::class)->make();
        factory(User::class, 10)->make();
        factory(App\Models\User::class, 10)->make();
        facotry($model)->make();
        factory(User::class, $count['user'])->make();
        factory($model, 10)->make();
        factory($model, $count)->make();
        factory($model['user'], $count[0])->make();
        factory($model['user'][0], $count[0][1])->make();
        factory($model[0], $count['user'])->make();
    }

    public function afterRefactor()
    {
        $model = null;
        $count = null;

        User::factory()->make();
        App\Models\User::factory()->make();
        User::factory()->count(10)->make();
        App\Models\User::factory()->count(10)->make();
        $model::factory()->make();
        User::factory()->count($count['user'])->make();
        $model::factory()->count(10)->make();
        $model::factory()->count($count)->make();
        $model['user']::factory()->count($count[0])->make();
        $model['user'][0]::factory()->count($count[0][1])->make();
        $model[0]::factory()->count($count['user'])->make();
    }
}
