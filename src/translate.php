<?php

require '../vendor/autoload.php';

use \Statickidz\GoogleTranslate;
global $translator;
$translator = new GoogleTranslate();

define('ANTI_BAN_DELAY_IN_SEC', 2);
define('SOURCE_LANG', isset($argv[1]) ? $argv[1] : 'en');
define('TO_LANG', isset($argv[2]) ? $argv[2] : 'en');
define('FILE_NAME', isset($argv[3]) ? $argv[3] : 'file.pot');

function getTranslated($str)
{
    global $translator;

    sleep(ANTI_BAN_DELAY_IN_SEC);

    try {
        $result = $translator->translate(SOURCE_LANG, TO_LANG, $str);
        if (!$result) {
            $result = '';
        }
    } catch (Exception $e) {
        $result = '';
    }

    echo $str . ' => ' . $result . PHP_EOL;
    return $result;
}

$content = file_get_contents(FILE_NAME);

$parts = explode("\n\n", $content);


$fs = fopen(__DIR__ . DIRECTORY_SEPARATOR . TO_LANG . '_output.po', "wb");
if (!is_resource($fs)) {
    return false;
}

$headerRegex = '/^\s*(".*")/m';
$commentRegex = '/#\s*(.*?)(?=[\n\r]|\*\))/s';
$valuesRegex = '/(msgstr\[?(?<index>[0-9])?\]?\s*"(.*?)")|((?<name>msgid_plural|msgid|msgctxt)\s*"(?<value>.*?)")/m';

foreach ($parts as $part) {

    $output = '';
    preg_match_all($headerRegex, $part, $matchedHeader, PREG_SET_ORDER);
    preg_match_all($commentRegex, $part, $matchedComment, PREG_SET_ORDER);
    preg_match_all($valuesRegex, $part, $matchedValues, PREG_SET_ORDER);

    foreach ($matchedComment as $comment) {
        $output .= $comment[0] . PHP_EOL;
    }

    if (sizeof($matchedHeader) == 0) {
        $msgid = false;
        $msgid_plural = false;

        foreach ($matchedValues as $value) {

            if (preg_match('/msgid\s/', $value[0])) {
                $msgid = $value['value'];
            }

            if (preg_match('/msgid_plural\s/', $value[0])) {
                $msgid_plural = $value['value'];
            }

            if (preg_match('/msgstr/', $value[0])) {
                if (strlen($value['index']) > 0) {
                    $output .= 'msgstr[' . $value['index'] . '] "' . (intval($value['index']) ? getTranslated($msgid_plural) : getTranslated($msgid)) . '"' . PHP_EOL;
                } else {
                    $output .= 'msgstr "' . getTranslated($msgid) . '"' . PHP_EOL;
                }
            } else {
                $output .= $value[0] . PHP_EOL;
            }

        }
    } else {
        $output .= 'msgid ""' . PHP_EOL;
        $output .= 'msgstr ""' . PHP_EOL;
        foreach ($matchedHeader as $header) {
            $output .= $header[0] . PHP_EOL;
        }
        $output .= '"Language: ' . TO_LANG . '\n"' . PHP_EOL;
    }

    fwrite($fs, $output . PHP_EOL);
}

fclose($fs);

echo PHP_EOL . PHP_EOL . 'FINISHED!' . PHP_EOL . PHP_EOL;