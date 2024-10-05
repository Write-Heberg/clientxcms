<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Logs;

/**
 * @see https://github.com/rap2hpoutre/laravel-log-viewer/blob/master/src/Rap2hpoutre/LaravelLogViewer/Pattern.php
 */
class Pattern
{

    /**
     * @var array<string, string>
     */
    private $patterns = [
        'logs' => '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/',
        'current_log' => [
            '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?)\](?:.*?(\w+)\.|.*?)',
            ': (.*?)( in .*?:[0-9]+)?$/i'
        ],
        'files' => '/\{.*?\,.*?\}/i',
    ];

    /**
     * @return string[]
     */
    public function all()
    {
        return array_keys($this->patterns);
    }

    /**
     * @param  string  $pattern
     * @param  null|string  $position
     * @return string pattern
     */
    public function getPattern($pattern, $position = null)
    {
        if ($position !== null) {
            return $this->patterns[$pattern][$position];
        }

        return $this->patterns[$pattern];
    }
}
