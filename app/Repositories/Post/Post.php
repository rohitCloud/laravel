<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Post;

use App\Adapters\Post as PostAdapter;
use App\Contracts\Repositories\Post as PostContract;
use App\Models\Post as PostModel;
use App\Repositories\Repository;

/**
 * @author Rohit Arora
 */
class Post extends Repository implements PostContract
{
    const OFFSET    = 0;
    const LIMIT     = 10;
    const SORT_BY   = PostModel::ID;
    const SORT_TYPE = Repository::SORT_ASC;

    /**
     * @param PostModel   $Model
     *
     * @param PostAdapter $Adapter
     *
     * @internal param PostModel $Post
     */
    public function __construct(PostModel $Model, PostAdapter $Adapter)
    {
        parent::__construct($Model, $Adapter);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function get($parameters)
    {
        return $this->process($parameters);
    }
}