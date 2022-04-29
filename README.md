# libretranslate
PHP Interface to the open source [LibreTranslate](https://github.com/LibreTranslate/LibreTranslate) project
## Install
### Composer <small>(recommended)</small>

```bash
composer require jefs42/libretranslate
```

```php
require('vendor/autoload.php');

use Jefs42\LibreTranslate;

$translator = new LibreTranslate();
```

### Manual
Download the LibreTranslate.php file from src/ directory and place somewhere in you project.

Include the file in your project:
```php
require_once("path/to/LibreTranslate.php");

use Jefs42\LibreTranslate;

$translator = new LibreTranslate();
```

## Usage
By default, this class will try to connect to and use a local installation of the LibreTranslate server at http://localhost:5000

Pass *host* and optionally *port*, *source* and/or *target* parameters to override defaults. 

```php
// use locally installed LibreTranslate server on port 5000 with default language settings
$translator = new LibreTranslate();

// specify server with alternate port
$translator = new LibreTranslate("https://libretranslate", 5042);

// use localhost, default port, but override default languages used in translations
$translator = new LibreTranslate(null, null, 'de', 'it');
```

[LibreTranslate Mirros](https://github.com/LibreTranslate/LibreTranslate#mirrors)

### Set API Key
Depending on the server settings, you may need to provide a valid API key to make translation request.
```php
$translator->setApiKey('xxxxx-xxxxx-xxxxx');
```

### Set Default Languages
The default source and target languages are set by the server. You may override these settings when constructing the class, or as needed:  

```php
// Set to translate from English to Swedish
$translator->setLanguages('en', 'sv');

// change only one - source or target
$translator->setSource('es');

$translator->setTarget('ru');
```
Each translation function also supports specifying the languages to use when calling the function.

----


### Detect Language
LibreTranslate will attempt to determine the language of the string passed to it.
```php
$lang = $translator->detect("mi nombre es jefs42");
// expected result: $lang = 'es'
```

### Translate Text
Translate a string of text, or an array of multiple texts. A server may or may not have a character limit set. For larger texts see Translate File.
```php
// translate text using current default source/target languages
$translatedText = $translator->translate("My name is jefs42");

// specifally request languages to use in translation.
// eg. from English to German
$translatedText = $translator->translate("My name is jefs42", "en", "de");

// translate multiple texts in one call
// returns array of translated texts
$translatedText = $translator->translate(["My name is jefs42", "Where is the bathroom?"]);
```

### Translate File
Translate a file of text.

Check $translator->Settings(), current supported formats appear to be - .txt, .odt, .odp, .docx and .pptx
```php
// translate file using current default source/target languages
$translatedText = $translator->translateFile("/full/path/to/file.txt");

// translate file with specific source/target languages
// eg. from English to Italian
$translatedText = $translator->translateFile("/full/path/to/file.txt", "en", "it");

```
* Translation server may have max size limits on post/files size.
* Could pass $_SERVER['FILES']['formfield']['tmp_name'] if using for a web form.

### Suggest
Submit a suggested translation to the server.
```php
// Submit suggestion using current source/target language
$translator->suggest('My name is jefs42', 'Mi nombre es jefs42');

// Specify languages for suggestion
$translator->suggest('My name is jefs42', 'Mi chiamo jefs42', 'en', 'it');
```
Sugesstions must be enabled on the LibreTranslate server. 

-----

### Get Available Languages
Get the list of languages available on the current server.
```php
$languages = $translator->Languages();
/*
Returns array of language codes/names:
[en] => 'English',
[it] => 'Italian',
...
*/
```

### Check Server Settings
Get settings current server is running with.
```php
$settings = $translator->Settings();
/*
Returns array of settings and their current values:
[api_keys] => 1,
[keyRequired] => ,
[char_limit] => -1,
...
*/
```
See [LibreTranslate Arguments](https://github.com/LibreTranslate/LibreTranslate#arguments) for server settings.

----

## LTManage
If you are running a locally hosted LibreTranslate server, you may have access to `ltmanage`. This allows you to view current keys and their request limits, as well as create new keys and delete current keys.

See [LibreTranslate Manage Keys](https://github.com/LibreTranslate/LibreTranslate#manage-api-keys) for details.

If `ltmanage` is found in the local path then the following additonal functions will be available for use:

### listKeys
Will return an array of current keys and their request limits.
```php
$keys = $translator->listKeys();
/*
Returns array of current keys and limits:
[
    'key1' => '500',
    'key2' => '50',
    ...
]
*/
```
### addKey
Create a new key for local server with optional request limit. 
```php
// create a new key limited to server defaults
$key = $translator->addKey();

// create a new key with a specific request limit (higher or lower than server default)
$key = $translator->addKey(400); // limit to 400 requests per minute

/*
Returns string of new key:
"xxxxx-xxxx-xxxx"
*/
```

### removeKey
Delete an existing key from the available keys.
```php
try {
    $translator->removeKey("xxxxx-xxxxx-xxxxx");
} catch (Exception $e) {
    // key doesn't exist
}
/*
Returns true on deletion
*/
```
