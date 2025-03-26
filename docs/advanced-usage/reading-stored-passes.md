---
title: Reading stored passes
weight: 3
---

The package contains a `PkPassReader` class that can be used to read passes from a file or a string. 

Here's how you can instantiate the `PkPassReader` class:

```php
use Spatie\LaravelMobilePass\Support\PkPassReader;

// from file
$reader = PkPassReader::fromFile('path/to/pass.pkpass');

// from string
$reader = PkPassReader::fromString($passData);
```

The `PkPassReader` class has the following methods:

- `containingFiles()`: Returns an array of files contained in the pass.
- `containsFile(string $filename)`: Returns a boolean indicating whether the pass contains a file with the given filename.
- `manifestProperties()`: Returns an array of properties from the pass manifest.
- `manifestProperty(string $key)`: Returns the value of the given property from the pass manifest.
- `passProperties()`: Returns an array of properties from the pass.json file.
- `passProperty(string $key)`: Returns the value of the given property from the pass.json file.
- `toArray()`: Returns an array representation of the pass.
