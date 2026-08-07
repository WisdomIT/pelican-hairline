# Hairline

A shadowless, hairline-bordered, high-density theme for **Pelican Panel**.

Hairline removes every drop shadow and separates surfaces with 1px borders instead,
then tightens spacing across the panel. Card grids that show a handful of values become
compact tables. The result is closer to a network-appliance console than to a
consumer dashboard: more information per screen, less chrome.

---

## What is Pelican Panel?

[Pelican](https://pelican.dev) is a free, open-source **game server control panel**.
It gives you a web interface to create and manage game servers — Minecraft, Terraria,
Palworld, Valheim, Enshrouded, Factorio and many more — with each server running in its
own isolated Docker container.

**Plugins** extend the panel without modifying any of its core files, and the
**[Pelican Hub](https://hub.pelican.dev/plugins)** is the official marketplace where you
find and install them. A *theme* is a plugin whose job is purely how the panel looks.

If you don't run a Pelican Panel, this repository won't be useful to you on its own.

---

## What it changes

| Area | Before | After |
|---|---|---|
| Type | 14px, no Hangul webfont | 13px base, Pretendard Variable (covers Latin + Hangul) |
| Surfaces | shadows, 12px radius | no shadows, 1px hairline borders, 6px radius |
| Sidebar | gray, roomy items | white with a border, compact items and group headers |
| Table rows | ~56px tall | ~35px, with icon sizes unified |
| Stat widgets | large number cards | label-above / value-below cells in a bordered table |
| Console header | six stat cards | one property table (name, status, address, CPU, memory, disk) |
| Command input | page background | dark, matching the terminal |
| Terminal | browser default monospace | resolves the panel's `--font-mono` |
| Startup / Settings | grids of cards | label-left / value-right rows |
| Health page | two-column cards | one hairline list |

Light and dark modes are both styled; light is the primary target.

## Install

**From the Hub** — find *Hairline* in the plugin list and install it.

**Manually** — download this repository, then either:

- use the **Import** button on the panel's plugin list to upload the folder as a zip,
  then press **Install**; or
- copy the folder into `plugins/` inside your panel directory
  (`/var/www/pelican/plugins` by default) and run:

  ```bash
  php artisan p:plugin:install
  ```

The folder name must stay `hairline-theme` — Pelican requires it to match the `id` in
`plugin.json`.

> Plugin installs run in the background, so give it a few seconds. If nothing happens,
> check that your queue worker is running.

## Requirements

- Pelican Panel `v1.0.0-beta35` or newer
- No build step, no Node.js — the theme ships plain CSS

## Uninstall

Disable or remove the plugin. The panel returns to its stock appearance immediately —
Hairline never modifies core files.

## Good to know

- **Webfont.** The theme loads *Pretendard Variable* from jsDelivr. The stock panel font
  has no Hangul glyphs, so Korean text falls back to a system font and looks unrelated to
  the Latin text. If your panel must not reach external CDNs, remove the `@import` at the
  top of `resources/theme.css`; everything else still works.
- **Charts.** The CPU / memory / network charts on the console page keep their stock
  appearance. Chart.js is bundled inside an Alpine component with no global handle, so a
  theme cannot reach its options.
- **Terminal font.** The theme sets the console terminal font from the panel's
  `--font-mono`. This takes precedence over the per-user *Console Font* profile setting.
- **Three core views are replaced** (both server-card views and the health page) because
  their layout cannot be reached from CSS alone. A major panel upgrade may need these
  re-checked against the originals.

## Contributing

Notes on how the theme is put together — and the measured pitfalls behind several of its
rules — are in [CONTRIBUTING.md](CONTRIBUTING.md).

## License

[MIT](LICENSE)
