<?php


namespace App;


use App\CLIUtils\CLIBox;
use App\CLIUtils\CLIStr;
use Exception;
use Gettext\Translation;
use ReflectionException;
use ReflectionMethod;

class CLI
{

    private $options = [
        'delay' => 1,
        'echo' => true,
    ];

    public function bootstrap($argv)
    {
        $anyError = [];

        // matches args in form of => command
        $command = preg_grep('/(^[^-].*)/', $argv);
        // matches args in form of => --option
        $dashDashes = preg_grep('/^--([a-z]+)/', $argv);
        // matches args in form of => -option:value or -option
        $dashes = preg_grep('/^-([a-zA-Z]+):?(.*)/', $argv);

        foreach ($dashDashes as $option) {
            try {
                (new ReflectionMethod($this, str_replace('--', 'dashDash', $option)))->invoke($this, $argv);
            } catch (ReflectionException $e) {
                $anyError[] = CLIStr::create('* Internal Error, maybe some methods removed.')->setColors('green');
                $anyError[] = CLIStr::create('')->setColors('green');
                $anyError[] = CLIStr::create("No options found in form of $option")->setColors('green');
            }
        }

        foreach ($dashes as $option) {
            try {
                $opt = explode(':', $option);
                $value = isset($opt[1]) ? $opt[1] : false;
                (new ReflectionMethod($this, str_replace('-', 'dash', $opt[0])))->invoke($this, $value);
            } catch (ReflectionException $e) {
                $anyError[] = CLIStr::create('* Internal Error, maybe some methods removed.')->setColors('green');
                $anyError[] = CLIStr::create('')->setColors('green');
                $anyError[] = CLIStr::create("No options found in form of $option")->setColors('green');

            }
        }

        if (isset($command[0])) {
            try {
                (new ReflectionMethod($this, $command[0]))->invoke($this, $command);
                return;
            } catch (ReflectionException $e) {
                $anyError[] = CLIStr::create("* No such command!")->setColors('green');
                $anyError[] = CLIStr::create("")->setColors('green');
                $anyError[] = CLIStr::create("The \"" . (isset($command[0]) ? $command[0] : '') . "\" is an invalid command.")->setColors('green');
            }
        }

        if (!empty($anyError)) {
            $anyError[] = CLIStr::create()->setColors('green');
            $anyError[] = CLIStr::create('More help? please run "php pomo --help"')->setColors('green');
            echo (new CLIBox([
                'tableColor' => 'red',
                'align' => 'center',
                'theme' => 3,
                'contentColor' => 'green',
            ]))->getBox(
                $anyError,
                CLIStr::create('Error!')->setColors('red')
            );
        } else {
            $this->dashDashHelp();
        }
    }

    public function dashDelay($delay)
    {
        $this->options['delay'] = intval($delay);
    }

    public function dashSilent()
    {
        $this->options['echo'] = false;
    }

    public function dashDashHelp()
    {
        $helpContent = [
            CLIStr::tableRow([
                'COMMAND',
                'DESCRIPTION',
            ], [
                "20",
                "50"
            ])->bold()->setColors('cyan'),
            CLIStr::create(),
            CLIStr::tableRow([
                'translate',
                'Takes 3 parameters and translates from <ISO-CODE> to <ISO-CODE> <FILE>.'
            ], [
                "20",
                "50"
            ]),
            CLIStr::tableRow([
                'compile',
                'Takes 1 parameters <FILE> and compiles translation files to mo.'
            ], [
                "20",
                "50"
            ]),
            CLIStr::create(),
            CLIStr::create(),
            CLIStr::tableRow([
                'OPTIONS',
                'DESCRIPTION'
            ], [
                "20",
                "50"
            ])->bold()->setColors('cyan'),
            CLIStr::create(),
            CLIStr::tableRow([
                '-delay:#',
                'Delay between each request in sec.'
            ], [
                "20",
                "50"
            ]),
            CLIStr::tableRow([
                '-silent',
                'Translated parts are not shown to you on the console '
            ], [
                "20",
                "50"
            ]),
            CLIStr::tableRow([
                '--version',
                'Retrieves current version of this tool'
            ], [
                "20",
                "50"
            ])
        ];

        echo (new CLIBox([
            'tableColor' => 'green',
            'titleColor' => 'white',
            'contentColor' => 'cyan',
            'padding' => 1,
            'align' => 'left',
        ]))->getBox(
            $helpContent,
            CLIStr::create(' Help! ')->setColors('white'),
            CLIStr::create(' POMO Translator ' . \Composer\InstalledVersions::getRootPackage()['pretty_version'] . ' ')->setColors('green')
        );
        exit();
    }

    public function dashVersion()
    {
        echo \Composer\InstalledVersions::getRootPackage()['pretty_version'] . PHP_EOL;
    }

    public function dashDashVersion()
    {
        $this->dashVersion();
    }

    public function translate($args)
    {
        $anyError = [];
        $file = array_pop($args);
        if (!is_file($file)) {
            $anyError[] = CLIStr::create("* File not found, please enter valid file address.")->setColors('green');
            $anyError[] = CLIStr::create("")->setColors('green');
        }
        $to = array_pop($args);
        $from = array_pop($args);
        if (!$to | !$from) {
            $anyError[] = CLIStr::create("* Please Enter Source and Destination Language code .")->setColors('green');
            $anyError[] = CLIStr::create("You specify target languages by using their ISO - 639 - 1 codes .")->setColors('green');
            $anyError[] = CLIStr::create("Take a look at https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes")->setColors('green');
        }
        if (!empty($anyError)) {
            $anyError[] = CLIStr::create()->setColors('green');
            $anyError[] = CLIStr::create('More help? please run "php pomo --help"')->setColors('green');
            echo (new CLIBox([
                'tableColor' => 'red',
                'align' => 'center',
                'theme' => 3,
            ]))->getBox($anyError,
                CLIStr::create('Error!')->setColors('red')->bold()
            );
            exit();
        }
        try {
            $parser = new Parser($file);
            $iterator = $parser->getLoader()->getIterator();
            $parser->getLoader()->setLanguage($to);
            $tranlator = new Translator($from, $to);
            /* @var $text string */
            /* @var $translation Translation */
            CLIStr::consoleWrite(
                PHP_EOL . CLIStr::create('Translation Started...')->setColors('black', 'cyan')
                . PHP_EOL . PHP_EOL . CLIStr::create('Please Wait')->setColors('green')
            );
            foreach ($iterator as $text => $translation) {
                $translated = $tranlator->getTranslated($text, $this->options['delay']);
                $translation->translate($translated);

                if ($translation->getPlural()) {
                    $translation->translatePlural($tranlator->getTranslated($translation->getPlural(), $this->options['delay']));
                }

                if ($this->options['echo']) {
                    CLIStr::consoleWrite(
                        CLIStr::create($text)->setColors('black', 'cyan')
                        . CLIStr::create(' ➤➤translated to➤➤ ')->setColors('green')
                        . CLIStr::create($translated)->setColors('black', 'green')
                        , true);
                }
            }

            $output = $parser->toPo(dirname($file));
            CLIStr::consoleWrite(
                PHP_EOL . CLIStr::create('Translation completed successfully! output file saved in:')->setColors('black', 'green')
                . PHP_EOL . CLIStr::create($output)->setColors('green') . PHP_EOL
                , true);

        } catch (Exception $e) {
            echo (new CLIBox([
                'tableColor' => 'red',
                'align' => 'center',
                'theme' => 3,
            ]))->getBox([
                CLIStr::create("An error occurred when parsing file")->setColors('green'),
                CLIStr::create($file)->setColors('green'),
                CLIStr::create(),
                CLIStr::create(),
                CLIStr::create($e->getMessage())->setColors('green')
            ],
                CLIStr::create('Parse Error!')->setColors('red')->bold()
            );
            exit();
        }
        return true;

    }

    public function compile($args)
    {
        $anyError = [];
        $file = array_pop($args);
        if (!is_file($file)) {
            $anyError[] = CLIStr::create("* File not found, please enter valid file address.")->setColors('green');
            $anyError[] = CLIStr::create("")->setColors('green');
        }
        if (!empty($anyError)) {
            $anyError[] = CLIStr::create('More help? please run "php pomo --help"')->setColors('green');

            echo (new CLIBox([
                'tableColor' => 'red',
                'align' => 'center',
                'theme' => 3,
            ]))->getBox($anyError,
                CLIStr::create('Error!')->setColors('red')
            );
            exit();
        }


        try {
            $parser = new Parser($file);
            $output = $parser->toMo(dirname($file));

            CLIStr::consoleWrite(
                PHP_EOL . CLIStr::create('Compiled successfully! output file saved in:')->setColors('black', 'green')
                . PHP_EOL . CLIStr::create($output)->setColors('green') . PHP_EOL
                , true);

        } catch (Exception $e) {
            echo (new CLIBox([
                'tableColor' => 'red',
                'align' => 'center',
                'theme' => 3,
            ]))->getBox([
                CLIStr::create("An error occurred when parsing file" . $file)->setColors('gree'),
                CLIStr::create()->setColors('green'),
                CLIStr::create($e->getMessage())->setColors('green'),

            ],
                CLIStr::create('Parse Error!')->setColors('red')

            );
            exit();
        }
        return true;
    }
}
