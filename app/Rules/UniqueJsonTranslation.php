<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueJsonTranslation implements ValidationRule
{
    protected string $table;

    protected string $column;

    protected string $locale;

    protected ?string $ignoreId; // Changed to string for ULID support

    protected string $idColumn; // To specify the ID column name

    public function __construct(string $table, string $column, string $locale, ?string $ignoreId = null, string $idColumn = 'id')
    {
        $this->table = $table;
        $this->column = $column;
        $this->locale = $locale;
        $this->ignoreId = $ignoreId;
        $this->idColumn = $idColumn;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)
            ->where("{$this->column}->{$this->locale}", $value);

        if ($this->ignoreId) {
            $query->where($this->idColumn, '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail(__('rule.unique_json_translation'));
        }
    }
}
