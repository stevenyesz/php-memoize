--TEST--
memoize - methods
--SKIPIF--
<?php include('skipif.inc'); ?>
--INI--
apc.enabled=1
apc.enable_cli=1
apc.file_update_protection=0
--FILE--
<?php
error_reporting(E_ALL|E_STRICT);
function test_func($f) {
  $args = func_get_args();
  $f = array_shift($args);
  $t = microtime(true);
  $ret = call_user_func_array($f, $args);
  printf("%s() returned %s in %.5fs\n", $f, var_export($ret, true), microtime(true) - $t);;
}

/* user class */

class Foo {
  static function expensive_method($s, $t = "foo") {
    echo "expensive_method() called\n";
    return $s . $t;
  }

  function dynamic_method() {
    echo "dynamic_method() called\n";
  }
}

$cb = array('Foo', 'expensive_method');
memoize($cb);

test_func($cb, 'hai');
test_func($cb, 'hai');
test_func($cb, 'hai', 'again');
test_func($cb, 'hai', 'again');

$foo = new Foo;
$cb = array($foo, 'dynamic_method');
memoize($cb);

test_func($cb);
test_func($cb);

/* internal class */

$cb = array('DateTime', 'createFromFormat');
memoize($cb);

date_default_timezone_set('Europe/London');
test_func($cb, 'j-M-Y', '15-Feb-2009');
test_func($cb, 'j-M-Y', '15-Feb-2009');
test_func($cb, 'j-M-Y', '01-Apr-1945');
test_func($cb, 'j-M-Y', '01-Apr-1945');
?>
===DONE===
<?php exit(0); ?>
--EXPECTF--
expensive_method() called
Array() returned 'haifoo' in %fs
Array() returned 'haifoo' in %fs
expensive_method() called
Array() returned 'haiagain' in %fs
Array() returned 'haiagain' in %fs
dynamic_method() called
Array() returned NULL in %fs
Array() returned NULL in %fs
Array() returned DateTime::__set_state(array(
   'date' => '2009-02-15 %s',
   'timezone_type' => 3,
   'timezone' => 'Europe/London',
)) in %fs
Array() returned DateTime::__set_state(array(
   'date' => '2009-02-15 %s',
   'timezone_type' => 3,
   'timezone' => 'Europe/London',
)) in %fs
Array() returned DateTime::__set_state(array(
   'date' => '1945-04-01 %s',
   'timezone_type' => 3,
   'timezone' => 'Europe/London',
)) in %fs
Array() returned DateTime::__set_state(array(
   'date' => '1945-04-01 %s',
   'timezone_type' => 3,
   'timezone' => 'Europe/London',
)) in %fs
===DONE===
