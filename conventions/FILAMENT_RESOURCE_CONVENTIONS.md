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

## Permission and Policy

-   If the model associated with a Filament Resource has a `creator_id` column, the Resource should define a `canViewAll` static method.
-   If the model associated with a Filament Resource has a `creator_id` column, the Resource must use the following `getEloquentQuery` method:

        ```php
        use Illuminate\Database\Eloquent\Builder;

        /**
         * @return Builder<{Model}>
         */
        public static function getEloquentQuery(): Builder
        {
            $query = parent::getEloquentQuery();

            if (! static::canViewAll()) {
                $query->where('creator_id', Filament::auth()->id());
            }

            return $query;
        }
        ```
        Where `{Model}` is the model type associated with the Filament Resource (e.g., `Page`).

-   The implementation of `canViewAll` should simply call the `can` method with the `viewAll` ability: `return static::can('viewAll');`.
-   If the model _does not_ have a `creator_id` column, the `canViewAll` method _should not_ be defined.
-   Every Filament Resource should implement the `HasShieldPermissions` interface from `\BezhanSalleh\FilamentShield\Contracts` then implement the `getPermissionPrefixes` method like this:

    ```php
    /**
     * @return string[]
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            {permissions}
        ];
    }
    ```

    Where `{permissions}` is the permissions associated with the Filament Resource (e.g., `view_all`).

## Imports

-   **Top-Level Imports:** Use top-level imports for classes and interfaces.
