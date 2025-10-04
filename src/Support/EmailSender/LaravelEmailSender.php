<?php

namespace RepeatToolkit\Support\EmailSender;



use Illuminate\Support\Facades\Mail;

final class LaravelEmailSender implements EmailSenderInterface
{
    public function send(AbstractEmailMessage $message): void
    {
        $message->validate();

        // Izvlači default "from" iz config/mail.php
        $defaultFrom = config('mail.from');
        $defaultEmail = Arr::get($defaultFrom, 'address');
        $defaultName  = Arr::get($defaultFrom, 'name');

        Mail::send([], [], function (\Illuminate\Mail\Message $m) use ($message, $defaultEmail, $defaultName) {

            // From — koristi prioritetno iz poruke, pa fallback na config
            $from = $message->getFrom();
            if ($from) {
                $m->from($from->email, $from->name);
            } elseif ($defaultEmail) {
                $m->from($defaultEmail, $defaultName);
            }

            // To / CC / BCC
            foreach ($message->getTo() as $addr)  { $m->to($addr->email,  $addr->name); }
            foreach ($message->getCc() as $addr)  { $m->cc($addr->email,  $addr->name); }
            foreach ($message->getBcc() as $addr) { $m->bcc($addr->email, $addr->name); }

            // Reply-To
            if ($rt = $message->getReplyTo()) {
                $m->replyTo($rt->email, $rt->name);
            }

            // Subject
            $m->subject($message->getSubject());

            // Body (HTML + text fallback)
            if ($html = $message->getHtml()) {
                $m->setBody($html, 'text/html');
            }
            if ($text = $message->getText()) {
                $m->addPart($text, 'text/plain');
            }

            // Attachments
            foreach ($message->getAttachments() as $att) {
                if ($att->path) {
                    $m->attach($att->path, array_filter([
                        'as'   => $att->name,
                        'mime' => $att->mime,
                    ]));
                } elseif ($att->data) {
                    $m->attachData($att->data, $att->name ?? 'attachment', array_filter([
                        'mime' => $att->mime,
                    ]));
                }
            }
        });
    }
}
