<?php

declare(strict_types=1);

namespace Wadakatu\LaravelFactoryRefactor\Tests\Files;

class RefactorTarget
{
    public function targetRefactor()
    {
        $model = null;
        $count = null;

        factory(User::class)->make();
        factory(App\Models\User::class)->make();
        factory(User::class, 10)->make();
        factory(App\Models\User::class, 10)->make();
        factory($model)->make();
        factory(User::class, $count['user'])->make();
        factory($model, 10)->make();
        factory($model, $count)->make();
        factory($model['user'], $count[0])->make();
        factory($model['user'][0], $count[0][1])->make();
        factory($model[0], $count['user'])->make();
    }
}
