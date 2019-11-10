<?php

namespace App\Providers;

use App\Constants\BanConstants;
use App\Constants\RatingConstants;
use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Schema\Types\LaravelEnumType;

class ConstantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @param TypeRegistry $typeRegistry
     * @return void
     */
    public function boot(TypeRegistry $typeRegistry)
    {
        $typeRegistry->register(
            new LaravelEnumType(RatingConstants::class, 'RatingConstant')
        );
        $typeRegistry->register(
            new LaravelEnumType(BanConstants::class, 'BanConstant')
        );

    }
}
