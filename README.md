# Liquid template engine for Laravel

Liquid is a PHP port of the [Liquid template engine for Ruby](https://github.com/Shopify/liquid), which was written by Tobias Lutke. Although there are many other templating engines for PHP, including Smarty (from which Liquid was partially inspired), Liquid had some advantages that made porting worthwhile:

 * Readable and human friendly syntax, that is usable in any type of document, not just html, without need for escaping.
 * Quick and easy to use and maintain.
 * 100% secure, no possibility of embedding PHP code.
 * Clean OO design, rather than the mix of OO and procedural found in other templating engines.
 * Seperate compiling and rendering stages for improved performance.
 * 100% Markup compatibility with a Ruby templating engine, making templates usable for either.
 * Unit tested: Liquid is fully unit-tested. The library is stable and ready to be used in large projects.

## Why Liquid?

Why another templating library?

Liquid was written to meet three templating library requirements: good performance, easy to extend, and simply to use.

## Installing

You can install this lib via [composer](https://getcomposer.org/):

    composer require simexis/laravel-liquid
    
Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider

### Laravel 5.5+:

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Liquid\LiquidServiceProvider::class,
```

If you want to use the facade to compiler:

```php
'Liquid' => Liquid\Facade::class,
```

```shell
php artisan vendor:publish --provider="Liquid\LiquidServiceProvider"
```

## Example template

	{% if products %}
		<ul id="products">
		{% for product in products %}
		  <li>
			<h2>{{ product.name }}</h2>
			Only {{ product.price | price }}

			{{ product.description | prettyprint | paragraph }}

			{{ 'it rocks!' | paragraph }}

		  </li>
		{% endfor %}
		</ul>
	{% endif %}

## How to use Liquid

    class HomeController extends Comtroller {
        
        public function index() 
        {
            $products = Products::all();
            
            return view('home', [
                'products' => $products 
            ]);  
        }
        
    }


To find more examples at the original Ruby implementation repository's [wiki page](https://github.com/Shopify/liquid/wiki).

## Requirements

 * PHP 5.3+
 * [laravel/framework 5.3+](https://github.com/laravel/framework)

## Issues

Have a bug? Please create an issue here on GitHub!

[https://github.com/jooorooo/laravel-liquid/issues](https://github.com/jooorooo/laravel-liquid/issues)