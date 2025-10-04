<?php

namespace RepeatToolkit\Support\EmailSender;



abstract class AbstractEmailMessage
{
    protected ?EmailAddress $from = null;
    /** @var EmailAddress[] */
    protected array $to = [];
    /** @var EmailAddress[] */
    protected array $cc = [];
    /** @var EmailAddress[] */
    protected array $bcc = [];
    protected ?EmailAddress $replyTo = null;

    protected string $subject = '';
    protected ?string $html = null;
    protected ?string $text = null;

    /** @var array<string,string> */
    protected array $headers = [];

    /** @var Attachment[] */
    protected array $attachments = [];

    // --- Fluent setters ---
    public function from(EmailAddress $from): static { $this->from = $from; return $this; }
    public function replyTo(EmailAddress $addr): static { $this->replyTo = $addr; return $this; }

    /** @param EmailAddress[] $list */
    public function to(array $list): static { $this->to = $list; return $this; }
    public function addTo(EmailAddress $addr): static { $this->to[] = $addr; return $this; }

    /** @param EmailAddress[] $list */
    public function cc(array $list): static { $this->cc = $list; return $this; }
    public function addCc(EmailAddress $addr): static { $this->cc[] = $addr; return $this; }

    /** @param EmailAddress[] $list */
    public function bcc(array $list): static { $this->bcc = $list; return $this; }
    public function addBcc(EmailAddress $addr): static { $this->bcc[] = $addr; return $this; }

    public function subject(string $subject): static { $this->subject = $subject; return $this; }
    public function html(?string $html): static { $this->html = $html; return $this; }
    public function text(?string $text): static { $this->text = $text; return $this; }

    /** @param array<string,string> $headers */
    public function headers(array $headers): static { $this->headers = $headers; return $this; }
    public function addHeader(string $name, string $value): static { $this->headers[$name] = $value; return $this; }

    /** @param Attachment[] $attachments */
    public function attachments(array $attachments): static { $this->attachments = $attachments; return $this; }
    public function addAttachment(Attachment $attachment): static { $this->attachments[] = $attachment; return $this; }

    // --- Getters (sender ih koristi) ---
    public function getFrom(): ?EmailAddress { return $this->from; }
    /** @return EmailAddress[] */ public function getTo(): array { return $this->to; }
    /** @return EmailAddress[] */ public function getCc(): array { return $this->cc; }
    /** @return EmailAddress[] */ public function getBcc(): array { return $this->bcc; }
    public function getReplyTo(): ?EmailAddress { return $this->replyTo; }

    public function getSubject(): string { return $this->subject; }
    public function getHtml(): ?string { return $this->html; }
    public function getText(): ?string { return $this->text; }

    /** @return array<string,string> */ public function getHeaders(): array { return $this->headers; }
    /** @return Attachment[] */ public function getAttachments(): array { return $this->attachments; }

    // Minimalna validacija – po potrebi proširi
    public function validate(): void
    {
        if (empty($this->to)) {
            throw new \LogicException('Email must have at least one "to" recipient.');
        }
        if ($this->subject === '') {
            throw new \LogicException('Email subject is required.');
        }
        if ($this->html === null && $this->text === null) {
            throw new \LogicException('Provide at least HTML or plain-text content.');
        }
    }
}
