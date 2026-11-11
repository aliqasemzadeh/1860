<?php

use App\Support\DiscardedOutput;

test('discarded output suppresses echoed stdout while returning the callback result', function () {
    ob_start();

    $result = DiscardedOutput::run(function (): string {
        echo "GETTING: https://api.torob.com/v4/base-product/sellers/ (Attempt #1)\n";

        return 'ok';
    });

    $leaked = ob_get_clean();

    expect($result)->toBe('ok')
        ->and($leaked)->toBe('');
});

test('discarded output still discards echo when the callback throws', function () {
    ob_start();

    try {
        DiscardedOutput::run(function (): void {
            echo "GETTING: https://api.torob.com/v4/base-product/sellers/ (Attempt #1)\n";

            throw new RuntimeException('Torob sellers response did not contain valid JSON.');
        });
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toContain('valid JSON');
    }

    $leaked = ob_get_clean();

    expect($leaked)->toBe('');
});
