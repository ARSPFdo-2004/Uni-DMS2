<?php
$zip = new ZipArchive;
if ($zip->open('University.docx') === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    // Quick text extraction from XML
    $text = strip_tags(str_replace(['<w:p', '</w:p>'], ["\n<w:p", "\n</w:p>"], $xml));
    echo "--- DOCX CONTENT START ---\n";
    echo substr($text, 0, 5000); // Read a good chunk to understand format
    echo "\n--- DOCX CONTENT END ---\n";
} else {
    echo "Failed to open docx.";
}
?>