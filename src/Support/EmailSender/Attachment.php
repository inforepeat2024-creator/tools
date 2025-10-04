<?php

namespace RepeatToolkit\Support\EmailSender;

final class Attachment
{
    // Either path OR raw data must be provided.
    public function __construct(
        public readonly ?string $path = null,
        public readonly ?string $data = null,     // binary or base64-decoded string
        public readonly ?string $name = null,     // suggested filename
        public readonly ?string $mime = null
    ) {
        if ($this->path === null && $this->data === null) {
            throw new \InvalidArgumentException('Attachment requires path or data.');
        }
    }
}