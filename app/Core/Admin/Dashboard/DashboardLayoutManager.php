<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Admin\Dashboard;

class DashboardLayoutManager
{
    protected $widgets = [];
    protected $columns = [[], [], [], []];

    public function initWidgets()
    {
        $this->widgets = app('extension')->getAdminCardsWidgets()->toArray();
        $this->layoutWidgets();
    }

    public function layoutWidgets()
    {
        usort($this->widgets, function ($a, $b) {
            return $b->cols <=> $a->cols;
        });

        foreach ($this->widgets as $widget) {
            $this->placeWidget($widget);
        }
    }

    protected function placeWidget(AdminCardWidget $widget)
    {
        for ($i = 0; $i < 4; $i++) {
            if ($this->canPlaceWidget($i, $widget->cols)) {
                for ($j = 0; $j < $widget->cols; $j++) {
                    $this->columns[($i + $j) % 4][] = $widget;
                }
                break;
            }
        }
    }

    protected function canPlaceWidget($startCol, $cols)
    {
        $canPlace = true;
        for ($i = 0; $i < $cols; $i++) {
            if (count($this->columns[($startCol + $i) % 4]) > 0) {
                $canPlace = false;
                break;
            }
        }
        return $canPlace;
    }

    public function getColumns()
    {
        return $this->columns;
    }
}
