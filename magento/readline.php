<?php
dahbug::setData('output', 'print');
dahbug::setData('ascii_notation', 'caret');
readline_callback_handler_install('', function() { });
$r = array(STDIN);
$w = NULL;
$e = NULL;

$abort = false;

while (!$abort) {
    $n = stream_select($r, $w, $e, null);
    stream_set_blocking(STDIN, 0);

    $c = stream_get_contents(STDIN, -1);
    if (strlen($c) > 1) {
        switch ($c) {
            case "\033[A":
                echo "\nArrow up\n";
                break;
            case "\033[B":
                echo "\nArrow down\n";
                break;
            case "\033[C":
                echo "\nArrow right\n";
                break;
            case "\033[D":
                echo "\nArrow left\n";
                break;
        }
    } else {
        $ord = ord($c);
        if ($ord < 32) {
            switch ($ord) {
                case 8:
                    echo "\nBackspace\n";
                    break;
                case 9:
                    echo "\nTab\n";
                    break;
                case 10:
                    echo "\n";
                    break;
                case 27:
                    echo "\nEscape pressed, exiting.\n";
                    $abort = true;
                    break;
                default:
                    echo $ord . "\n";
            }
        } elseif($ord == 127) {
            echo "\nBackspace\n";
        } else {
            echo $c;
        }
    }
}
