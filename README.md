# JDZ AdminKit

Framework-agnostic primitives for building admin list/edit screens. No HTTP,
session, view or ORM coupling — you feed each helper plain values from whatever
framework you run (Slim, Symfony, …) and get back a computed result.

Built on [`jdz/database`](https://jdz.joffreydemetz.com/database) (query building)
and [`jdz/form`](https://jdz.joffreydemetz.com/form) (form data).

## Installation

```bash
composer require jdz/adminkit
```

## Requirements

- PHP >= 8.2
- [`jdz/database`](https://jdz.joffreydemetz.com/database) ^2.1
- [`jdz/form`](https://jdz.joffreydemetz.com/form) ^1.0

## What's in the box

| Class | Purpose |
|---|---|
| `List\Paginator` | Clamp a requested page against a total and derive the SQL offset |
| `List\FilterStateResolver` | Merge defaults < stored < request < forced filter state; inject token/page/ordering |
| `List\OrderingValidator` | Decide whether drag-and-drop reordering is allowed for the current list |
| `List\SearchTerm` | Split a `CODE:text` search string |
| `List\FilterFormScaffold` | Build the standard filterbar on a `jdz/form` form: hidden carriers, `searchbox` / `sorting` / `filters` fieldsets; labels through an injected `fn(string $key): string` translator |
| `Query\AdminQuery` | `SelectQuery` with category / version / keyword select+filter helpers |
| `Item\UniquenessChecker` | "is this column value already taken" against a `DatabaseInterface` |
| `Item\DataSanitizer` | Coerce posted form values to match their column definitions before save |

## Example

```php
use JDZ\AdminKit\List\Paginator;

$p = new Paginator(total: 128, page: 3, limit: 20);
$p->start;    // 40   (SQL offset)
$p->nbPages;  // 7
$p->page;     // 3    (clamped if out of range)
```

## Scope

Orchestration only — data access, HTTP, view rendering and the admin UI value
objects ([`jdz/adminui`](https://jdz.joffreydemetz.com/adminui)) stay in the
consuming framework. Row assembly and view-payload building are intentionally
NOT part of this package: they are tightly coupled to per-app rendering hooks
and belong with the consumer.

## Tests

```
composer test
```

## Changelog

- **1.1.0** — `List\FilterFormScaffold` (standard admin filterbar builder).
- **1.0.0** — Initial release.

## License

MIT — see [LICENSE](LICENSE).
