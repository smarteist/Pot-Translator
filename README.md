# POT Translator

It is a command line interface translator and compiler for ```.pot``` files, you can translate your translation files 
by using google translate api automatically.  

## Installation

```bash
git clone https://github.com/smarteist/Pot-Translator.git
```
And run 
```bash
composer install
```


## Basic Usage
first run terminal in project directory.
You can use this tool by passing 3 parameters for ```translate.php``` in ```src``` folder like so:
```bash
php pomo translate <source lang ISO-639-1 code> <destination lang ISO-639-1 code> <.pot file address>
```
For example this command
```bash
php pomo translate en es /tmp/test.pot
```
It will translate ```test.pot``` file from ```english``` to ```spanish```

To compile translation files to mo, run this command :
```bash
php pomo compile <.po file address>
```
The output file will be saved next to the original file in that directory.

## Options
|option     | description |
|-----------|-------------|
|--help     |Helps you :D |
|--version  |Shows current version of this tool  |
|-silent    |Translated parts are not shown to you on the console  |
|-delay:3   |It sets a time interval in given seconds between each request to prevent you from being blocked by Google.  |
## Dependencies
Special thanks to developers of:

https://github.com/php-gettext/Gettext

https://github.com/Stichoza/google-translate-php
## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)
