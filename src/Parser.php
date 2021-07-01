<?php


namespace App;

use Exception;
use Gettext\Generator\PoGenerator;
use Gettext\Loader\PoLoader;
use Gettext\Generator\MoGenerator;
use Gettext\Translations;


class Parser
{
    /**
     * @var Translations
     */
    private $currentMoFile;

    /**
     * @var string current file name
     */
    private $fileName;

    public function __construct($poFile)
    {
        if (file_exists($poFile)) {
            //import from a .po file:
            $this->currentMoFile = (new PoLoader())->loadFile($poFile);
            $this->fileName = pathinfo($poFile, PATHINFO_FILENAME);
        } else throw new Exception("File \"$poFile\" not found!");
    }


    public function getLoader()
    {
        return $this->currentMoFile;
    }


    public function toMo($outputDir)
    {
        //export to a .mo file:
        $outputFile = $outputDir . DIRECTORY_SEPARATOR . $this->fileName . '_' . $this->currentMoFile->getLanguage() . '.mo';
        $generator = new MoGenerator();
        $generator->generateFile($this->currentMoFile, $outputFile);
        return $outputFile;
    }

    public function toPo($outputDir)
    {
        //export to a .po file:
        $outputFile = $outputDir . DIRECTORY_SEPARATOR . $this->fileName . '_' . $this->currentMoFile->getLanguage() . '.po';
        $generator = new PoGenerator();
        $generator->generateFile($this->currentMoFile, $outputFile);
        return $outputFile;
    }
}
