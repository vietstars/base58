# Base58

## Install

``` bash
$ composer require vietstars/base58
```

## Usage

``` php
$base58 = new Vietstars\Base58;

$encoded = $base58->encode(random_bytes(256));
$decoded = $base58->decode($encoded);
```

If you are encoding to and from integer use the implicit decodeInteger() and encodeInteger() methods.

``` php
$integer = $base58->encodeInteger(987654321); 
print $base58->decodeInteger("1TFvCj", true); 
```

Also note that encoding a string and an integer will yield different results.

``` php
$string = $base58->encode("987654321"); 
$integer = $base58->encodeInteger(987654321); 
```
