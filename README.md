# POT Translator

It is a translator for ```.pot``` files by using google translate and php cli 

## Installation

```bash
git clone https://github.com/smarteist/Pot-Translator.git
```
And run 
```bash
composer install
```


## Usage
You can use this translator by passing 3 parameters for ```translate.php``` in ```src``` folder like so:
```bash
php translate.php <source lang unicode> <destination lang unicode> <.pot file directory>
```
For example this command
```bash
php translate.php en es /tmp/test.pot
```
translates ```test.pot``` file from ```english``` to ```spanish```

## Dependencies
Special thanks to developers of this library:
https://github.com/statickidz/php-google-translate-free

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)
