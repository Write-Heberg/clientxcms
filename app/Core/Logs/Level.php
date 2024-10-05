<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Logs;

/**
 * @see https://github.com/rap2hpoutre/laravel-log-viewer/blob/master/src/Rap2hpoutre/LaravelLogViewer/Level.php
 */
class Level
{
    /**
     * @var array<string, string>
     */
    private array $levelsClasses = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'danger',
        'critical' => 'danger',
        'alert' => 'danger',
        'emergency' => 'danger',
        'processed' => 'info',
        'failed' => 'warning',
    ];

    /**
     * @var array<string, string>
     */
    private array $icons = [
        'debug' => 'info-circle',
        'info' => 'info-circle',
        'notice' => 'info-circle',
        'warning' => 'exclamation-triangle',
        'error' => 'exclamation-triangle',
        'critical' => 'exclamation-triangle',
        'alert' => 'exclamation-triangle',
        'emergency' => 'exclamation-triangle',
        'processed' => 'info-circle',
        'failed' => 'exclamation-triangle'
    ];

    /**
     * @return string[]
     */
    public function all()
    {
        return array_keys($this->icons);
    }

    /**
     * @param  string  $level
     * @return string
     */
    public function img($level)
    {
        return $this->icons[$level];
    }

    /**
     * @param  string  $level
     * @return string
     */
    public function cssClass($level)
    {
        return $this->levelsClasses[$level];
    }
}
