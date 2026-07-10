# Warqna v54 Hotfix

This hotfix fixes the Blade parsing error reported on the home page:

- Rebuilt `resources/views/layouts/app.blade.php` using clean multi-line Blade syntax.
- Removed inline `@auth/@else/@endauth` directives from HTML attributes.
- Added safe profile/user variables at the top of the layout.
- Fixed invalid Blade ternary expression in `resources/views/room/seat.blade.php`.
- Verified the patched files with PHP syntax checks.

After extracting this version, run:

```bash
php artisan optimize:clear
php artisan view:clear
php artisan serve
```

If the old cached view is still loaded, delete files inside `storage/framework/views` except `.gitignore`.
