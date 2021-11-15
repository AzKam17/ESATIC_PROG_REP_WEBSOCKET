<?php

namespace App\MessageHandler;

use App\Message\SaveMessage;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SaveMessageHandler implements MessageHandlerInterface
{
    private $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(SaveMessage $message){
        $room = $message->getRoom();
        $msg = $message->getMessage();

        $msg->setCode(null)->setRoom($room);

        $this->em->persist($msg);
        $this->em->flush();
    }
}