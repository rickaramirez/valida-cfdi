<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 18/06/16
 * Time: 09:03 AM
 */

namespace Blacktrue;

use Illuminate\Support\ServiceProvider;

class ValidaCfdiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            Blacktrue\Validacion\Schema::class,
            Blacktrue\Validacion\Schema::class
        );

        $this->app->bind(
            Blacktrue\Validacion\Importes::class,
            Blacktrue\Validacion\Importes::class
        );
    }
}