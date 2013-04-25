# MiMeil

**MiMeil** - flexible MIME message builder covering most useful specification features.

It has no dependencies and works out of the box with **PHP 5.3 and up** - simply include it and you're ready to go. **iconv** and **mbstring** extensions are recommended for certain features.

Check for last PHP 5.2-compatible version in the first revision of this repo.

**Unless you're using Laravel MiMeil is just a single file (`mimeil.php`).**

[ [Method reference](http://proger.i-forge.net/MiMeil) ]

## [Laravel bundle](http://bundles.laravel.com/bundle/mimeil)
```
php artisan bundle:install mimeil
```

After this:

* Add it to your `application/bundles.php`; if you use `'auto' => true` class aliases will be created automatically.
* Create `application/config/mimeil.php` (use `config-sample.php` as a template) to override default values used when building/sending an e-mail. Keys are names of instance properties of `MiMeil`.
* Use `LaMeil` class instead of `MiMeil` if you want to have a convenient Laravel-oriented layer that, for example, logs errors and uses your config files.
