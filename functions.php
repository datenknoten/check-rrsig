<?php
/* ----------------------------------------------------------------------------
 * "THE VODKA-WARE LICENSE" (Revision 42):
 * <tim@datenkonten.me> wrote this file.  As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a vodka in return.     Tim Schumacher
 * ----------------------------------------------------------------------------
 */

function getValueFromQuery($resolver, $host, $rr_type, $field, $all = true) {
    $retval = [];
    $results = $resolver->query($host,$rr_type);
    foreach($results->answer as $rr) {
        if ($rr->type == $rr_type) {
            $retval[] = $rr->$field;
            if (!$all)
                break;
        }
    }
    if ((!$all) && (count($retval) == 1)) {
        $retval = $retval[0];
    }
    return $retval;
}