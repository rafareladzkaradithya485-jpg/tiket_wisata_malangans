<?php
$files = [
    'images/placeholder.png',
    'images/jatim_park_1.png',
    'images/gunung_bromo.png',
    'images/museum_angkut.png',
];

$pngData = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAa0lEQVR4nO3TQQ0AIAwEsL9/5XcIYpYJYQ0Co+6ghf0HVDwIwYAAAAAAAAgJ+E8DRkYTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExM8F8Bcvw8u7OQAAAABJRU5ErkJggg=='
);

foreach ($files as $file) {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($file, $pngData);
    echo "created: $file\n";
}
