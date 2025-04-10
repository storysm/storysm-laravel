# MODEL CONVENTIONS

These conventions aim to improve code clarity and static analysis using PHPStan.

## Ulids

All models should use the `HasUlids` trait. The `ulid` column is a string and should not be nullable. For example:

```php
use App\Concerns\HasUlids;
class Page extends Model {
    use HasUlids;
```

## Docblock Annotations

-   **Model Properties:** Use `@property` to define the model's database columns. Include the data type and nullability (if applicable). Place these annotations above the class definition.
-   **Nullability:** Ensure that the nullability of properties is correctly reflected in the docblocks.
-   **Relationships:** Use `@property-read` for relationships. Specify the related model class. For example:

```php
/**
 * @property string $title
 * @property ?string $description
 */
```

## Factories

-   **Factory Usage:** Use the `HasFactory` trait.
-   **Factory Docblock:** Add a `/** @use HasFactory<\Database\Factories\{ModelName}Factory> */` docblock _before_ the `use HasFactory;` statement. This helps PHPStan understand the factory relationship.

    ```php
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory;
    ```

    Replace `PageFactory` with the actual name of your model's factory.

## Translatable Attributes

-   **`$translatable` Property:** When a model supports translation, the `$translatable` property must be declared as a public array, annotated with a `@var array<int, string>` docblock.

    ```php
    /** @var array<int, string> */
    public $translatable = ['title', 'description'];
    ```

## Mass Assignment

-   **`$guarded` Property:** To allow mass assignment, the `$guarded` property should be defined as a protected array. If you want to allow all fields to be mass-assigned, set it to an empty array.

    ```php
    /** @var array<int, string> */
    protected $guarded = [];
    ```

## Relationship Return Types

-   **Explicit Return Types:** Use explicit return types for relationship methods (e.g., `BelongsTo`, `HasOne`).
-   **Relationship Docblocks:** Add a `@return` docblock to relationship methods, specifying the related model and the current model. Also, add a return type hint to the function signature itself.

    ```php
    /**
     * @return BelongsTo<{RelatedModel}, $this>
     */
    public function relatedModel(): BelongsTo
    {
        return $this->belongsTo({RelatedModel}::class);
    }

    ```

    Replace `{RelatedModel}`with the related model class (e.g., `Page::class`) and `relatedModel` with the camelCase name of the related model (e.g., `page`). The first type parameter is the related model and the second is the current model.

-   **Attributes:** Prioritize `App\Concerns\Has{RelatedModel}Attribute` trait when you need to define custom accessors or mutators for relationship attributes. Replace {RelatedModel} with the singular, PascalCase name of the related model.

## Imports

-   **Top-Level Imports:** Use top-level imports for classes.
