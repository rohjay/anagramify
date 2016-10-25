<?php

$debug = empty($_GET['debug']) ? false : true;
$verbose = empty($_GET['verbose']) ? false : true;
$dict = empty($_GET['dict']) ? True : False;

$gt = !empty($_GET['gt']) ? $_GET['gt'] : false;

if ( $debug || $verbose ) echo '<plaintext>';

$start = microtime(true);
$start_mem = $last_mem = memory_get_usage();
if ( $debug ) echo "Starting at $start using $start_mem bytes of data.\n";


require_once __DIR__ . '/anagram_functions.php';

if ( $dict ) {
    $dictionary = array_filter(array_map('trim', explode("\n", file_get_contents(__DIR__.'/dictionary.txt'))));
} else {
    $dictionary = array_filter(array_map('trim', explode("\n", file_get_contents(__DIR__.'/dictionary2.txt'))));
}

$dictionary = array_unique(array_map('strtolower', $dictionary));

if ( $verbose ) {
    $count = 0;
    $shortest = 99;
    $longest = 0;
    $longest_word = $shortest_word = false;
    foreach ( $dictionary as $word ) {
        $length = strlen($word);
        if ( $length > $longest ) {
            $longest = $length;
            $longest_word = $word;
        }
        if ( $length < $shortest ) {
            $shortest = $length;
            $shortest_word = $word;
        }
        ++$count;
    }

    echo "\nDictionary Word Stats\n=====================\n";
    echo "Shortest ($shortest):\t$shortest_word\n";
    echo "Longest ($longest):\t$longest_word\n";
    echo "Total words:\t$count\n\n";
}

if ( $debug ) logger("Dictionary created and analyzed...", $start, $last_mem);

/*
    Step 1:
        build a function to split a word apart and count all the letters
*/
$input = urldecode($_GET['input']);
$input_letter_count = count_letters($input);
if ( $debug ) logger("Parsed input...", $start, $last_mem);

/*
    Step 2:
        loop through the dictionary, and see if any words will fit.
*/
$potential_words = get_potential_words($dictionary, $input);
unset($dictionary); // We don't need all that shhhtuff in memory any more =D
if ( $gt ) {
    foreach ( $potential_words as $k => $v ) {
        if ( $k <= $gt ) {
            unset($potential_words[$k]);
        }
    }
}
if ( $verbose ) echo "Potential words for '$input': " . print_r($potential_words,1) . "\n\n";
if ( $debug ) logger("Filtered dictionary down to potential words", $start, $last_mem);

/*
    Step 3:
        loop through all of our potential words with context:
            Since our potential words are ordered by length, we know
                which arrays we can look through to add up to the
                right number of characters. Words that are too long,
                we don't have to consider. Words that are too short,
                we can see if there is an array of good length'd words
                waiting.
            We can add strings of words into the "potential words array"
                seeking to put multiple words together.
        building strings until possibilities match the letter counts exactly.
*/
$anagrams = build_anagrams_from_potentials($potential_words, $input);
if ( $debug ) logger("Discovered anagrams...", $start, $last_mem);

if ( $debug ) {
    echo "Anagrams for '$input': " . print_r($anagrams,1) . "\n\n";
} else {
    echo "<h1>$input</h1>\n";

    if ( !empty($anagrams) ) {
        echo "<ol>\n";
        foreach ( $anagrams as $anagram ) {
            printf("<li style='padding-left:1em'>%s</li>\n", $anagram);
        }
        echo "</ol>";
    } else {
        echo "<p>This is gonna need a bigger dict.</p>";
        echo "<img src='http://media3.giphy.com/media/iEm2b0AI1LPgY/giphy.gif'>";
    }
}

