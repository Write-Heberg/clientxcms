<?php

namespace App\View;

class ThemeViewFinder extends \Illuminate\View\FileViewFinder
{

    public function findInPaths($name, $paths)
    {
        $paths = array_reverse($paths);
        $order = [];
        $other = [];
        foreach ($paths as $path){
            if (!strpos($path, 'vendor/')){
                $order[] = $path;
            } else {
                $other[] = $path;
            }
        }
        $paths = array_merge($other, $order);
        return parent::findInPaths($name, $paths);
    }

    protected function parseNamespaceSegments($name): array
    {
        try {
            return parent::parseNamespaceSegments($name);
        } catch (\InvalidArgumentException $e) {
            $segments = explode(static::HINT_PATH_DELIMITER, $name);
            $segments[0] = $segments[0] . '_default';
            return parent::parseNamespaceSegments(join(static::HINT_PATH_DELIMITER, $segments));
        }
    }
}
