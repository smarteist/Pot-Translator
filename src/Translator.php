<?php


namespace App;


use ErrorException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Translator extends GoogleTranslate
{

    /**
     * Translator constructor.
     * @param $sourceLang
     * @param $destinationLang
     */
    public function __construct($sourceLang, $destinationLang)
    {
        parent::__construct();
        $this->setSource($sourceLang);
        $this->setTarget($destinationLang);
    }

    public function getTranslated($string, $delay = 1)
    {
        sleep($delay);

        try {
            return $this->translate($string);
        } catch (ErrorException $e) {
            return $string;
        }
    }


}
