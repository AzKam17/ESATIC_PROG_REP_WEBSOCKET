<?php

namespace App\Message;

use App\Entity\Message;
use App\Entity\Room;

class SaveMessage
{
    private $room;
    private $message;

    /**
     * @param $room
     * @param $message
     */
    public function __construct($room, $message)
    {
        $this->room = $room;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getRoom():Room
    {
        return $this->room;
    }

    /**
     * @param mixed $room
     */
    public function setRoom($room): void
    {
        $this->room = $room;
    }

    /**
     * @return mixed
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }


}