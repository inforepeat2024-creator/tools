<?php

namespace RepeatToolkit\Support\EmailSender;

interface EmailSenderInterface
{
    public function send(AbstractEmailMessage $message): void;
}