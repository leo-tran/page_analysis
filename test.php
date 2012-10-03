<?php
// Get a file into an array.  In this example we'll go through HTTP to get
// the HTML source of a URL.
        echo '<table><tbody>';
        $lines = file('Fish.txt');
        
        foreach ($lines as $line) {

            $words = preg_split('/\/NN|\/RB/', $line);
            foreach ($words as $word) {
                $ret = end(preg_split('/ /', $word));
                if (ctype_alpha($ret))
                    echo '<tr><td>' . $ret . '</td><td>-->>WordNet:</td><td>'
                    . shell_exec('cd C:\Program Files\WordNet\2.1\bin && wn '.$ret.' -synsn')
                    . '</td></tr>';
            }
        }
        echo '</tbody></table>';

        echo '<hr />';
?>