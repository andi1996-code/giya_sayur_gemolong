<?php
// Test write permission ke storage
$testFile = __DIR__ . '/storage/app/livewire-tmp/test-' . time() . '.txt';
$result = file_put_contents($testFile, 'Test write at ' . date('Y-m-d H:i:s'));

if ($result !== false) {
    echo "✅ SUCCESS: Web server bisa menulis ke storage/app/livewire-tmp/<br>";
    echo "File dibuat: " . basename($testFile) . "<br>";
    echo "Path: " . $testFile . "<br>";

    // Hapus test file
    unlink($testFile);
    echo "✅ Test file berhasil dihapus<br>";
} else {
    echo "❌ ERROR: Web server TIDAK bisa menulis ke storage/app/livewire-tmp/<br>";
    echo "Path: " . $testFile . "<br>";
    echo "Last error: " . error_get_last()['message'] ?? 'Unknown error';
}

// Test permission info
echo "<hr>";
echo "Storage path: " . realpath(__DIR__ . '/storage/app/livewire-tmp') . "<br>";
echo "Is writable: " . (is_writable(__DIR__ . '/storage/app/livewire-tmp') ? 'YES ✅' : 'NO ❌') . "<br>";
echo "Is readable: " . (is_readable(__DIR__ . '/storage/app/livewire-tmp') ? 'YES ✅' : 'NO ❌') . "<br>";
