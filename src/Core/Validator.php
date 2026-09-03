<?php
declare(strict_types=1);

namespace Gfc\Core;

final class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function required(string $field, string $label): self
    {
        if (trim((string) ($this->data[$field] ?? '')) === '') {
            $this->errors[$field] = $label . ' est obligatoire.';
        }
        return $this;
    }

    public function phone(string $field, string $label): self
    {
        $v = (string) ($this->data[$field] ?? '');
        if ($v !== '' && !preg_match('/^\+?[0-9 ]{8,20}$/', $v)) {
            $this->errors[$field] = $label . ' n\'est pas un numéro valide.';
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        $v = $this->data[$field] ?? null;
        if ($v !== null && $v !== '' && !in_array($v, $allowed, true)) {
            $this->errors[$field] = $label . ' est invalide.';
        }
        return $this;
    }

    public function between(string $field, int $min, int $max, string $label): self
    {
        $v = $this->data[$field] ?? null;
        if ($v !== null && $v !== '' && ((int) $v < $min || (int) $v > $max)) {
            $this->errors[$field] = sprintf('%s doit être entre %d et %d.', $label, $min, $max);
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
