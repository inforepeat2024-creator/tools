<?php

namespace RepeatToolkit\Support\EmailSender;



use Illuminate\Support\Facades\Mail;

final class LaravelEmailSender implements EmailSenderInterface
{
    public function __construct(
        private readonly ?string $defaultFromEmail = null,
        private readonly ?string $defaultFromName = null,
    ) {}

    public function send(AbstractEmailMessage $message): void
    {
        $message->validate();

        Mail::send([], [], function (\Illuminate\Mail\Message $m) use ($message) {

            // From
            $from = $message->getFrom();
            if ($from) {
                $m->from($from->email, $from->name);
            } elseif ($this->defaultFromEmail) {
                $m->from($this->defaultFromEmail, $this->defaultFromName);
            }

            // To / Cc / Bcc
            foreach ($message->getTo() as $addr)  { $m->to($addr->email,  $addr->name); }
            foreach ($message->getCc() as $addr)  { $m->cc($addr->email,  $addr->name); }
            foreach ($message->getBcc() as $addr) { $m->bcc($addr->email, $addr->name); }

            // Reply-To
            if ($rt = $message->getReplyTo()) {
                $m->replyTo($rt->email, $rt->name);
            }

            // Subject
            $m->subject($message->getSubject());

            // Body (HTML + optional text part)
            if ($html = $message->getHtml()) {
                $m->setBody($html, 'text/html');
            }
            if ($text = $message->getText()) {
                $m->addPart($text, 'text/plain');
            }

            // Custom headers (ako ih driver podržava)
            foreach ($message->getHeaders() as $name => $value) {
                $m->getSwiftMessage()?->getHeaders()->addTextHeader($name, $value);
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
