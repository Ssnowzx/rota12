<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Input Validator
 *
 * Fluent, chainable validation for request data.
 *
 * Usage:
 *   $v = (new Validator($_POST))
 *       ->required('title')
 *       ->maxLength('title', 190)
 *       ->email('email')
 *       ->unique('email', 'admin_users', 'email', $existingId);
 *
 *   if ($v->passes()) { … } else { $errors = $v->errors(); }
 */
final class Validator
{
    /** @var array<string, mixed> Input data being validated. */
    private array $data;

    /** @var array<string, string[]> Accumulated error messages keyed by field name. */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data The input data to validate (e.g. $_POST).
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // -------------------------------------------------------------------------
    // Rules
    // -------------------------------------------------------------------------

    /**
     * The field must be present and non-empty (after trimming whitespace).
     */
    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = $this->value($field);

        if ($value === null || trim((string) $value) === '') {
            $this->addError($field, "{$label} is required.");
        }

        return $this;
    }

    /**
     * The field must be a valid e-mail address (when not empty).
     */
    public function email(string $field, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = trim((string) $this->value($field));

        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($field, "{$label} must be a valid email address.");
        }

        return $this;
    }

    /**
     * The numeric value of the field must be ≤ $max.
     */
    public function max(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = $this->value($field);

        if ($value !== null && trim((string) $value) !== '' && (float) $value > $max) {
            $this->addError($field, "{$label} must be at most {$max}.");
        }

        return $this;
    }

    /**
     * The numeric value of the field must be ≥ $min.
     */
    public function min(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = $this->value($field);

        if ($value !== null && trim((string) $value) !== '' && (float) $value < $min) {
            $this->addError($field, "{$label} must be at least {$min}.");
        }

        return $this;
    }

    /**
     * The string length must be ≤ $max characters (multibyte-safe).
     */
    public function maxLength(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = (string) $this->value($field);

        if (mb_strlen($value) > $max) {
            $this->addError($field, "{$label} must not exceed {$max} characters.");
        }

        return $this;
    }

    /**
     * The string length must be ≥ $min characters (multibyte-safe).
     */
    public function minLength(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = (string) $this->value($field);

        if (trim($value) !== '' && mb_strlen($value) < $min) {
            $this->addError($field, "{$label} must be at least {$min} characters.");
        }

        return $this;
    }

    /**
     * The field value must be numeric.
     */
    public function numeric(string $field, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = $this->value($field);

        if ($value !== null && trim((string) $value) !== '' && !is_numeric($value)) {
            $this->addError($field, "{$label} must be a number.");
        }

        return $this;
    }

    /**
     * The field value must be one of the allowed values.
     *
     * @param string[] $allowed
     */
    public function in(string $field, array $allowed, string $label = ''): static
    {
        $label = $label ?: $this->humanize($field);
        $value = $this->value($field);

        if ($value !== null && trim((string) $value) !== '' && !in_array($value, $allowed, true)) {
            $list = implode(', ', $allowed);
            $this->addError($field, "{$label} must be one of: {$list}.");
        }

        return $this;
    }

    /**
     * The field value must not already exist in $table.$column.
     *
     * Optionally pass $exceptId to ignore the row with that primary-key value
     * (useful for UPDATE operations where the existing record is allowed).
     *
     * @param string   $field     Input field name.
     * @param string   $table     Database table to query (identifier — not user-supplied).
     * @param string   $column    Column to check (identifier — not user-supplied).
     * @param int|null $exceptId  Row ID to exclude from the uniqueness check.
     * @param string   $label     Human-readable field label.
     */
    public function unique(
        string $field,
        string $table,
        string $column,
        ?int   $exceptId = null,
        string $label    = ''
    ): static {
        $label = $label ?: $this->humanize($field);
        $value = $this->value($field);

        if ($value === null || trim((string) $value) === '') {
            return $this;
        }

        if ($exceptId !== null) {
            $sql    = "SELECT COUNT(*) AS `cnt` FROM `{$table}` WHERE `{$column}` = ? AND `id` != ?";
            $params = [$value, $exceptId];
        } else {
            $sql    = "SELECT COUNT(*) AS `cnt` FROM `{$table}` WHERE `{$column}` = ?";
            $params = [$value];
        }

        $row = DB::selectOne($sql, $params);

        if ((int) ($row['cnt'] ?? 0) > 0) {
            $this->addError($field, "{$label} is already taken.");
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Result inspection
    // -------------------------------------------------------------------------

    /**
     * Return TRUE when no validation errors have been accumulated.
     */
    public function passes(): bool
    {
        return count($this->errors) === 0;
    }

    /**
     * Return TRUE when at least one validation error exists.
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Return all errors as an associative array of field => string[].
     *
     * @return array<string, string[]>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Return the first error message for a field, or NULL if none.
     *
     * @param string $field
     * @return string|null
     */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Return a flat list of all error messages across all fields.
     *
     * @return string[]
     */
    public function allErrors(): array
    {
        $all = [];
        foreach ($this->errors as $messages) {
            foreach ($messages as $message) {
                $all[] = $message;
            }
        }
        return $all;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Retrieve a raw value from the input data, or NULL if absent.
     *
     * @param string $field
     * @return mixed
     */
    private function value(string $field)
    {
        // Support dot-notation for nested arrays, e.g. 'address.city'.
        $keys  = explode('.', $field);
        $value = $this->data;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Append an error message for a field.
     *
     * @param string $field
     * @param string $message
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Convert a snake_case field name into a human-readable label.
     *
     * @param string $field
     * @return string
     */
    private function humanize(string $field): string
    {
        // Strip dot-notation path prefix.
        $parts = explode('.', $field);
        $name  = end($parts);
        return ucwords(str_replace(['_', '-'], ' ', $name));
    }
}
