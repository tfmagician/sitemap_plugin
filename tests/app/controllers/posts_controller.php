<?php
class PostsController extends Controller
{

    var $paginate = array(
        'Post' => array(
            'limit' => 2,
        ),
    );

    function search($limit = 2)
    {
        $this->paginate['Post']['limit'] = $limit;
        $this->paginate();
    }

}
