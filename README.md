# Zerotoprod\DocblockAnnotator

![](art/logo.png)

[![Repo](https://img.shields.io/badge/github-gray?logo=github)](https://github.com/zero-to-prod/docblock-annotator)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/docblock-annotator/test.yml?label=test)](https://github.com/zero-to-prod/docblock-annotator/actions)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/zero-to-prod/docblock-annotator/backwards_compatibility.yml?label=backwards_compatibility)](https://github.com/zero-to-prod/docblock-annotator/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/zero-to-prod/docblock-annotator?color=blue)](https://packagist.org/packages/zero-to-prod/docblock-annotator/stats)
[![php](https://img.shields.io/packagist/php-v/zero-to-prod/docblock-annotator.svg?color=purple)](https://packagist.org/packages/zero-to-prod/docblock-annotator/stats)
[![Packagist Version](https://img.shields.io/packagist/v/zero-to-prod/docblock-annotator?color=f28d1a)](https://packagist.org/packages/zero-to-prod/docblock-annotator)
[![License](https://img.shields.io/packagist/l/zero-to-prod/docblock-annotator?color=pink)](https://github.com/zero-to-prod/docblock-annotator/blob/main/LICENSE.md)
[![wakatime](https://wakatime.com/badge/github/zero-to-prod/docblock-annotator.svg)](https://wakatime.com/badge/github/zero-to-prod/docblock-annotator)
[![Hits-of-Code](https://hitsofcode.com/github/zero-to-prod/docblock-annotator?branch=main)](https://hitsofcode.com/github/zero-to-prod/docblock-annotator/view?branch=main)

## Contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Documentation Publishing](#documentation-publishing)
  - [Automatic Documentation Publishing](#automatic-documentation-publishing)
- [Usage](#usage)
  - [Instantiation](#Instantiation)
  - [updateDirectory](#updatedirectory)
  - [updateFiles](#updatefiles)
- [Local Development](./LOCAL_DEVELOPMENT.md)
- [Contributing](#contributing)

## Introduction

An annotator for PHP docblocks.

## Requirements

- PHP 7.4 or higher.

## Installation

Install `Zerotoprod\DocblockAnnotator` via [Composer](https://getcomposer.org/):

```bash
composer require zero-to-prod/docblock-annotator
```

This will add the package to your project's dependencies and create an autoloader entry for it.

## Documentation Publishing

You can publish this README to your local documentation directory.

This can be useful for providing documentation for AI agents.

This can be done using the included script:

```bash
# Publish to default location (./docs/zero-to-prod/docblock-annotator)
vendor/bin/zero-to-prod-docblock-annotator

# Publish to custom directory
vendor/bin/zero-to-prod-docblock-annotator /path/to/your/docs
```

### Automatic Documentation Publishing

You can automatically publish documentation by adding the following to your `composer.json`:

```json
{
    "scripts": {
        "post-install-cmd": [
            "zero-to-prod-docblock-annotator"
        ],
        "post-update-cmd": [
            "zero-to-prod-docblock-annotator"
        ]
    }
}
```

## Usage

### Instantiation

Create a new `DocblockAnnotator` instance with your desired configuration:

```php
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;
use Zerotoprod\DocblockAnnotator\Statement;
use Zerotoprod\DocblockAnnotator\Modifier;
use PhpParser\ParserFactory;

$DocblockAnnotator = new DocblockAnnotator(
    modifiers: [Modifier::public],
    statements: [Statement::ClassMethod], // can also handle raw string: class_method
    success: fn(string $file, string $value) => echo "Updated: $file",
    failure: fn(Throwable $e) => echo $e->getMessage(),
    (new ParserFactory)->createForHostVersion()
);
```

Arguments:

- `modifiers`: Array of visibility modifiers to target
    - `public`
    - `protected`
    - `private`
- `statements`: Array of statement types to process
    - `class_method`
    - `const`
    - `class`
    - `class_const`
    - `enum_case`
    - `enum`
    - `function`
    - `trait`
    - `property`
    - `interface`
- `success`: Callback function executed after successful file processing
- `failure`: Callback function executed when an error occurs
- `Parser`: Optional PHP-Parser instance (defaults to host PHP version)

### updateDirectory

Updates docblocks in all PHP files within a directory.

```php
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;

/* @var DocblockAnnotator $DocblockAnnotator */
$DocblockAnnotator->updateDirectory(
    comments: ['@link https://github.com/zero-to-prod/docblock-annotator'],
    directory: 'src',
    recursive: true
);
```

### updateFiles

Updates docblocks for a specified array of files.

```php
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;

/* @var DocblockAnnotator $DocblockAnnotator */
$DocblockAnnotator->updateFiles(
    comments: ['@link https://github.com/zero-to-prod/docblock-annotator'],
    files: ['src/MyClass.php', 'src/AnotherClass.php']
);
```

## Contributing

Contributions, issues, and feature requests are welcome!
Feel free to check the [issues](https://github.com/zero-to-prod/docblock-annotator/issues) page if you want to contribute.

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Commit changes (`git commit -m 'Add some feature'`).
4. Push to the branch (`git push origin feature-branch`).
5. Create a new Pull Request.
