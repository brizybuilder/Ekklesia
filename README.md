### Composer Library

Placeholders and integrations with https://www.ekklesia360.com/ - ministrybrands

### Install

Using [Composer](http://getcomposer.org), add `bagrinsergiu/ekklesia` to `require`, in `repositories` the `git@github.com:bagrinsergiu/Ekklesia.git` and make sure you have access to this repository.   
Example of `composer.json`: 

```json
{
  "require": {
    "bagrinsergiu/ekklesia": "~1.0"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:bagrinsergiu/Ekklesia.git"
    }
  ]
}
```

```bash
$ composer update
```


  


### Requirements

PHP 7.4 or newer (tested on 7.4 and 8.3).

### Tests

```bash
$ composer install
$ vendor/bin/phpunit
```

Notes for installing the dev dependencies on PHP 8.x:

- `brizybuilder/content-placeholder` pulls in `zendframework/zend-code`, which does not support PHP 8. Install with a root-level `"replace": {"zendframework/zend-code": "*"}` plus `laminas/laminas-code`, the same way brizy-cloud does. Do not add the `replace` to this package's `composer.json`: it would leak to consumers.
- Every Twig 1.x/2.x release has open security advisories, so recent Composer versions refuse to install them unless you pass `--no-security-blocking`.
