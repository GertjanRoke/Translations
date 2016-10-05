# Translations
Easy way to make your database multilanguage

## Install
```bash
composer require gertjanroke/translations
```

## After install

### Publish files

If all your models are directly in your app folder and your using the laravel convention, you do not need to publish the config file.
But if your somehow not following this convention, like putting your models in a own folder.
Than you need to publish the config file and define where your translation models are stored.

If you need to publish the config file, than first do the following think before running the publish command.

Add the following line to `config/app.php`.

at `providers`:

```php
'providers' => [
	...
	Gertjanroke\Notification\NotificationServiceProvider::class,
	...
],
```

To publish the config file run the following command:
```bash
php artisan vendor:publish --provider="Gertjanroke\Notification\NotificationServiceProvider"
```

## Usage

Now comes the fun part:

To use the translations trait you only need to add a `use` inside your model class.
You can do so by:
```php
class Page extends Model
{
	...
	use Translations;
	...
}
```

and add the `use` above your class like so:

```php
...
use Gertjanroke\Translations\Traits\Translations;

class Page extends Model
{
	...
```

Now your almost done.

The only thing you need to do is make a translation model for the model you just added the trait.

Something like:
```php
class PageTranslations extends Model
{
    //
}
```

The important part is that out of the box this package uses a model name like the one above.
If you want to override this just edit the config file and your good to go.

## Saving your translations

The first thing you need to do is check with way works best for you.
This package comes with a default request attribute on with he he build a foreach to save all values per locale.
This format is as following:
```html
<input type="text" name="trans[<locale>][<attribute>]">
```

If your do not like the key word `trans` you can easly change this in the config.
To something like:
```html
<input type="text" name="translations[<locale>][<attribute>]">
```
or
```html
<input type="text" name="i18n[<locale>][<attribute>]">
```
or what ever you like.

After you made your forms you now need to save the data.
This package comes with two ways to do so.
Just call the following function from your model and send the request object with it.
like so:
```php
public function store(Request $request)
{
	...
	$page = new Page();
	$page->saveTranslations($request);
	...
}
```
or the short version:
```php
public function store(Request $request)
{
	...
	$page = new Page();
	$page->saveTrans($request);
	...
}
```

And the package will create a new row in your database table or it will update its existing row.

## Magic funtions

This package als comes with two handy funtions that let you do things like:
```php
$page->title
```
and it will check if that attribute existing in your translations table.
If it exists, the value will be returned in the currunt locale of your application.

The other magic function is dynamic getters.
Let take the example from above and use that as a method.
```php
$page->getTitle();
```
This will get the title just like the funtion above,
but now you can specify with locale version you want of the given attribute.
```php
$page->getTitle('en');
```
You can change `'en'` for the require locale.

## Custom Foreign key

If for some reason you do not like the way laravel handles the foreign keys and you want to use your own conventions,
Thust do so by setting the `transForeignKey` variable in your main model.
For example:
```php
...
use Gertjanroke\Translations\Traits\Translations;

class Page extends Model
{
	...
	use Translations;

    protected $transForeignKey = 'pages_id';
    ...
}
```
