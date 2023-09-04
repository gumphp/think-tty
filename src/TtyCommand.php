<?php
namespace gumphp\tty;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
class TtyCommand extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('gump:tty')
            ->setDescription('tty');
    }

    protected function execute(Input $input, Output $output)
    {
        dump(__METHOD__);
    }
}