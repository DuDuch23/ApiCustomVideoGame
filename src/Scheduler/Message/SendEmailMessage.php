<?php
namespace App\Scheduler\Message;

use App\Scheduler\Message;
use App\User;

class SendEmailMessage
{
    public function __construct(
        private int $id,
        private string $recipientEmail
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }
}