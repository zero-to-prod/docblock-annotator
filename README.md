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
- [Usage](#usage)
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

This will add the package to your project’s dependencies and create an autoloader entry for it.

## Usage

Add comments to class members.

```php
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;
use Zerotoprod\DocblockAnnotator\Annotator;

DocblockAnnotator::updateDirectory(
    'src',
    ['@link https://github.com/zero-to-prod/docblock-annotator'],
    [Annotator::public, Annotator::private, Annotator::protected],
    [Annotator::method, Annotator::property, Annotator::constant, Annotator::enum, Annotator::enum_case],
    fn(string $file, string $value) => echo $value,
    fn(Throwable $Throwable) => echo $Throwable->getMessage(),
    true // recursive
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
