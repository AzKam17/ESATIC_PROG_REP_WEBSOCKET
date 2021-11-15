<?php

namespace App\WebSocket;

use App\Entity\Message;
use App\Entity\Room;
use App\Entity\RoomsManager;
use App\Message\SaveMessage;
use App\Repository\MessageRepository;
use App\Repository\RoomRepository;
use App\Repository\RoomsManagerRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MessageHandler implements MessageComponentInterface
{
    //Objet utilisé comme tableau pout toutes les connections
    private $connections;

    //Room manager
    private $roomManager;

    private $encoders;
    private $normalizers;
    private $serializer;
    private $em;
    private $roomRepo;
    private $msgRepo;
    private $bus;

    public function __construct(EntityManagerInterface $manager, RoomRepository $roomRepository, MessageRepository $msgRepo, MessageBusInterface $bus)
    {
        //Entity Manager pour le stockage des informations
        $this->em = $manager;

        $this->bus = $bus;

        //SplObjectStorage est pareil que pour un tableau mais il s'agit d'une collection d'objets
        $this->connections = new \SplObjectStorage();

        //On crée de nouvelles rooms à chaque redémarrage du serveur
        $this->roomManager = (new RoomsManager())
            ->addRoom((new Room())->setLib('Les zinzins'))
            ->addRoom((new Room())->setLib('Les Magiciens '));
        $this->em->persist($this->roomManager);
        $this->em->flush();

        $this->roomRepo = $roomRepository;
        $this->msgRepo = $msgRepo;

        $this->encoders = [new JsonEncoder()];
        $this->normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);

    }

    //Fonction appelée lors d'une nouvelle connexion
    function onOpen(ConnectionInterface $conn)
    {
        //Une nouvelle connexion est ajoutée à la collection de connexion
        $this->connections->attach($conn);
    }

    //Fonction appelée lors de la fermetture d'une connexion
    function onClose(ConnectionInterface $conn)
    {
        //Une connexion fermée est détachée de la collection de messages
        $this->connections->detach($conn);
    }

    //Fonction exécutée à la survenue d'une erreur
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        //Lors de la survenue d'une erreur, on déconnecte la connexion responsable
        $this->connections->detach($conn);
        $conn->close();
    }

    //Fonction appelée à la réception d'un message
    function onMessage(ConnectionInterface $from, $msg)
    {
        $toSend = "default";
        try{
            /* ---- PHASE D'ANALYSE DU MESSAGE ---- */
            //Mesasge reçu du client
            $msg1 = new Message();
            //Désérialisation du message
            $this->serializer->deserialize($msg, Message::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $msg1]);

            /* ---- PHASE DE TRAITEMENT DU MESSAGE ---- */
            $type = $msg1->getType() ?? "";
            if($type === 'init'){
                //Le client vient de se connecter, on lui envoie la liste des groupes
                $toSend = [];
                foreach ($this->roomManager->getRooms() as $r){
                    $toSend[] = [
                        'id' => $r->getId(),
                        'lib' => $r->getLib(),
                        'type' => 'init'
                    ];
                }
                $toSend = json_encode($toSend);
                $from->send($toSend);
            }else if($type === 'msg'){
                //On recherche la salle à laquelle appartient le message
                foreach ($this->roomManager->getRooms() as $r){
                    //Si la room est trouvée on enregistre dans la base de données
                    if($r->getId() === intval($msg1->getCode())){
                        //$this->bus->dispatch(new SaveMessage($r, $msg1));
                        $msg1->setCode(null);
                        $msg1->setRoom($r);
                        $this->em->persist($msg1);
                        $this->em->flush();
                    }
                }

                //On envoie le message à tout le monde
                foreach($this->connections as $connection)
                {
                    $connection->send(
                        json_encode(
                            [
                                [
                                    'id' => $msg1->getId(),
                                    'room' => $msg1->getRoom()->getId(),
                                    'sender' => $msg1->getSender(),
                                    'content' => $msg1->getContent(),
                                    'type' => 'msg',
                                    'code' => implode("", explode(
                                        " ",
                                        strtolower($msg1->getRoom()->getLib()
                                        )
                                    )).'#'.$msg1->getRoom()->getId()
                                ]
                            ]
                        )
                    );
                }

            }else if($type === "getRoomMessages"){
                $toSend = [];
                $rooms = $this->msgRepo->findMessagesFromRoomId($msg1->getContent());
                if($rooms ?? false){
                    foreach ($rooms as $message){
                        $toSend[] = [
                            'id' => $message->getId(),
                            'room' => $message->getRoom()->getId(),
                            'sender' => $message->getSender(),
                            'content' => $message->getContent(),
                            'type' => $message->getType(),
                            'code' => implode("", explode(
                                    " ",
                                    strtolower($message->getRoom()->getLib()
                                    )
                                )).'#'.$message->getRoom()->getId()
                        ];
                    }
                }
                $from->send(json_encode($toSend));
            }else if($type === 'canvas'){
                //On recherche la salle à laquelle appartient le message
                foreach ($this->roomManager->getRooms() as $r){
                    //Si la room est trouvée on enregistre dans la base de données
                    if($r->getId() === intval($msg1->getCode())){
                        //$this->bus->dispatch(new SaveMessage($r, $msg1));
                        $msg1->setCode(null);
                        $msg1->setRoom($r);
                        $this->em->persist($msg1);
                        $this->em->flush();
                    }
                }

                //On envoie le message à tout le monde
                foreach($this->connections as $connection)
                {
                    if($connection === $from){
                        continue;
                    }
                    $connection->send(
                        json_encode(
                            [
                                [
                                    'id' => $msg1->getId(),
                                    'room' => $msg1->getRoom()->getId(),
                                    'sender' => $msg1->getSender(),
                                    'content' => $msg1->getContent(),
                                    'type' => 'canvas',
                                    'code' => implode("", explode(
                                            " ",
                                            strtolower($msg1->getRoom()->getLib()
                                            )
                                        )).'#'.$msg1->getRoom()->getId()
                                ]
                            ]
                        )
                    );
                }
            }


            //$typeMsg = $msg1->getType() ?? "";
            /*$toSend = $this->serializer->serialize(
                $this->roomManager,
                'json',
                [
                    'groups' => 'roomsInit'
                ]
            );*/
            //$toSend = $this->serializer->normalize($this->roomManager, null, [AbstractNormalizer::ATTRIBUTES => ['id']]);
            //$from->send(json_encode($toSend));
            //$from->send("qsfqsfqsf");

            //Un message reçu est envoyé à toutes les connexions à l'exception de l'expéditeur
             /*foreach($this->connections as $connection)
            {

                if($connection === $from)
                {
                    continue;
                }
                try{

                    $msg1
                        ->setSender("AzKam");
                }catch (\Exception $e){
                    $e->getMessage();
                }
            } */
        }catch(\Exception $e){
            $from->send($e->getMessage().' '.$e->getTraceAsString());
        }
    }
}