<?php
namespace gumphp\tty;

use think\Service;

class TtyService extends Service
{
    public function register()
    {

    }

    public function boot()
    {
        $this->commands([
            \gumphp\tty\TtyCommand::class,
        ]);
    }
}