<?php

it('redirects the home page to the protected dashboard', function () {
    $this->get('/')
        ->assertRedirect('/dashboard');
});