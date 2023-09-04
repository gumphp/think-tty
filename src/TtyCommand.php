<?php
namespace gumphp\tty;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Connection\TcpConnection;

class TtyCommand extends Command
{
    const CMD = 'htop';

    const ALLOW_CLIENT_INPUT = true;

    protected function configure()
    {
        // 指令配置
        $this->setName('gump:tty')
            ->setDescription('tty')
            ->addArgument('action')
        ;
    }

    protected function execute(Input $input, Output $output)
    {
        $worker = new Worker("websocket://0.0.0.0:7778");
        $worker->name = 'phptty';
        $worker->user = 'www';

        $worker->onConnect = [$this, 'onConnect'];
        $worker->onMessage = [$this, 'onMessage'];
        $worker->onClose = [$this, 'onClose'];
        $worker->onWorkerStop = [$this, 'onWorkerStop'];

        Worker::runAll();
    }

    public function onConnect(TcpConnection $connection)
    {
        $descriptorspec = [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ];

        unset($_SERVER['argv']);
        $env = array_merge(['COLUMNS'=>130, 'LINES'=> 50], $_SERVER);
        $connection->process = proc_open(self::CMD, $descriptorspec, $pipes, null, $env);
        $connection->pipes = $pipes;

        stream_set_blocking($pipes[0], 0);
        $connection->process_stdout = new TcpConnection($pipes[1]);
        $connection->process_stdout->onMessage = function($process_connection, $data)use($connection){
            $connection->send($data);
        };
        $connection->process_stdout->onClose = function($process_connection)use($connection) {
            // Close WebSocket connection on process exit.
            $connection->close();
        };
        $connection->process_stdin = new TcpConnection($pipes[2]);
        $connection->process_stdin->onMessage = function($process_connection, $data)use($connection) {
            $connection->send($data);
        };
    }

    public function onMessage(TcpConnection $connection, $data)
    {
        if (self::ALLOW_CLIENT_INPUT) {
            fwrite($connection->pipes[0], $data);
        }
    }

    public function onClose(TcpConnection $connection)
    {
        $connection->process_stdin->close();
        $connection->process_stdout->close();
        fclose($connection->pipes[0]);

        $connection->pipes = null;
        proc_terminate($connection->process);
        proc_close($connection->process);

        $connection->process = null;
    }

    public function onWorkerStop($worker)
    {
        foreach($worker->connections as $connection) {
            $connection->close();
        }
    }
}