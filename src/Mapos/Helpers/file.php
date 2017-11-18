<?php

function system_extension_mime_types()
{
    # Returns the system MIME type mapping of extensions to MIME types, as defined in /etc/mime.types.
    $out = array();
    $file = fopen('/etc/mime.types', 'r');
    while (($line = fgets($file)) !== false) {
        $line = trim(preg_replace('/#.*/', '', $line));
        if (!$line) {
            continue;
        }
        $parts = preg_split('/\s+/', $line);
        if (count($parts) == 1) {
            continue;
        }
        $type = array_shift($parts);
        foreach ($parts as $part) {
            $out[$part] = $type;
        }
    }
    fclose($file);
    return $out;
}

function system_extension_mime_type($file)
{
    # Returns the system MIME type (as defined in /etc/mime.types) for the filename specified.
    #
    # $file - the filename to examine
    static $types;
    if (!isset($types)) {
        $types = system_extension_mime_types();
    }
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if (!$ext) {
        $ext = $file;
    }
    $ext = strtolower($ext);
    return isset($types[$ext]) ? $types[$ext] : null;
}
