<?php

function logger($description, $start_time, &$last_mem)
{
    $time_since = microtime(true) - $start_time;
    $current_mem = memory_get_usage();
    $mem_diff = $current_mem - $last_mem;
    $last_mem = $current_mem;
    echo "Description: $description\nDuration: {$time_since}s\t//\tCurrent Memory: $current_mem  \t//\tMem diff: $mem_diff\n\n";
}

function count_letters($input)
{
    if ( !is_string($input) ) {
        throw new Exception("Input is not a string! GO DRUNK RYAN! YOU'RE HOME!");
    }
    $input = str_replace(array(" ", "\t", "\n"), '', $input);
    $letters = str_split($input);
    $return = array();
    foreach ($letters as $letter) {
        if ( empty($return[$letter]) ) {
            $return[$letter] = 0;
        }
        $return[$letter]++;
    }
    return $return;
}

function get_potential_words(&$dictionary, &$input)
{
    $potential_words = array();
    foreach ( $dictionary as $word ) {
        if ( check_if_good_word($word, $input) ) {
            $letter_count = strlen($word);
            $potential_words[$letter_count][$word] = count_letters($word);
        }
    }
    ksort($potential_words);
    return $potential_words;
}

function check_if_good_word($word, $input)
{
    // If it's the same word, or the word to check against is longer than the input word, no bueno
    $no_space_word = str_replace(' ', '', $word);
    if ( $input == $word || strlen($no_space_word) > strlen($input) ) {
        return false;
    }
    $good_word = true;
    $input_word_count = count_letters($input);
    $letter_counts = count_letters($no_space_word);
    foreach ($letter_counts as $letter => $count) {
        if ( empty($input_word_count[$letter]) || $count > $input_word_count[$letter] ) {
            $good_word = false;
        }
    }
    return $good_word;
}

function build_anagrams_from_potentials($potential_words, $input_word)
{
    if ( empty($potential_words) ) {
        return;
    }
    $combinations = array();
    $input_length = strlen($input_word);

    // This is easy: They're the same length, and can't have any more of any
    // character than what's in them. Hence, if they're the same length, it's
    // definitely an anagram. Tada! =D
    if ( !empty($potential_words[$input_length]) ) {
        $anagrams = array_keys($potential_words[$input_length]);
        unset($potential_words[$input_length]);
    } else {
        $anagrams = array();
    }

    ksort($potential_words); // Sort them by word length
    $reverse_potential_words = $potential_words;
    krsort($reverse_potential_words);

    // Loop through the biggest words to the smallest, if the length of the
    // biggest words + smallest words is > input length, we can quickly move on.
    foreach ( $reverse_potential_words as $big_length => $big_array ) {
        foreach ( $potential_words as $small_length => $small_array ) {
            $combination_length = $big_length + $small_length;
            if ( $combination_length > $input_length ) {
                // No chance in matching these to other words...
                // let's not loop through them again...
                unset($potential_words[$big_length]);
                unset($reverse_potential_words[$big_length]); // for memory redux =]
                break;
            } else {
                // Loop through to find anagrams and/or combinations.
                $big_words = array_keys($big_array);
                $small_words = array_keys($small_array);
                foreach ( $big_words as $big_word ) {
                    foreach ( $small_words as $small_word ) {
                        $combination_word = $big_word . ' ' . $small_word;
                        if ( check_if_good_word($combination_word, $input_word) ) {
                            $combinations[$combination_length][$combination_word] = count_letters($combination_word);
                        }
                    }
                }
            }
        }
    }
    // Now if we have combinations... recurse. oh fuck.
    if ( count($combinations) > 0 ) {
        $combination_anagrams = build_anagrams_from_potentials($combinations, $input_word);
        if ( !empty($combination_anagrams) ) {
            $anagrams = array_merge( $anagrams, $combination_anagrams );
        }
    }
    return $anagrams;
}

function get_word_count_diff(&$potential_word_count, &$input_word_count)
{
    $what_input_needs_still = array();

    foreach ( $input_word_count as $letter => $count ) {
        if ( !isset($potential_word_count[$letter]) ) {
            $what_input_needs_still[$letter] = $count;
        } elseif ( $potential_word_count[$letter] != $count ) {
            $what_input_needs_still[$letter] = $count - $potential_word_count[$letter];
        }
    }

    return $what_input_needs_still;
}
