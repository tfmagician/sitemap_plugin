<?php
class PostsController extends Controller
{

    var $paginate = array(
        'Post' => array(
            'limit' => 2,
        ),
    );

}
