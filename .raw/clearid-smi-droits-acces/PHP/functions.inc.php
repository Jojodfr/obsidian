<?php
function var_dump_ret($mixed = null) {
  ob_start();
  var_dump($mixed);
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

function array_recursive_search_key_map($needle, $haystack) {
    foreach($haystack as $first_level_key=>$value) {
        if ($needle === $value) {
            return array($first_level_key);
        } elseif (is_array($value)) {
            $callback = array_recursive_search_key_map($needle, $value);
            if ($callback) {
                return array_merge(array($first_level_key), $callback);
            }
        }
    }
    return false;
}

function array_get_nested_value($keymap, $keyvalue)
{
    $nest_depth = sizeof($keymap);
    $value = false;
    for ($i = 0; $i < $nest_depth; $i++) {
		if ($keymap[$i] == $keyvalue) continue;
		else {
			$value = $keymap[$i];
			break;
		}
    }

    return $value;
}

function echo_flush($msg = "") {
	echo $msg;
	@ob_flush(); //force output to browser
	flush();
}