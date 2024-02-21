<?php

function ocrSpanishReceipt($imagePath) 
{
    $language = 'spa';

    // Command to run Tesseract
    $command = "tesseract $imagePath stdout -l $language";

    // Execute the command and capture the output
    $outputText = shell_exec($command);

    return $outputText;
}
