# AGENTS.md

WordPress plugin generating [ThumbHash](https://evanw.github.io/thumbhash/) placeholders for images. PHP >= 8.2, pnpm, [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) for local dev, Pest + Playwright for tests, changesets for releases.

## Local development

Local URL: derive dynamically via `pnpm launch` / `wp-env status --json`

```bash
pnpm run env start     # dev env (port 9783); `pnpm env` is a pnpm builtin
pnpm env:test start    # test env (port 9784)
pnpm wp <command>      # WP-CLI inside the dev env
```

## Key commands

```bash
pnpm format:php       # Pint
pnpm format:assets    # Prettier
pnpm analyse          # PHPStan
pnpm validate:php     # required PHP version declared consistently
pnpm test:pest        # Pest tests inside the test env
pnpm test:e2e         # Playwright e2e tests (`test:e2e:dev` for UI, `test:e2e:install`)
pnpm test             # Pest + reset test env + e2e
```

## Code style

- derive from the existing code
- prefix getters with `get`; methods that request data from a remote API may use `fetch`
- names must reflect what code does; if a change alters a class/function's behavior, check its name still fits; propose renames that touch call sites
- use domain terms from `CONTEXT.md` for names, if present
- don't rename untouched code
- keep comments as short as possible
- do not add comments if not really required
- only use languages I know. No python please
- Use `/** */` block comments (not `//`) for anything that's not an "end of the line" comment
- docblocks state _what_ a thing does, never _why_ it does it or why it was built that way (exception: Inline docs like e.g. "using the raw value because..."); no {@see} cross-references at all; skip the docblock entirely if the name is self-explanatory; hard ceiling of 3 lines, shorter preferred.
- Exception: operational knowledge that would cause a bug if lost (a WP-core quirk, a library gotcha, "deliberately absent" behavior) — relocate it to a new ADR under `docs/adr/` (only if definitely important, max terse like inline comments), then trim the code docblock to the rule above.

## Before making changes

- if the change is big, always first propose what you are planning, even if not in plan mode

## After making changes

- Keep inline code comments as short as possible. Be extremely concise and sacrifice grammar for the sake of concision
- Close GitHub issues that are solved by the current edit (see #issue-tracker)

### PHP

- Must stay compatible with the minimum PHP version in `composer.json` (CI tests 8.2 and 8.3)
- Classes in `CamelCase`, methods in `camelCase`
- After making changes, run `pnpm analyse` and `pnpm test:pest`
- Add new tests when it makes sense. Don't overdo it! Pest tests in `tests/Unit/`, e2e in `tests/E2E/tests/`
- Keep new code DRY! Propose deduplicating existing code. Ask if unsure.
- Prefer fewer, bigger classes: bundle related functionality in one class/file instead of many small ones. A class doing several related, contained jobs is fine
- Prefer WordPress-native functionality over reimplementing it (e.g. `list_files()` over `glob()`).

## Do not touch without explicit request

- Release tooling (`pnpm version`, `.changeset/`, `.github/workflows/`, `config/cli/` dist commands)
- No commits without explicit request
- do not edit TODO files

## Commits and pull requests

- Keep PR descriptions as brief as possible. The title carries the meaning — most PRs
  here have an empty body or a few bullets at most. No headings, tables or walkthroughs.
- Never mention the agent, assistant or tool that wrote the code, in commit messages or
  PR descriptions. No `Co-Authored-By` trailers, no "Generated with ..." footers.

## Project structure

| Path | Purpose |
|------|---------|
| `wp-thumbhash.php` | Plugin bootstrap |
| `src/` | PHP classes (`Hirasso\WPThumbhash\` namespace) |
| `src/CLI/` | WP-CLI `thumbhash` command |
| `src/Enums/` | Enums |
| `assets/` | Admin JS/CSS, thumbhash custom element |
| `config/` | php-scoper, CLI helpers (`config/cli/cli.js`), stubs |
| `docs/` | Installation and usage docs |
| `tests/Unit/` | Pest tests (run in wp-env test env) |
| `tests/E2E/` | Playwright e2e tests + helper plugin |
| `tests/__fixtures__/` | Test images |

## Agent skills

### Issue tracker

Issues and PRDs live as GitHub issues (`gh issue …`).

### Triage labels

Canonical vocabulary (`needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`), as GitHub labels.

### Domain docs

Single-context: `CONTEXT.md` + `docs/adr/` at the repo root.
