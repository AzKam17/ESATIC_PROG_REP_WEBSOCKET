<?php

namespace App\Command;

use App\WebSocket\MessageHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebsocketServerCommand extends Command
{
    //Commande à exécuter pour lancer le serveur
    protected static $defaultName = "run:websocket-server";

    private $messageHandler;

    public function __construct(MessageHandler $msgHand){
        parent::__construct();
        $this->messageHandler = $msgHand;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Port d'exécution du serveur
        $port = 9930;
        $output->writeln("[ - CUSTOM PHP WEBSOCKET SERVER - ] \t \t Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->messageHandler
                )
            ),
            $port
        );
        $server->run();

        //Appel de la commande async
        $command = $this->getApplication()->find('messenger:consume');

        $arguments = [
            'receivers'    => ['async']
        ];

        $command->run(new ArrayInput($arguments), $output);

        return 0;
    }
}