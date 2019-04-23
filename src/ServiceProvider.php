<?php
namespace ReBing0512\BaseCore;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    public function boot()
    {
        # 资源文件
        $this->publishes([
            __DIR__.'/app/Global' => app_path('Global'),
        ], 'global');

    }


    public function register()
    {
        //
    }

}
