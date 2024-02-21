<?php

interface StreamInterface 
{
    public function write($data);
    public function writeLine($data);
}

function printTo(StreamInterface $destination, $toPrint) {
    $destination->write($toPrint);
}

function printLineTo(StreamInterface $destination, $toPrint) {
    $destination->writeLine($toPrint);
}

function getStreamFromArgs($args) 
{
    foreach ($args as $arg) 
    {
        if (strpos($arg, '--printTo=') === 0) 
        {
            $value = substr($arg, strlen('--printTo='));

            if ($value === 'StringStream') 
            {
                return new StringStream();
            } 
            elseif (is_string($value) && !empty($value)) 
            {
                return new FileStream($value);
            }
        }
    }

    return new StdOutStream();  // default
}

class StdOutStream implements StreamInterface 
{
    public function write($data) {
        echo $data;
    }

    public function writeLine($data) {
        echo $data . PHP_EOL;
    }
}

class StringStream implements StreamInterface 
{
    private $content = '';

    public function write($data) {
        $this->content .= $data;
    }

    public function writeLine($data) {
        $this->content .= $data . PHP_EOL;
    }

    public function getContent() {
        return $this->content;
    }
}

class FileStream implements StreamInterface 
{
    private $fileHandle;

    public function __construct($filename, $mode = 'w') 
    {
        $this->fileHandle = fopen($filename, $mode);
    }

    public function write($data) 
    {
        fwrite($this->fileHandle, $data);
    }

    public function writeLine($data) 
    {
        fwrite($this->fileHandle, $data . PHP_EOL);
    }

    public function close() 
    {
        fclose($this->fileHandle);
    }
}


/*

$stdout = new StdOutStream();
$stringDestination = new StringStream();
$fileDestination = new FileStream('output.txt'); // This creates (or overwrites) a file named "output.txt"

// Print to stdout
printTo($stdout, "Hello to stdout!");
printLineTo($stdout, "Hello to stdout with a new line.");

// Print to string stream
printTo($stringDestination, "Hello to string stream!");
printLineTo($stringDestination, "Hello to string stream with a new line.");

// Print to file
printTo($fileDestination, "Hello to file!");
printLineTo($fileDestination, "Hello to file with a new line.");

// Close the file when done writing
$fileDestination->close();

// Get the content from the string stream
$content = $stringDestination->getContent();
echo "\nStringStream content: $content";

?>
*/