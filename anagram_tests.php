<?php

require_once __DIR__ . '/anagram_functions.php';

class Anagram_Tests extends PHPUnit_Framework_TestCase
{
    public $dictionary = array(
        'test',
        'meta',
        'foo',
        'meat',
        'teammate',
        'matte',
        'the meat',
        'nachos',
        'a',
        'aa',
        'b'
    );

    public function testcount_letters()
    {
        $inputs = array(
            'mate' => array('m'=>1,'a'=>1,'t'=>1,'e'=>1),
            'zoo' => array('z'=>1,'o'=>2),
            'pow wow' => array('p'=>1,'o'=>2,'w'=>3)
        );

        foreach ( $inputs as $input => $expected ) {
            $actual = count_letters($input);
            $this->assertEquals($expected, $actual);
        }
    }

    public function dataProviderTestRemove_Whitespace()
    {
        return array(
            array('test', 'test'),
            array('test ', 'test'),
            array('t e s t   ', 'test'),
            array(' test', 'test'),
            array(' test ', 'test'),
            array('		test		', 'test'),
            array('test
                ', 'test'), // intentional goofy whitespace... ew.
            array('the quick brown fox jumped over the lazy dog', 'thequickbrownfoxjumpedoverthelazydog')
        );
    }

    /**
     * @dataProvider dataProviderTestRemove_Whitespace
     */
    public function testremove_whitespace($input, $expected)
    {
        $actual = remove_whitespace($input);
        $this->assertEquals($expected, $actual);
    }

    public function testget_potential_words()
    {
        $input = 'team';
        $actual = get_potential_words($this->dictionary, $input);
        $expected = array(
            1 => array(
                'a' => array('a'=>1)
            ),
            4 => array(
                'meta' => array('m'=>1,'e'=>1,'t'=>1,'a'=>1),
                'meat' => array('m'=>1,'e'=>1,'a'=>1,'t'=>1),
            )
        );

        $this->assertEquals($expected, $actual);
    }

    public function testget_potential_words_count()
    {
        $input = 'teammate';
        $actual = get_potential_words($this->dictionary, $input);
        $expected = array(
            1 => array(
                'a' => array(
                    'a' => 1,
                ),
            ),
            2 => array(
                'aa' => array(
                    'a' => 2,
                ),
            ),
            4 => array(
                'meta' => array(
                    'm' => 1,
                    'e' => 1,
                    't' => 1,
                    'a' => 1,
                ),
                'meat' => array(
                    'm' => 1,
                    'e' => 1,
                    'a' => 1,
                    't' => 1,
                ),
            ),
            5 => array(
                'matte' => array(
                    'm' => 1,
                    'a' => 1,
                    't' => 2,
                    'e' => 1,
                ),
            ),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testget_potential_words_cant_count_same_word()
    {
        $input = 'test';
        $actual = get_potential_words($this->dictionary, $input);
        $expected = array();

        $this->assertEquals($expected, $actual);
    }

    public function check_if_good_wordTrue_dataprovider()
    {
        return array(
            array('teammate', 'meta'),
            array('teammate', 'team'),
            array('teammate', 'meat'),
            array('teammate', 'ate'),
            array('teammate', 'eat'),
            array('teammate', 'at'),
            array('teammate', 'me'),
            array('teammate', 'em'),
        );
    }

    /**
     * @dataProvider check_if_good_wordTrue_dataprovider
     */
    public function testcheck_if_good_wordTrue($input, $dictionary)
    {
        $this->assertTrue(check_if_good_word($dictionary, $input));
    }

    public function testcheck_if_good_wordTooLong()
    {
        $this->assertFalse(check_if_good_word("this is not a good fit", "foo"));
    }

    public function testcheck_if_good_wordJustRightSpaces()
    {
        $this->assertTrue(check_if_good_word("foo bar", "fboaor"));
    }

    public function testget_word_count_diff()
    {
        $input = 'teammate';
        $input_word_count = count_letters($input);
        $potential_word_count = count_letters('meta');

        $expected = array(
            't'=>1,
            'e'=>1,
            'a'=>1,
            'm'=>1
        );
        
        $actual = get_word_count_diff($potential_word_count, $input_word_count);
        
        $this->assertEquals($expected, $actual);
    }
}