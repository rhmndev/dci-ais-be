<?php

if (! function_exists('formatRupiah')) {
    function formatRupiah(float $amount): string
    {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }
}
