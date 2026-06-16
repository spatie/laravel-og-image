# Changelog

All notable changes to `laravel-og-image` will be documented in this file.

## 1.3.1 - 2026-06-16

This release re-publishes the fixes from `1.1.3` and `1.1.4` under a version that correctly supersedes `1.3.0`. Those two tags were accidentally numbered below `1.3.0`, so Composer kept resolving the older `1.3.0` as the latest stable release. No code was lost: this tag includes the Laravel 13 support from `1.3.0` together with the query parameter handling fixes.

### What's Changed

* Ensure OG images for a page URL with existing query parameters is handled correctly by @beblife in https://github.com/spatie/laravel-og-image/pull/8
* Handle existing query parameters in generateForUrl by @freekmurze in https://github.com/spatie/laravel-og-image/pull/9

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/1.3.0...1.3.1

## 1.1.4 - 2026-06-16

### What's Changed

* Handle existing query parameters in generateForUrl by @freekmurze in https://github.com/spatie/laravel-og-image/pull/9

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/1.1.3...1.1.4

## 1.1.3 - 2026-06-16

### What's Changed

* Bump ramsey/composer-install from 3 to 4 by @dependabot[bot] in https://github.com/spatie/laravel-og-image/pull/4
* Bump dependabot/fetch-metadata from 2.5.0 to 3.0.0 by @dependabot[bot] in https://github.com/spatie/laravel-og-image/pull/6
* Bump dependabot/fetch-metadata from 3.0.0 to 3.1.0 by @dependabot[bot] in https://github.com/spatie/laravel-og-image/pull/7
* Update PHP and Laravel version requirements by @wannevancamp in https://github.com/spatie/laravel-og-image/pull/5
* Ensure OG images for a page URL with existing query parameters is handled correctly by @beblife in https://github.com/spatie/laravel-og-image/pull/8

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/spatie/laravel-og-image/pull/4
* @wannevancamp made their first contribution in https://github.com/spatie/laravel-og-image/pull/5
* @beblife made their first contribution in https://github.com/spatie/laravel-og-image/pull/8

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/1.3.0...1.1.3

## 1.2.0 - 2026-03-02

### What's Changed

* Add `url` parameter to `<x-og-image>` component, allowing you to pass an existing image URL directly instead of generating one via screenshot

**Full Changelog**: https://github.com/spatie/laravel-og-image/compare/1.1.2...1.2.0

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
