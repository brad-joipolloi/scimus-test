<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('api.')
    ->middleware(['auth:api'])
    ->namespace('Api')
    ->group(function (Router $router) {
        $router->name('user.')
            ->prefix('user')
            ->group(function (Router $router) {
                $router->get('role', 'UserController@roleIndex')->name('role.index');

                $router->get('', 'UserController@index')->name('index');
                $router->post('', 'UserController@store')->name('store');
                $router->get('{user}', 'UserController@show')->name('show');
                $router->post('{user}/on-board', 'UserController@onboard')->name('on-board');
                $router->put('{user}', 'UserController@update')->name('update');
                $router->delete('{user}', 'UserController@destroy')->name('destroy');
                $router->post('{user}/restore', 'UserController@restore')->name('destroy');
            });

        $router->name('kiosk.')
            ->prefix('kiosk')
            ->group(function (Router $router) {
                $router->get('', 'KioskController@index')->name('index');
                $router->get('{kiosk}', 'KioskController@show')->name('show');
                $router->get('{kiosk}/logs', 'KioskController@showLogs')->name('show.logs');
                $router->put('{kiosk}', 'KioskController@update')->name('update');
                $router->delete('{kiosk}', 'KioskController@destroy')->name('destroy');
            });

        $router->name('package.')
            ->prefix('package')
            ->group(function (Router $router) {
                $router->get('versions', 'PackageVersionController@index')->name('versions');

                $router->get('', 'PackageController@index')->name('index');
                $router->post('', 'PackageController@store')->name('store');
                $router->get('{package}', 'PackageController@show')->name('show');
                $router->put('{package}', 'PackageController@update')->name('update');
                $router->post('{package}', 'PackageController@duplicate')->name('duplicate');
                $router->delete('{package}', 'PackageController@destroy')->name('destroy');

                $router->get('{package}/version', 'PackageVersionController@index')->name('version.index');
                $router->post('{package}/version', 'PackageVersionController@store')->name('version.store');
                $router->get('{package}/version/{packageVersion}', 'PackageVersionController@show')->name('version.show');
                $router->put('{package}/version/{packageVersion}', 'PackageVersionController@update')->name('version.update');
                $router->delete('{package}/version/{packageVersion}', 'PackageVersionController@destroy')->name('version.delete');
                $router->post('{package}/version/{packageVersion}/valid', 'PackageVersionController@valid')->name('version.valid');
                $router->post('{package}/version/{packageVersion}/deploy', 'PackageVersionController@deploy')->name('version.deploy');
                $router->post('{package}/version/{packageVersion}/asset', 'PackageVersionController@uploadAsset')->name('version.asset.upload');
                $router->get('{package}/version/{packageVersion}/asset', 'PackageVersionController@searchAsset')->name('version.asset.search');
            });

        $router->name('help.')
            ->prefix('help')
            ->group(function (Router $router) {
                $router->get('', 'HelpTopicController@index')->name('index');
                $router->get('context', 'HelpTopicController@showByContext')->name('show_context');
                $router->get('{helpTopic}', 'HelpTopicController@show')->name('show');
                $router->put('{helpTopic}', 'HelpTopicController@update')->name('update');
            });

        $router->name('site.')
            ->prefix('site')
            ->group(function (Router $router) {
                $router->get('', 'SiteController@index')->name('index');
            });

        $router->name('custom_page.')
            ->prefix('custom_page')
            ->group(function (Router $router) {
                $router->get('', 'CustomPageController@index')->name('index');
                $router->get('{customPage}', 'CustomPageController@show')->name('show');
            });
    });

Route::name('api.information')
    ->middleware(['auth:api'])
    ->namespace('Api')
    ->group(function (Router $router) {
        $router->get('', 'ApiInformationController@resources')->name('resources');
    });

Route::name('api.')
    ->namespace('Api')
    ->prefix('kiosk')
    ->middleware(['api'])
    ->group(function (Router $router) {
        $router->post('health-check', 'KioskController@healthCheck')->name('kiosk.health-check');
        $router->post('register', 'KioskController@register')->name('kiosk.register');
        $router->post('package/{package}/version/{packageVersion}/download', 'KioskController@download')->name('kiosk.package.download');
    });
