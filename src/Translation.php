<?php

namespace Chilister\Translation;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class Translation extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {

        Nova::script('translation', __DIR__.'/../dist/js/tool.js');
        Nova::style('translation', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make('Lang & Translations', [

            MenuItem::make('Languages')
                ->path('/translation/languages'),

            MenuItem::make('Groups')
                ->path('/translation/groups'),

            MenuItem::make('Translations')
                ->path('/translation/list'),

        ])->icon('globe-alt')->collapsible();

    }
}
