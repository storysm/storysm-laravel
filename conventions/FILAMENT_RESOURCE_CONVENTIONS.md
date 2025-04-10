# FILAMENT RESOURCE CONVENTIONS

These conventions aim to improve code clarity and static analysis using PHPStan.

## Model Labels and Plural Model Labels

-   Each Filament Resource should override the `getModelLabel()` and `getPluralModelLabel()` methods.
-   These methods should retrieve the labels from the corresponding language file with this format `lang/{language-code}/{lowercased-singular-model-name}.php` (e.g., `lang/en/page.php`).
-   The key in the language file should be `'{lowercased-singular-model-name}.resource.model_label'` (e.g., `'page.resource.model_label'`).
-   The value should be formatted as `singular|plural` (e.g., `'Page|Pages'`).
-   If the language does not support pluralization, repeat the value (e.g., `'Halaman|Halaman'`).
-   Use `trans_choice('page.resource.model_label', 1)` for the singular form.
-   Use `trans_choice('page.resource.model_label', 2)` for the plural form.
