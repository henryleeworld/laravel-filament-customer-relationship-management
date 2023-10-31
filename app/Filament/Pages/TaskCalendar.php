<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TaskCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.task-calendar';

    public function getHeading(): string
    {
        return __('Task Calendar');
    }

    public static function getNavigationLabel(): string
    {
        return __('Task Calendar');
    }
}
