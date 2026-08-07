# Contributing

## How the theme is put together

Four layers, in order of preference — reach for a lower one only when the one above
genuinely cannot do the job.

| Layer | Mechanism | Handles |
|---|---|---|
| Palette | `FilamentColor::register` (re-registration) | primary, gray scale, status colors |
| CSS | render hook `STYLES_AFTER` injects `<style>` | radius, borders, sidebar, typography, density |
| JS | render hook `SCRIPTS_BEFORE` injects `<script>` | console terminal font (an xterm option) |
| View override | `View::prependLocation(resources/views-override)` | structure CSS cannot reach |

Class prefix is `hl-`, the view namespace is `hairline::`, and injected tags carry a
`data-hairline` attribute so you can find them in devtools.

### Overridden core views

| View | Why |
|---|---|
| `livewire/server-entry` | server card — remove the vertical status bar, use a text badge, collapse nested padding |
| `livewire/server-entry-placeholder` | loading card — **if you don't override it too, the layout jumps when loading finishes** |
| `filament/pages/health` | health page — written with Tailwind utilities rather than `fi-*`, so CSS cannot be scoped to it |

These are copies of core views. Diff them against the originals after a panel upgrade.

## Verify against real markup

The single most useful habit here: **never write a selector without checking that the
class exists.** Filament's class names are not always what they look like, and several
rules in this theme were dead for a while because of a guess.

Rendering a page server-side is enough for most checks:

```php
// php artisan tinker
$u = App\Models\User::first();
Illuminate\Support\Facades\Auth::onceUsingId($u->id);
$resp = app(Illuminate\Contracts\Http\Kernel::class)
    ->handle(Illuminate\Http\Request::create('https://your-panel/admin/health', 'GET'));
file_put_contents('/tmp/page.html', $resp->getContent());
```

For a Livewire table, note that many are **deferred** (`deferLoading()`): the markup is
empty until you set `isTableLoaded`.

```php
Livewire\Livewire::test(ListFiles::class)->set('isTableLoaded', true)->html();
```

The compiled Filament CSS at `public/css/filament/filament/app.css` is the other half of
the story — it tells you which rule you are actually fighting, and at what specificity.

## Measured pitfalls

Each of these cost real debugging time.

- 🔴 **Plugin providers boot *before* core.** Registering colors directly in `boot()` gets
  overwritten by `FilamentServiceProvider`. Defer with `$this->app->booted()`.
- 🔴 **`class` is required in `plugin.json`** (the Filament Plugin contract). Without it the
  plugin errors with `Undefined array key "class"`. There is no `providers` key —
  `src/Providers/` is auto-discovered. `id` must equal the root folder name.
- 🔴 **`panel_version` needs a caret.** Without `^` it is an exact match and the plugin
  goes incompatible on the next panel release.
- 🔴 **Filament draws many borders as a `ring`, which is a `box-shadow`.** Setting
  `box-shadow: none` to kill a drop shadow also deletes the border
  (`.fi-btn`, `.fi-fo-repeater-item`). Put a real `border` back.
- 🔴 **Custom properties set in an inline `style` attribute always beat a stylesheet.**
  Grid column counts arrive as `--cols-lg` inline, so overriding that variable does
  nothing — override the computed `grid-template-columns` instead. And clear
  `grid-column` too, or a leftover `span 5` will create implicit columns.
- 🔴 **Widget width comes from `<x-filament-widgets::widget>`, not CSS.** That component
  moves `getColumnSpan()` onto `grid-column`. A CSS workaround keyed on `wire:key` fails —
  the attribute disappears after hydration.
- 🔴 **Do not subclass a core chart widget and mount it beside the original.** Hiding the
  core one with CSS and registering the subclass broke the entire console page on the
  client (server render was fine). Chart styling is left unsolved for this reason.
- **`.fi-ta-cell` has `padding: 0`.** Row height lives on the inner `.fi-ta-text`,
  the cells that hold actions or checkboxes, and the icon-button box — reducing one of
  them changes nothing visible.
- **`.fi-icon-btn` carries `margin: -8px`** to enlarge its hit area. Give it a border and
  buttons overlap; give the row a `gap` and the negative margin cancels it exactly.
- **A hidden label still renders**, as `label.fi-sr-only`. It is `position: absolute`, so
  it occupies no grid cell — a two-column field layout then puts the *value* in column one.
- **Colors are registered as hex but exposed as `oklch`.** Keep that in mind when
  verifying a palette.
- **Widget order comes from `ConsoleWidgetPosition`.** `AboveConsole` shares a slot with
  other plugins and loses to boot order; use `Top` to be first.

## Scope rule

A selector should be as narrow as the problem. Two failures from opposite directions,
both in this codebase:

- a fix scoped to `.fi-fieldset` did not reach the same widget outside a fieldset;
- a form-table rule applied to "all forms" broke a screen that looked like a form but was
  a list of button + description pairs.

When a change produces several unrelated-looking symptoms at once, suspect one
over-broad rule rather than fixing each symptom.
