# CakePHP5 AdminLTE Theme

clone from https://github.com/maiconpinto/cakephp-adminlte-theme

### PHP runtime requirements

PHP **8.4.27 or later in the 8.4 series** is required. Composer's
`require.php` is `~8.4.27` (`>=8.4.27 <8.5.0`); PHP 8.3, 8.4.0–8.4.26
and 8.5 are not supported. `config.platform.php` is `8.4.27` for dependency
resolution only: it does **not** install or upgrade the actual PHP runtime.

This repository is a theme plugin. It has one Composer lock file and no Docker,
CI, deployment, cron or server configuration. The additional Composer manifest
in the vendored Ionicons assets has no PHP runtime requirement. Runtime changes
must also be made in each consuming application; keep its existing CLI/FPM and
OS variant and pin official Docker images to a concrete PHP patch version.

Run Composer in the consuming project's designated Docker environment, using
PHP 8.4.27 and the extensions required by the lock file (including `intl`,
`mbstring`, `dom` and `xmlwriter`). For a standalone verification, use a temporary
PHP 8.4.27 Docker image with those extensions and Composer installed. Update
lock metadata without upgrading dependency versions, then compare the complete
`packages` and `packages-dev` entries before and after:

```sh
composer update --lock --no-install --no-scripts --no-plugins
composer install --no-interaction --no-scripts --no-plugins
composer validate --strict
composer check-platform-reqs
php tests/php-requirement.php /usr/local/bin/composer
vendor/bin/phpunit --display-all-issues
composer audit --locked
```

The boundary regression uses the Semver parser bundled in the Composer PHAR
(pass its path if different). It checks the manifest and lock PHP constraints,
the dependency resolution platform, and every locked dependency's PHP constraint.
Run the regression and PHPUnit with Docker `--network none`, `error_reporting=E_ALL`,
no production credentials, and a dedicated test database if database access is
needed. The current configuration tests do not query a database. Composer's
dependency download and advisory audit require network access and should run
separately. Keep deprecations visible and record them separately from failures.

Before treating the server update as complete, verify all of the following in
each consuming application's environment:

- CLI, FPM and every cron/job process actually run PHP >=8.4.27 and <8.5.
  Check each binary explicitly; a successful Composer platform override is not
  evidence of the installed version.
- All required extensions and the intended `php.ini`, `conf.d` and FPM pool
  settings load in both CLI and FPM. Run `composer check-platform-reqs` there.
- Nginx points to the correct FPM socket or port. Restart/reload the appropriate
  service and confirm the version serving requests.
- OS package installations use environment-appropriate names such as `php8.4`
  and `php8.4-fpm` in deployment commands, cron and sudo rules, with no patch
  number in executable/service names. Official containers normally use `php`
  and `php-fpm` instead.
- Service users retain the required sudo permissions and cron uses the intended
  executable and configuration, independent of an interactive shell's PATH.

Repository changes alone do not complete vulnerability remediation on servers.

Verification on 2026-10-22 used a temporary image built from `php:8.4.27-fpm`
(Debian trixie), with `intl`, Composer 2.10.3 and `php.ini-development` plus
`error_reporting=E_ALL`. CLI/FPM reported 8.4.27; extension/configuration loading
and `php-fpm -tt` passed. Composer strict validation, platform checks and the
boundary regression passed. PHPUnit passed 2 tests / 2 assertions with external
network access disabled and no application credentials or database connection.
There is no separate application security test suite in this repository;
`composer audit --locked` found no advisories. No PHP deprecations were emitted
by these checks; Composer reported that the root package version could not be
detected and defaulted to 1.0.0. All 42 locked package entries were unchanged;
Composer regenerated platform/hash and format/API metadata. `package-lock.json`
was unchanged. Consuming applications, live HTTP/FPM integration and servers
were not tested or updated.

### Maintain bundled browser assets

The npm toolchain is pinned with Volta to Node.js 22.23.4 and npm 12.0.2.
`engines` and `.npmrc` also check compatibility when using npm without Volta.
Use the pinned tools and run:

```sh
npm ci
npm run assets:sync
npm run assets:check
```

`package.json` pins Bootstrap 3.4.1, jQuery 3.7.1, FastClick 1.0.6,
Font Awesome 4.7.0 and jQuery slimScroll 1.3.8. Commit `package-lock.json`
and the generated browser files in `webroot/bower_components/` together.
The sync command copies distributions and attribution files; it does not rebuild
the upstream libraries or copy their build manifests into the public directory.
`assets:check` verifies that the committed files match the installed packages.

The existing asset paths are preserved for applications such as babypad-pfm.
Composer consumers use the committed files and do not need Node.js or npm to
install or run the theme. When changing a dependency, check consumer compatibility
before updating its exact version and regenerating the assets.

Ionicons 2.0.0 and the libraries in `webroot/plugins/` remain vendored separately.
Ionicons 2.0.0 has no matching npm release, so this migration preserves its files.
The npm migration does not upgrade Bootstrap beyond version 3 or establish the
PHP/Node versions running in consuming applications. SBOM tools must distinguish
upstream compatibility ranges from the actual project toolchain.

### Enable Plugin

```php
// src/Application.php

public function bootstrap()
{
    $this->addPlugin('AdminLTE');
}
```

### Enable Theme

```php
// src/Controller/AppController.php
use Cake\Event\EventInterface;

public function beforeRender(EventInterface  $event)
{
    $this->viewBuilder()->setTheme('AdminLTE');
}
```

### Enable Form

```php
// src/View/AppView.php

public function initialize()
{
    $this->loadHelper('Form', ['className' => 'AdminLTE.Form']);
}
```

### Configure

```php
// new config/adminlte.php file

return [
    'Theme' => [
        'title' => 'AdminLTE',
        'logo' => [
            'mini' => '<b>A</b>LT',
            'large' => '<b>Admin</b>LTE'
        ],
        'login' => [
            'show_remember' => true,
            'show_register' => true,
            'show_social' => true
        ],
        'folder' => ROOT,
        'skin' => 'blue'
    ]
];

// config/bootstrap.php

Configure::load('adminlte', 'default');
```

# Customize Layout

If you want to [Customize Layout](https://github.com/maiconpinto/cakephp-adminlte-theme/wiki/Customize-Layout)

# What's the features

### Layouts

There are 9 layout files.

- boxed
- collapsed
- default **it's the main layout**
- fixed
- lockscreen
- login
- print
- register
- top

### View Blocks

There are 3 Blocks where you can extend your theme.

- **css**

```php
<?php echo $this->fetch('css'); ?>
```

Example:

```php
<?php echo $this->Html->css('my-page', ['block' => 'css']); ?>
```

- **script**

```php
<?php echo $this->fetch('script'); ?>
```

Example:

```php
<?php echo $this->Html->script('my-page', ['block' => 'script']); ?>
```

- **scriptBottom**

```php
<?php echo $this->fetch('scriptBottom'); ?>
```

Example:

```php
<?php $this->start('scriptBottom'); ?>
    <script>
      $(function () { console.log('ready'); });
    </script>
<?php  $this->end(); ?>
```

### Elements

There are 7 element files.

- Element/
    - aside/
        - form
        - sidebar-menu
        - user-panel
    - aside-control-sidebar
    - aside-main-sidebar
    - footer
    - nav-top

### Flash Message

The theme is prepared to show Flash Messages.

```php
<?php echo $this->Flash->render(); ?>
<?php echo $this->Flash->render('auth'); ?>
```

### Bake

One of the better Cake features. The theme is prepared to use Bake.

```
bin/cake bake all user --theme AdminLTE
```

### View

- **AdminLTEView**

This is one the better theme feature. It change the pattern how Cake show view files.

Basically, you overwrite any theme, plugin and prefix files.

1. src/Template/Plugin/$theme/Plugin/$plugin/$prefix/
2. src/Template/Plugin/$theme/Plugin/$plugin/
3. src/Template/Plugin/$theme/$prefix/
4. src/Template/Plugin/$theme/

### FormHelper

FormHelper by default has format template based on Foundation template. This helper overwrite these templates.

### Behavior

- **DatepickerBehavior**

When you configure `App.defaultLocale` to `pt_BR` this Behavior is util.

### Locale

When you configure `App.defaultLocale` to `pt_BR` this Locale is util.

### Page debug

Added link to default page of CakePHP.

![Page debug](docs/page-debug.png)

# Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request
