<?php

namespace App\Providers;

use App\Constants\BanTypeConstants;
use App\Constants\RatingTypeConstants;
use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Schema\Types\LaravelEnumType;

class ConstantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @param TypeRegistry $typeRegistry
     * @return void
     */
    public function boot(TypeRegistry $typeRegistry)
    {
        $typeRegistry->register(
            new LaravelEnumType(RatingTypeConstants::class, 'RatingConstant')
        );
        $typeRegistry->register(
            new LaravelEnumType(BanTypeConstants::class, 'BanConstant')
        );
    }
}
