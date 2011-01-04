<?php
class PostFixture extends CakeTestFixture
{
    var $name = 'Post';

    var $fields = array(
        'id'          => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
        'title'       => array('type' => 'text',    'null' => false, 'default' => NULL),
        'body'        => array('type' => 'text',    'null' => false, 'default' => NULL),
        'created'     => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'modified'    => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'indexes' => array(
            'PRIMARY'     => array('column' => 'id',          'unique' => 1),
        ),
        'tableParameters' => array(
            'charset' => 'utf8',
            'collate' => 'utf8_unicode_ci',
            'engine'  => 'InnoDB',
        ),
    );

    var $records = array(
        array(
            'id' => 1,
            'title' => 'title1',
            'body' => 'body1',
            'created' => '2011-01-01 10:00:00',
            'modified' => '2011-01-01 11:00:00',
        ),
        array(
            'id' => 2,
            'title' => 'title2',
            'body' => 'body2',
            'created' => '2011-01-02 10:00:00',
            'modified' => '2011-01-02 11:00:00',
        ),
        array(
            'id' => 3,
            'title' => 'title3',
            'body' => 'body3',
            'created' => '2011-01-03 10:00:00',
            'modified' => '2011-01-03 11:00:00',
        ),
    );

}
