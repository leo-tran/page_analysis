<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Page Analyzer
        </title>
        <link rel="stylesheet" href="/css/jquery.mobile-1.1.1.min.css" />
        <link type="text/css" rel="stylesheet" href="/css/my.css">
        <script src="/script/plugin/jquery-1.7.2.min.js">
        </script>
        <script src="/script/jquery.mobile-1.1.1.min.js">
        </script>
        <script src="/script/my.js" type="text/javascript"></script>
    </script>
</head>
<body>
    <!-- Home -->
    <div data-role="page" id="page1">
        <div data-theme="a" data-role="header">
            <h3>
                Page Analyzer
            </h3>
        </div>
        <div data-role="content" style="text-align: center;">
            <form action="" method="POST" data-ajax="false">
                <div data-role="fieldcontain" data-theme="b">
                    <fieldset data-role="controlgroup">
                        <input name="url" id="url" placeholder="http://qut.edu.au" type="search"
                               value="<?php echo ( isset($_POST['url']) ? $_POST['url'] : ''); ?>" />

                    </fieldset>
                </div>
            </form>

            <div data-role="navbar">
                <ul>
                    <li><h2>Results</h2></li>
                </ul>
            </div>

            <?php

            function printTag($tags) {
                foreach ($tags as $t) {
                    echo $t['token'] . "/" . $t['tag'] . " ";
                }
                echo "\n";
            }

            function innerHTML($el) {
                $doc = new DOMDocument();
                $doc->appendChild($doc->importNode($el, TRUE));
                $html = trim($doc->saveHTML());
                $tag = $el->nodeName;
                return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
            }

            if (isset($_POST['url']) && preg_match('/http/', $_POST['url'])) {
                try {
                    // create curl resource
                    $ch = curl_init($_POST['url']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    
                    // Parsing as HTML
                    $doc = new DOMDocument($result);
                    @$doc->loadHTML($result);
                    $site = $doc->documentElement;
                    
                    // Can also get title, description here but not nessecarily
                    $body = $doc->getElementsByTagName('body')->item(0);
                    
                    // Get only the content
                    $wiki = innerHTML($body);
                    
                } catch (Exception $e) {
                    echo $e->getMessage();
                    die;
                }

                // remove all HTML tags
                $wiki = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $wiki);
                $wiki = strip_tags($wiki);
                $wiki = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $wiki);

                // POS tags
                require 'POS.php';
                $tagger = new PosTagger('lexicon.txt');

                // Counting set
                $noun = 0;
                $adjective = 0;
                $verb = 0;

                // Get each word to tags and wordnet
                $words = preg_split('/ /', $wiki);
                
                // ensure that the word just be proceesed once
                $words = array_unique($words);

                foreach ($words as $word) {
                    // if word is anphabetical
                    if (ctype_alpha($word)) {
                        $tag = end($tagger->tag($word));
                        if ($tag['tag'] == 'NN') {
                            echo '<div data-role="collapsible" data-theme="e"
                                    data-expanded-icon="arrow-d" data-inset="true">';
                            $noun++;
                            echo '<h2>' . $tag['token'] . '/' . $tag['tag'] . '</h2>';
                            echo '<ul data-role="listview" data-theme="b"><li>';
                            
                            // comunicate with eWORDNET and get the result
                            $ret = shell_exec('cd C:\Program Files\WordNet\2.1\bin && wn ' . $tag['token'] . ' -synsn');
                            echo $ret;
                            echo '</li></ul></div>';
                        } else if ($tag['tag'] == 'RB') {
                            echo '<div data-role="collapsible" data-theme="a" 
                                    data-expanded-icon="arrow-d" data-inset="true">';
                            $adjective++;
                            echo '<h2>' . $tag['token'] . '/' . $tag['tag'] . '</h2>';
                            echo '<ul data-role="listview" data-theme="e"><li>';
                            $ret = shell_exec('cd C:\Program Files\WordNet\2.1\bin && wn ' . $tag['token'] . ' -synsn');
                            echo $ret;
                            echo '</li></ul></div>';
                        } else if ($tag['tag'] == 'VB') {
                            echo '<div data-role="collapsible" data-theme="c" 
                                    data-expanded-icon="arrow-d" data-inset="true">';
                            $verb++;
                            echo '<h2>' . $tag['token'] . '/' . $tag['tag'] . '<h2/>';
                            echo '<ul data-role="listview" data-theme="a"><li>';
                            $ret = shell_exec('cd C:\Program Files\WordNet\2.1\bin && wn ' . $tag['token'] . ' -synsn');
                            echo $ret;
                            echo '</li></ul></div>';
                        }
                    }
                }
                echo '<div data-role="footer">';

                echo "<h4>Site Conclusion: $noun Nouns, $verb Verbs and $adjective Adjectives are being used</h4>";
                echo '</div>';
            } else {
                echo '<p>Input URL wanted to analyze</p>';
            }
            ?>

        </div>
    </div>
</body>
</html>
