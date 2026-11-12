<?php

it('serves the public landing page', function () {
    $this->get('/')->assertOk()->assertInertia(fn ($page) => $page->component('Landing'));
});
