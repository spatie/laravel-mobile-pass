---
title: Reading stored passes
weight: 4
---

The package ships a `PkPassReader` class that reads Apple passes, either from a file or from a string.

Here's how you instantiate it:

```php
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

// from file
$reader = PkPassReader::fromFile('path/to/pass.pkpass');

// from string
$reader = PkPassReader::fromString($passData);
```

Once you have a reader, you can call:

- `containingFiles()`: returns an array of files contained in the pass.
- `containsFile(string $filename)`: returns a boolean indicating whether the pass contains a file with the given filename.
- `manifestProperties()`: returns an array of properties from the pass manifest.
- `manifestProperty(string $key)`: returns the value of the given property from the pass manifest.
- `passProperties()`: returns an array of properties from the pass.json file.
- `passProperty(string $key)`: returns the value of the given property from the pass.json file.
- `toArray()`: returns an array representation of the pass.

The reader is also handy in your test suite. When you're asserting against a generated pass, pull it through `PkPassReader` and poke at the fields and manifest directly instead of eyeballing the raw bytes.
