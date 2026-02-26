# Changelog

All notable changes to `laravel-og-image` will be documented in this file.

## 1.1.2 - 2026-02-26

### What's Changed

* Fix middleware destroying ->original by preserving it across setContent() calls by @mattiasgeniar in https://github.com/spatie/laravel-og-image/pull/2

### New Contributors

* @mattiasgeniar made their first contribution in https://github.com/spatie/laravel-og-image/pull/2

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/1.1.1...1.1.2

## 1.1.1 - 2026-02-24

### What's Changed

* Fix fallback cache poisoning by non-HTML responses

When a non-HTML response (such as an RSS/Atom feed) was processed by the middleware with a fallback registered, `storeInCache()` was called before verifying the template was actually injected. Since non-HTML responses lack a `</body>` tag, the injection silently failed but the cache entry persisted with the wrong URL. Because all fallback pages share the same content hash, this poisoned the cache permanently.

The fix moves `storeInCache()` to after the injection attempt and only executes it when the template was successfully injected.

## 1.1.0 - 2026-02-24

- Add Laravel Boost skill

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/1.0.0...1.1.0

## 1.0.0 - 2026-02-24

- Stable release
- Fixed incorrect method names in documentation
- Fixed overridable method listings in customizing actions documentation
- Added middleware auto-registration note to installation docs

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/0.0.3...1.0.0

## 0.0.3 - 2026-02-23

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/0.0.2...0.0.3

## 0.0.2 - 2026-02-22

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/0.0.1...0.0.2

## 0.0.1 - 2026-02-19

**Full Changelog**: https://github.com/spatie/laravel-og-image/commits/0.0.1
