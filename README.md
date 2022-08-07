# Laravel Nova Future Trend

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chrysanthos/laravel-nova-future-trend.svg?style=flat-square)](https://packagist.org/packages/chrysanthos/laravel-nova-future-trend)
[![Total Downloads](https://img.shields.io/packagist/dt/chrysanthos/laravel-nova-future-trend.svg?style=flat-square)](https://packagist.org/packages/chrysanthos/laravel-nova-future-trend)
![GitHub Actions](https://github.com/chrysanthos/laravel-nova-future-trend/actions/workflows/main.yml/badge.svg)

Laravel Nova includes a way to generate Trend metrics and display values over time via a line chart. It doesn't however offer a way to generate the future graphs starting from the current date . This packages allows you to generate the graph for the near future. 

## Installation

You can install the package via composer:

```bash
composer require chrysanthos/laravel-nova-future-trend
```

## Usage

```bash
php artisan nova:trend ScheduledMessagesTrend
```

Extend the FutureTrend class instead of Nova's defualt Trend class.
```diff
- use Laravel\Nova\Metrics\Trend;
- class FutureMessagesTrend extends Trend

+ use Chrysanthos\LaravelNovaFutureTrend\FutureTrend;
+ class FutureMessagesTrend extends FutureTrend

```

![Example image](https://chrysanthos.xyz/wp-content/uploads/2022/08/future-messages.png)


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email me@chrysanthos.xyz instead of using the issue tracker.

## Credits

-   [Chrysanthos Prodromou](https://github.com/chrysanthos)
-   [All Contributors](../../contributors)

## Licence

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/chrysanthos/laravel-nova-future-trend) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.
