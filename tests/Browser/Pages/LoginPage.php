<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class LoginPage extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/login';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@input-email' => '#email',
            '@input-password' => '#password',
            '@input-remember' => 'label[for="remember"]',
            '@submit-login' => 'form[action="' . route('login') . '"] button[type="submit"]',
            '@forgot-password' => 'form[action="' . route('login') . '"] a[href="' . route('password.request') . '"]',
        ];
    }
}
