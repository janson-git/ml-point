PHP wrap tools for php-ml library.

```php
php -d auto_prepend_file=$PWD/prepend.php -a
Interactive mode enabled

// now we have test $data object DataFrameCsv typed
php > $data->shape();
690
15
php > $data->head();
+---+-------+-------+---+---+---+---+------+---+---+----+----+----+-------+-----+---+
| 0 | 1     | 2     | 3 | 4 | 5 | 6 | 7    | 8 | 9 | 10 | 11 | 12 | 13    | 14  |   |
+---+-------+-------+---+---+---+---+------+---+---+----+----+----+-------+-----+---+
| b | 30.83 | 0     | u | g | w | v | 1.25 | t | t | 01 | f  | g  | 00202 | 0   | + |
| a | 58.67 | 4.46  | u | g | q | h | 3.04 | t | t | 06 | f  | g  | 00043 | 560 | + |
| a | 24.50 | 0.5   | u | g | q | h | 1.5  | t | f | 0  | f  | g  | 00280 | 824 | + |
| b | 27.83 | 1.54  | u | g | w | v | 3.75 | t | t | 05 | t  | g  | 00100 | 3   | + |
| b | 20.17 | 5.625 | u | g | w | v | 1.71 | t | f | 0  | f  | s  | 00120 | 0   | + |
+---+-------+-------+---+---+---+---+------+---+---+----+----+----+-------+-----+---+
php > 

```

only `shape()`, `head()` and `tail()` methods vailable now