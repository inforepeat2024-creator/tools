<?php

namespace RepeatToolkit\Support\EmailSender;

final class EmailAddress
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $name = null
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email: {$email}");
        }
    }
}