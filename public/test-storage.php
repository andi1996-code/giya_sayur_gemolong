<?php
// Test write permission ke storage
$storagePath = dirname(__DIR__) . '/storage/app/livewire-tmp';
$testFile = $storagePath . '/test-' . time() . '.txt';

echo "<h3>🧪 Test Storage Permission untuk Livewire Upload</h3>";
echo "<hr>";

// Test 1: Check if directory exists
echo "<b>1. Directory Check:</b><br>";
echo "Path: " . $storagePath . "<br>";
echo "Exists: " . (file_exists($storagePath) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "Is Directory: " . (is_dir($storagePath) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "Is Writable: " . (is_writable($storagePath) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "Is Readable: " . (is_readable($storagePath) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "<hr>";

// Test 2: Try to write
echo "<b>2. Write Test:</b><br>";
$result = @file_put_contents($testFile, 'Test write at ' . date('Y-m-d H:i:s'));

if ($result !== false) {
    echo "✅ SUCCESS: Web server bisa menulis ke storage/app/livewire-tmp/<br>";
    echo "File dibuat: " . basename($testFile) . "<br>";
    echo "Bytes written: " . $result . "<br>";

    // Hapus test file
    if (unlink($testFile)) {
        echo "✅ Test file berhasil dihapus<br>";
    }
} else {
    echo "❌ ERROR: Web server TIDAK bisa menulis ke storage/app/livewire-tmp/<br>";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "<br>";
    }
}
echo "<hr>";

// Test 3: Check symbolic link
echo "<b>3. Symbolic Link Check:</b><br>";
$publicStorage = __DIR__ . '/storage';
echo "Public storage path: " . $publicStorage . "<br>";
echo "Exists: " . (file_exists($publicStorage) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "Is Link: " . (is_link($publicStorage) ? 'YES ✅' : 'NO ❌') . "<br>";
if (is_link($publicStorage)) {
    echo "Target: " . readlink($publicStorage) . "<br>";
}

if (!file_exists($publicStorage)) {
    echo "<br><div style='background: #ffe6e6; padding: 10px; border-left: 4px solid red;'>";
    echo "<b>❌ STORAGE LINK TIDAK ADA!</b><br><br>";
    echo "<b>Solusi:</b><br>";
    echo "1. Close browser ini<br>";
    echo "2. Right-click file <b>INSTALL.bat</b><br>";
    echo "3. Pilih <b>Run as Administrator</b><br>";
    echo "4. Tunggu sampai selesai<br>";
    echo "5. Refresh halaman ini<br>";
    echo "</div>";
}
echo "<hr>";

// Test 4: Check actual storage/app/public
echo "<b>4. Storage App Public Check:</b><br>";
$actualPublic = dirname(__DIR__) . '/storage/app/public';
echo "Path: " . $actualPublic . "<br>";
echo "Exists: " . (file_exists($actualPublic) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "Is Writable: " . (is_writable($actualPublic) ? 'YES ✅' : 'NO ❌') . "<br>";

// List livewire-tmp
$livewireTmpPublic = $actualPublic . '/livewire-tmp';
echo "<br>Livewire-tmp public: " . $livewireTmpPublic . "<br>";
echo "Exists: " . (file_exists($livewireTmpPublic) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "Is Writable: " . (is_writable($livewireTmpPublic) ? 'YES ✅' : 'NO ❌') . "<br>";
echo "<hr>";

echo "<b>✅ Jika semua check di atas HIJAU, upload seharusnya bekerja!</b>";
