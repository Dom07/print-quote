<?php

test('estimate sandbox page loads successfully', function () {
    $this->get('/estimate-sandbox')
        ->assertOk()
        ->assertSee('Estimate Sandbox')
        ->assertSee('Temporary calculation screen for testing paper inputs.');
});

test('estimate sandbox calculates paper kilograms for valid data', function () {
    $this->post('/estimate-sandbox', [
        'length' => 20,
        'width' => 30,
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
    ])
        ->assertOk()
        ->assertSee('38.7097')
        ->assertSee('42.5806')
        ->assertSee('46.4516')
        ->assertSee('Temporary paper weight calculation only. No values are saved.');
});

test('estimate sandbox returns validation errors for invalid data', function () {
    $this->post('/estimate-sandbox', [
        'length' => 0,
        'width' => 'wide',
        'gsm' => -1,
        'no_of_sheets' => 0,
        'no_of_sheets_with_wastage' => 1.5,
        'no_of_sheets_to_process' => -2,
    ])
        ->assertSessionHasErrors([
            'length',
            'width',
            'gsm',
            'no_of_sheets',
            'no_of_sheets_with_wastage',
            'no_of_sheets_to_process',
        ]);
});
